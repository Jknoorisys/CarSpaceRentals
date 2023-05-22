<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brands;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class FeaturedcarPriceController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function getFeaturedcarPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
           
            $price = DB::table('featured_car_prices')->first();

            if (!empty($price)) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-featured-car-price.success'),
                    'data'      => $price
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.get-featured-car-price.failure'),
                ],400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function editFeaturedcarPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'price_id'    => ['required','alpha_dash', Rule::notIn('undefined')],
            'admin_id'    => ['required','alpha_dash', Rule::notIn('undefined')],
            'admin_type'  => ['required', 
                Rule::in(['user', 'dealer'])
            ],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
           
            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }

            $price_id = $request->price_id;
            $price = $request->price;

            $featuredPrice = DB::table('featured_car_prices')->where('id', '=', $price_id)->first();

            if (!empty($featuredPrice)) {
                $updatePrice = DB::table('featured_car_prices')->where('id', '=', $price_id)->update(['price' => $price, 'updated_at' => Carbon::now()]);
                if ($updatePrice) {

                    $adminData = [
                        'id'        => Str::uuid(),
                        'user_id'   => $request->admin_id,
                        'user_type' => $request->admin_type,
                        'activity'  => 'Featured Car Price is Updated by '.ucfirst($request->admin_type).' '.$admin->name.' from '.$featuredPrice->currency.$featuredPrice->price.' to '.$featuredPrice->currency.$price,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
    
                    DB::table('admin_activities')->insert($adminData);

                    return response()->json([
                        'status'    => 'success',
                        'message'   => trans('msg.admin.edit-featured-car-price.success'),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.admin.edit-featured-car-price.failure'),
                    ],400);
                }
                
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.edit-featured-car-price.invalid'),
                ],400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }
    
}
