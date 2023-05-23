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
            'dealer_id' => ['required','alpha_dash', Rule::notIn('undefined')],
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

            $search = $request->search ? $request->search : '';
            $status = $request->status ? $request->status : '';

            $dealer_id = $request->dealer_id;
            $dealer = validateDealer($dealer_id);
            if (empty($dealer) || $dealer->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-dealer'),
                ],400);
            }

            $db = DB::table('bookings as sc')
                        ->where('sc.dealer_id', '=', $dealer_id)
                        ->leftJoin('locations', 'locations.id', '=', 'sc.location_id')
                        ->leftJoin('plots', 'plots.id', '=', 'sc.plot_id')
                        ->leftJoin('cars', 'cars.id', '=', 'sc.car_id');

            if (!empty($search)) {
                $db->where('cars.name', 'LIKE', "%$search%");
                $db->orWhere('locations.name', 'LIKE', "%$search%");
            }

            if (!empty($status)) {
                $db->where('sc.status', '=', $status);
            }

            $total = $db->count();
            $plots = $db->orderBy('park_in_date')
                        ->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get(['locations.name as location_name', 'plots.plot_number as plot_number', 'cars.name as car_name', 'sc.*']);

            if (!($plots->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-dealer-plots.success'),
                    'total'     => $total,
                    'data'      => $plots
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-dealer-plots.failure'),
                    'data'      => []
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

    public function getDealerCars(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'  => 'required',
            'dealer_id' => ['required','alpha_dash', Rule::notIn('undefined')],
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
            $dealer_id = $request->dealer_id;

            $dealer = validateDealer($dealer_id);
            if (empty($dealer)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-dealer'),
                ],400);
            }

            $per_page = 10;
            $page_number = $request->input(key:'page_number', default:1);

            $db = DB::table('cars')->where('dealer_id', '=', $dealer_id);

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $db->where('name', 'LIKE', "%$search%");
            }

            $total = $db->count();
            $cars = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->orderBy('name')
                                    ->get();

            if (!($cars->isEmpty())) {

                foreach ($cars as $car) {
                    $car->photos = DB::table('car_photos')->where('car_id', '=', $car->id)->first(['id','car_id','photo1','photo2','photo3','photo4','photo5']);
                }

                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-dealer-cars.success'),
                    'total'     => $total,
                    'data'      => $cars
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-dealer-cars.failure'),
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

    public function getDealerLocations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'  => 'required',
            'dealer_id' => ['required','alpha_dash', Rule::notIn('undefined')],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {

            $dealer_id = $request->dealer_id;
            $dealer = validateDealer($dealer_id);
            if (empty($dealer) || $dealer->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-dealer'),
                ],400);
            }

            $locations = DB::table('bookings as sc')
                        ->where('sc.dealer_id', '=', $dealer_id)
                        ->leftJoin('locations', 'locations.id', '=', 'sc.location_id')
                        ->leftJoin('plots', 'plots.id', '=', 'sc.plot_id')
                        ->leftJoin('cars', 'cars.id', '=', 'sc.car_id')
                        ->distinct()
                        ->orderBy('park_in_date')
                        ->get(['locations.*']);

            if (!($locations->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-dealer-locations.success'),
                    'data'      => $locations
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-dealer-locations.failure'),
                    'data'      => []
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

    public function getPlotsBasedOnLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'  => 'required',
            'location_id' => ['required','alpha_dash', Rule::notIn('undefined')],
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
            $location = validateLocation($location_id);
            if (empty($location) || $location->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-location'),
                ],400);
            }

            $plots = DB::table('plots as sc')
                        ->where('sc.location_id', '=', $location_id)
                        ->orderBy('sc.plot_number')
                        ->get();

            if (!($plots->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-dealer-plots.success'),
                    'data'      => $plots
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-dealer-plots.failure'),
                    'data'      => []
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

    public function getDealerAllPlotsList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'  => 'required',
            'dealer_id' => ['required','alpha_dash', Rule::notIn('undefined')],
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

            $dealer_id = $request->dealer_id;
            $dealer = validateDealer($dealer_id);
            if (empty($dealer) || $dealer->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-dealer'),
                ],400);
            }

            $db = DB::table('bookings as sc')
                        ->where('sc.dealer_id', '=', $dealer_id)
                        ->leftJoin('plots', 'plots.id', '=', 'sc.plot_id');

            $total = $db->count();
            $plots = $db->orderBy('park_in_date')
                        ->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get(['plots.*']);

            if (!($plots->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-dealer-plots.success'),
                    'total'     => $total,
                    'data'      => $plots
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-dealer-plots.failure'),
                    'data'      => []
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

    public function getAvailablePlotsByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'      => 'required',
            'location_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
            'start_date'    => 'required|date',
            'duration_type' => ['required', Rule::in(['day', 'week', 'month', 'year'])],
            'duration'      => 'required|numeric'
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

            $location = validateLocation($location_id);
            if (empty($location) || $location->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-location'),
                ],400);
            }

            if (!empty($location)) {

                $duration_type = $request->duration_type;
                $duration = $request->duration;
                $start_date = Carbon::createFromFormat('Y-m-d', $request->start_date);
                
                if (!empty($duration_type) && $duration_type == 'day') {
                    $end_date = $start_date->addDays($duration);
                } elseif(!empty($duration_type) && $duration_type == 'week') {
                    $end_date = $start_date->addWeeks($duration);
                } elseif(!empty($duration_type) && $duration_type == 'month') {
                    $end_date = $start_date->addMonths($duration);
                } elseif(!empty($duration_type) && $duration_type == 'year') {
                    $end_date = $start_date->addYears($duration);
                } else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.dealer.get-available-plots.invalid-duration-type'),
                    ],400);
                }
                
                if (!empty($end_date)) {
                    $db = DB::table('bookings as sc');
                    
                    $availablePlots = $db->where([['location_id', '=' , $location_id], ['status', '=', 'active']])
                                         
                                        ->where(function ($query) use ($start_date, $end_date) {
                                            $query->whereDate('sc.park_in_date', '>=', $start_date)
                                                ->whereDate('sc.park_in_date', '<=', $end_date);
                                        })
                                         ->orderBy('park_in_date')
                                         ->get();

                                         return $availablePlots;
                }
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
