<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
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
            'email' => 'required|unique:users',
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
            $result = DB::table('users')
                ->where('email', $req->input('email'))
                ->get();

            if (!empty($result)) {
                $otp = rand(1000, 9999);
                $data = $req->input();
                $user = [
                    'id' => Str::uuid(), 'name' => $data['name'], 'password' => Hash::make($data['password']),
                    'email' => $data['email'], 'mobile' => $data['mobile'], 'email_otp' => $otp, 'created_at' => Carbon::now()
                ];
                $saveUser = DB::table('users')->insert($user);

                $email = ['to' => $data['email']];
                $mail_details = [
                    'subject' => 'Testing Application OTP',
                    'body' => 'Your OTP is : ' . $otp
                ];
                $data = array(
                    'name' => $data['name'],
                    'otp' => $otp
                );
                Mail::send('User_Mail.mail', $data, function ($message) use ($email) {
                    $message->to($email['to'])->subject('Email Verification');
                });
                // $user = $register->save();
                if ($saveUser) {
                    return response()->json(
                        [
                            'status'    => 'success',
                            'data' => $user,
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
            $verificationCode   =  DB::table('users')->where('email_otp', $otp)->where('id', $id)->update(['is_verified' => 'yes']);
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
    public function resendregOTP(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'email'   => 'required',


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
        }
        try {
            $email = $req->email;
            $user = User::where('email', $email)->take(1)->first();
            // return $provider;exit;
            if (!empty($user)) {
                if ($user->is_verified == 'no') {
                    // echo 'Hiiiii';exit();
                    $email_otp = rand(1000, 9999);
                    $resend =  User::where('email', '=', $email)->update(['email_otp' => $email_otp, 'is_verified' => 'yes', 'updated_at' => date('Y-m-d H:i:s')]);
                    if ($resend == true) {
                        $user = User::where('email', '=', $email)->first();
                        $email = ['to' => $req->email];
                        $mail_details = [
                            'subject' => 'Testing Application OTP',
                            'body' => 'Your OTP is : ' . $email_otp
                        ];
                        $data = array(
                            'name' => $user->name,
                            'otp' => $email_otp
                        );
                        Mail::send('User_Mail.resenOTPmail', $data, function ($message) use ($email) {
                            $message->to($email['to'])->subject('Resend Email Verification');
                        });
                        return response()->json([
                            'status'    => 'success',
                            'message'   =>  __('msg.user.otp.resendotp'),
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   =>   __('msg.user.otp.alreadyverify'),
                    ], 400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   =>  __('msg.user.otp.registerfirst'),
                ], 400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => __('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function login(Request $req)
    {
        
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'email' => 'required',
            'password'   => 'required',
            'device_id' => 'required',
            'ip_address' => 'required',

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
                $user  = user::where('email', $email)
                    ->take(1)->first();

                if ($user) {
                    // return $user->password;exit;
                    // if ($user->is_email_verified == 'verified') {
                    if (Hash::check($password,$user->password)) {


                        if ($user->status == 'active') {
                            $claims = array(
                                'exp'   => Carbon::now()->addDays(1)->timestamp,
                                'uuid'  => $user->id
                            );
                            // return $claims;exit;
                            $user->token = $service->getSignedAccessTokenForUser($user, $claims);
                            $currentDate = Carbon::now()->format('Y-m-d');
                            $currentTime = Carbon::now()->format('H:i:s');
                            // return ($currentTime);exit;
                            $user_id  = DB::table('users')->where('email', $email)->where('password', $user->password)->take(1)->first();
                            // return $user_id->id;exit;

                            
                            $userLog = ['id' => Str::uuid('36'), 'user_id' => $user_id->id,  'login_date' => $currentDate, 
                            'device_id' => $req->device_id,'ip_address' => $req->ip_address,'login_time' => $currentTime, 
                            'user_type' => 'user','device_id' => $req->device_id,'ip_address' => $req->ip_address,'created_at' => Carbon::now()];
                            $logintime =  DB::table('login_activities')->insert($userLog);
                            $user_id->user_login_activity_id=$userLog['id'];
                            $user_id->JWT_token = $user->token;
                            return response()->json(
                                [
                                    'status'    => 'success',
                                    'data' => $user_id,
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
    public function forgetpassword(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'email'   => 'required',

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
        }
        try {

            $email = $req->email;
            $user = User::where('email', $email)->first();
            if (!empty($user)) {
                $token = Str::random(60);
                $user['token'] = $token;
                $user['is_verified'] = 'yes';
                $userPass = $user->save();
                $mailsent = Mail::to($req->email)->send(new ForgetPassword($user->name, $token));
                if ($mailsent == true) {
                    return response()->json(
                        [
                            'status'    => 'success',
                            'data' => $user,
                            'message'   =>  __('msg.user.forgetpass.emailsent'),
                        ],
                        200
                    );
                } else {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  __('msg.user.forgetpass.emailnotsent'),
                        ],
                        400
                    );
                }
            } else {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>  __('msg.user.forgetpass.notreg'),
                    ],
                    400
                );
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
            ], 500);
        }
    }
    public function forgotPasswordValidate(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language'  =>   'required',
            'token' => 'required',
            'password'   => 'required|max:20||min:8',
            'confirm_password' => 'required|same:password',

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
        }
        try {
            $user = User::where('token', $req->token)->first();
            if ($user) {

                $password = $req->password;
                if ($password == $req->confirm_password) {
                    $user->password = Hash::make($req->password);
                    $user->token = '';
                    $info = $user->save();
                    if ($info) {
                        return response()->json(
                            [
                                'status'    => 'success',
                                'message'   =>  __('msg.user.forgetpass.reset'),
                            ],
                            200
                        );
                    } else {
                        return response()->json(
                            [
                                'status'    => 'failed',
                                'message'   =>  __('msg.user.forgetpass.notreset'),
                            ],
                            400
                        );
                    }
                } else {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  __('msg.user.forgetpass.passnotmatch'),
                        ],
                        400
                    );
                }
            } else {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>  __('msg.user.forgetpass.tokennotmatch'),
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
    public function logout(Request $req)
    {


        $validator = Validator::make($req->all(), [
            'language'  =>   'required',
            'login_activity_id' => 'required',

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
        }
        try {
            $login_time = DB::table('login_activities')->where('id',$req->login_activity_id)->first();
            $currentloginTime = $login_time->login_time;
            $currentlogoutTime = Carbon::now()->format('H:i:s');
            $loginTime = Carbon::parse($currentlogoutTime);
            $logoutTime = Carbon::parse($currentloginTime);

            // Calculate the duration
            $duration = $logoutTime->diffInMinutes($loginTime);
            // return $duration;exit;
            $logoutime =  DB::table('login_activities')->where('id', $req->login_activity_id)->update(['logout_time' => $currentlogoutTime, 'duration' => $duration.' Minutes','updated_at' => Carbon::now()]);
            if ($logoutime) {
                JWTAuth::parseToken()->invalidate();

                return response()->json(
                    [
                        'status'    => 'success',
                        'message'   =>  __('msg.user.logout.success'),
                    ],
                    200
                );
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
        // try {
        //     JWTAuth::parseToken()->invalidate();
        //     return response()->json(['message' => 'Logged out successfully']);
        // } catch (\Throwable $e) {
        //     return response()->json(['message' => 'Failed to logout'], 500);
        // }
    }
}
