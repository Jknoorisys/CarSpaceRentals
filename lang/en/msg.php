<?php

return [

    'Validation Failed!' => 'Validation Failed!',
    'error' => 'Something went wrong, please try again ...',

    // Admin By javeriya Kauser
    'admin' => [

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
            'failure' => 'No Such Customer Found',
        ],

        'customer-status' => [
            'success' => 'Customer Updated Successfully',
            'failure' => 'Unable to Update Customer',
        ],
    ],

    'user' => [

        'error' => 'Something went wrong, please try again ...',

        'validation' => [
            'fail' => 'Validation Failed!',

        ],

        'register' => [
            'success' => 'OTP sent to your entered email address',
            'fail' => 'Failed to send OTP',
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
