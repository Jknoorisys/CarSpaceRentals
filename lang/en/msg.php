<?php

return [

    'Validation Failed!' => 'Validation Failed!',
    'error' => 'Something went wrong, please try again ...',

    // Admin By javeriya Kauser
    'admin' => [

        // admin activity
        'invalid-admin' => 'Admin Not Found',
        'invalid-super-admin' => 'Not a Super Admin',
        'Customer'      => 'Customer',
        'Activated'     => 'Activated',
        'Inactivated'   => 'Inactivated',

        // Manage Car Brands
        'get-brands' => [
            'success' => 'Car Brands Fetched Successfully',
            'failure' => 'No Car Brands Found',
        ],

        'get-brand' => [
            'success' => 'Car Brand Detail Fetched Successfully',
            'failure' => 'No Such Car Brand Found',
        ],

        'add-brand' => [
            'success' => 'Car Brand Added Successfully',
            'failure' => 'Unable to Add Car Brand',
        ],

        'edit-brand' => [
            'success' => 'Car Brands Updated Successfully',
            'failure' => 'Unable to Update Car Brand',
        ],

        // Mange Customers
        'get-customers' => [
            'success' => 'Customers List Fetched Successfully',
            'failure' => 'No Customer Found',
        ],

        'get-customer' => [
            'success' => 'Customer Detail Fetched Successfully',
            'failure' => 'Customer Not Found',
        ],

        'customer-status' => [
            'active'   => 'Customer Activated Successfully',
            'inactive' => 'Customer Inactivated Successfully',
            'failure'  => 'Unable to Change Customer Status',
            'invalid'  => 'Customer Not Found',
        ],

        'make-admin'   => [
            'success' => 'Customer Successfully Marked as Admin',
            'failure' => 'Unable to make Customer as Admin',
            'invalid' => 'Customer Not Found',
        ],
    ],

    'user' => [

        'error' => 'Something went wrong, please try again ...',

        'validation' => [
            'fail' => 'Validation Failed!',
            'inactive' => 'INactive User',
            'incpass' => 'Incorrect Password',
            'incmail' => 'Incorrect Mail',
            'login' => 'Login Successfull'
        ],

        'register' => [
            'success' => 'OTP sent to your entered email address',
            'fail' => 'Failed to send OTP',
            'incmail' => 'Incorrect Mail',
            'incpass' => 'Incorrect Password'
        ],
    
        'otp' => [
            'otpver' => 'OTP Matched!! Registration Successfull',
            'otpnotver' => 'OTP not match!',
            'resendotp' => 'OTP resent successfuly',
            'alreadyverify' => 'Your mail is already verified',
            'registerfirst' => 'You have to register first',
        ],
        
        'forgetpass' => [

            'emailsent' => 'Email sent successfuly',
            'emailnotsent' => 'Unable to send mail',
            'notreg' => 'Mail not registered',
            'reset' => 'Password reset successfuly',
            'notreset' => 'Password not set',
            'passnotmatch' => 'Password not match',
            'swr' => 'Something Went Wrong'
        ],

        'logout' => [

            'success' => 'Logout Successful',
        ],

      
    ],

];
