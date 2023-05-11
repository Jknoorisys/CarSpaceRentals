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
            'plot_numbers'         => 'required',
            'no_of_lines'          => 'required',
            'no_of_plots_per_line' => 'required',
            'rent_per_day'         => 'required',
            'rent_per_week'        => 'required',
            'rent_per_month'       => 'required',
            'rent_per_year'        => 'required',
            'photo'                => 'required|image|mimes:jpeg,png,jpg,svg',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $id                     = Str::uuid();
            $name                   = $request->name ? : '';
            $lat                    = $request->lat ? : '';
            $long                   = $request->long ? : '';
            $location               = $request->location ? : '';
            $plot_numbers           = $request->plot_numbers ? : '';
            $no_of_lines            = $request->no_of_lines ? : '';
            $no_of_plots_per_line   = $request->no_of_plots_per_line ? : '';
            $rent_per_day           = $request->rent_per_day ? : '';
            $rent_per_week          = $request->rent_per_week ? : '';
            $rent_per_month         = $request->rent_per_month ? : '';
            $rent_per_year          = $request->rent_per_year ? : '';

            $plots = explode(',',$plot_numbers);
            $plote_name = $plots ? ($plots[0].'-'.end($plots)) : '';

            $file = $request->file('photo');
            if ($file) {
                $extension = $file->getClientOriginalExtension();
                $filename = time().'.'.$extension;
                $file->move('assets/uploads/location-photos/', $filename);
                $photo = 'assets/uploads/location-photos/'.$filename  ;
            }

            $locationData = [ 
                'id'            => $id, 
                'name'          => $name,
                'lat'           => $lat, 
                'long'          => $long,
                'location'      => $location,
                'plot_numbers'  => $plote_name,
                'no_of_lines'   => $no_of_lines,
                'no_of_plots_per_line' => $no_of_plots_per_line,
                'rent_per_day'  => $rent_per_day,
                'rent_per_week' => $rent_per_week,
                'rent_per_month'=> $rent_per_month,
                'rent_per_year' => $rent_per_year,
                'photo'         => $request->file('photo') ? $photo : '',
                'created_at'    => Carbon::now()
            ];

            $location = DB::table('locations')->insert($locationData);

            if ($location) {

                foreach ($plots as $plot) {
                    $plotData = [
                        'id'          => Str::uuid(),
                        'location_id' => $id,
                        'plot_number' => $plot,
                        'created_at'  => Carbon::now()
                    ];
                    
                    DB::table('plots')->insert($plotData);
                }

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
                $locationDetails->total_plots = DB::table('plots')->where('location_id', '=', $location_id)->count();
                $locationDetails->plots = DB::table('plots')->where('location_id', '=', $location_id)->get();
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-location-details.success'),
                    'data'      => $locationDetails
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-location-details.failure'),
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
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-location-details.failure'),
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

    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'             => 'required',
            'location_id'          => ['required','alpha_dash', Rule::notIn('undefined')],
            'name'                 => 'required',
            'lat'                  => 'required',
            'long'                 => 'required',
            'location'             => 'required',
            'plot_numbers'         => 'required',
            'no_of_lines'          => 'required',
            'no_of_plots_per_line' => 'required',
            'rent_per_day'         => 'required',
            'rent_per_week'        => 'required',
            'rent_per_month'       => 'required',
            'rent_per_year'        => 'required',
            'photo'                => 'required|image|mimes:jpeg,png,jpg,svg',
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
            $name                   = $request->name ? : '';
            $lat                    = $request->lat ? : '';
            $long                   = $request->long ? : '';
            $location               = $request->location ? : '';
            $plot_numbers           = $request->plot_numbers ? : '';
            $no_of_lines            = $request->no_of_lines ? : '';
            $no_of_plots_per_line   = $request->no_of_plots_per_line ? : '';
            $rent_per_day           = $request->rent_per_day ? : '';
            $rent_per_week          = $request->rent_per_week ? : '';
            $rent_per_month         = $request->rent_per_month ? : '';
            $rent_per_year          = $request->rent_per_year ? : '';

            $plots = explode(',',$plot_numbers);
            $plote_name = $plots ? ($plots[0].'-'.end($plots)) : '';

            $file = $request->file('photo');
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
                'plot_numbers'  => $plote_name,
                'no_of_lines'   => $no_of_lines,
                'no_of_plots_per_line' => $no_of_plots_per_line,
                'rent_per_day'  => $rent_per_day,
                'rent_per_week' => $rent_per_week,
                'rent_per_month'=> $rent_per_month,
                'rent_per_year' => $rent_per_year,
                'photo'         => $request->file('photo') ? $photo : '',
                'updated_at'    => Carbon::now()
            ];

            $location = DB::table('locations')->where('id', '=', $location_id)->update($locationData);

            if ($location) {
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

    public function changeLocationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'    => 'required',
            'location_id' => ['required','alpha_dash', Rule::notIn('undefined')],
            'status'      => ['required', 
                                Rule::in(['active', 'inactive'])
                            ]
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

            // $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            // if (empty($admin) || $admin->status != 'active') {
            //     return response()->json([
            //         'status'    => 'failed',
            //         'message'   => trans('msg.admin.invalid-admin'),
            //     ],400);
            // }

            $user = DB::table('locations')->where('id', '=', $location_id)->first();
            if (!empty($user)) {
                $statusChange = DB::table('locations')->where('id', '=', $location_id)->update(['status' => $status, 'updated_at' => Carbon::now()]);
                if ($statusChange) {

                    // $status == 'active' ? $msg = trans('msg.admin.Activated') : $msg = trans('msg.admin.Inactivated');
                    // $adminData = [
                    //     'id'        => Str::uuid(),
                    //     'user_id'   => $request->admin_id,
                    //     'user_type' => $request->admin_type,
                    //     'type'      => trans('msg.admin.Customer').' '.$msg,
                    //     'description' => $user->name.' '.$msg,
                    //     'created_at'  => Carbon::now()
                    // ];

                    // DB::table('admin_activities')->insert($adminData);
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
