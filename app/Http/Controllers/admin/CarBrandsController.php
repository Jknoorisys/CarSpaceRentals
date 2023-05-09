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
            $total = $db->count();

            $brands = $db->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
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
                    'status'    => 'failed',
                    'message'   => trans('msg.admin.get-brands.failure'),
                    'data'      => [],
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

    public function addCarBrand(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required',
            'name'     => 'required|unique:brands,name',
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
            $brandData = [ 'id' => Str::uuid('36'), 'name' => $name, 'created_at' => Carbon::now()];
            $brand = DB::table('brands')->insert($brandData);
            if ($brand) {
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
            'id'       => 'required|alpha_dash',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $brand = DB::table('brands')->where('id', '=', $request->id)->first();
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
            'id'       => 'required|alpha_dash',
            'name'     => 'required|unique:brands,name',
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
            $brand = DB::table('brands')->where('id', '=', $request->id)->update(['name' => $name, 'updated_at' => Carbon::now()]);
            if ($brand) {
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
