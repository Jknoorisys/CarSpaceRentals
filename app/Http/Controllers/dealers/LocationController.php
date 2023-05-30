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
    
    public function getLocationList(Request $request)
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
           
            $locations = DB::table('locations')
                        ->where('status', '=', 'active')
                        ->orderBy('name')
                        ->get();

            if (!($locations->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-locations.success'),
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

    public function getLinesBasedOnLocations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'  => 'required',
            'location_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
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

            $lines = DB::table('plot_lines')
                        ->where([['location_id', '=', $location_id],['status', '=', 'active']])
                        ->orderBy('line_name')
                        ->get();

            if (!($lines->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-lines.success'),
                    'data'      => $lines
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-lines.failure'),
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

    public function getAvailablePlotsByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'      => 'required',
            'location_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
            'lane_id'       => ['required','alpha_dash', Rule::notIn('undefined')],
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
            $line_id     = $request->lane_id ? $request->lane_id : '';

            $location = validateLocation($location_id);
            if (empty($location) || $location->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-location'),
                ],400);
            }

            $line = validateLine($line_id);
            if (empty($line) || $line->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-line'),
                ],400);
            }

            $duration_type = $request->duration_type;
            $duration = $request->duration;
            $start_date = Carbon::createFromFormat('Y-m-d', $request->start_date);
            $end_date = $start_date->copy(); 

            if (!empty($duration_type) && $duration_type == 'day') {
                $dayDuration = $duration - 1;
                $end_date->addDays($dayDuration);
            } elseif (!empty($duration_type) && $duration_type == 'week') {
                $end_date->addWeeks($duration);
            } elseif (!empty($duration_type) && $duration_type == 'month') {
                $end_date->addMonths($duration);
            } elseif (!empty($duration_type) && $duration_type == 'year') {
                $end_date->addYears($duration);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => trans('msg.dealer.get-available-plots.invalid-duration-type'),
                ], 400);
            }

            if (!empty($end_date)) {
                $start_date_formatted = $start_date->format('Y-m-d');
                $end_date_formatted = $end_date->format('Y-m-d');
                
                $db = DB::table('plots as sc')->where('sc.location_id', '=' , $location_id)->where('sc.line_id', '=' , $line_id);

                $selected_duration = [
                    'park_in_date'  => $start_date_formatted,
                    'park_out_date' => $end_date_formatted,
                    'duration_type' => $duration_type,
                    'duration'      => $duration
                ];

                $total = $db->count();
                $locationPlots = $db->orderBy('sc.plot_position')->get();
                $availablePlots = [];
                foreach ($locationPlots as $plot) {
                    $bookedPlots = DB::table('bookings as sc')
                                        ->where('sc.plot_id', '=' , $plot->id)
                                        ->where('sc.location_id', '=' , $location_id)
                                        ->where('sc.line_id', '=' , $line_id)
                                        ->leftJoin('plots', 'plots.id', '=', 'sc.plot_id')
                                        ->whereIn('sc.status', ['active', 'upcoming'])
                                        ->where(function ($query) use ($start_date_formatted, $end_date_formatted) {
                                            $query->where(function ($query) use ($start_date_formatted, $end_date_formatted) {
                                                $query->where('sc.park_in_date', '>=', $start_date_formatted)
                                                    ->where('sc.park_in_date', '<=', $end_date_formatted);
                                            })->orWhere(function ($query) use ($start_date_formatted, $end_date_formatted) {
                                                $query->where('sc.park_out_date', '>=', $start_date_formatted)
                                                    ->where('sc.park_out_date', '<=', $end_date_formatted);
                                            })->orWhere(function ($query) use ($start_date_formatted, $end_date_formatted) {
                                                $query->where('sc.park_in_date', '<=', $start_date_formatted)
                                                    ->where('sc.park_out_date', '>=', $end_date_formatted);
                                            });
                                        })
                                        ->first(['plots.*', DB::raw("'booked' as status")]);

                    if (empty($bookedPlots)) {
                        $plot->status = 'available';
                        $availablePlots[] = $plot;
                    } else {
                        $availablePlots[] = $bookedPlots;
                    }
                }
            }
                
            if (!empty($availablePlots)) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-available-plots.success'),
                    'total'     => $total,
                    'data'      => ['selected_duration' => $selected_duration, 'lane_details' => $line, 'plots' => $availablePlots]
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.dealer.get-available-plots.failure'),
                    'data'      => []
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

    public function getSelectedPlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'      => 'required',
            'plot_ids'      => ['required', Rule::notIn('undefined')],
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
            $duration_type = $request->duration_type;
            $duration = $request->duration;

            $plot_ids =  explode(',',$request->plot_ids);
            if (!empty($plot_ids)) {
                $total_selected_plots = count($plot_ids);
                $total_amount = 0;

                foreach ($plot_ids as $plot_id) {
                    $plot = DB::table('plots')->where('id', '=', $plot_id)->first();
                    $selected_plot = [];
                    $selected_plot['plot_name'] = $plot ? $plot->plot_name : '';

                    if ($total_selected_plots < 5) {
                        if ($duration_type == 'day') {
                            $selected_plot['plot_price'] = $plot ? $plot->single_daily : 0;
                            $total_amount = $total_amount + ($plot ? $plot->single_daily : 0) ;
                        } elseif($duration_type == 'week') {
                            $selected_plot['plot_price'] = $plot ? $plot->single_weekly : 0;
                            $total_amount = $total_amount + ($plot ? $plot->single_weekly : 0) ;
                        } else {
                            $selected_plot['plot_price'] = $plot ? $plot->single_monthly : 0;
                            $total_amount = $total_amount + ($plot ? $plot->single_monthly : 0) ;
                        }
                    } elseif($total_selected_plots >= 5 && $total_selected_plots < 10) {
                        if ($duration_type == 'day') {
                            $selected_plot['plot_price'] = $plot ? $plot->five_daily : 0;
                            $total_amount = $total_amount + ($plot ? $plot->five_daily : 0) ;
                        } elseif($duration_type == 'week') {
                            $selected_plot['plot_price'] = $plot ? $plot->five_weekly : 0;
                            $total_amount = $total_amount + ($plot ? $plot->five_weekly : 0) ;
                        } else {
                            $selected_plot['plot_price'] = $plot ? $plot->five_monthly : 0;
                            $total_amount = $total_amount + ($plot ? $plot->five_monthly : 0) ;
                        }
                    } else{
                        if ($duration_type == 'day') {
                            $selected_plot['plot_price'] = $plot ? $plot->ten_daily : 0;
                            $total_amount = $total_amount + ($plot ? $plot->ten_daily : 0) ;
                        } elseif($duration_type == 'week') {
                            $selected_plot['plot_price'] = $plot ? $plot->ten_weekly : 0;
                            $total_amount = $total_amount + ($plot ? $plot->ten_weekly : 0) ;
                        } else {
                            $selected_plot['plot_price'] = $plot ? $plot->ten_monthly : 0;
                            $total_amount = $total_amount + ($plot ? $plot->ten_monthly : 0) ;
                        }
                    }

                    $selected_plots[] = $selected_plot;
                }

                $total_price = $total_amount * $duration;

                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.dealer.get-selected-plots.success'),
                    'data'      => ['selected_plots' => $selected_plots, 'duration' => $duration.' ' . $duration_type.'s', 'total_price' => $total_price]
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.dealer.get-selected-plots.failure'),
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
                        ->whereIn('sc.status', ['active', 'upcoming'])
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
                        ->get(['locations.name as location_name', 'plots.plot_name as plot_name', 'cars.name as car_name', 'sc.*']);

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
                    $car->location = DB::table('bookings')->where('car_id', '=', $car->id)->leftJoin('locations','locations.id','=','bookings.location_id')->first(['locations.*']);
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
                        ->whereIn('sc.status', ['active', 'upcoming'])
                        ->leftJoin('locations', 'locations.id', '=', 'sc.location_id')
                        ->distinct()
                        ->orderBy('locations.name')
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

            $plots = DB::table('bookings as sc')
                            ->where('sc.location_id', '=', $location_id)
                            ->whereIn('sc.status', ['active', 'upcoming'])
                            ->where('sc.car_id', '=', '')
                            ->leftJoin('plots', 'plots.id', '=', 'sc.plot_id')
                            ->distinct()
                            ->orderBy('plots.plot_position')
                            ->get(['plots.*','sc.id as booking_id']);

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

    // Not Required
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

}
