<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Libraries\Services;

class UserController extends Controller
{
    public function profile(Request $req)
    {
        $req->all();
        return $req;
    }
}
