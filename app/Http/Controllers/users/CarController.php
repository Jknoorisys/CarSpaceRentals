<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Dealers;
use Illuminate\Support\Facades\App;

class CarController extends Controller
{
    public function __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    // By Aaisha Shaikh

    // Not Required
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
                'error'   => $e->getMessage()
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
                'error'   => $e->getMessage()
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
            $search = $req->search ? $req->search : '';

            $db = DB::table('cars')->leftJoin('dealers','dealers.id','=','cars.dealer_id')
                                    ->leftJoin('bookings','bookings.car_id','=','cars.id')
                                    ->leftJoin('locations','bookings.location_id','=','locations.id')
                                    ->leftJoin('brands','brands.id','=','cars.brand')
                                    ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                                    ->where('cars.is_assgined','=','yes')
                                    ->select('cars.*','dealers.name as dealer_name','brands.name as brand_name',
                                'locations.name as location_name','car_photos.photo1 as car_image',
                                'locations.lat as location_latitude','locations.long as location_longitude',
                                'locations.location as location_address');

                                if (!empty($condition)) 
                                {
                                    $db->where('cars.condition',$condition);
                                }

                                if(!empty($type))
                                {
                                    $t = explode(',',$type);
                                    $db->whereIn('cars.type',$t);
                                }

                                if(!empty($year))
                                {
                                    $db->where('cars.year_of_manufacturing', 'LIKE', "%$year%");
                                }

                                if(!empty($fuel_type))
                                {
                                    $fuel = explode(',',$fuel_type);
                                    $db->whereIn('cars.fuel_type',$fuel);
                                }

                                if(!empty($brand))
                                {
                                    $b = explode(',',$brand);
                                    $db->whereIn('cars.brand',$b);
                                }
                                
                                if(!empty($price))
                                {
                                    $db->where('cars.price', 'LIKE', "%$price%");
                                }
                                
                                if (!empty($search)) {

                                    $db->where('cars.name', 'LIKE', "%$search%");
                                }

                                $total = $db->count();

                                $filter = $db->offset(($page_number - 1) * $per_page)
                                            ->limit($per_page)
                                            ->get();
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
                                    // ->get();
                                    // return $db;
            // $bookings = DB::table('bookings')->where('bookings.status','=','active')->get();
            // // return $bookings->car_id;
            // // if(!empty($bookings['car_id']))
            // $bookedCars = [];
            // foreach($bookings as $row)
            // {
            //     $car_id = $row->car_id;
            //     if(!empty($car_id))
            //     {
            //         $db = DB::table('bookings')->leftJoin('plots','plots.id','=','bookings.plot_id')
            //                                                 ->leftJoin('locations','locations.id','=','bookings.location_id')
            //                                                 ->leftJoin('dealers','dealers.id','=','bookings.dealer_id')
            //                                                 ->leftJoin('cars','cars.id','=','bookings.car_id')
            //                                                 ->leftJoin('brands','brands.id','=','cars.brand')
            //                                                 ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
            //                                                 ->where('bookings.status','=','active')
            //                                                 ->where('bookings.car_id','=',$car_id)
            //                                                 ->select('bookings.*','cars.name as car_name','cars.condition as car_condition',
            //                                                 'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
            //                                                 'cars.fuel_type as car_fuel_type','cars.price as car_price','locations.name as location_name',
            //                                                 'locations.lat as location_latitude','locations.long as location_longitude','locations.location as location_address',
            //                                                 'brands.name as car_brand_name','car_photos.photo1 as car_image');
                
            //                                                 if (!empty($condition)) 
            //                     {
            //                         $db->where('cars.condition',$condition);
            //                     }

            //                     if(!empty($type))
            //                     {
            //                         $t = explode(',',$type);
            //                         $db->whereIn('cars.type',$t);
            //                     }

            //                     if(!empty($year))
            //                     {
            //                         $db->where('cars.year_of_manufacturing', 'LIKE', "%$year%");
            //                     }

            //                     if(!empty($fuel_type))
            //                     {
            //                         $fuel = explode(',',$fuel_type);
            //                         $db->whereIn('cars.fuel_type',$fuel);
            //                     }

            //                     if(!empty($brand))
            //                     {
            //                         $b = explode(',',$brand);
            //                         $db->whereIn('cars.brand',$b);
            //                     }
                                
            //                     if(!empty($price))
            //                     {
            //                         $db->where('cars.price', 'LIKE', "%$price%");
            //                     }
                                
            //                     if (!empty($search)) {

            //                         $db->where('cars.name', 'LIKE', "%$search%");
            //                     }

            //                     $total = $db->count();

            //                     $filter = $db->offset(($page_number - 1) * $per_page)
            //                                 ->limit($per_page)
            //                                 ->first();

                                            
            //                     $bookedCars[] = $filter;
                                


            //     }
                
            // }
            // if ($bookedCars) {
            //     return response()->json([
            //         'status'    => 'success',
            //         'message'   => trans('msg.user.get-car.success'),
            //         'total'     => $total,
            //         'data'      => $bookedCars
            //     ],200);
            // } else {
            //     return response()->json([
            //         'status'    => 'success',
            //         'message'   => trans('msg.user.get-car.failure'),
            //         'data'      => [],
            //     ],200);
            // }

            
        //    return $db->car_id;
            
                // $db =DB::table('bookings')->leftJoin('plots','plots.id','=','bookings.plot_id')
                //                         ->leftJoin('locations','locations.id','=','bookings.location_id')
                //                         ->leftJoin('dealers','dealers.id','=','bookings.dealer_id')
                //                         ->leftJoin('cars','cars.id','=','bookings.car_id')
                //                         ->leftJoin('brands','brands.id','=','cars.brand')
                //                         ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                //                         ->where('bookings.status','=','active')
                //                         ->select('bookings.*','cars.name as car_name','cars.condition as car_condition',
                //                         'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
                //                         'cars.fuel_type as car_fuel_type','cars.price as car_price','locations.name as location_name',
                //                         'locations.lat as location_latitude','locations.long as location_longitude','locations.location as location_address',
                //                         'brands.name as car_brand_name','car_photos.photo1 as car_image');

                           

                
            
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'   => $e->getMessage()
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
            
            $db = DB::table('cars')->leftjoin('dealers','dealers.id','=','cars.dealer_id')
                        ->leftjoin('bookings','bookings.car_id','=','cars.id')
                        ->leftjoin('locations','locations.id','=','bookings.location_id')
                        ->leftjoin('brands','brands.id','=','cars.brand')
                        ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                        ->where('cars.is_featured','=','yes')
                        ->where('cars.is_assgined','=','yes')
                        ->select('cars.*','locations.name as location_name','locations.lat as location_latitude',
                        'locations.long as location_longitude','locations.location as location_address',
                        'brands.name as brand_name','car_photos.photo1 as car_image');
                        
            
            $total = $db->count();

            $featured = $db->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get();

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
                'error'   => $e->getMessage()
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
            $car = DB::table('cars')->where('id', '=', $req->car_id)->first();
            if(!empty($car))
            {
                $car_details = DB::table('cars')->leftJoin('bookings','bookings.car_id','=','cars.id')
                                    ->leftJoin('locations','locations.id','=','bookings.location_id')
                                    ->leftJoin('dealers','dealers.id','=','cars.dealer_id')
                                    ->leftJoin('plots','plots.id','=','bookings.plot_id')
                                    ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                                    ->where('cars.id','=',$req->car_id)
                                    ->where('cars.is_assgined','=','yes')
                                    ->select('cars.*','locations.name as location_name',
                                    'locations.location as car_location','locations.lat as location_latitude',
                                    'locations.long as location_longitude','plots.plot_name as car_plot_name',
                                    'dealers.name as dealer_name','dealers.email as dealer_email',
                                    'dealers.company as dealer_company','dealers.profile as dealer_profile','dealers.mobile as dealer_mobile_no',
                                    'car_photos.photo1 as car_image1','car_photos.photo2 as car_image2','car_photos.photo3 as car_image3',
                                    'car_photos.photo4 as car_image4','car_photos.photo5 as car_image5'
                                    )->first();

                if(!empty($car_details)){
                    return response()->json([
                        'status'    => 'Success',
                        'message'   => trans('msg.user.car_details.success'),
                        'data' => $car_details,
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'Failed',
                        'message'   => trans('msg.user.car_details.failure'),
                    ],400);
                }
                                    
            }else{
                return response()->json([
                    'status'    => 'Failed',
                    'message'   => trans('msg.user.get-car.failure'),
                ],400);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'   => $e->getMessage()
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
            $car = DB::table('cars')->where('id','=', $req->car_id)->first();

            if(!empty($car))
            {
                $car_details = DB::table('cars')->leftJoin('bookings','bookings.car_id','=','cars.id')
                                    ->leftJoin('dealers','dealers.id','=','bookings.dealer_id')
                                    ->leftJoin('brands','brands.id','=','cars.brand')
                                    ->leftJoin('plots','plots.id','=','bookings.plot_id')
                                    ->leftjoin('locations','locations.id','=','bookings.location_id')
                                    ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                                    ->where('cars.id','=',$req->car_id)
                                    ->where('cars.is_assgined','=','yes')
                                    ->select('cars.*','locations.name as location_name',
                                    'locations.location as car_location','locations.lat as location_latitude',
                                    'locations.long as location_longitude','plots.plot_name as car_plot_name',
                                    'dealers.name as dealer_name','dealers.email as dealer_email',
                                    'dealers.company as dealer_company','dealers.profile as dealer_profile',
                                    'dealers.mobile as dealer_mobile_no','brands.name as brand_name',
                                    'car_photos.photo1 as car_image1','car_photos.photo2 as car_image2',
                                    'car_photos.photo3 as car_image3',
                                    'car_photos.photo4 as car_image4','car_photos.photo5 as car_image5'
                                    )->first();

                if(!empty($car_details)){
                    $featured_car = DB::table('cars')->leftjoin('dealers','dealers.id','=','cars.dealer_id')
                                        ->leftjoin('bookings','bookings.car_id','=','cars.id')
                                        ->leftjoin('locations','locations.id','=','bookings.location_id')
                                        ->leftjoin('brands','brands.id','=','cars.brand')
                                        ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                                        ->where('cars.is_featured','=','yes')
                                        ->where('cars.is_assgined','=','yes')
                                        ->where('cars.dealer_id','=',$car_details->dealer_id)
                                        ->select('cars.*','locations.name as location_name','brands.name as brand_name',
                                        'car_photos.photo1 as car_image1','car_photos.photo2 as car_image2','car_photos.photo3 as car_image3',
                                        'car_photos.photo4 as car_image4','car_photos.photo5 as car_image5')
                                        ->get();

                    $car_details->list_of_featured_car = $featured_car;
                    return response()->json([
                        'status'    => 'Success',
                        'message'   => trans('msg.user.car_details.success'),
                        'data' => $car_details,
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'Failed',
                        'message'   => trans('msg.user.car_details.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'Failed',
                    'message'   => trans('msg.user.get-car.failure'),
                ],400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'   => $e->getMessage()
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
            $dealer = DB::table('dealers')->where('id', '=', $req->dealer_id)->first();
            if(!empty($dealer))
            {
                $car = DB::table('cars')->leftJoin('dealers','dealers.id','=','cars.dealer_id')
                            ->leftJoin('bookings','bookings.car_id','=','cars.id')
                            ->leftJoin('brands','brands.id','=','cars.brand')
                            ->leftJoin('locations','locations.id','=','bookings.location_id')
                            ->leftJoin('plots','plots.id','=','bookings.plot_id')
                            ->leftJoin('car_photos','car_photos.car_id','=','cars.id')
                            ->where('cars.dealer_id','=',$req->dealer_id)
                            // ->where('cars.is_featured','=','yes')
                            ->where('cars.is_assgined','=','yes')
                            ->select('cars.*','locations.name as location_name','locations.lat as location_latitude',
                            'locations.long as location_longitude',
                            'locations.location as car_location','plots.plot_name as car_plot_name',
                            'dealers.name as dealer_name','dealers.email as dealer_email',
                            'dealers.company as dealer_company','dealers.profile as dealer_profile','dealers.mobile as dealer_mobile_no','brands.name as brand_name',
                            'car_photos.photo1 as car_image1','car_photos.photo2 as car_image2','car_photos.photo3 as car_image3',
                            'car_photos.photo4 as car_image4','car_photos.photo5 as car_image5')
                            ->get();
                            
                if(!($car->isEmpty())){
                    return response()->json([
                        'status'    => 'Success',
                        'message'   => trans('msg.user.car_details.success'),
                        'data'      => $car,
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'Failed',
                        'message'   => trans('msg.user.car_details.failure'),
                        'data'      => [],
                    ],400);
                }                   
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getCarBrands(Request $request)
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
           $db = DB::table('brands');

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $db->where('name', 'LIKE', "%$search%");
            }
            
            $total = $db->count();

            $brands = $db->orderBy('name')
                        ->get();

            if (!($brands->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-brands.success'),
                    'total'     => $total,
                    'data'      => $brands
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-brands.failure'),
                    'data'      => [],
                ],200);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function getUserIpAddress(Request $request)
    {
        $userIpAddress = $request->ip();
        
        // You can use $userIpAddress for further processing
        
        return $userIpAddress;
    }
}
