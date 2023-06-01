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

class LocationController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function addLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'             => 'required',
            'name'                 => 'required',
            'lat'                  => 'required',
            'long'                 => 'required',
            'location'             => 'required',
            'layout'               => 'required|image|mimes:jpeg,png,jpg,svg,pdf',
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

            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }

            $id         = Str::uuid();
            $name       = $request->name ? $request->name : '';
            $lat        = $request->lat ? $request->lat : '';
            $long       = $request->long ? $request->long : '';
            $location   = $request->location ? $request->location : '';

            $file = $request->file('layout');
            if ($file) {
                $extension = $file->getClientOriginalExtension();
                $filename = time().'.'.$extension;
                $file->move('assets/uploads/location-photos/', $filename);
                $layout = 'assets/uploads/location-photos/'.$filename  ;
            }

            $locationData = [ 
                'id'            => $id, 
                'name'          => $name,
                'lat'           => $lat, 
                'long'          => $long,
                'location'      => $location,
                'layout'        => $request->file('layout') ? $layout : '',
                'created_at'    => Carbon::now()
            ];

            $location = DB::table('locations')->insert($locationData);

            $adminData = [
                'id'        => Str::uuid(),
                'user_id'   => $request->admin_id,
                'user_type' => $request->admin_type,
                'activity'  => 'Rental Location named '.$name.' is added by '.ucfirst($request->admin_type).' '.$admin->name,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            $admin_activity = DB::table('admin_activities')->insert($adminData);
            if($location && $admin_activity){
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.add-location.success'),
                    'data'      => $locationData
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.add-location.failure'),
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

            $db = DB::table('locations');

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $db->where('name', 'LIKE', "%$search%");
                $db->orWhere('location', 'LIKE', "%$search%");
            }

            $total = $db->count();
            $locations = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->orderBy('name')
                                    ->get();

            if (!($locations->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-locations.success'),
                    'total'     => $total,
                    'data'      => $locations
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-locations.failure'),
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
            $locationDetails = DB::table('locations')->where('id', '=', $location_id)->first();

            if (!empty($locationDetails)) {

                $start_date = Carbon::today()->format('Y-m-d');
                $end_date = Carbon::today()->format('Y-m-d');
                $locationLines = DB::table('plot_lines')->where('location_id', '=' , $location_id)->orderBy('created_at')->get();
                $total_lines = DB::table('plot_lines')->where('location_id', '=' , $location_id)->count();
                $availableLines = [];

                foreach ($locationLines as $line) {
                    // $availableLines[] = $line->line_name;
                    $availablePlots = [];

                    $locationPlots = DB::table('plots')->where('location_id', '=' , $location_id)->orderBy('plot_direction')->orderBy('plot_position')->where('line_id', '=' , $line->id)->get();
                    $total_plots   = DB::table('plots')->where('location_id', '=' , $location_id)->count();

                    foreach ($locationPlots as $plot) {
                        $bookedPlots = DB::table('bookings as sc')
                                            ->where('sc.plot_id', '=' , $plot->id)
                                            ->where('sc.location_id', '=' , $location_id)
                                            ->where('sc.line_id', '=' , $line->id)
                                            ->leftJoin('plots', 'plots.id', '=', 'sc.plot_id')
                                            ->whereIn('sc.status', ['active', 'upcoming'])
                                            ->where(function ($query) use ($start_date, $end_date) {
                                                $query->where(function ($query) use ($start_date, $end_date) {
                                                    $query->where('sc.park_in_date', '>=', $start_date)
                                                        ->where('sc.park_in_date', '<=', $end_date);
                                                })->orWhere(function ($query) use ($start_date, $end_date) {
                                                    $query->where('sc.park_out_date', '>=', $start_date)
                                                        ->where('sc.park_out_date', '<=', $end_date);
                                                })->orWhere(function ($query) use ($start_date, $end_date) {
                                                    $query->where('sc.park_in_date', '<=', $start_date)
                                                        ->where('sc.park_out_date', '>=', $end_date);
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
                    // $availableLines[] = $availablePlots;
                    $availableLines[] = [
                        'lane' => $line->line_name,
                        'plots' => $availablePlots,
                    ];
                }                
                
                $locationDetails->details = $availableLines;
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-location-details.success'),
                    'total_lines' => $locationDetails->details ? $total_lines : 0,
                    'total_plots' => $locationDetails->details ? $total_plots : 0,
                    'data'      => $locationDetails
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

    public function getLocation(Request $request)
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
            $locationDetails = DB::table('locations')->where('id', '=', $location_id)->first();

            if (!empty($locationDetails)) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-location-details.success'),
                    'data'      => $locationDetails
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

    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'             => 'required',
            'location_id'          => ['required','alpha_dash', Rule::notIn('undefined')],
            'name'                 => 'required',
            'lat'                  => 'required',
            'long'                 => 'required',
            'location'             => 'required',
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

            $location_id            = $request->location_id;

            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }

            $oldLocation = validateLocation($location_id);
            if (empty($oldLocation)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-location'),
                ],400);
            }
            // return $oldLocation->layout;
            $name      = $request->name ? $request->name : '';
            $lat       = $request->lat ? $request->lat : '';
            $long      = $request->long ? $request->long : '';
            $location  = $request->location ? $request->location : '';

            $file = $request->file('layout');
            if ($file) {
                $extension = $file->getClientOriginalExtension();
                $filename = time().'.'.$extension;
                $file->move('assets/uploads/location-photos/', $filename);
                $photo = 'assets/uploads/location-photos/'.$filename  ;
            }

            $locationData = [ 
                'name'          => $name,
                'lat'           => $lat, 
                'long'          => $long,
                'location'      => $location,
                'layout'        => $request->file('layout') ? $photo : $oldLocation->layout,
                'updated_at'    => Carbon::now()
            ];

            $location = DB::table('locations')->where('id', '=', $location_id)->update($locationData);

            if ($location) {

                $adminData = [
                    'id'        => Str::uuid(),
                    'user_id'   => $request->admin_id,
                    'user_type' => $request->admin_type,
                    'activity'  => 'The Rental location ('.$oldLocation->name.') details are updated by '.ucfirst($request->admin_type).' '.$admin->name,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];

                DB::table('admin_activities')->insert($adminData);

                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.edit-location.success'),
                    'data'      => $locationData
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.edit-location.failure'),
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

    public function changeLocationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'    => 'required',
            'location_id' => ['required','alpha_dash', Rule::notIn('undefined')],
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
            $location_id = $request->location_id;
            $status = $request->status;

            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }
            
            $location = DB::table('locations')->where('id', '=', $location_id)->first();
            if (!empty($location)) {
                $statusChange = DB::table('locations')->where('id', '=', $location_id)->update(['status' => $status, 'updated_at' => Carbon::now()]);
                if ($statusChange) {
                    DB::table('plot_lines')->where('location_id', '=', $location_id)->update(['status' => $status, 'updated_at' => Carbon::now()]);
                    DB::table('plots')->where('location_id', '=', $location_id)->update(['status' => $status, 'updated_at' => Carbon::now()]);

                    $status == 'active' ? $msg = 'activated' : $msg = 'inactivated';
                    $adminData = [
                        'id'        => Str::uuid(),
                        'user_id'   => $request->admin_id,
                        'user_type' => $request->admin_type,
                        'activity'  => 'Rental Location ('.$location->name.') is '.$msg.' by '.ucfirst($request->admin_type).' '.$admin->name,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
    
                    DB::table('admin_activities')->insert($adminData);

                    return response()->json([
                        'status'    => 'success',
                        'message'   => trans('msg.admin.location-status.'.$status),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.admin.location-status.failure'),
                    ],400);
                }
                
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.location-status.invalid'),
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
