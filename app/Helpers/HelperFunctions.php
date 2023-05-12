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