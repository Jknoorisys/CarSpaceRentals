<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brands;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class AdminController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function getAllAdmins(Request $request)
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

            $users = DB::table('users')->where('is_admin', '=', 'admin')->select('id', DB::raw("'user' as user_type"),'name','email','mobile','profile','is_admin','status','created_at','updated_at');
            $dealers = DB::table('dealers')->where('is_admin', '=', 'admin')->select('id',DB::raw("'dealer' as user_type"), 'name','email','mobile','profile','is_admin','status','created_at','updated_at');

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $users->where('name', 'LIKE', "%$search%");
                $dealers->where('name', 'LIKE', "%$search%");
            }
            
            $db = $users->union($dealers);

            $total = $db->count();

            $admins = $db->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->orderBy('name')
                        ->get();

            if (!($admins->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-admins.success'),
                    'total'     => $total,
                    'data'      => $admins
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-admins.failure'),
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

    public function changeAdminStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'    => 'required',
            'admin_id'    => ['required','alpha_dash', Rule::notIn('undefined')],
            'admin_type'  => ['required', 
                Rule::in(['user', 'dealer'])
            ],
            'status'       => ['required', 
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
            $admin_id = $request->admin_id;
            $admin_type = $request->admin_type;
            $status = $request->status;

            if ($admin_type == 'user') {
                $user = DB::table('users')->where('id', '=', $admin_id)->first();
            } else {
                $user = DB::table('dealers')->where('id', '=', $admin_id)->first();
            }
            
            if (!empty($user)) {

                if ($admin_type == 'user') {
                    $statusChange = DB::table('users')->where('id', '=', $admin_id)->update(['status' => $status, 'updated_at' => Carbon::now()]);
                } else {
                    $statusChange = DB::table('dealers')->where('id', '=', $admin_id)->update(['status' => $status, 'updated_at' => Carbon::now()]);
                }

                if ($statusChange) {
                    return response()->json([
                        'status'    => 'success',
                        'message'   => trans('msg.admin.admin-status.'.$status),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.admin.admin-status.failure'),
                    ],400);
                }
                
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.admin-status.invalid'),
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

    public function getAdminLoginActivity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'page_number'   => 'required|numeric',
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
            $admin_id = $request->admin_id;
            $admin_type = $request->admin_type;
            $data = ['id' => $admin_id, 'admin_type' => $admin_type];
            
            $admin = validateAdmin($data);
            if (empty($admin)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-admin'),
                ],400);
            }

            $per_page = 10;
            $page_number = $request->input(key:'page_number', default:1);

            $db = DB::table('login_activities')->where([['user_id', '=', $admin_id],['login_activities.user_type','=',$admin_type]]);

            if ($admin_type == 'user') {
                $db->leftjoin('users', function($join) {
                                $join->on('users.id','=','login_activities.user_id')
                                    ->where('login_activities.user_type','=','user');
                            });
                $db->select('login_activities.*', 'users.name as admin_name');
            } else {
                $db->leftjoin('dealers', function($join) {
                                $join->on('dealers.id','=','login_activities.user_id')
                                    ->where('login_activities.user_type','=','dealer');
                            });
                $db->select('login_activities.*', 'dealers.name as admin_name');
            }
            
            $total = $db->count();
            $activities = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->orderBy('login_date')
                                    ->get();

            if (!($activities->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-admin-activities.success'),
                    'total'     => $total,
                    'data'      => $activities
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-admin-activities.failure'),
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

    public function getAdminActionHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'page_number'   => 'required|numeric',
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

            $db = DB::table('admin_activities');
            
            $total = $db->count();
            $activities = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->orderBy('admin_activities.created_at')
                                    ->get();

            if (!($activities->isEmpty())) {

                foreach ($activities as $activity) {
                    $user_type = $activity->user_type;
                    if ($user_type == 'user') {
                        $admin = DB::table('users')->where('id', '=', $activity->user_id)->first(['name as admin_name']);
                    } else {
                        $admin = DB::table('dealers')->where('id', '=', $activity->user_id)->first(['name as admin_name']);
                    }
                    
                    $activity->admin_name = $admin ? $admin->admin_name : '';
                };
                
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-admin-actions-history.success'),
                    'total'     => $total,
                    'data'      => $activities
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-admin-actions-history.failure'),
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
