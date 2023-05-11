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
        'Dealer'        => 'Dealer',
        'Activated'     => 'Activated',
        'Inactivated'   => 'Inactivated',

        // Manage Car Brands
        'get-brands' => [
            'success' => 'Car Brands Fetched Successfully',
            'failure' => 'No Car Brands Found',
        ],

        'get-brand' => [
            'success' => 'Car Brand Detail Fetched Successfully',
            'failure' => 'Car Brand Not Found',
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

        'get-customer-activities' => [
            'success' => 'Customer Login Activities Fetched Successfully',
            'failure' => 'No Customer Login Activity Found',
        ],

        // Mange Dealers
        'get-dealers' => [
            'success' => 'Dealers List Fetched Successfully',
            'failure' => 'No Dealer Found',
        ],

        'get-dealer' => [
            'success' => 'Dealer Detail Fetched Successfully',
            'failure' => 'Dealer Not Found',
        ],

        'dealer-status' => [
            'active'   => 'Dealer Activated Successfully',
            'inactive' => 'Dealer Inactivated Successfully',
            'failure'  => 'Unable to Change Dealer Status',
            'invalid'  => 'Dealer Not Found',
        ],

        'make-dealer-admin'   => [
            'success' => 'Dealer Successfully Marked as Admin',
            'failure' => 'Unable to make Dealer as Admin',
            'invalid' => 'Dealer Not Found',
        ],

        'get-dealer-activities' => [
            'success' => 'Dealer Login Activities Fetched Successfully',
            'failure' => 'No Dealer Login Activity Found',
        ],

        'get-dealer-details' => [
            'success' => 'Dealer Details Fetched Successfully',
            'failure' => 'Dealer Not Found',
        ],

        'get-dealer-cars' => [
            'success' => 'Dealer Car List Fetched Successfully',
            'failure' => 'Dealer Car Not Found',
        ],

        'get-dealer-plots' => [
            'success' => 'Dealer Booked Plot List Fetched Successfully',
            'failure' => 'Dealer Booked Plot Not Found',
        ],

        // Manage Locations
        'add-location' => [
            'success' => 'Rental Location Added Successfully',
            'failure' => 'Unable to Add Rental Location',
        ],

        'get-locations' => [
            'success' => 'Locations List Fetched Successfully',
            'failure' => 'Location Not Found',
        ],

        'get-location-details' => [
            'success' => 'Locations Details Fetched Successfully',
            'failure' => 'Location Not Found',
        ],

        'location-status' => [
            'active'   => 'Location Activated Successfully',
            'inactive' => 'Location Inactivated Successfully',
            'failure'  => 'Unable to Change Location Status',
            'invalid'  => 'Location Not Found',
        ],
    ],

    // User By Aisha Shaikh 
    'user' => [

        'error' => 'Something went wrong, please try again ...',

        'validation' => [
            'fail' => 'Validation Failed!',
            'inactive' => 'Inactive User',
            'incpass' => 'Incorrect Password',
            'incmail' => 'Incorrect Email',
            'login' => 'Login Successfull'
        ],

        'register' => [
            'success' => 'OTP sent to your entered email address',
            'fail' => 'Failed to send OTP',
            'incmail' => 'Incorrect Email',
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
            'swr' => 'Something Went Wrong',
            'tokennotmatch' => 'Token not match!'
        ],

        'logout' => [
            'success' => 'Logout Successful',
        ],

      
    ],


    //Dealer By Aaisha Shaikh

    'dealer' => [

        'profile' => [

            'success' => 'Dealer Profile',
            'dealernotfound' => 'Dealer not Found!',
            'image' => 'Profile Updated Successfuly',
            'notimage' => 'Profile not Updated',
            'updated' => 'Dealer Profile Updated Successfuly',
            'notupdated' => 'Unable to update profile'
        ],
    ],

    // Dealer
    'dealer' => [
        // Location Module By Javeriya Kauser
        'get-locations' => [
            'success' => 'Locations List Fetched Successfully',
            'failure' => 'Location Not Found',
        ],

        'get-location-details' => [
            'success' => 'Locations Details Fetched Successfully',
            'failure' => 'Location Not Found',
        ]
    ],
];
