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

class CarBrandsController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function getCarBrands(Request $request)
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

            $db = DB::table('brands');

            $search = $request->search ? $request->search : '';
            if (!empty($search)) {
                $db->where('name', 'LIKE', "%$search%");
            }
            
            $total = $db->count();

            $brands = $db->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->orderBy('name')
                        ->get();

            if (!($brands->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-brands.success'),
                    'total'     => $total,
                    'data'      => $brands
                ],200);
            } else {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-brands.failure'),
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

    public function addCarBrand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'name'     => 'required|unique:brands,name',
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
            $name = $request->name;

            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }

            $brandData = [ 'id' => Str::uuid(), 'name' => $name, 'created_at' => Carbon::now()];
            $brand = DB::table('brands')->insert($brandData);

            if ($brand) {

                $adminData = [
                    'id'        => Str::uuid(),
                    'user_id'   => $request->admin_id,
                    'user_type' => $request->admin_type,
                    'activity'  => 'Car brand named '.$name.' is added by '.ucfirst($request->admin_type).' '.$admin->name,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];

                DB::table('admin_activities')->insert($adminData);

                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.add-brand.success'),
                    'data'      => $brandData
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.add-brand.failure'),
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

    public function getCarBrand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'brand_id'       => ['required','alpha_dash', Rule::notIn('undefined')],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $brand = DB::table('brands')->where('id', '=', $request->brand_id)->first();
            if ($brand) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.get-brand.success'),
                    'data'      => $brand
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.get-brand.failure'),
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

    public function editCarBrand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'brand_id'       => ['required','alpha_dash', Rule::notIn('undefined')],
            'name'     => 'required|unique:brands,name',
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
            $name = $request->name;

            $admin = validateAdmin(['id' => $request->admin_id, 'admin_type' => $request->admin_type]);
            if (empty($admin) || $admin->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.invalid-admin'),
                ],400);
            }

            $brand = DB::table('brands')->where('id', '=', $request->brand_id)->first();
            if (empty($brand)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.edit-brand.invalid'),
                ],400);
            }

            $updateBrand = DB::table('brands')->where('id', '=', $request->brand_id)->update(['name' => $name, 'updated_at' => Carbon::now()]);
            if ($updateBrand) {

                $adminData = [
                    'id'        => Str::uuid(),
                    'user_id'   => $request->admin_id,
                    'user_type' => $request->admin_type,
                    'activity'  => 'The Car brand name is updated by '.ucfirst($request->admin_type).' '.$admin->name.' from '.$brand->name.' to '.$name,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];

                DB::table('admin_activities')->insert($adminData);

                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.admin.edit-brand.success'),
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.edit-brand.failure'),
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
