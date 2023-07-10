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
use App\Mail\dealerforgetpass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    // By Aaisha Shaikh
    public function register(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language'          =>   'required',
            'name'   => 'required|min:3',
            'password'   => 'required|max:20||min:8',
            'email' => 'required|email|unique:dealers',
            'mobile' => 'required|numeric|unique:dealers',
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
                $otp = rand(100000, 999999);
                $data = $req->input();

                $dealer = [
                    'id' => Str::uuid(), 'name' => $data['name'], 'password' => Hash::make($data['password']),
                    'email' => $data['email'], 'mobile' => $data['mobile'], 'email_otp' => $otp, 'created_at' => Carbon::now()
                ];

                $saveDealer = DB::table('dealers')->insert($dealer);
                
                $data = [
                    'salutation' => trans('msg.email.Dear'),
                    'name'=> $req->name,
                    'otp'=> $otp, 
                    'msg'=> trans('msg.email.Let’s get you Registered with us!'), 
                    'otp_msg'=> trans('msg.email.Your One time Password to Complete your Registrations is')
                ];

                $email =  ['to'=> $req->email];
                Mail::send('email_template', $data, function ($message) use ($email) {
                    $message->to($email['to']);
                    $message->subject(__('msg.email.Email Verification'));
                });

                if ($saveDealer) {
                    return response()->json([
                            'status'    => 'success',
                            'data' => $dealer,
                            'message'   => __('msg.user.register.success'),
                        ], 200);
                } else {
                    return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.user.register.fail'),
                        ], 400);
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
            'id' => ['required','alpha_dash', Rule::notIn('undefined')]
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
            $match_otp = DB::table('dealers')->where('id',$id)->where('email_otp',$otp)->take(1)->first();

            if(!empty($match_otp))
            {
                $verificationCode   =  DB::table('dealers')->where('email_otp', $otp)->where('id', $id)->update(['is_verified' => 'yes']);
                if ($verificationCode) {
                    return response()->json([
                            'status'    => 'success',
                            'message'   =>  __('msg.user.otp.otpver'),
                        ], 200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   =>   __('msg.user.otp.failure'),
                    ], 400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   =>   __('msg.user.otp.otpnotver'),
                ], 400);
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
            'email'   => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  __('msg.user.validation.fail'),
                ], 400);
        }

        try {

            $email = $req->email;
            $dealer = Dealers::where('email', $email)->take(1)->first();

            if (!empty($dealer)) {

                if ($dealer->is_verified == 'no') {
                    $email_otp = rand(100000, 999999);
                    $resend =  Dealers::where('email', '=', $email)->update(['email_otp' => $email_otp,'updated_at' => date('Y-m-d H:i:s')]);
                    if ($resend == true) {
                        $dealer = Dealers::where('email', '=', $email)->first();
                        $data = [
                            'salutation' => trans('msg.email.Dear'),
                            'name'=> $req->name,
                            'otp'=> $email_otp, 
                            'msg'=> trans('msg.email.Let’s get you Registered with us!'), 
                            'otp_msg'=> trans('msg.email.Your One time Password to Complete your Registrations is')
                        ];
        
                        $email =  ['to'=> $req->email];
                        Mail::send('email_template', $data, function ($message) use ($email) {
                            $message->to($email['to']);
                            $message->subject(__('msg.email.Email Verification'));
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

    public function forgetpassword(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'email'   => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  __('msg.user.validation.fail'),
                ], 400);
        }

        try {

            $email = $req->email;
            $dealer = Dealers::where('email', $email)->first();
            if (!empty($dealer)) {
                $token = Str::random(60);
                $dealer['token'] = $token;
                // $dealer['is_verified'] = 'yes';
                $dealerPass = $dealer->save();
                
                $data = ['salutation' => trans('msg.email.Dear'), 
                'name'=> $dealer->name,'url'=> 'http://tabanimasala.com/carspacerental-site/auth/reset-password?user_type=dealer&token='.$token,
                'msg'=> trans('msg.email.Need to reset your password?'),
                'url_msg'=> trans('msg.No problem! Just click on the button below and you’ll be on your way.')];
                $email =  ['to'=> $dealer->email];
                Mail::send('reset_password_mail', $data, function ($message) use ($email) {
                    $message->to($email['to']);
                    $message->subject(trans('msg.email.Forget Password'));
                });

                if ($dealerPass) {
                    return response()->json([
                            'status'    => 'success',
                            'data' => $dealer,
                            'message'   =>  __('msg.user.forgetpass.emailsent'),
                        ], 200);
                } else {
                    return response()->json([
                            'status'    => 'failed',
                            'message'   =>  __('msg.user.forgetpass.emailnotsent'),
                    ], 400);
                }
            } else {
                return response()->json([
                        'status'    => 'failed',
                        'message'   =>  __('msg.user.forgetpass.notreg'),
                    ], 400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'   => $e->getMessage()
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
            return response()->json([
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  __('msg.user.validation.fail'),
                ], 400);
        }

        try {
            $dealer = Dealers::where('token', $req->token)->first();
            if ($dealer) {

                $password = $req->password;
                if ($password == $req->confirm_password) {
                    $dealer->password = Hash::make($req->password);
                    $dealer->token = '';
                    $info = $dealer->save();
                    if ($info) {
                        return response()->json([
                                'status'    => 'success',
                                'message'   =>  __('msg.user.forgetpass.reset'),
                            ], 200);
                    } else {
                        return response()->json([
                                'status'    => 'failed',
                                'message'   =>  __('msg.user.forgetpass.notreset'),
                            ], 400);
                    }
                } else {
                    return response()->json([
                            'status'    => 'failed',
                            'message'   =>  __('msg.user.forgetpass.passnotmatch'),
                        ], 400);
                }
            } else {
                return response()->json([
                        'status'    => 'failed',
                        'message'   =>  __('msg.user.forgetpass.tokennotmatch'),
                    ], 400);
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
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'email' => 'required|email',
            'password'   => 'required',
            // 'device_id' => 'required',
            'ip_address' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  __('msg.user.validation.fail'),
            ], 400);
        }
        
        try {
            $service = new Services();
            $email = $req->email;
            $password = $req->password;
            $dealer  = Dealers::where('email', $email)
                ->take(1)->first();

            if ($dealer) {
                if (Hash::check($password,$dealer->password)) {
                    if ($dealer->status == 'active') {
                        $claims = array(
                            'exp'   => Carbon::now()->addDays(1)->timestamp,
                            'uuid'  => $dealer->id
                        );

                        $dealer->token = $service->getSignedAccessTokenForUser($dealer, $claims);
                        $dealer->save();
                        $currentDate = Carbon::now()->format('Y-m-d');
                        $currentTime = Carbon::now()->format('H:i:s');

                        $dealer_id  = DB::table('dealers')->where('email', $email)->where('password', $dealer->password)->take(1)->first();

                        $dealerLog = [
                            'id' => Str::uuid('36'), 
                            'user_id' => $dealer_id->id,  
                            'login_date' => $currentDate, 
                            'login_time' => $currentTime,
                            'user_type' => 'dealer',
                            'ip_address' => $req->ip_address,
                            'created_at' => Carbon::now()
                        ];
                        
                        $logintime =  DB::table('login_activities')->insert($dealerLog);
                        $dealer_id->dealer_login_activity_id = $dealerLog['id'];
                        $dealer_id->JWT_token = $dealer->token;
                        return response()->json([
                                'status'    => 'success',
                                'data' => $dealer_id,
                                'message'   =>   __('msg.user.validation.login'),
                            ], 200);
                    } else {
                        return response()->json([
                                'status'    => 'failed',
                                'message'   =>  __('msg.user.validation.inactive'),
                            ], 400);
                    }
                }else {
                    return response()->json([
                            'status'    => 'failed',
                            'message'   =>  __('msg.user.validation.incpass'),
                        ],400
                    );
                }
            } else {
                return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.user.forgetpass.notreg'),
                    ], 400);
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
            return response()->json([
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  __('msg.user.validation.fail'),
                ], 400);
        }

        try {
            $login_time = DB::table('login_activities')->where('id',$req->login_activity_id)->first();
            $currentloginTime = $login_time->login_time;
            $currentlogoutTime = Carbon::now()->format('H:i:s');
            $loginTime = Carbon::parse($currentlogoutTime);
            $logoutTime = Carbon::parse($currentloginTime);

            // Calculate the duration
            $timeDifference = $logoutTime->diff($loginTime);

            $hours = $timeDifference->h;
            $minutes = $timeDifference->i;
            $seconds = $timeDifference->s;

            $duration = "";
            if ($hours > 0) {
                $duration .= $hours . ($hours === 1 ? ' hour' : ' hours');
            }
            
            if ($minutes > 0) {
                $duration .= ($duration !== '' ? ' ' : '') . $minutes . ($minutes === 1 ? ' minute' : ' minutes');
            }
            
            if ($seconds > 0 && $hours === 0 && $minutes === 0) {
                $duration .= ($duration !== '' ? ' ' : '') . $seconds . ($seconds === 1 ? ' second' : ' seconds');
            }

            $logoutime =  DB::table('login_activities')->where('id', $req->login_activity_id)->update(['logout_time' => $currentlogoutTime, 'duration' => $duration.' Minutes','updated_at' => Carbon::now()]);
            
            if ($logoutime) {
                JWTAuth::parseToken()->invalidate();

                return response()->json([
                        'status'    => 'success',
                        'message'   =>  __('msg.user.logout.success'),
                    ], 200);
            } else {
                return response()->json([
                        'status'    => 'failed',
                        'message'   =>  __('msg.user.logout.fail'),
                    ], 400);
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
