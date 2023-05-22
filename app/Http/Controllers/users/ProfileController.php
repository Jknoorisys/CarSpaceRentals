<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function getProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'user_id'   => 'required',

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
            $user = DB::table('users')->where('id', $req->user_id)->take(1)->first();
            if (!empty($user)) {
                return response()->json(
                    [
                        'status'    => 'success',
                        'data' => $user,
                        'message'   =>  __('msg.user.profile.success'),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>  __('msg.user.profile.usernotfound'),
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

    public function UpdateProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'user_id'   => 'required',
            'profile' => 'required',

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
            $user = DB::table('users')->where('id', $req->user_id)->take(1)->first();
            // return $req->profile_image;exit;
            if (!empty($user)) {

                $profile = optional($req->file('profile'))->getClientOriginalName();
                $file_name = time() . '.' . $profile;
                $save = $req->file('profile')->move('user_profile_photo', $file_name);
                $saveProfile = User::where('id', $req->user_id)->update(['profile' => ('user_profile_photo/' . $file_name)]);
                if ($saveProfile) {
                    $UpdateProfile = DB::table('users')->where('id',$req->user_id)->first();
                    return response()->json(
                        [
                            'status'    => 'success',
                            'message'   =>  __('msg.user.profile.image'),
                        ],
                        200
                    );
                } else {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  __('msg.user.profile.notimage'),
                        ],
                        400
                    );
                }
            } else {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>  __('msg.dealer.profile.dealernotfound'),
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

    public function UpdateProfileDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'user_id'   => 'required',
            'name' => 'regex:/^[\pL\s]+$/u|min:3',
            'mobile' => 'numeric',
            'email' => 'unique:users'
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
            $user_id  = $request->user_id;
            $name      = $request->name;
            $mobile    = $request->mobile;
            $email   = $request->email;

            $user = DB::table('users')->where('id', $user_id)->first();
            if (!empty($user)) {
                $file = $request->file('profile_image');
                if ($file) {

                    $extension = $file->getClientOriginalName();
                    $file_path = 'user_profile_photo/';
                    $filename = time() . '.' . $extension;

                    $upload = $file->move($file_path, $filename);
                }
                $update_data = array(
                    'name'       => (isset($name) && !empty($name)) ? $name : $user->name,
                    'mobile'     => (isset($mobile) && !empty($mobile)) ? $mobile : $user->mobile,
                    'profile'    => (isset($filename) && !empty($filename)) ? ('user_profile_photo/' . $filename) : $user->profile,
                    'email'    => (isset($email) && !empty($email)) ? $email : $user->email,
                    'updated_at' => date('Y-m-d H:i:s')
                );

                $updateProfile = User::where('id', $user_id)->update($update_data);

                if ($updateProfile) {
                    $storeInfo = DB::table('users')->where('id', $user_id)->where('status', 'active')->first();
                    return response()->json([
                        'status'  =>  'success',
                        'message' => __('msg.user.profile.updated'),
                        'patient' => $storeInfo,
                    ], 200);
                } else {
                    return response()->json([
                        'status'      => 'failed',
                        'message'     => __('msg.user.profile.notupdated'),
                    ], 400);
                }
            } else {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>  __('msg.user.profile.usernotfound'),
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

    


}
