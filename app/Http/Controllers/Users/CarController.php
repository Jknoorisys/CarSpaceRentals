<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarController extends Controller
{

    public function CarList(Request $req)
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

            $db = DB::table('dealer_plots')->join('plots','plots.id','=','dealer_plots.plot_id')
                        ->join('locations','locations.id','=','dealer_plots.location_id')
                        ->join('dealers','dealers.id','=','dealer_plots.dealer_id')
                        ->join('cars','cars.id','=','dealer_plots.car_id')
                        ->select('dealer_plots.*','cars.name as car_name','cars.brand as car_brand','cars.condition as car_condition',
                        'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
                        'cars.fuel_type as car_fuel_type','cars.price as car_price','locations.name as location_name');
                        // return $db;exit;

            // $search = $req->search ? $req->search : '';
            // if (!empty($search)) {
            //     $db->where('name', 'LIKE', "%$search%");
            // }
            
            $total = $db->count();

            $brands = $db->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get();

            if (!($brands->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.get-car.success'),
                    'total'     => $total,
                    'data'      => $brands
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.get-car.failure'),
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

    public function getCarListByname(Request $req)
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
            $per_page = 10;
            $page_number = $req->input(key:'page_number', default:1);

            $db = DB::table('dealer_plots')->join('plots','plots.id','=','dealer_plots.plot_id')
            ->join('locations','locations.id','=','dealer_plots.location_id')
            ->join('dealers','dealers.id','=','dealer_plots.dealer_id')
            ->join('cars','cars.id','=','dealer_plots.car_id')
            ->select('dealer_plots.*','cars.name as car_name','cars.brand as car_brand','cars.condition as car_condition',
            'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
            'cars.fuel_type as car_fuel_type','cars.price as car_price','locations.name as location_name');

            $search = $req->search ? $req->search : '';
            if (!empty($search)) {
                $db->where('cars.name', 'LIKE', "%$search%");
            }
            
            $total = $db->count();

            $brands = $db->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get();

            if (!($brands->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.get-car.success'),
                    'total'     => $total,
                    'data'      => $brands
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.get-car.failure'),
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

    public function carFilter(Request $req)
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
            $condition = $req->car_condition ? $req->car_condition : '';
            $type = $req->car_type ? $req->car_type : '';
            $year = $req->year_of_manufacturing ? $req->year_of_manufacturing : '';
            $fuel_type = $req->fuel_type ? $req->fuel_type  : '';
            $brand = $req->car_brand_id ? $req->car_brand_id : '';
            $price = $req->car_price ? $req->car_price : '';

            // if(!empty($condition))
            // {
                $db = DB::table('dealer_plots')->join('plots','plots.id','=','dealer_plots.plot_id')
                            ->join('locations','locations.id','=','dealer_plots.location_id')
                            ->join('dealers','dealers.id','=','dealer_plots.dealer_id')
                            ->join('cars','cars.id','=','dealer_plots.car_id')
                            ->join('brands','brands.id','=','cars.brand')
                            ->select('dealer_plots.*','cars.name as car_name','cars.condition as car_condition',
                            'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
                            'cars.fuel_type as car_fuel_type','cars.price as car_price','locations.name as location_name',
                            'brands.name as car_brand_name');

                            
                            if (!empty($condition)) 
                            {
                                $db->where('cars.condition', 'LIKE', "%$condition%");
                            }
                            elseif(!empty($type))
                            {
                                $db->where('cars.type', 'LIKE', "%$type%");
                            }
                            elseif(!empty($year))
                            {
                                $db->where('cars.year_of_manufacturing', 'LIKE', "%$year%");
                            }
                            elseif(!empty($fuel_type))
                            {
                                $db->where('cars.fuel_type', 'LIKE', "%$fuel_type%");
                            }
                            elseif(!empty($brand))
                            {
                                $db->where('cars.brand', 'LIKE', "%$brand%");
                            }
                            elseif(!empty($price))
                            {
                                $db->where('cars.price', 'LIKE', "%$price%");
                            }
                            
                            $total = $db->count();

                            $filter = $db->offset(($page_number - 1) * $per_page)
                                        ->limit($per_page)
                                        ->get();
                            // return $filter;exit;
                            if (!($filter->isEmpty())) {
                                return response()->json([
                                    'status'    => 'success',
                                    'message'   => trans('msg.user.get-car.success'),
                                    'total'     => $total,
                                    'data'      => $filter
                                ],200);
                            } else {
                                return response()->json([
                                    'status'    => 'success',
                                    'message'   => trans('msg.user.get-car.failure'),
                                    'data'      => [],
                                ],200);
                            }
            // }
            

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
            ], 500);
        }
    }

}
