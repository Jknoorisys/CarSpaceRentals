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
use Ramsey\Uuid\Uuid;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class DashboardController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

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

            $data['tota_customers'] = DB::table('users')->where('is_verified', '=', 'yes')->count();
            $data['tota_dealers']   = DB::table('dealers')->where('is_verified', '=', 'yes')->count();
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
}
