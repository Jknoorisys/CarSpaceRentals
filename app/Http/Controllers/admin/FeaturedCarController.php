<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class FeaturedCarController extends Controller
{
    public function __construct() 
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function getFeaturedCar(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'page_number'   => 'required||numeric',
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
        try 
        {
            $per_page = 4;
            $page_number = $req->input(key:'page_number', default:1);

            $db = DB::table('featured_cars')->join('dealers','dealers.id','=','featured_cars.dealer_id')
                        ->join('cars','cars.id','=','featured_cars.car_id')
                        ->select('featured_cars.*','cars.name as car_name','cars.condition as car_condition',
                        'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
                        'cars.fuel_type as car_fuel_type','cars.price as car_price','dealers.name as dealer_name');
            $total = $db->count();
            $search = $req->search ? $req->search : ''; 
            if (!empty($search)) {
                $db->where('cars.name', 'LIKE', "%$search%");
                $db->orWhere('dealers.name', 'LIKE', "%$search%");
            }

            $brands = $db->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get();

            if (!($brands->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.featured-get-car.success'),
                    'total'     => $total,
                    'data'      => $brands
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.featured-get-car.failure'),
                    'data'      => [],
                ],200);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
            ], 500);
        }
    }

    public function GetDetailsFeaturedCar(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'car_id' => ['required','alpha_dash', Rule::notIn('undefined')],
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
        try 
        {
            $car = DB::table('featured_cars')->join('dealers','dealers.id','=','featured_cars.dealer_id')
                        ->join('cars','cars.id','=','featured_cars.car_id')->where('featured_cars.car_id',$req->car_id)
                        ->select('featured_cars.*','cars.name as car_name','dealers.name as dealer_name')->first();
            if(!empty($car))
            {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.featured-get-car.success'),
                    'data'      => $car
                ],200);
            }
            else
            {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.featured-get-car.failed'),
                    'data'      => []
                ],200);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
            ], 500);
        }
    }

}
