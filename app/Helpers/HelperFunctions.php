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