<?php

namespace App\Libraries;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\PlatformNotification;
Use Config\constants;

class Services
{
    public function getSignedAccessTokenForUser($isUservalid, array $claims)
    {
        return JWTAuth::customClaims($claims)->fromUser($isUservalid);
    }

}
