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

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class DealerController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function getDealers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'page_number'   => 'required||numeric',
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

            $db = DB::table('dealers')->where('is_admin', '=', 'no')->where('is_verified', '=', 'yes');

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $db->where('name', 'LIKE', "%$search%");
                $db->orWhere('email', 'LIKE', "%$search%");
            }

            $total = $db->count();
            $dealers = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->orderBy('name')
                                    ->get();

            if (!($dealers->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-dealers.success'),
                    'total'     => $total,
                    'data'      => $dealers
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-dealers.failure'),
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

    public function changeDealerstatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'   => 'required',
            'dealer_id'  => ['required','alpha_dash', Rule::notIn('undefined')],
            'admin_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
            'admin_type' => ['required', 
                Rule::in(['user', 'dealer'])
            ],
            'status'     => ['required', 
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
            $user_id = $request->dealer_id;
            $status = $request->status;

            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }

            $user = DB::table('dealers')->where('id', '=', $user_id)->first();
            if (!empty($user)) {
                $statusChange = DB::table('dealers')->where('id', '=', $user_id)->update(['status' => $status, 'updated_at' => Carbon::now()]);
                if ($statusChange) {

                    $status == 'active' ? $msg = 'activated' : $msg = 'inactivated';
                    $adminData = [
                        'id'        => Str::uuid(),
                        'user_id'   => $request->admin_id,
                        'user_type' => $request->admin_type,
                        'activity'  => 'Dealer '.$user->name.' is '.$msg.' by '.ucfirst($request->admin_type).' '.$admin->name,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];

                    DB::table('admin_activities')->insert($adminData);
                    return response()->json([
                        'status'    => 'success',
                        'message'   => trans('msg.admin.dealer-status.'.$status),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.admin.dealer-status.failure'),
                    ],400);
                }
                
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.dealer-status.invalid'),
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

    public function makeDealerAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'dealer_id'       => ['required','alpha_dash', Rule::notIn('undefined')],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user_id = $request->dealer_id;
            $status = 'admin';

            $user = DB::table('dealers')->where('id', '=', $user_id)->first();
            if (!empty($user)) {
                $statusChange = DB::table('dealers')->where('id', '=', $user_id)->update(['is_admin' => $status, 'updated_at' => Carbon::now()]);
                if ($statusChange) {
                    return response()->json([
                        'status'    => 'success',
                        'message'   => trans('msg.admin.make-dealer-admin.success'),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.admin.make-dealer-admin.failure'),
                    ],400);
                }
                
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.make-dealer-admin.invalid'),
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

    public function getDealerLoginActivity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'    => 'required',
            'dealer_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
            'page_number' => 'required||numeric',
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

            $db = DB::table('login_activities')->where([['user_id', '=', $dealer_id],['login_activities.user_type','=','dealer']])
                                ->leftjoin('dealers', function($join) {
                                    $join->on('dealers.id','=','login_activities.user_id')
                                        ->where('login_activities.user_type','=','dealer');
                                });

            $total = $db->count();
            $activities = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->orderBy('login_date')
                                    ->get(['login_activities.*', 'dealers.name as user_name']);

            if (!($activities->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-dealer-activities.success'),
                    'total'     => $total,
                    'data'      => $activities
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-dealer-activities.failure'),
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

    public function getDealerDetails(Request $request)
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
            $dealer = DB::table('dealers')->where('id','=', $dealer_id)->first();

            if (!empty($dealer)) {
                $dealer->cars = DB::table('cars')->where('dealer_id', '=', $dealer_id)->get();

                foreach ($dealer->cars as $car) {
                    $car->photos = DB::table('car_photos')->where('car_id', '=', $car->id)->first(['id','car_id','photo1','photo2','photo3','photo4','photo5']);
                }

                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-dealer-details.success'),
                    'data'      => $dealer
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.get-dealer-details.failure'),
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
                    'message'   => trans('msg.admin.get-dealer-cars.success'),
                    'total'     => $total,
                    'data'      => $cars
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-dealer-cars.failure'),
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

    public function dealersBookedPlots(Request $request)
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

            $db = DB::table('bookings as sc')->where([['sc.dealer_id', '=', $dealer_id]])
                        ->leftJoin('plots', 'plots.id', '=', 'sc.plot_id')
                        ->leftJoin('cars', 'cars.id', '=', 'sc.car_id')
                        ->leftJoin('locations', 'locations.id', '=', 'sc.location_id');

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $db->where('locations.name', 'LIKE', "%$search%");
                // $db->orWhere('cars.name', 'LIKE', "%$search%");
            }

            $total = $db->count();
            $plots = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->orderBy('locations.name')
                                    ->get(['sc.*','plots.plot_name','cars.name as car_name','locations.name as location_name']);

            if (!($plots->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-dealer-plots.success'),
                    'total'     => $total,
                    'data'      => $plots
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-dealer-plots.failure'),
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
}
