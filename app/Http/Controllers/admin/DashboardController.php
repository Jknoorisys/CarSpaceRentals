<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    // By Javeriya Kauser
    public function dashboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'  => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {

            $data['tota_customers'] = DB::table('users')->where('is_verified', '=', 'yes')->where('is_admin', '!=', 'super_admin')->count();
            $data['tota_dealers']   = DB::table('dealers')->where('is_verified', '=', 'yes')->where('is_admin', '!=', 'super_admin')->count();
            $data['tota_locations'] = DB::table('locations')->count();
            $data['tota_plots']     = DB::table('plots')->count();

            $data['latest_dealers'] =  DB::table('dealers')->where('is_verified', '=', 'yes')->orderBy('created_at','desc')->take('10')->get();

            if (!empty($data)) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-location-details.success'),
                    'data'      => $data
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.get-location-details.failure'),
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

    // by Aaisha Shaikh
    public function TransactionHistory(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                    'status'    => 'failed',
                    'errors'    =>  $validator->errors(),
                    'message'   =>  __('msg.user.validation.fail'),
                ],400
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
            if (!empty($search)) {

                $transaction->where('locations.name', 'LIKE', "%$search%");
                $transaction->orWhere('dealers.name', 'LIKE', "%$search%");
                $transaction->orWhere('cars.name', 'LIKE', "%$search%");
                $transaction->orWhere('plots.plot_name', 'LIKE', "%$search%");
                $transaction->orWhere('park_in_date', $search);
                $transaction->orWhere('park_out_date', $search);
                
            }

            $total = $transaction->count();
            $data = $transaction->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get();

            foreach($data as $row){
                $plot_ids = $row->plot_ids;
                $car_ids = $row->car_id;

                $plot_id_array = explode(',', $plot_ids);
                $car_id_array = explode(',', $car_ids);

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

            if(!empty($transaction)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-payment-history.success'),
                    'total'     => $total,
                    'data'      => $data,
                ], 200);
            }else{
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
