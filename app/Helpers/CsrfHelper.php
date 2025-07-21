<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class CsrfHelper
{
    /**
     * Ensure a valid CSRF token exists in the session
     */
    public static function ensureTokenExists()
    {
        if (!Session::has('_token')) {
            Session::regenerateToken();
        }
        
        return Session::token();
    }
}
