<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ApiService
{
    /**
     * Send a request to the API using the current session token
     */
    public static function request($method, $endpoint, $data = [])
    {
        $token = Session::get('auth_token');
        
        // Extract the actual token part if it contains a pipe
        $tokenParts = explode('|', $token);
        $accessToken = count($tokenParts) === 2 ? $tokenParts[1] : $token;
        
        return Http::withToken($accessToken)
            ->$method(url("/api/{$endpoint}"), $data);
    }
    
    /**
     * Get data from the API using the current session token
     */
    public static function get($endpoint, $data = [])
    {
        return self::request('get', $endpoint, $data);
    }
    
    /**
     * Post data to the API using the current session token
     */
    public static function post($endpoint, $data = [])
    {
        return self::request('post', $endpoint, $data);
    }
    
    /**
     * Put data to the API using the current session token
     */
    public static function put($endpoint, $data = [])
    {
        return self::request('put', $endpoint, $data);
    }
    
    /**
     * Delete data from the API using the current session token
     */
    public static function delete($endpoint, $data = [])
    {
        return self::request('delete', $endpoint, $data);
    }
}
