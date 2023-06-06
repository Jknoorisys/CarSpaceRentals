<?php

namespace App\Http\Controllers\dealers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FeaturedCarController extends Controller
{
    public function __construct() 
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    // By Aaisha Shaikh 
    
    // Not Required
    public function addCarToFeaturedList(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language'          =>  'required',
            'dealer_id'         =>  ['required', 'alpha_dash', Rule::notIn('undefined')],
            'car_id'            =>  ['required', 'alpha_dash', Rule::notIn('undefined')],
            'start_date'        => 'required',
            'end_date'          => 'required',
            'faetured_days'     => 'required',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.user.validation.fail'),
                'errors'    => $validator->errors()
            ], 400);
        }
        
        try 
        {

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => __('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
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
                    'message'   => trans('msg.dealer.get-featured-car-price.success'),
                    'data'      => $price
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.dealer.get-featured-car-price.failure'),
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
