<?php

namespace App\Http\Controllers\stripe;

use App\Http\Controllers\Controller;
use App\Models\Dealers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Stripe\Stripe;

class PlotBookingController extends Controller
{
    private $stripe;
    private $stripe_key;

    public function __construct() {
        // Multiligual
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

         // Create a new instance of the Stripe client using the Stripe API key obtained from the 'STRIPE_SECRET' environment variable
         $this->stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET')
        );

        // Set the Stripe API key globally using the 'setApiKey' method from the 'Stripe' class
        $this->stripe_key = Stripe::setApiKey(env('STRIPE_SECRET'));
    }
    
    // By Javeriya Kauser
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
            $dealer_id = $request->dealer_id;
            $dealer = validateDealer($dealer_id);
            if (empty($dealer) || $dealer->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-dealer'),
                ],400);
            }

            $location_id = $request->location_id;
            $location = validateLocation($location_id);
            if (empty($location) || $location->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-location'),
                ],400);
            }

            $line_id = $request->lane_id;
            $line = validateLine($line_id);
            if (empty($line) || $line->status != 'active') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.helper.invalid-line'),
                ],400);
            }

            if ($request->park_in_date <= Carbon::today()->format('Y-m-d')) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.dealer.get-available-plots.invalid-start_date'),
                ],400);
            }

            $plot_ids = $request->plot_ids;
            $rent     = $request->rent;

            // Set the success and cancel URLs for the checkout session
            // $success_url = url('api/dealer/stripe/plot-booking-successful');
            $success_url = 'http://localhost:4200/landing-page-dealer/success';
            // $cancel_url = url('api/dealer/stripe/plot-booking-failed');
            $cancel_url = 'http://localhost:4200/landing-page-dealer/failure';

            // Create a new Stripe checkout session object 
            $session = \Stripe\Checkout\Session::Create([
                'success_url' => $success_url,
                'cancel_url' => $cancel_url,
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                      'price_data'=> [
                        'currency'=> env('STRIPE_CURRENCY'),
                        'unit_amount'=> $rent * 100,
                        'product_data'=> [
                            'name'=> 'Plot Booking',
                            ],
                        ],
                      'quantity'=> 1,
                    ],
                ],
                'mode' => 'payment',
                'currency' => env('STRIPE_CURRENCY'),
            ]);

             // if session does not exists, throw an erorr
            if (!$session || empty($session)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.stripe.session.failure'),
                ],400);
            }
            
            $payment_data = [
                'id'                => Str::uuid(),
                'session_id'        => $session->id,
                'payment_method'    => 'stripe',
                'payment_for'       => 'plot',
                'dealer_id'         => $dealer_id,
                'location_id'       => $location_id,
                'line_id'           => $line_id,
                'plot_ids'          => $plot_ids,
                'no_of_plots'       => $request->no_of_plots,
                'duration'          => $request->duration,
                'duration_type'     => $request->duration_type,
                'park_in_date'      => $request->park_in_date,
                'park_out_date'     => $request->park_out_date,
                'rent'              => $request->rent,
                'currency'          => $session->currency,
                'payment_status'    => $session->payment_status,
                'session_status'    => $session->status,
                'created_at'        => Carbon::now()
            ];

            // Insert the booking data into the booking table
            $payment = DB::table('payment_histories')->insert($payment_data);

            $session_data = [
                'session_id'  => $session->id,
                'success_url' => $session->success_url,
                'cancel_url'  => $session->cancel_url,
                'stripe_url'  => $session->url
            ];

            if($payment){
                return response()->json([
                    'status'    => 'success',
                    'message'   => trans('msg.stripe.session.success'),
                    'data'      => $session_data
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.stripe.session.failure'),
                ],400);
            }
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $err  = 'Status:' . $e->getHttpStatus() . '<br>';
            $err  .= 'Type:' . $e->getError()->type . '<br>';
            $err  .= 'Code:' . $e->getError()->code . '<br>';
            // param is '' in this case
            $err  .= 'Param:' . $e->getError()->param . '<br>';
            $err  .= 'Message:' . $e->getError()->message . '<br>';
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $err
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
             return response()->json([
                 'status'    => 'failed',
                 'message'   => trans('msg.error'),
                 'error'     => $e->getMessage()
             ],500);
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function plotPaymentSuccessfull(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language'   => 'required',
            'session_id' => 'required',
            'selected_plots' => 'required|json'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try{

            $session_id = $request->session_id;
            $selected_plots = json_decode($request->selected_plots, TRUE);

            // Fetch payment details based on the given 'session_id' from the 'Bookings' table
            $payment_details = DB::table('payment_histories')->where('session_id', '=', $session_id)->first();

            if (!empty($payment_details)) {

               // retrive session from stripe using session_id
               $session = \Stripe\Checkout\Session::Retrieve(
                    $payment_details->session_id,
                    []
                );

                if($session->payment_status == "paid" && $session->status == "complete"){

                    $payment_data  =  [
                        'payment_id'     => $session->payment_intent,
                        'payer_email'    => $session->customer_details->email ? $session->customer_details->email : '',
                        'amount_paid'    => $session->amount_total/100,
                        'payment_status' => $session->payment_status,
                        'session_status' => $session->status,
                        'updated_at'     => Carbon::now(),
                    ];

                    // update data in booking table
                    $update = DB::table('payment_histories')->where('session_id', '=', $payment_details->session_id)->update($payment_data);

                    if ($update) {
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

                        // helper function tp generate and send invoice
                        generatePlotInvoicePdf($invoice_data);
                    }

                    return response()->json([
                        'status'    => 'success',
                        'message'   => trans('msg.stripe.success'),
                    ],200);
                }elseif ($session->payment_status == "unpaid" && $session->status == "open") {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.stripe.failure'),
                        'stripe'  => [
                            'session_id'  => $session->id,
                            'stripe_url'  => $session->url,
                        ],
                    ],400);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.stripe.failure'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.stripe.invalid'),
                ],400);
            }
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $err  = 'Status:' . $e->getHttpStatus() . '<br>';
            $err  .= 'Type:' . $e->getError()->type . '<br>';
            $err  .= 'Code:' . $e->getError()->code . '<br>';
            // param is '' in this case
            $err  .= 'Param:' . $e->getError()->param . '<br>';
            $err  .= 'Message:' . $e->getError()->message . '<br>';
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $err
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
           // Network communication with Stripe failed
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function plotPaymentFailed(Request $request){
        $validator = Validator::make($request->all(), [
            'language'   => 'required',
            'session_id' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try{

            $session_id = $request->session_id;

            // Fetch payment details based on the given 'session_id' from the 'Bookings' table
            $payment_details = DB::table('payment_histories')->where('session_id', '=', $session_id)->first();

            if (!empty($payment_details)) {

                // retrive session from stripe using session_id
                $session = \Stripe\Checkout\Session::Retrieve(
                    $payment_details->session_id,
                    []
                );

                if($session->status == "open"){

                    // if session status is open, expire that session using session_id
                    $expire = $this->stripe->checkout->sessions->expire(
                        $payment_details->session_id,
                        []
                    );

                    $payment_data  =  [
                        'payment_status' => $expire->payment_status,
                        'session_status' => $expire->status,
                        'updated_at'     => Carbon::now(),
                    ];

                    // update status in booking table
                    $update = DB::table('payment_histories')->where('session_id', '=', $payment_details->session_id)->update($payment_data);

                    if($update){
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => trans('msg.stripe.failure'),
                        ],400);
                    }
                } else if ($session->status == "complete") {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.stripe.paid'),
                    ],400);
                } else if ($session->status == "expired") {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.stripe.expaired'),
                    ],400);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => trans('msg.stripe.failure'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => trans('msg.stripe.invalid'),
                ],400);
            }
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $err  = 'Status:' . $e->getHttpStatus() . '<br>';
            $err  .= 'Type:' . $e->getError()->type . '<br>';
            $err  .= 'Code:' . $e->getError()->code . '<br>';
            // param is '' in this case
            $err  .= 'Param:' . $e->getError()->param . '<br>';
            $err  .= 'Message:' . $e->getError()->message . '<br>';
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $err
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
           // Network communication with Stripe failed
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => trans('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }
}
