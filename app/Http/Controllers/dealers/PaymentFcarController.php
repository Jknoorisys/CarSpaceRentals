<?php

namespace App\Http\Controllers\dealers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentFcarController extends Controller
{
    public function __construct() 
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function orange_payment_for_car_booking(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language'          =>  'required',
            'dealer_id'         =>  ['required', 'alpha_dash', Rule::notIn('undefined')],
            'location_id'       =>  ['required', 'alpha_dash', Rule::notIn('undefined')],
            'line_id'           =>  ['required', 'alpha_dash', Rule::notIn('undefined')],
            'car_id'            => 'required',
            'amount_paid'       => 'required',
            'duration'          => 'required|numeric',
            'duration_type'     => 'required', Rule::in(['day', 'week', 'month', 'year']),
            'park_in_date'      => 'required',
            'park_out_date'     => 'required',
            'rent'              => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.user.validation.fail'),
                'errors'    => $validator->errors()
            ], 400);
        }
        
        try 
        {
            $dealer_id = $req->dealer_id;
            $dealer = validateDealer($dealer_id);
            if (empty($dealer) || $dealer->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-dealer'),
                ],400);
            }

            $location_id = $req->location_id;
            $location = validateLocation($location_id);
            if (empty($location) || $location->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-location'),
                ],400);
            }

            $line_id = $req->line_id;
            $line = validateLine($line_id);
            if (empty($line) || $line->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-line'),
                ],400);
            }

            $ch = curl_init("https://api.orange.com/oauth/v3/token?");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Basic REpMRWMwSVBjeGZ3VndjaGpxV004dm1PWXBqQU5FNXg6M3FNNGk4UkwzNFlHQ2RJNw=='
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
            $orange_token = curl_exec($ch);
            curl_close($ch);
            $token_details = json_decode($orange_token, TRUE);
            // return $token_details;
            $token = 'Bearer ' . $token_details['access_token'];
            // $lang = $this->session->userdata('language');
            // if($lang=='french') $lang = 'fr';
            // else $lang = 'en';

            // get payment link
            $order_id = rand(1000000, 000000) . "_Featured_Car";

            $json_post_data =
                json_encode(
                    array(
                        "merchant_key" => "0e40eab1",
                        //   "currency" => "EUR", 
                        "currency" => "XAF",
                        //"currency" => "OUV", 
                        "order_id" => $order_id,
                        "amount" => $req->amount_paid,
                        "return_url" => "https://www.fb.com",
                        "cancel_url" => "https://www.google.com",
                        "notif_url" => "https://www.google.com",
                        "lang" => $req->language,
                        "reference" => "Featured Cars"
                    )
                );
            // echo $order_id; die();
            $data = json_decode($json_post_data, true);
            // return $data;
            $ch = curl_init("https://api.orange.com/orange-money-webpay/cm/v1/webpayment?");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "Authorization: " . $token,
                "Accept: application/json"
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_post_data);
            $orange_url = curl_exec($ch);
            curl_close($ch);
            $url_details = json_decode($orange_url, TRUE);
            // return $url_details;

            if (isset($url_details)) {

                $booking_detail = array(
                    'id'                 => Str::uuid(),
                    'car_id'           => $req->car_id,
                    'location_id'        => $req->location_id,
                    'line_id'            => $req->line_id,
                    'dealer_id'          => $req->dealer_id,
                    'notification_token' => $url_details['notif_token'],
                    'session_id'         => $url_details['pay_token'],//payment_token
                    'payment_id'         => 'orange_' . $data['order_id'],
                    'payment_method'     => 'orange',
                    'amount_paid'        => $req->amount_paid,
                    'payment_for'        => 'car',
                    'duration'           => $req->duration,
                    'duration_type'      => $req->duration_type,
                    'park_in_date'       => $req->park_in_date,
                    'park_out_date'      => $req->park_out_date,
                    'rent'               => $req->rent,
                    'payer_email'        => $dealer->email,
                    'currency'           => $data['currency'],
                    'created_at'         => Carbon::now(),
                );
                // return $booking_detail;
                if (!empty($booking_detail)) {

                    $save_booking = DB::table('payment_histories')->insert($booking_detail);
                    if ($save_booking) {
                        $data = [
                            "pay_token" => $url_details['pay_token'],
                            "notif_token" => $url_details['notif_token'],
                            "orange_redirect_url" => $url_details['payment_url']
                        ];
                        return response()->json(
                            [
                                'status'    => 'success',
                                'payment_url' => $data,
                                'message'   => __('msg.dealer.payment.redirect_success'),
                            ],
                            200
                        );
                    } else {
                        return response()->json(
                            [
                                'status'    => 'failed',
                                'message'   => __('msg.dealer.payment.redirect_fail'),
                            ],
                            400
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => __('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function orange_car_payment_succss(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'language'          =>   'required',
            'dealer_id'   => ['required', 'alpha_dash', Rule::notIn('undefined')],
            'pay_token'   => ['required', 'alpha_dash', Rule::notIn('undefined')],
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.user.validation.fail'),
                'errors'    => $validator->errors()
            ], 400);
        }
        try {

            $selected_plots = json_decode($req->selected_plots, TRUE);
            // $pay_token = $req->pay_token;
            $data = DB::table('payment_transactions')->where('pay_token', $req->pay_token)->take(1)->first();
            $order_payment_id =  $data->payment_id;
            $amount = $data->amount;
            $pay_token = $data->pay_token;
            $ch = curl_init("https://api.orange.com/oauth/v3/token?");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Basic REpMRWMwSVBjeGZ3VndjaGpxV004dm1PWXBqQU5FNXg6M3FNNGk4UkwzNFlHQ2RJNw=='
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
            $orange_token = curl_exec($ch);
            // return $orange_token;
            curl_close($ch);
            $token_details = json_decode($orange_token, TRUE);
            $token = 'Bearer ' . $token_details['access_token'];

            // get payment link
            $json_post_data = json_encode(
                array(
                    "order_id" => $order_payment_id,
                    "amount" => $amount,
                    "pay_token" => $pay_token
                )
            );
            // echo $json_post_data; die();
            $ch = curl_init("https://api.orange.com/orange-money-webpay/cm/v1/transactionstatus?");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "Authorization: " . $token,
                "Accept: application/json"
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_post_data);
            $payment_res = curl_exec($ch);
            return $payment_res;
            curl_close($ch);
            $payment_details = json_decode($payment_res, TRUE);
            $transaction_id = $payment_details['txnid'];
            echo json_encode($payment_details);
            die();

            if ($payment_details['status'] == 'SUCCESS' && $data['status'] == 'unpaid') {
                $success_payment = DB::table('payment_transactions')->where('pay_token', $req->paytoken)->update(['status', 'paid']);
                // return $success_payment;
                if ($success_payment) {
                    foreach ($selected_plots as $plot) {
                        $booking_data = [
                            'id'            => Str::uuid(),
                            'plot_id'       => $plot['plot_id'],
                            'line_id'       => $payment_details->line_id,
                            'location_id'   => $payment_details->location_id,
                            'dealer_id'     => $payment_details->dealer_id,
                            'park_in_date'  => $payment_details->park_in_date,
                            'park_out_date' => $payment_details->park_out_date,
                            'duration_type' => $payment_details->duration_type,
                            'duration'      => $payment_details->duration,
                            'rent'          => $plot['rent'],
                            'created_at'    => Carbon::now()
                        ];
                        $booking = DB::table('bookings')->insert($booking_data);
                    }
                }

                if ($booking) {
                    $dealer = Dealers::find($payment_details->dealer_id);
                    $paymentDetails = DB::table('payment_histories as sc')
                                            ->where('sc.id', '=', $payment_details->id)
                                            ->leftJoin('locations', 'locations.id', '=', 'sc.location_id')
                                            ->leftJoin('plot_lines', 'plot_lines.id', '=', 'sc.line_id')
                                            ->first(['sc.*','locations.name as location_name','plot_lines.line_name']);

                    foreach ($selected_plots as $plot) {
                        $plotData = DB::table('plots')->where('plots.id', '=', $plot['plot_id'])
                                            ->leftJoin('locations', 'locations.id', '=', 'plots.location_id')
                                            ->leftJoin('plot_lines', 'plot_lines.id', '=', 'plots.line_id')
                                            ->first(['plots.*','locations.name as location_name','plot_lines.line_name']);
                        $plots[] = [
                            'plot_id'       => $plot['plot_id'],
                            'location_name'     => $plotData->location_name,
                            'line_name'         => $plotData->line_name,
                            'plot_name'     => $plotData->plot_name,
                            'duration'     => $paymentDetails->duration.' '.ucfirst($paymentDetails->duration_type),
                            'rent'          => $plot['rent'],
                        ];
                    }

                    // generate invoice pdf and send to customer
                    $invoice_data = [
                        'trxn_id'           => $payment_details->id,
                        'invoice_number'    => (string)rand(10000, 20000),
                        'dealer_name'       => $dealer ? $dealer->name : '',
                        'dealer_email'      => $dealer ? $dealer->email : '',
                        'park_in_date'      => $paymentDetails ? date('d M Y', strtotime($paymentDetails->park_in_date)) : '',
                        'park_out_date'     => $paymentDetails ? date('d M Y', strtotime($paymentDetails->park_out_date)) : '',
                        'location_name'     => $paymentDetails ? $paymentDetails->location_name : '',
                        'line_name'         => $paymentDetails ? $paymentDetails->line_name : '',
                        'plots'             => $plots ? $plots : '',
                        'amount_paid'       => $session->amount_total/100,
                        'currency'          => $session->currency,
                        'date'              => Carbon::now()->format('d.m.Y')
                    ];
                    // return $invoice_data;

                    // helper function tp generate and send invoice
                    generateInvoicePdf($invoice_data);
                }

                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.stripe.success'),
                ],200);
            } else {
                return "false";
            }
            
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => __('msg.user.error'),
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
