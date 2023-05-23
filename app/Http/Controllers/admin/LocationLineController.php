<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Locations;
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

class LocationLineController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function addLocationLine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'                  => 'required',
            'location_id'               => ['required','alpha_dash', Rule::notIn('undefined')],
            'lane_name'                 => 'required',
            'no_of_plots_in_left'       => 'required|numeric',
            'no_of_plots_in_right'      => 'required|numeric',
            'default_single_daily'      => 'required',
            'default_single_weekly'     => 'required',
            'default_single_monthly'    => 'required',
            'default_five_daily'        => 'required',
            'default_five_weekly'       => 'required',
            'default_five_monthly'      => 'required',
            'default_ten_daily'         => 'required',
            'default_ten_weekly'        => 'required',
            'default_ten_monthly'       => 'required',
            'admin_id'                  => ['required','alpha_dash', Rule::notIn('undefined')],
            'admin_type'                => ['required', Rule::in(['user', 'dealer'])],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {

            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }

            $location_id =  $request->location_id;

            $oldLocation = validateLocation($location_id);
            if (empty($oldLocation)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-location'),
                ],400);
            }

            $id                     = Str::uuid();
            $line_name              = $request->lane_name ? $request->lane_name : '';
            $no_of_plots_in_left    = $request->no_of_plots_in_left ? $request->no_of_plots_in_left : '';
            $no_of_plots_in_right   = $request->no_of_plots_in_right ? $request->no_of_plots_in_right : '';
            $default_single_daily   = $request->default_single_daily ? $request->default_single_daily : '';
            $default_single_weekly  = $request->default_single_weekly ? $request->default_single_weekly : '';
            $default_single_monthly = $request->default_single_monthly ? $request->default_single_monthly : '';
            $default_five_daily     = $request->default_five_daily ? $request->default_five_daily : '';
            $default_five_weekly    = $request->default_five_weekly ? $request->default_five_weekly : '';
            $default_five_monthly   = $request->default_five_monthly ? $request->default_five_monthly : '';
            $default_ten_daily      = $request->default_ten_daily ? $request->default_ten_daily : '';
            $default_ten_weekly     = $request->default_ten_weekly ? $request->default_ten_weekly : '';
            $default_ten_monthly    = $request->default_ten_monthly ? $request->default_ten_monthly : '';

            $laneData = [ 
                'id'                    => $id, 
                'location_id'           => $location_id, 
                'line_name'             => $line_name,
                'no_of_plots_in_left'   => $no_of_plots_in_left, 
                'no_of_plots_in_right'  => $no_of_plots_in_right,
                'default_single_daily'  => $default_single_daily,
                'default_single_weekly' => $default_single_weekly,
                'default_single_monthly'=> $default_single_monthly,
                'default_five_daily'    => $default_five_daily,
                'default_five_weekly'   => $default_five_weekly,
                'default_five_monthly'  => $default_five_monthly,
                'default_ten_daily'     => $default_ten_daily,
                'default_ten_weekly'    => $default_ten_weekly,
                'default_ten_monthly'   => $default_ten_monthly,
                'created_at'            => Carbon::now()
            ];

            $lane = DB::table('plot_lines')->insert($laneData);

            if ($lane) {

                for ($i=1; $i <= $no_of_plots_in_left; $i++) { 
                    $lefData = [
                        'id'                    => Str::uuid(), 
                        'location_id'           => $location_id, 
                        'line_id'               => $id,
                        'plot_name'             => $line_name.'L'.$i,
                        'plot_direction'        => 'left',
                        'plot_position'         => $i,
                        'single_daily'          => $default_single_daily,
                        'single_weekly'         => $default_single_weekly,
                        'single_monthly'        => $default_single_monthly,
                        'five_daily'            => $default_five_daily,
                        'five_weekly'           => $default_five_weekly,
                        'five_monthly'          => $default_five_monthly,
                        'ten_daily'             => $default_ten_daily,
                        'ten_weekly'            => $default_ten_weekly,
                        'ten_monthly'           => $default_ten_monthly,
                        'created_at'            => Carbon::now()
                    ];

                    $lefPlots = DB::table('plots')->insert($lefData);
                }

                for ($i=1; $i <= $no_of_plots_in_right; $i++) { 
                    $rightData = [
                        'id'                    => Str::uuid(), 
                        'location_id'           => $location_id, 
                        'line_id'               => $id,
                        'plot_name'             => $line_name.'R'.$i,
                        'plot_direction'        => 'right',
                        'plot_position'         => $i,
                        'single_daily'          => $default_single_daily,
                        'single_weekly'         => $default_single_weekly,
                        'single_monthly'        => $default_single_monthly,
                        'five_daily'            => $default_five_daily,
                        'five_weekly'           => $default_five_weekly,
                        'five_monthly'          => $default_five_monthly,
                        'ten_daily'             => $default_ten_daily,
                        'ten_weekly'            => $default_ten_weekly,
                        'ten_monthly'           => $default_ten_monthly,
                        'created_at'            => Carbon::now()
                    ];

                    $rightPlots = DB::table('plots')->insert($rightData);
                }

                $adminData = [
                    'id'        => Str::uuid(),
                    'user_id'   => $request->admin_id,
                    'user_type' => $request->admin_type,
                    'activity'  => 'Lane named '.$line_name.' is added by '.ucfirst($request->admin_type).' '.$admin->name,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];

                DB::table('admin_activities')->insert($adminData);

                $laneData['plotDetails'] = DB::table('plots')->where([['location_id', '=', $location_id],['line_id', '=', $id]])->orderBy('plot_name')->get();
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.add-line.success'),
                    'data'      => $laneData
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.add-line.failure'),
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

    public function changeLaneStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'    => 'required',
            'lane_id' => ['required','alpha_dash', Rule::notIn('undefined')],
            'status'      => ['required', 
                                Rule::in(['active', 'inactive'])
                            ],
            'admin_id'    => ['required','alpha_dash', Rule::notIn('undefined')],
            'admin_type'  => ['required', 
                Rule::in(['user', 'dealer'])
            ],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $line_id = $request->lane_id;
            $status = $request->status;

            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }
            
            $lane = DB::table('plot_lines')->where('id', '=', $line_id)->first();
            if (!empty($lane)) {
                $statusChange = DB::table('plot_lines')->where('id', '=', $line_id)->update(['status' => $status, 'updated_at' => Carbon::now()]);
                if ($statusChange) {
                    DB::table('plots')->where('line_id', '=', $line_id)->update(['status' => $status, 'updated_at' => Carbon::now()]);

                    $status == 'active' ? $msg = 'activated' : $msg = 'inactivated';
                    $adminData = [
                        'id'        => Str::uuid(),
                        'user_id'   => $request->admin_id,
                        'user_type' => $request->admin_type,
                        'activity'  => 'Lane Details ('.$lane->line_name.') is '.$msg.' by '.ucfirst($request->admin_type).' '.$admin->name,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
    
                    DB::table('admin_activities')->insert($adminData);

                    return response()->json([
                        'status'    => 'success',
                        'message'   => trans('msg.admin.lane-status.'.$status),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.admin.lane-status.failure'),
                    ],400);
                }
                
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.lane-status.invalid'),
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

    public function getAllLines(Request $request)
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

            $db = DB::table('plot_lines as sc')->leftJoin('locations', 'locations.id', '=', 'sc.location_id');

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $db->where('locations.name', 'LIKE', "%$search%");
                $db->orWhere('sc.line_name', 'LIKE', "%$search%");
            }

            $total = $db->count();
            $lines = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->orderBy('sc.line_name')
                                    ->get(['locations.name as location_name', 'sc.*']);

            if (!($lines->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-lines.success'),
                    'total'     => $total,
                    'data'      => $lines
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-lines.failure'),
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

    public function getLineDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'  => 'required',
            'lane_id'   => ['required','alpha_dash', Rule::notIn('undefined')]
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {

            $line_id = $request->lane_id;
            $lineDetails = DB::table('plot_lines')->where('id', '=', $line_id)->first();

            if (!empty($lineDetails)) {
                $lineDetails->plots = DB::table('plots')->where('line_id', '=', $line_id)->orderBy('plot_name')->get();
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-line-details.success'),
                    'data'      => $lineDetails
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.get-line-details.failure'),
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

    public function editPlotDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'          => 'required',
            'plot_id'           => ['required','alpha_dash', Rule::notIn('undefined')],
            'plot_name'         => 'required',
            'single_daily'      => 'required',
            'single_weekly'     => 'required',
            'single_monthly'    => 'required',
            'five_daily'        => 'required',
            'five_weekly'       => 'required',
            'five_monthly'      => 'required',
            'ten_daily'         => 'required',
            'ten_weekly'        => 'required',
            'ten_monthly'       => 'required',
            'admin_id'          => ['required','alpha_dash', Rule::notIn('undefined')],
            'admin_type'        => ['required', Rule::in(['user', 'dealer'])],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {

            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }

            $plot_id = $request->plot_id;

            $plotDetails = validatePlot($plot_id);
            if (empty($plotDetails)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-plot'),
                ],400);
            }

           $plot_name = $request->plot_name ? $request->plot_name : ''; 
           $single_daily = $request->single_daily ? $request->single_daily : ''; 
           $single_weekly = $request->single_weekly ? $request->single_weekly : ''; 
           $single_monthly = $request->single_monthly ? $request->single_monthly : ''; 
           $five_daily = $request->five_daily ? $request->five_daily : ''; 
           $five_weekly = $request->five_weekly ? $request->five_weekly : ''; 
           $five_monthly = $request->five_monthly ? $request->five_monthly : ''; 
           $ten_daily = $request->ten_daily ? $request->ten_daily : ''; 
           $ten_weekly = $request->ten_weekly ? $request->ten_weekly : ''; 
           $ten_monthly = $request->ten_monthly ? $request->ten_monthly : ''; 

           $plotData = [
                'plot_name'     => $plot_name,
                'single_daily'  => $single_daily,
                'single_weekly' => $single_weekly,
                'single_monthly'=> $single_monthly,
                'five_daily'    => $five_daily,
                'five_weekly'   => $five_weekly,
                'five_monthly'  => $five_monthly,
                'ten_daily'     => $ten_daily,
                'ten_weekly'    => $ten_weekly,
                'ten_monthly'   => $ten_monthly,
                'updated_at'    => Carbon::now()
           ];

           $plot = DB::table('plots')->where('id', '=', $plot_id)->update($plotData);

           $adminData = [
                'id'        => Str::uuid(),
                'user_id'   => $request->admin_id,
                'user_type' => $request->admin_type,
                'activity'  => 'Plot named '.$plotDetails->plot_name.' is Updated by '.ucfirst($request->admin_type).' '.$admin->name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            DB::table('admin_activities')->insert($adminData);

           if ($plot) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.edit-plot.success'),
                ],200);
           } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.edit-plot.failure'),
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
