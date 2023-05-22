<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    public function featuredCarList(Request $req)
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
            // $db = DB::table('featured_cars')->join('cars','cars.id','=','featured_cars.car_id')
            //             // ->join('dealers','dealers.id','=','featured_cars.dealer_id')
            //             ->join('dealers','dealers.id','=','dealer_plots.dealer_id')
            //             ->join('locations','locations.id','=','dealer_plots.location_id')
            //             ->select('featured_cars.*','cars.name as car_name','cars.condition as car_condition',
            //             'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type','locations.name as location_name',
            //             'cars.fuel_type as car_fuel_type','cars.price as car_price')
            //             ->get();
            $db = DB::table('cars')->leftjoin('dealers','dealers.id','=','cars.dealer_id')
                        ->leftjoin('dealer_plots','dealer_plots.car_id','=','cars.id')
                        ->leftjoin('locations','locations.id','=','dealer_plots.location_id')
                        ->leftjoin('brands','brands.id','=','cars.brand')
                        ->where('cars.is_featured','=','yes')
                        ->select('cars.*','locations.name as location_name','brands.name as brand_name');
                        
            
            $total = $db->count();

            $featured = $db->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get();
                // return $brands;
            if (!($featured->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.get-car.featured'),
                    'total'     => $total,
                    'data'      => $featured
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.get-car.notfeature'),
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

    public function CarDetails(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'car_id' => ['required','alpha_dash', Rule::notIn('undefined')],
            ''
            
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
            $car = DB::table('cars')->where('id',$req->car_id)->first();
            if(!empty($car))
            {
                return "car";
            }
            else
            {
                return "not found";
            }

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
            ], 500);
        }
    }

}
