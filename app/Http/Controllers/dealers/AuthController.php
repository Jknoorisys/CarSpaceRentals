<?php

namespace App\Http\Controllers\dealers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Dealers;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Libraries\Services;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgetPassword;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function register(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language'          =>   'required',
            'name'   => 'required|regex:/^[\pL\s]+$/u|min:3',
            'password'   => 'required|max:20||min:8',
            'email' => 'required|unique:dealers',
            'mobile' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.user.validation.fail'),
                'errors'    => $validator->errors()
            ], 400);
        }
        try {
            $result = DB::table('dealers')
                ->where('email', $req->input('email'))
                ->get();

            if (!empty($result)) {
                $otp = rand(1000, 9999);
                $data = $req->input();
                $dealer = [
                    'id' => Str::uuid('36'), 'name' => $data['name'], 'password' => Hash::make($data['password']),
                    'email' => $data['email'], 'mobile' => $data['mobile'], 'email_otp' => $otp, 'created_at' => Carbon::now()
                ];
                $saveDealer = DB::table('dealers')->insert($dealer);

                $email = ['to' => $data['email']];
                $mail_details = [
                    'subject' => 'Testing Application OTP',
                    'body' => 'Your OTP is : ' . $otp
                ];
                $data = array(
                    'name' => $data['name'],
                    'otp' => $otp
                );
                Mail::send('Dealer_Mail.mail', $data, function ($message) use ($email) {
                    $message->to($email['to'])->subject('Email Verification');
                });
                // $user = $register->save();
                if ($saveDealer) {
                    return response()->json(
                        [
                            'status'    => 'success',
                            'data' => $dealer,
                            'message'   => __('msg.user.register.success'),
                        ],
                        200
                    );
                } else {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   => __('msg.user.register.fail'),
                        ],
                        400
                    );
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => __('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOTP(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'email_otp'   => 'required',
            'id' => 'required||string'

        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   => __('msg.user.validation.fail'),
                ],
                400
            );
        }

        try {
            $otp = $req->email_otp;
            $id = $req->id;
            #Validation Logic
            $verificationCode   =  DB::table('dealers')->where('email_otp', $otp)->where('id', $id)->update(['is_verified' => 'yes']);
            if ($verificationCode == true) {
                return response()->json(
                    [
                        'status'    => 'success',
                        'message'   =>  __('msg.user.otp.otpver'),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>   __('msg.user.otp.otpnotver'),
                    ],
                    400
                );
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    public function login(Request $req)
    {
        $data = $req->only('language', 'email', 'password');
        $validator = Validator::make($data, [
            'language' => 'required',
            'email' => 'required',
            'password'   => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  __('msg.user.validation.fail'),
                ],
                400
            );
        } else {
            try {
                $service = new Services();
                $email = $req->email;
                $password = $req->password;
                $dealer  = Dealers::where('email', $email)
                    ->take(1)->first();

                if ($dealer) {
                    // return $dealer->password;exit;
                    // if ($dealer->is_email_verified == 'verified') {
                    if (Hash::check($password,$dealer->password)) {


                        if ($dealer->status == 'active') {
                            $claims = array(
                                'exp'   => Carbon::now()->addDays(1)->timestamp,
                                'uuid'  => $dealer->id
                            );
                            // return $claims;exit;
                            $dealer->token = $service->getSignedAccessTokenForUser($dealer, $claims);
                            $currentDate = Carbon::now()->format('Y-m-d');
                            $currentTime = Carbon::now()->format('H:i:m');
                            // return ($currentTime);exit;
                            $dealer_id  = DB::table('dealers')->where('email', $email)->where('password', $dealer->password)->take(1)->first();
                            // return $dealer_id;exit;

                            $dealerLog = ['id' => Str::uuid('36'), 'user_id' => $dealer_id->id,  'login_date' => $currentDate, 'login_time' => $currentTime, 'user_type' => 'dealer'];
                            $logintime =  DB::table('login_activities')->insert($dealerLog);

                            return response()->json(
                                [
                                    'status'    => 'success',
                                    'data' => $dealer,
                                    'login_activity_id' => $dealerLog['id'],
                                    'message'   =>   __('msg.user.validation.login'),
                                ],
                                200
                            );
                        } else {
                            return response()->json(
                                [
                                    'status'    => 'failed',
                                    'message'   =>  __('msg.user.validation.inactive'),
                                ],
                                400
                            );
                        }
                    }else {
                        return response()->json(
                            [
                                'status'    => 'failed',
                                'message'   =>  __('msg.user.validation.incpass'),
                            ],
                            400
                        );
                    }
                } else {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  __('msg.user.validation.incmail'),
                        ],
                        400
                    );
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'status'  => 'failed',
                    'message' =>  __('msg.user.error'),
                    'error'   => $e->getMessage()
                ], 500);
            }
        }
    }
}
