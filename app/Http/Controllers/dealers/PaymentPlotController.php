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

class PaymentPlotController extends Controller
{
    public function orange_payment_for_plot_booking(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'language'          =>   'required',
            'dealer_id'   => ['required', 'alpha_dash', Rule::notIn('undefined')],
            'location_id'   => ['required', 'alpha_dash', Rule::notIn('undefined')],
            'plots_id' => ['required'],
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.user.validation.fail'),
                'errors'    => $validator->errors()
            ], 400);
        }
        try {
            // $plots_ids = explode(',',$req->plots_id);
            // return $plots_ids;
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
            $order_id = rand(1000000, 000000) . "_plot_booking";

            $json_post_data =
                json_encode(
                    array(
                        "merchant_key" => "0e40eab1",
                        //   "currency" => "EUR", 
                        "currency" => "XAF",
                        //"currency" => "OUV", 
                        "order_id" => $order_id,
                        "amount" => $req->amount,
                        "return_url" => "http://fb.com",
                        "cancel_url" => "http://google.com",
                        "notif_url" => "http://google.com",
                        "lang" => "en",
                        "reference" => "Plot Booking"
                    )
                );
            // echo $order_id; die();
            $data = json_decode($json_post_data, true);
            // return $data['order_id'];
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
                    'id' => Str::uuid(),
                    'plot_id' => $req->plots_id,
                    'location_id' => $req->location_id,
                    'dealer_id' => $req->dealer_id,
                    'notif_token' => $url_details['notif_token'],
                    'pay_token' => $url_details['pay_token'],
                    'payment_id' => 'orange_' . $data['order_id'],
                    'payment_method' => 'orange',
                    'amount' => $req->amount,
                    'payment_for' => 'plot',
                    'status' => 'unpaid',
                    'created_at' => Carbon::now(),
                );
                // return $booking_detail;
                if (!empty($booking_detail)) {

                    $save_booking = DB::table('payment_transactions')->insert($booking_detail);
                    if ($save_booking) {

                        return response()->json(
                            [
                                'status'    => 'success',
                                'payment_url' => $url_details['payment_url'],
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

    public function orange_payment_success(Request $req)
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
            $pay_token = $req->pay_token;
            $data = DB::table('payment_transactions')->where('pay_token',$req->pay_token)->take(1)->first(); 
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
            return $token;
            // get payment link
            $json_post_data = json_encode(
                array(
                    "order_id" => $order_payment_id,
                    "amount" => $amount,
                    "pay_token" => $pay_token
                )
            );
            echo $json_post_data; die();
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
            echo json_encode($payment_details); die();

            if ($payment_details['status'] == 'SUCCESS' && $data['status'] == 'unpaid') {
                $success_payment = DB::table('payment_transactions')->where('pay_token',$req->paytoken)->update(['status','paid']);
                return $success_payment;
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
