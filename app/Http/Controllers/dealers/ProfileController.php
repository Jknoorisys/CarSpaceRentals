<?php

namespace App\Http\Controllers\dealers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Dealers;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }
    
    // By Aaisha Shaikh
    public function getProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'dealer_id'   => ['required','alpha_dash', Rule::notIn('undefined')],

        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  trans('msg.user.validation.fail'),
                ],
                400
            );
        }

        try {
            $dealer = DB::table('dealers')->where('id', $req->dealer_id)->first();
            if (!empty($dealer)) {
                return response()->json(
                    [
                        'status'    => 'success',
                        'data' => $dealer,
                        'message'   =>  trans('msg.dealer.profile.success'),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>  trans('msg.dealer.profile.dealernotfound'),
                    ],
                    400
                );
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  trans('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function UpdateProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'dealer_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
            'profile_image' => 'required|image|mimes:jpg,jpeg,svg,png'
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  trans('msg.user.validation.fail'),
                ],
                400
            );
        }

        try {
            $dealer = DB::table('dealers')->where('id', $req->dealer_id)->take(1)->first();

            if (!empty($dealer)) {

                $file1 = $req->file('profile_image');
                if ($file1) {
                    $extension = $file1->getClientOriginalExtension();
                    $filename1 = time().'.'.$extension;
                    $file1->move('assets/uploads/dealer_profile_photo/', $filename1);
                    $photo = 'assets/uploads/dealer_profile_photo/'.$filename1;
                }

                $saveProfile = dealers::where('id', $req->dealer_id)->update(['profile' => $req->profile_image ? $photo : '', 'updated_at' => Carbon::now()]);
                if ($saveProfile) {
                    $updatedProfile = DB::table('dealers')->where('id',$req->dealer_id)->first();
                    return response()->json(
                        [
                            'status'    => 'success',
                            'message'   =>  trans('msg.dealer.profile.image'),
                            'data' => $updatedProfile,
                        ],
                        200
                    );
                } else {
                    return response()->json(
                        [
                            'status'    => 'failed',
                            'message'   =>  trans('msg.dealer.profile.notimage'),
                        ],
                        400
                    );
                }
            } else {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>  trans('msg.dealer.profile.dealernotfound'),
                    ],
                    400
                );
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  trans('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function UpdateProfileDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'dealer_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
            'name' => 'regex:/^[\pL\s]+$/u|min:3',
            'mobile' => 'numeric',
            'company' => 'regex:/^[\pL\s]+$/u|min:3',
            // 'email' => 'unique:dealers',
            'profile_image' => 'image|mimes:jpg,jpeg,svg,png'
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  trans('msg.user.validation.fail'),
                ],
                400
            );
        }

        try {
            $dealer_id  = $request->dealer_id;
            $name      = $request->name;
            $mobile    = $request->mobile;
            // $email   = $request->email;
            $company_name   = $request->company_name;

            $dealer = DB::table('dealers')->where('id', $dealer_id)->first();
            if (!empty($dealer)) {

                $file1 = $request->file('profile_image');
                if ($file1) {
                    $extension = $file1->getClientOriginalExtension();
                    $filename1 = time().'.'.$extension;
                    $file1->move('assets/uploads/dealer_profile_photo/', $filename1);
                    $photo = 'assets/uploads/dealer_profile_photo/'.$filename1;
                }

                $update_data = array(
                    'name'       => (isset($name) && !empty($name)) ? $name : $dealer->name,
                    'mobile'     => (isset($mobile) && !empty($mobile)) ? $mobile : $dealer->mobile,
                    'profile'    => $request->profile_image ? $photo : $dealer->profile,
                    // 'email'      => (isset($email) && !empty($email)) ? $email : $dealer->email,
                    'company'    => (isset($company_name) && !empty($company_name)) ? $company_name : $dealer->company,
                    'updated_at' => Carbon::now()
                );

                $updateProfile = Dealers::where('id', $dealer_id)->update($update_data);

                if ($updateProfile) {
                    $storeInfo = DB::table('dealers')->where('id', $dealer_id)->where('status', 'active')->first();
                    return response()->json([
                        'status'  =>  'success',
                        'message' => trans('msg.dealer.profile.updated'),
                        'patient' => $storeInfo,
                    ], 200);
                } else {
                    return response()->json([
                        'status'      => 'failed',
                        'message'     => trans('msg.dealer.profile.notupdated'),
                    ], 400);
                }
            } else {
                return response()->json(
                    [
                        'status'    => 'failed',
                        'message'   =>  trans('msg.dealer.profile.dealernotfound'),
                    ],
                    400
                );
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  trans('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
