<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Get the response for a logout action.
     */
    public function toResponse($request)
    {
        return redirect()->route('reseller.login');
    }
}
