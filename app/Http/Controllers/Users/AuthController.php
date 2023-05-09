<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Libraries\Services;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgetPassword;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $req)
    {
        $data = $req->only('language', 'name', 'password', 'email','mobile_no');

        $validator = Validator::make($data, [
            'language'          =>   'required',
            'name'   => 'required|regex:/^[\pL\s]+$/u|min:3',
            'password'   => 'required|max:20||min:8',
            'email' => 'required|unique:users',
            'mobile_no' => 'required|numeric',

        ]);

        if ($validator->fails()) 
        {
            return response()->json(
                [
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  trans('validation.custom.input.invalid'),
                ],
                400
            );
        } 
        else 
        {
            try
            {
                $result = DB::table('users')
                ->where('email', $req->input('email'))
                ->get();
            
                if (!empty($result)) {
                    $req->validate([
                        'name'   => 'required|alpha_num',
                        'email' => 'required|unique:users',
                    ]);
                      $otp = rand(1000, 9999);
                    $data = $req->input();
                    $register = new User;
                
                    $register->name = $data['name'];
                    $register->uid = rand(1000,9999);
                    // $encrypted_password =
                    $register->password = md5($data['password']);
                    $register->email = $data['email'];
                    $register->mobile_no = $data['mobile_no'];
                      $register->otp =  $otp;
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
                    $user = $register->save();
                    if ($user) {
                        return response()->json(
                            [
                                'status'    => 'success',
                                'data' => $register,
                                'message'   =>  trans('validation.custom.user.otpsend'),
                            ],
                            200
                        );
                    } else {
                        return response()->json(
                            [
                                'status'    => 'failed',
                                'message'   =>  trans('validation.custom.user.smw'),
                            ],
                            400
                        );
                    }
                }
            }
            catch (\Throwable $e)
                {
                    return response()->json([
                        'status'  => 'failed',
                        'message' => trans('validation.custom.invalid.request'),
                        'error'   => $e->getMessage()
                    ],500);
                }
        }   
    }
    public function verifyOTP(Request $req)
    {
        $data = $req->only('language','otp','id');
        $validator = Validator::make($data, [
            'language' => 'required',
            'otp'   => 'required',
            'id' => 'required||numeric'
            
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  trans('validation.custom.input.invalid'),
                ],
                400
            );
        } 
        else
        {
            try
            {
                $otp = $req->otp;
                $id =$req->id;
                #Validation Logic
                $verificationCode   =  DB::table('users')->where('otp', $otp)->where('id',$id)->update(['is_verified' => 'yes', 'email_verified_at' => date('Y-m-d H:i:s')]);
                if($verificationCode == true)
                {
                    return response()->json(
                        [
                            'status'    => 'success',
                            'message'   =>  trans('validation.custom.user.otpverified'),
                        ],
                        200
                    );
                }
                else
                {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  trans('validation.custom.user.otpnotver'),
                        ],
                        400
                    );
                }
            }
            catch (\Throwable $e)
            {
                return response()->json([
                    'status'  => 'failed',
                    'message' => trans('validation.custom.invalid.request'),
                    'error'   => $e->getMessage()
                ],500);
            }
                
        }
    }
    public function resendregOTP(Request $req)
    {
        $data = $req->only('language', 'email');
        $validator = Validator::make($data, [
            'language' => 'required',
            'email'   => 'required',
            
            
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  trans('validation.custom.input.invalid'),
                ],
                400
            );
        } 
        else
        {
            try
            {
                $email = $req->email;
                $user = User::where('email',$email)->take(1)->first();
                // return $provider;exit;
                if(!empty($user))
                {
                    if($user->is_verified == 'no')
                    {
                        // echo 'Hiiiii';exit();
                        $email_otp = rand(1000,9999);
                        $resend =  User :: where('email','=',$email)->update(['otp' => $email_otp, 'is_verified' => 'yes', 'email_verified_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')]);
                        if($resend == true)
                        {
                            $user = User::where('email','=',$email)->first();
                            $email = ['to'=> $req->email];
                            $mail_details = [
                                'subject' => 'Testing Application OTP',
                                'body' => 'Your OTP is : '. $email_otp
                            ];         
                            $data = array('name'=>$user->name,
                                        'otp'=>$email_otp);
                            Mail::send('User_Mail.resenOTPmail', $data, function($message)use($email)  {
                                $message->to($email['to'])->subject
                                ('Resend Email Verification');
                            });
                            return response()->json([
                                'status'    => 'success',
                                'message'   =>  trans('validation.custom.user.resendotp'),
                            ],200);

                        }
                    }
                    else
                    {
                        return response()->json([
                            'status'    => 'failed',
                            'message'   =>  trans('validation.custom.user.alreadymailverified'),
                        ],400);
                    }
                }
                else
                {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   =>  trans('validation.custom.user.registerfirst'),
                    ],400);
                }
            }
            catch (\Throwable $e)
            {
                return response()->json([
                    'status'  => 'failed',
                    'message' => trans('validation.custom.invalid.request'),
                    'error'   => $e->getMessage()
                ],500);
            }
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
                    'message'   =>  trans('validation.custom.input.invalid'),
                ],
                400
            );
        } 
        else
        {
            try
            {
                $service = new Services();
                $email = $req->email;
                $password = md5($req->password);
                $user  = user::
                    where('email', $email)
                    ->where('password', $password)
                    ->take(1)->first();
                // return $user;exit;

                if ($user) {
                    // if ($user->is_email_verified == 'verified') {
                        if ($user->status == 0) {
                            $claims = array(
                                'exp'   => Carbon::now()->addDays(1)->timestamp,
                                'uuid'  => $user->id
                            );
                            // return $claims;exit;
                            $user->token = $service->getSignedAccessTokenForUser($user,$claims);
                            // return $user->token;exit;
                            return response()->json(
                                [
                                    'status'    => 'success',
                                    'data' => $user,
                                    'message'   =>  trans('validation.custom.user.login'),
                                ],
                                200
                            );
                        } else {
                            return response()->json(
                                [
                                    'status'    => 'failed',
                                    'message'   =>  trans('validation.custom.user.block'),
                                ],
                                400
                            );
                        }
                
                } else {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  trans('validation.custom.user.incemailpass'),
                        ],
                        400
                    );
                }
            }
            catch (\Throwable $e)
            {
                return response()->json([
                    'status'  => 'failed',
                    'message' => trans('validation.custom.invalid.request'),
                    'error'   => $e->getMessage()
                ],500);
            }
               
        }
    }
    public function forgetpassword(Request $req)
    {
        $data = $req->only('language', 'email');
        $validator = Validator::make($data, [
            'language' => 'required',
            'email'   => 'required',
            
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  trans('validation.custom.input.invalid'),
                ],
                400
            );
        } 
        else
        {
            try
            {
                
                $email = $req->email;
                $user = User::where('email', $email)->first();
                if (!empty($user)) 
                {
                    $token = Str::random(60);
                    $user['token'] = $token;
                    $user['is_verified'] = 'yes';
                    $userPass = $user->save();
                    // $url = 'https://umrahmall.net/ndashaka/cust-forgot-password/'.$user['token'];
                    $mailsent = Mail::to($req->email)->send(new ForgetPassword($user->name, $token));
                    if ($mailsent == true) {
                        return response()->json(
                            [
                                'status'    => 'success',
                                'data' => $user,
                                // 'url' => $url,
                                'message'   =>  trans('validation.custom.user.emailsent'),
                            ],
                            200
                        );
                    } else {
                        return response()->json(
                            [
                                'status'    => 'failed',
                                'message'   =>  trans('validation.custom.user.emailnotsend'),
                            ],
                            400
                        );
                    }
                } 
                else 
                {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  trans('validation.custom.user.notreg'),
                        ],
                        400
                    );
                    
                }
            }
            catch (\Throwable $e)
            {
                return response()->json([
                    'status'  => 'failed',
                    'message' => trans('validation.custom.invalid.request'),
                    'error'   => $e->getMessage()
                ],500);
            }
        }
    }
    public function forgotPasswordValidate(Request $req)
    {
        $data = $req->only('language', 'token', 'password', 'confirm_password');
        $validator = Validator::make($data, [
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
                    'message'   =>  trans('validation.custom.input.invalid'),
                ],
                400
            );
        } 
        else 
        {
            try
            {
                $user = User::where('token', $req->token)->first();
                if ($user) 
                {

                    $user->password = md5($req->password);
                    if ($user->password == md5($req->confirm_password)) {
                        $user->token = '';
                        $info = $user->save();
                        if ($info) {
                            return response()->json(
                                [
                                    'status'    => 'success',
                                    'message'   =>  trans('validation.custom.user.resetsucc'),
                                ],
                                200
                            );
                        } else {
                            return response()->json(
                                [
                                    'status'    => 'failed',
                                    'message'   =>  trans('validation.custom.user.invalid'),
                                ],
                                400
                            );
                        }
                    } else {
                        return response()->json(
                            [
                                'status'    => 'failed',
                                'message'   =>  trans('validation.custom.user.passnotmatch'),
                            ],
                            400
                        );
                    }
                } 
                else 
                {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  trans('validation.custom.user.wrong'),
                        ],
                        400
                    );
                }
            }
            catch (\Throwable $e)
            {
                return response()->json([
                    'status'  => 'failed',
                    'message' => trans('validation.custom.invalid.request'),
                    'error'   => $e->getMessage()
                ],500);
            }
        }
    }
    public function profile(Request $req)
    {
        echo "hiiii";
    }
}
