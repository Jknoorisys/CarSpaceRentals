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
            'line_name'                 => 'required',
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

            $location_id =  $request->location_id;

            $oldLocation = validateLocation($location_id);
            if (empty($oldLocation)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-location'),
                ],400);
            }

            $id                     = Str::uuid();
            $line_name              = $request->line_name ? $request->line_name : '';
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

            // $plots = explode(',',$plot_numbers);
            // $plote_name = $plots ? ($plots[0].'-'.end($plots)) : '';

            // $file = $request->file('photo');
            // if ($file) {
            //     $extension = $file->getClientOriginalExtension();
            //     $filename = time().'.'.$extension;
            //     $file->move('assets/uploads/location-photos/', $filename);
            //     $photo = 'assets/uploads/location-photos/'.$filename  ;
            // }

            // $locationData = [ 
            //     'id'            => $id, 
            //     'name'          => $name,
            //     'lat'           => $lat, 
            //     'long'          => $long,
            //     'location'      => $location,
            //     'plot_numbers'  => $plote_name,
            //     'no_of_lines'   => $no_of_lines,
            //     'no_of_plots_per_line' => $no_of_plots_per_line,
            //     'rent_per_day'  => $rent_per_day,
            //     'rent_per_week' => $rent_per_week,
            //     'rent_per_month'=> $rent_per_month,
            //     'rent_per_year' => $rent_per_year,
            //     'photo'         => $request->file('photo') ? $photo : '',
            //     'created_at'    => Carbon::now()
            // ];

            // $location = DB::table('locations')->insert($locationData);

            // if ($location) {

            //     foreach ($plots as $plot) {
            //         $plotData = [
            //             'id'          => Str::uuid(),
            //             'location_id' => $id,
            //             'plot_number' => $plot,
            //             'created_at'  => Carbon::now()
            //         ];
                    
            //         DB::table('plots')->insert($plotData);
            //     }

            //     $adminData = [
            //         'id'        => Str::uuid(),
            //         'user_id'   => $request->admin_id,
            //         'user_type' => $request->admin_type,
            //         'activity'  => 'Rental Location named '.$name.' is added by '.ucfirst($request->admin_type).' '.$admin->name,
            //         'created_at' => Carbon::now(),
            //         'updated_at' => Carbon::now()
            //     ];

            //     DB::table('admin_activities')->insert($adminData);

            //     return response()->json([
            //         'status'    => 'success',
            //         'message'   => trans('msg.admin.add-location.success'),
            //         'data'      => $locationData
            //     ],200);
            // } else {
            //     return response()->json([
            //         'status'    => 'failed',
            //         'message'   => trans('msg.admin.add-location.failure'),
            //     ],400);
            // }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }
}
