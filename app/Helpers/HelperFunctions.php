<?php

    use Illuminate\Support\Facades\DB;

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