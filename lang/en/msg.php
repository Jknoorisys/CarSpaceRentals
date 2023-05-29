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
            'invalid' => 'Car Brand Not Found',
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

        'edit-location' => [
            'success' => 'Rental Location Updated Successfully',
            'failure' => 'Unable to Update Rental Location',
            'invalid' => 'Location Not Found',
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

        // Manage Location Lines
        'add-line' => [
            'success' => 'Lane Details Added Successfully',
            'failure' => 'Unable to Add Lane Details',
        ],

        'lane-status' => [
            'active'   => 'Lane Activated Successfully',
            'inactive' => 'Lane Inactivated Successfully',
            'failure'  => 'Unable to Change Lane Status',
            'invalid'  => 'Lane Not Found',
        ],

        'get-lines' => [
            'success' => 'Lane List Fetched Successfully',
            'failure' => 'Lane Not Found',
        ],

        'get-line-details' => [
            'success' => 'Line Details Fetched Successfully',
            'failure' => 'Line Not Found',
        ],

        'edit-plot' => [
            'success' => 'Plot Details Updated Successfully',
            'failure' => 'Unable to Update Plot Details',
            'invalid' => 'Plot Not Found',
        ],

        // Manage Admins
        'get-admins' => [
            'success' => 'Admins List Fetched Successfully',
            'failure' => 'No Admin Found',
        ],

        'admin-status' => [
            'active'   => 'Admin Activated Successfully',
            'inactive' => 'Admin Inactivated Successfully',
            'failure'  => 'Unable to Change Admin Status',
            'invalid'  => 'Admin Not Found',
        ],

        'get-admin-activities' => [
            'success' => 'Admin Login Activities Fetched Successfully',
            'failure' => 'No Admin Login Activity Found',
        ],

        'get-admin-actions-history' => [
            'success' => 'Admins Action History Fetched Successfully',
            'failure' => 'No Admin Action History Found',
        ],

        // Manage Featured Car Price
        'get-featured-car-price' => [
            'success' => 'Featured Car Price Fetched Successfully',
            'failure' => 'Featured Car Price Not Found',
        ],

        'edit-featured-car-price' => [
            'success' => 'Featured Car Price Updated Successfully',
            'failure' => 'Unable to Update Featured Car Price',
            'invalid' => 'Featured Car Price Not Found',
        ],

        'featured-get-car' => [
            'success' => 'Featured Car List',
            'failed' => 'Not Featured Car'
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
            'success' => 'OTP Sent on Registered Email',
            'fail' => 'Failed to Send OTP, Please try again...',
            'incmail' => 'Incorrect Email',
            'incpass' => 'Incorrect Password'
        ],
    
        'otp' => [
            'otpver' => 'Registration Successfull!',
            'otpnotver' => 'OTP does not match!',
            'resendotp' => 'OTP Resent successfuly',
            'alreadyverify' => 'Your email is already verified',
            'registerfirst' => 'Please Register First...',
            'failure' => 'Unable to Verify OTP, Please Try Again'
        ],
        
        'forgetpass' => [

            'emailsent' => 'Email sent successfuly',
            'emailnotsent' => 'Unable to send mail',
            'notreg' => 'Mail not registered',
            'reset' => 'Password reset successfuly',
            'notreset' => 'Password not set',
            'passnotmatch' => 'Password not match',
            'swr' => 'Something Went Wrong',
            'tokennotmatch' => 'Token does not match!'
        ],

        'logout' => [
            'success' => 'Logout Successful',
            'fail' => 'Incorrect ID',
        ],

        'profile' => [
            'success' => 'User Profile details',
            'usernotfound' => 'User Not Found',
            'image' => 'Profile Photo Updated Successfully',
            'notimage' => 'Unable to update the profile',
            'updated' => 'User Details Updated Successfully',
            'notupdated' => 'Unable to update the details',
            
        ],

        'get-car' => [
            'success' => 'Car List',
            'failure' => 'Car Not Found',
            'featured' => 'Featured Car List',
            'notfeature' => 'No Featured Car List'
        ],

        'car_details' => [
            'success' => 'Car Details',
            'failure' => 'Car Details Not Found',
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
        ],

        'get-lines' => [
            'success' => 'Lane List Based On Selected Location Fetched Successfully',
            'failure' => 'Lane Not Found',
        ],

        'get-available-plots' => [
            'success' => 'Plots Based On Selected Date, Location and Lane Fetched Successfully',
            'failure' => 'Plots Not Found',
        ],

        'get-selected-plots' => [
            'success' => 'Selected Plot Details Fetched Successfully',
            'failure' => 'No Plot Selected',
        ],

        // Dealer Car Module By Javeriya
        'get-dealer-cars' => [
            'success' => 'Dealer Car List Fetched Successfully',
            'failure' => 'No Car Found',
        ],

        'get-dealer-plots' => [
            'success' => 'Dealer Plots List Fetched Successfully',
            'failure' => 'No Plot Found',
        ],

        'get-dealer-locations' => [
            'success' => 'Dealer Locations List Fetched Successfully',
            'failure' => 'No Location Found',
        ],

        'get-available-plots' => [
            'success' => 'Available Plots List Fetched Successfully',
            'failure' => 'No Plot Available',
            'invalid-duration-type' => 'Invalid Duration Type',
        ],

        // Dealer Auth and Profile Module By Aaisha Shaikh
        'profile' => [
            'success' => 'Dealer Profile',
            'dealernotfound' => 'Dealer not Found!',
            'image' => 'Profile Updated Successfuly',
            'notimage' => 'Profile not Updated',
            'updated' => 'Dealer Profile Updated Successfuly',
            'notupdated' => 'Unable to update profile'
        ],

        'car' => [
            'success' => 'Car added successfuly',
            'fail' => 'Unable to add add',
            'carnotfound' => 'Car not found',
            'carupdated' => 'Car Updated Successfuly!',
            'carnotupdate' => 'Unable to update the car',
            'cardetail' => 'Car Details'
        ],

    ],

    // Helpers
    'helper' => [
        'invalid-admin'    => 'Admin Not Found',
        'invalid-super-admin' => 'Not a Super Admin',
        'invalid-customer' => 'Customer Not Found',
        'invalid-dealer'   => 'Dealer Not Found',
        'invalid-location' => 'Location Not Found',
        'invalid-line'     => 'Lane Not Found',
        'invalid-plot'     => 'Plot Not Found',
        'invalid-car'      => 'Car Not Found',
    ],

    // email
    'email' => [
        'Email Verification' => 'Email Verification',
        'Forget Password'    => 'Forget Password',
        'Reset Your Password' => 'Reset Your Password',
        'Welcome on Board'   => 'Welcome on Board',
        'Visit Our Platform' => 'Visit Our Platform',
        'Find us'            => 'Find us',
        'Terms & Conditions' => 'Terms & Conditions',
        'Privacy Policy'     => 'Privacy Policy',
        'FAQs'               => 'FAQs',
        'Dear'               => 'Dear',
        'Let’s get you Registered with us!' => 'Let’s get you Registered with us!',
        'Your One time Password to Complete your Registrations is' => 'Your One time Password to Complete your Registrations is',
        'Need to reset your password?'  => 'Need to reset your password?',
        'No problem! Just click on the button below and you’ll be on yor way.' => 'No problem! Just click on the button below and you’ll be on yor way.',
    ],

    'No problem! Just click on the button below and you’ll be on your way.' => 'No problem! Just click on the button below and you’ll be on your way.',

];
