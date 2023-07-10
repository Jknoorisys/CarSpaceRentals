<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
class Jwtmiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');
        $token  = null;

        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            $token = $matches[1];
        }

        if (is_null($header) && is_null($token)) {
            return response()->json(
                [
                    'status'       =>  "failed",
                    'errors'       =>  "",
                    'message'      =>  trans('validation.custom.input.tokenotfound'),
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }
        try {
            $user                  =  JWTAuth::parseToken()->getPayload();
            $request->uuid         =  $user['uuid'];

        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){

                return response()->json(
                            [
                                'status'       =>  "failed",
                                'errors'       =>  "",
                                'message'      =>  trans('validation.custom.input.invalidtoken'),
                            ],
                            Response::HTTP_UNAUTHORIZED
                );

            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){

                return response()->json(
                            [
                                'status'       =>  "failed",
                                'errors'       =>  "",
                                'message'      =>  "Expired!",
                            ],
                            Response::HTTP_UNAUTHORIZED
                );

            }else{
                return response()->json(
                            [
                                'status'       =>  "failed",
                                'errors'       =>  "",
                                'message'      =>  "Token Not Found",
                            ],
                            Response::HTTP_BAD_REQUEST
                );

            }
        }

        return $next($request);
    }
}
