<?php

namespace App\Http\Controllers\stripe;

use App\Http\Controllers\Controller;
use App\Models\Brands;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PlotBookingController extends Controller
{
    public function __construct() {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function plotPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'      => 'required',
            'dealer_id'     => ['required','alpha_dash', Rule::notIn('undefined')],
            'location_id'   => ['required','alpha_dash', Rule::notIn('undefined')],
            'lane_id'       => ['required','alpha_dash', Rule::notIn('undefined')],
            'plot_ids'      => 'required',
            'no_of_plots'   => 'required|numeric',
            'duration'      => 'required|numeric',
            'duration_type' => ['required', Rule::in(['day', 'week', 'month', 'year'])],
            'park_in_date'  => 'required',
            'park_out_date' => 'required',
            'rent'          => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            return $request->all();
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }
}
