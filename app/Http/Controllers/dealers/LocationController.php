<?php

namespace App\Http\Controllers\dealers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class LocationController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function getLocations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'  => 'required',
            'page_number' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $per_page = 10;
            $page_number = $request->input(key:'page_number', default:1);

            $db = DB::table('locations')->where('status', '=', 'active');

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $db->where('name', 'LIKE', "%$search%");
                $db->orWhere('location', 'LIKE', "%$search%");
            }

            $total = $db->count();
            $locations = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->get();

            if (!($locations->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-locations.success'),
                    'total'     => $total,
                    'data'      => $locations
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-locations.failure'),
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

    public function getLocationDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'  => 'required',
            'location_id' => ['required','alpha_dash', Rule::notIn('undefined')]
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {

            $location_id = $request->location_id;
            $locationDetails = DB::table('locations')->where([['id', '=', $location_id],['status', '=', 'active']])->first();

            if (!empty($locationDetails)) {
                $locationDetails->total_plots = DB::table('plots')->where('location_id', '=', $location_id)->count();
                $locationDetails->plots = DB::table('plots')->where('location_id', '=', $location_id)->orderBy('plot_number')->get();
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-location-details.success'),
                    'data'      => $locationDetails
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.dealer.get-location-details.failure'),
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

    public function getDealerPlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'  => 'required',
            'dealer_id' => ['required','alpha_dash', Rule::notIn('undefined')]
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {

            $location_id = $request->location_id;
            $locationDetails = DB::table('locations')->where([['id', '=', $location_id],['status', '=', 'active']])->first();

            if (!empty($locationDetails)) {
                $locationDetails->total_plots = DB::table('plots')->where('location_id', '=', $location_id)->count();
                $locationDetails->plots = DB::table('plots')->where('location_id', '=', $location_id)->get();
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-location-details.success'),
                    'data'      => $locationDetails
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.dealer.get-location-details.failure'),
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
