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

class CustomerController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function getCustomers(Request $request)
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

            $db = DB::table('users');

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $db->where('name', 'LIKE', "%$search%");
                $db->orWhere('email', 'LIKE', "%$search%");
            }

            $total = $db->count();
            $customers = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->get();

            if (!($customers->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-customers.success'),
                    'total'     => $total,
                    'data'      => $customers
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-customers.failure'),
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

    public function changeCustomerStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'    => 'required',
            'customer_id' => ['required','alpha_dash', Rule::notIn('undefined')],
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
            $user_id = $request->customer_id;
            $status = $request->status;

            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }

            $user = DB::table('users')->where('id', '=', $user_id)->first();
            if (!empty($user)) {
                $statusChange = DB::table('users')->where('id', '=', $user_id)->update(['status' => $status, 'updated_at' => Carbon::now()]);
                if ($statusChange) {

                    $status == 'active' ? $msg = trans('msg.admin.Activated') : $msg = trans('msg.admin.Inactivated');
                    $adminData = [
                        'id'        => Str::uuid(),
                        'user_id'   => $request->admin_id,
                        'user_type' => $request->admin_type,
                        'type'      => trans('msg.admin.Customer').' '.$msg,
                        'description' => $user->name.' '.$msg,
                        'created_at'  => Carbon::now()
                    ];

                    DB::table('admin_activities')->insert($adminData);
                    return response()->json([
                        'status'    => 'success',
                        'message'   => trans('msg.admin.customer-status.'.$status),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.admin.customer-status.failure'),
                    ],400);
                }
                
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.customer-status.invalid'),
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

    public function makeCustomerAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'     => 'required',
            'customer_id'  => ['required','alpha_dash', Rule::notIn('undefined')],
            // 'admin_id' => ['required','alpha_dash', Rule::notIn('undefined')],
            // 'admin_type'   => ['required', 
            //     Rule::in(['user', 'dealer'])
            // ],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user_id = $request->customer_id;
            $status = 'admin';

            // $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            // if (empty($admin) || $admin->is_admin != 'super_admin' || $admin->status != 'active') {
            //     return response()->json([
            //         'status'    => 'failed',
            //         'message'   => trans('msg.admin.invalid-admin'),
            //     ],400);
            // }

            $user = DB::table('users')->where('id', '=', $user_id)->first();
            if (!empty($user)) {
                $statusChange = DB::table('users')->where('id', '=', $user_id)->update(['is_admin' => $status, 'updated_at' => Carbon::now()]);
                if ($statusChange) {
                    return response()->json([
                        'status'    => 'success',
                        'message'   => trans('msg.admin.make-admin.success'),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.admin.make-admin.failure'),
                    ],400);
                }
                
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.make-admin.invalid'),
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

    public function getCustomerLoginActivity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'page_number'   => 'required|numeric',
            'customer_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $customer_id = $request->customer_id;

            $per_page = 10;
            $page_number = $request->input(key:'page_number', default:1);

            $db = DB::table('login_activities')->where([['user_id', '=', $customer_id],['login_activities.user_type','=','user']])
                            ->leftjoin('users', function($join) {
                                $join->on('users.id','=','login_activities.user_id')
                                    ->where('login_activities.user_type','=','user');
                            });

            $total = $db->count();
            $activities = $db->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->get(['login_activities.*', 'users.name as user_name']);

            if (!($activities->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-customer-activities.success'),
                    'total'     => $total,
                    'data'      => $activities
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-customer-activities.failure'),
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
