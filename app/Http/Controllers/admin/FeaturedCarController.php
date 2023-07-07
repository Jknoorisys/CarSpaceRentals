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

    // By Aaisha Shaikh
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
                        ->join('car_photos','car_photos.car_id','=','cars.id')
                        ->select('featured_cars.*','cars.name as car_name','cars.condition as car_condition',
                        'cars.year_of_manufacturing as car_manufacture_year','cars.type as car_type',
                        'cars.fuel_type as car_fuel_type','cars.price as car_price','dealers.name as dealer_name',
                        'car_photos.photo1 as car_image1','car_photos.photo2 as car_image2','car_photos.photo3 as car_image3',
                        'car_photos.photo4 as car_image4','car_photos.photo5 as car_image5');

            $search = $req->search ? $req->search : ''; 
            if (!empty($search)) {
                $db->where('cars.name', 'LIKE', "%$search%");
                $db->orWhere('dealers.name', 'LIKE', "%$search%");
            }

            $total = $db->count();
            $brands = $db->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->orderby('featured_cars.created_at','desc')
                        ->get();

            if (!($brands->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.get-featured-car.success'),
                    'total'     => $total,
                    'data'      => $brands
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.user.get-featured-car.failure'),
                    'data'      => [],
                ],200);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'     => $e->getMessage()
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
                        ->join('cars','cars.id','=','featured_cars.car_id')
                        ->join('car_photos','car_photos.car_id','=','cars.id')
                        ->where('featured_cars.car_id',$req->car_id)
                        ->select('featured_cars.*','cars.name as car_name','dealers.name as dealer_name',
                        'car_photos.photo1 as car_image1','car_photos.photo2 as car_image2','car_photos.photo3 as car_image3',
                        'car_photos.photo4 as car_image4','car_photos.photo5 as car_image5')->first();

            if(!empty($car))
            {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-featured-car-details.success'),
                    'data'      => $car
                ],200);
            }else{
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-featured-car-details.failed'),
                    'data'      => []
                ],200);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'     => $e->getMessage()
            ], 500);
        }
    }

    public function TransactionHistory(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',

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
            
            $transaction = DB::table('payment_histories')
            ->leftJoin('dealers','dealers.id','=','payment_histories.dealer_id')
            ->leftJoin('cars','cars.id','=','payment_histories.car_id')
            ->leftJoin('locations','locations.id','=','payment_histories.location_id')
            ->leftJoin('plot_lines','plot_lines.id','=','payment_histories.line_id')
            ->leftJoin('plots','plots.id','=','payment_histories.plot_ids')
            ->select('payment_histories.*','dealers.name as dealer_name',
            'locations.name as location_name','plot_lines.line_name as line_name');

            
            $search = $req->search ? $req->search : '';
            $startDate = $req->start_date ? $req->start_date : '';
            $endDate = $req->end_date ? $req->end_date : '';
            if(!empty($startDate && $endDate))
            {
                $transaction->whereDate('payment_histories.park_in_date','>=',$startDate);
                $transaction->whereDate('payment_histories.park_out_date','<=',$endDate);
                $transaction->orwhereDate('payment_histories.created_at','>=',$startDate);
                $transaction->orwhereDate('payment_histories.created_at','<=',$endDate);
            }
            if (!empty($search)) {

                $transaction->where('locations.name', 'LIKE', "%$search%");
                $transaction->orWhere('dealers.name', 'LIKE', "%$search%");
                $transaction->orWhere('cars.name', 'LIKE', "%$search%");
                $transaction->orWhere('plots.plot_name', 'LIKE', "%$search%");
                $transaction->orWhere('park_in_date', $search);
                $transaction->orWhere('park_out_date', $search);
                $transaction->orWhereDate('payment_histories.created_at','=', $search);
                
            }
            $total = $transaction->count();
            $data = $transaction->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get();
        // return $data;
        foreach($data as $row)
        {
            // return $row->car_id;

            $plot_ids = $row->plot_ids;
            $car_ids = $row->car_id;
            // return $car_ids;
            $plot_id_array = explode(',', $plot_ids);
            $car_id_array = explode(',', $car_ids);
            // return $car_id_array;
            $plots = [];
            $cars = [];
            foreach($plot_id_array as $plot_id)
            {
                $plot = DB::table('plots')->where('id', $plot_id)->select('plot_name')->first();
                if ($plot) {
                    $plots[] = $plot->plot_name;
                }
            }
            foreach($car_id_array as $cid)
            {
                $car = DB::table('cars')->where('id', $cid)->select('name')->first();
                if ($car) {
                    $cars[] = $car->name;
                }
            }
        
            $row->plots = $plots;
            $row->cars = $cars;
            



        }
        

        if(!empty($transaction))
        {
            return response()->json([
                'status'    => 'success',
                'message'   => trans('msg.admin.get-payment-history.success'),
                'total'     => $total,
                'data'      => $data,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.admin.get-payment-history.failed'),
                
            ], 400);
        }    

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' =>  __('msg.user.error'),
                'error'     => $e->getMessage()
            ], 500);
        }
    }

}
