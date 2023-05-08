<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Libraries\Services;

class UserController extends Controller
{
    public function register(Request $req)
    {
        $data = $req->only('language', 'name', 'phone', 'password', 'email');

        $validator = Validator::make($data, [
            'language'          =>   'required',
            'name'   => 'required|regex:/^[\pL\s]+$/u|min:3',
            'password'   => 'required|max:20||min:8',
            'email' => 'required|unique:users',
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
                    //   $otp = rand(1000, 9999);
                    $data = $req->input();
                    $register = new User;
                
                    $register->name = $data['name'];
                    // $encrypted_password =
                    $register->password = md5($data['password']);
                    $register->email = $data['email'];
                    //   $register->otp =  $otp;
                    //   $email = ['to' => $data['email']];
                    //   $mail_details = [
                    //     'subject' => 'Testing Application OTP',
                    //     'body' => 'Your OTP is : ' . $otp
                    //   ];
                    //   $data = array(
                    //     'name' => $data['name'],
                    //     'otp' => $otp
                    //   );
                    //   Mail::send('mail', $data, function ($message) use ($email) {
                    //     $message->to($email['to'])->subject('Email Verification');
                    //   });
                    $user = $register->save();
                    if ($user) {
                        return response()->json(
                            [
                                'status'    => 'success',
                                'data' => $register,
                                'message'   =>  trans('validation.custom.input.otpsend'),
                            ],
                            200
                        );
                    } else {
                        return response()->json(
                            [
                                'status'    => 'failed',
                                'message'   =>  trans('validation.custom.input.invalid'),
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
                // return $email;exit;
                $password = md5($req->password);
                $user  = user::
                    where('email', $email)
                    ->where('password', $password)
                    ->take(1)->first();
                if ($user) {
                    // if ($user->is_email_verified == 'verified') {
                        if ($user->status == 0) {
                            $claims = array(
                                'exp'   => Carbon::now()->addDays(1)->timestamp,
                                'uuid'  => $user->id
                            );
                            // return $claims;exit;
                            $user->token = $service->getSignedAccessTokenForUser($user,$claims);
                            // return $token;exit;
                            return response()->json(
                                [
                                    'status'    => 'success',
                                    'data' => $user,
                                    'message'   =>  trans('validation.custom.input.login'),
                                ],
                                200
                            );
                        } else {
                            return response()->json(
                                [
                                    'status'    => 'failed',
                                    'message'   =>  trans('validation.custom.input.block'),
                                ],
                                400
                            );
                        }
                
                } else {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  trans('validation.custom.input.incemailpass'),
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
