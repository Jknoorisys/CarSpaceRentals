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

class LocationController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function addLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                 => 'required',
            'language'             => 'required',
            'lat'                  => 'required',
            'long'                 => 'required',
            'location'             => 'required',
            'plot_numbers'         => 'required|json',
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
}
