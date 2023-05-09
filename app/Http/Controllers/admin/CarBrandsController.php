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
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $brands = Brands::all();
            if (!($brands->isEmpty())) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.admin.get-brands.success'),
                    'data'      => $brands
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.admin.get-brands.failure'),
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
            'name'     => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $name = $request->name;
            $brand = Brands::updateOrCreate(
                ['name' => $name],
                [ 'id' => Str::uuid('36'), 'name' => $name, 'created_at' => Carbon::now()]
            );
            if ($brand) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.admin.add-brand.success'),
                    'data'      => $brand
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.admin.add-brand.failure'),
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
