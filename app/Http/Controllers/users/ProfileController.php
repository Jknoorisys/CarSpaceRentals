<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function getProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'user_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
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
            $user = DB::table('users')->where('id', '=', $req->user_id)->first();
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
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function UpdateProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'user_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
            'profile' => 'required|image|mimes:jpg,jpeg,svg,png',

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
            $user = DB::table('users')->where('id', '=', $req->user_id)->first();

            if (!empty($user)) {

                $file1 = $req->file('profile');
                if ($file1) {
                    $extension = $file1->getClientOriginalExtension();
                    $filename1 = time().'.'.$extension;
                    $file1->move('assets/uploads/user_profile_photo/', $filename1);
                    $photo = 'assets/uploads/user_profile_photo/'.$filename1;
                }

                $saveProfile = User::where('id', $req->user_id)->update(['profile' => $req->profile ? $photo : '', 'updated_at' => Carbon::now()]);
                if ($saveProfile) {
                    $userDetails = DB::table('users')->where('id', '=', $req->user_id)->first();
                    return response()->json(
                        [
                            'status'    => 'success',
                            'message'   =>  __('msg.user.profile.image'),
                            'data'      => $userDetails,
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
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function UpdateProfileDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'user_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
            'name' => 'required|string',
            'mobile' => 'required|numeric',
            // 'email' => 'required|email|unique:users',
            'profile_image' => 'image|mimes:jpg,jpeg,svg,png'
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
            // $email   = $request->email;

            $user = DB::table('users')->where('id', $user_id)->first();
            if (!empty($user)) {
                $file1 = $request->file('profile_image');
                if ($file1) {
                    $extension = $file1->getClientOriginalExtension();
                    $filename1 = time().'.'.$extension;
                    $file1->move('assets/uploads/user_profile_photo/', $filename1);
                    $photo = 'assets/uploads/user_profile_photo/'.$filename1;
                }

                $update_data = array(
                    'name'       => (isset($name) && !empty($name)) ? $name : $user->name,
                    'mobile'     => (isset($mobile) && !empty($mobile)) ? $mobile : $user->mobile,
                    'profile'    => $request->profile_image ? $photo : $user->profile,
                    // 'email'      => (isset($email) && !empty($email)) ? $email : $user->email,
                    'updated_at' => Carbon::now()
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
                'error'   => $e->getMessage()
            ], 500);
        }
    } 
}
