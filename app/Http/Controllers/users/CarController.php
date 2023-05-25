<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Dealers;
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

            $db = DB::table('bookings')->join('plots','plots.id','=','bookings.plot_id')
                        ->join('locations','locations.id','=','bookings.location_id')
                        ->join('dealers','dealers.id','=','bookings.dealer_id')
                        ->join('cars','cars.id','=','bookings.car_id')
                        ->join('car_photos','car_photos.car_id','=','cars.id')
                        ->select('bookings.*','cars.name as car_name','cars.brand as car_brand','cars.condition as car_condition',
                        'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
                        'cars.fuel_type as car_fuel_type','cars.price as car_price','locations.name as location_name','car_photos.photo1 as car_image');
                        // return $db;exit;

            // $search = $req->search ? $req->search : '';
            // if (!empty($search)) {
            //     $db->where('name', 'LIKE', "%$search%");
            // }
            
            $total = $db->count();

            $cars = $db->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get();

            if (!($cars->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.get-car.success'),
                    'total'     => $total,
                    'data'      => $cars
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

            $db = DB::table('bookings')->join('plots','plots.id','=','bookings.plot_id')
            ->join('locations','locations.id','=','bookings.location_id')
            ->join('dealers','dealers.id','=','bookings.dealer_id')
            ->join('cars','cars.id','=','bookings.car_id')
            ->join('car_photos','car_photos.car_id','=','cars.id')
            ->select('bookings.*','cars.name as car_name','cars.brand as car_brand','cars.condition as car_condition',
            'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
            'cars.fuel_type as car_fuel_type','cars.price as car_price','locations.name as location_name','car_photos.photo1 as car_image');

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
                $db = DB::table('bookings')->join('plots','plots.id','=','bookings.plot_id')
                            ->join('locations','locations.id','=','bookings.location_id')
                            ->join('dealers','dealers.id','=','bookings.dealer_id')
                            ->join('cars','cars.id','=','bookings.car_id')
                            ->join('brands','brands.id','=','cars.brand')
                            ->join('car_photos','car_photos.car_id','=','cars.id')
                            ->select('bookings.*','cars.name as car_name','cars.condition as car_condition',
                            'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
                            'cars.fuel_type as car_fuel_type','cars.price as car_price','locations.name as location_name',
                            'brands.name as car_brand_name','car_photos.photo1 as car_image');

                            
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
            //             ->join('dealers','dealers.id','=','bookings.dealer_id')
            //             ->join('locations','locations.id','=','bookings.location_id')
            //             ->select('featured_cars.*','cars.name as car_name','cars.condition as car_condition',
            //             'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type','locations.name as location_name',
            //             'cars.fuel_type as car_fuel_type','cars.price as car_price')
            //             ->get();
            $db = DB::table('cars')->leftjoin('dealers','dealers.id','=','cars.dealer_id')
                        ->leftjoin('bookings','bookings.car_id','=','cars.id')
                        ->leftjoin('locations','locations.id','=','bookings.location_id')
                        ->leftjoin('brands','brands.id','=','cars.brand')
                        ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                        ->where('cars.is_featured','=','yes')
                        ->select('cars.*','locations.name as location_name','brands.name as brand_name','car_photos.photo1 as car_image');
                        
            
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
                $car_details = DB::table('bookings')->leftJoin('cars','cars.id','=','bookings.car_id')
                                    ->leftJoin('locations','locations.id','=','bookings.location_id')
                                    ->leftJoin('dealers','dealers.id','=','bookings.dealer_id')
                                    ->leftJoin('plots','plots.id','=','bookings.plot_id')
                                    ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                                    ->where('cars.id','=',$req->car_id)
                                    ->select('bookings.*','cars.name as car_name','cars.condition as car_condition',
                                    'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
                                    'cars.fuel_type as car_fuel_type','cars.year_of_registration as car_year_of_registration',
                                    'cars.kms_driven as car_kms_driven','cars.ownership as car_ownership',
                                    'cars.insurance_validity as car_insurance_validity','cars.no_of_seats as car_seats',
                                    'cars.milage as car_milage','cars.engin as car_engin','cars.description as car_description',
                                    'cars.top_speed as car_speed','cars.color as car_color','locations.name as location_name',
                                    'locations.location as car_location','plots.plot_name as car_plot_name',
                                    'cars.price as car_price','dealers.name as dealer_name','dealers.email as dealer_email',
                                    'dealers.company as dealer_company','dealers.profile as dealer_profile','dealers.mobile as dealer_mobile_no',
                                    'car_photos.photo1 as car_image1','car_photos.photo2 as car_image2','car_photos.photo3 as car_image3',
                                    'car_photos.photo4 as car_image4','car_photos.photo5 as car_image5'

                                    )
                                    ->first();
                                    // return $car_details;
                                    if(!empty($car_details))
                                    {
                                        return response()->json([
                                            'status'    => 'Success',
                                            'message'   => trans('msg.user.car_details.success'),
                                            'data' => $car_details,
                                        ],200);
                                    }
                                    else
                                    {
                                        return response()->json([
                                            'status'    => 'Failed',
                                            'message'   => trans('msg.user.car_details.failure'),
                                        ],400);
                                    }
                                    
            }
            else
            {
                return response()->json([
                    'status'    => 'Failed',
                    'message'   => trans('msg.user.get-car.failure'),
                ],400);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
            ], 500);
        }
    }

    public function Car_details_and_featured_car(Request $req)
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
            $car = DB::table('cars')->where('id',$req->car_id)->first();
            if(!empty($car))
            {
                $car_details = DB::table('bookings')->leftJoin('cars','cars.id','=','bookings.car_id')
                                    ->leftJoin('locations','locations.id','=','bookings.location_id')
                                    ->leftJoin('dealers','dealers.id','=','bookings.dealer_id')
                                    ->leftJoin('plots','plots.id','=','bookings.plot_id')
                                    ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                                    ->where('cars.id','=',$req->car_id)
                                    ->select('bookings.*','cars.name as car_name','cars.condition as car_condition',
                                    'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
                                    'cars.fuel_type as car_fuel_type','cars.year_of_registration as car_year_of_registration',
                                    'cars.kms_driven as car_kms_driven','cars.ownership as car_ownership',
                                    'cars.insurance_validity as car_insurance_validity','cars.no_of_seats as car_seats',
                                    'cars.milage as car_milage','cars.engin as car_engin','cars.description as car_description',
                                    'cars.top_speed as car_speed','cars.color as car_color','locations.name as location_name',
                                    'locations.location as car_location','plots.plot_name as car_plot_name',
                                    'cars.price as car_price','dealers.name as dealer_name','dealers.email as dealer_email',
                                    'dealers.company as dealer_company','dealers.profile as dealer_profile','dealers.mobile as dealer_mobile_no',
                                    'car_photos.photo1 as car_image1','car_photos.photo2 as car_image2','car_photos.photo3 as car_image3',
                                    'car_photos.photo4 as car_image4','car_photos.photo5 as car_image5'
                                    )
                                    ->first();
                                    // return $car_details;
                                    if(!empty($car_details))
                                    {
                                        // return $car_details->dealer_id;
                                        $featured_car = DB::table('cars')->leftjoin('dealers','dealers.id','=','cars.dealer_id')
                                                            ->leftjoin('bookings','bookings.car_id','=','cars.id')
                                                            ->leftjoin('locations','locations.id','=','bookings.location_id')
                                                            // ->leftjoin('brands','brands.id','=','cars.brand')
                                                            ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                                                            ->where('cars.is_featured','=','yes')
                                                            ->where('cars.dealer_id','=',$car_details->dealer_id)
                                                            ->select('cars.*','locations.name as location_name',
                                                            'car_photos.photo1 as car_image1','car_photos.photo2 as car_image2','car_photos.photo3 as car_image3',
                                                            'car_photos.photo4 as car_image4','car_photos.photo5 as car_image5')
                                                            ->get();
                                                            // return $featured_car;
                                        $car_details->list_of_featured_car = $featured_car;
                                        return response()->json([
                                            'status'    => 'Success',
                                            'message'   => trans('msg.user.car_details.success'),
                                            'data' => $car_details,
                                        ],200);
                                    }
                                    else
                                    {
                                        return response()->json([
                                            'status'    => 'Failed',
                                            'message'   => trans('msg.user.car_details.failure'),
                                        ],400);
                                    }
                                    
            }
            else
            {
                return response()->json([
                    'status'    => 'Failed',
                    'message'   => trans('msg.user.get-car.failure'),
                ],400);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
            ], 500);
        }
    }

    public function other_car_from_same_dealer(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
            'dealer_id' => ['required','alpha_dash', Rule::notIn('undefined')],
            
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
            $dealer = DB::table('dealers')->where('id',$req->dealer_id)->first();
            if(!empty($dealer))
            {
                $car = DB::table('bookings')->leftJoin('dealers','dealers.id','=','bookings.dealer_id')
                            ->leftJoin('cars','cars.id','=','bookings.car_id')
                            ->leftJoin('locations','locations.id','=','bookings.location_id')
                            ->leftJoin('plots','plots.id','=','bookings.plot_id')
                            ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                            ->where('bookings.dealer_id','=',$req->dealer_id)
                            ->select('bookings.*','cars.name as car_name','cars.condition as car_condition',
                            'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
                            'cars.fuel_type as car_fuel_type','cars.year_of_registration as car_year_of_registration',
                            'cars.kms_driven as car_kms_driven','cars.ownership as car_ownership',
                            'cars.insurance_validity as car_insurance_validity','cars.no_of_seats as car_seats',
                            'cars.milage as car_milage','cars.engin as car_engin','cars.description as car_description',
                            'cars.top_speed as car_speed','cars.color as car_color','locations.name as location_name',
                            'locations.location as car_location','plots.plot_name as car_plot_name',
                            'cars.price as car_price','dealers.name as dealer_name','dealers.email as dealer_email',
                            'dealers.company as dealer_company','dealers.profile as dealer_profile','dealers.mobile as dealer_mobile_no',
                            'car_photos.photo1 as car_image1','car_photos.photo2 as car_image2','car_photos.photo3 as car_image3',
                            'car_photos.photo4 as car_image4','car_photos.photo5 as car_image5')
                            ->get();
                            
                if(!empty($car))
                {
                    return response()->json([
                        'status'    => 'Success',
                        'message'   => trans('msg.user.car_details.success'),
                        'data' => $car,
                    ],200);
                }
                else
                {
                    return response()->json([
                        'status'    => 'Failed',
                        'message'   => trans('msg.user.car_details.failure'),
                    ],400);
                }   
                
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
            ], 500);
        }
    }


}
