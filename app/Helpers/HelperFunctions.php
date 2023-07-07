<?php

use App\Models\PaymentHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

    function validateAdmin($data)
    {
        if ($data && $data['admin_type'] == 'user') {
            $admin = DB::table('users')->where('id', '=', $data['id'])->whereIn('is_admin', ['admin', 'super_admin'])->first();
        } else {
            $admin = DB::table('dealers')->where('id', '=', $data['id'])->whereIn('is_admin', ['admin', 'super_admin'])->first();
        }
        
        return $admin;
    }

    function validateCustomer($customer_id)
    {
        return DB::table('users')->where('id', '=', $customer_id)->first();
    }

    function validateDealer($dealer_id)
    {
        return DB::table('dealers')->where('id', '=', $dealer_id)->first();
    }

    function validateLocation($location_id)
    {
        return DB::table('locations')->where('id', '=', $location_id)->first();
    }

    function validateLine($line_id)
    {
        return DB::table('plot_lines')->where('id', '=', $line_id)->first();
    }

    function validatePlot($plot_id)
    {
        return DB::table('plots')->where('id', '=', $plot_id)->first();
    }

    function validateCar($car_id)
    {
        return DB::table('cars')->where('id', '=', $car_id)->first();
    }

    function generatePlotInvoicePdf($invoice) {
        ini_set('memory_limit', '8G');

        // send data to invoice.blade.php and generate pdf using Barryvdh\DomPDF
        $pdf = Pdf::loadView('plot_invoice', $invoice);

        // save pdf in storage path i.e. storage/app/invoices
        $pdf_name = 'plot_invoice_'.time().'.pdf';
        $path = Storage::put('plot-invoices/'.$pdf_name,$pdf->output());

        // update pdf_url in database subscriptions table
        $invoice_url = ('storage/app/plot-invoices/'.$pdf_name);
        PaymentHistory::where('id', '=', $invoice['trxn_id'])->update(['invoice_url' => $invoice_url, 'updated_at' => Carbon::now()]);

        // send invoice to customer
        $email = $invoice['dealer_email'];
        $data1 = ['salutation' => __('msg.stripe.Dear'),'name'=> $invoice['dealer_name'], 'msg'=> trans('msg.This email serves to confirm the successful setup of your subscription with Us.'), 'msg1'=> trans('msg.We are delighted to welcome you as a valued subscriber and are confident that you will enjoy the benefits of Premium Services.'),'msg2' => trans('msg.stripe.Thank you for your trust!')];

        Mail::send('invoice_email', $data1, function ($message) use ($pdf_name, $email, $pdf) {
            $message->to($email)->subject('Invoice');
            $message->attachData($pdf->output(), $pdf_name, ['as' => $pdf_name, 'mime' => 'application/pdf']);
        });
        
        return $path;
    }

    function generateCarInvoicePdf($invoice) {
        ini_set('memory_limit', '8G');

        // send data to invoice.blade.php and generate pdf using Barryvdh\DomPDF
        $pdf = Pdf::loadView('car_invoice', $invoice);

        // save pdf in storage path i.e. storage/app/invoices
        $pdf_name = 'car_invoice_'.time().'.pdf';
        $path = Storage::put('car-invoices/'.$pdf_name,$pdf->output());

        // update pdf_url in database subscriptions table
        $invoice_url = ('storage/app/car-invoices/'.$pdf_name);
        PaymentHistory::where('id', '=', $invoice['trxn_id'])->update(['invoice_url' => $invoice_url, 'updated_at' => Carbon::now()]);

        // send invoice to customer
        $email = $invoice['dealer_email'];
        $data1 = ['salutation' => __('msg.stripe.Dear'),'name'=> $invoice['dealer_name'], 'msg'=> trans('msg.This email serves to confirm the successful setup of your subscription with Us.'), 'msg1'=> trans('msg.We are delighted to welcome you as a valued subscriber and are confident that you will enjoy the benefits of Premium Services.'),'msg2' => trans('msg.stripe.Thank you for your trust!')];

        Mail::send('invoice_email', $data1, function ($message) use ($pdf_name, $email, $pdf) {
            $message->to($email)->subject('Invoice');
            $message->attachData($pdf->output(), $pdf_name, ['as' => $pdf_name, 'mime' => 'application/pdf']);
        });
        
        return $path;
    }

    