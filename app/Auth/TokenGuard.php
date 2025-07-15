<?php

namespace App\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class TokenGuard implements Guard
{
    use GuardHelpers;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;
        $token = $this->getTokenFromRequest();

        if (!empty($token)) {
            // Custom token format: "custom_token_1"
            if (strpos($token, 'custom_token_') === 0) {
                $tokenId = (int) str_replace('custom_token_', '', $token);
                
                // Find token by ID
                $tokenModel = PersonalAccessToken::find($tokenId);
                
                if ($tokenModel && 
                    ($tokenModel->expires_at === null || $tokenModel->expires_at > now())) {
                    // Find the user
                    $user = User::find($tokenModel->tokenable_id);
                    
                    if ($user) {
                        $this->user = $user;
                        return $user;
                    }
                }
            } 
            // Support for regular Sanctum tokens as well
            else {
                $tokenParts = explode('|', $token);
                
                if (count($tokenParts) === 2) {
                    $tokenId = $tokenParts[0];
                    
                    // Find the token by ID
                    $tokenModel = PersonalAccessToken::find($tokenId);
                    
                    if ($tokenModel && 
                        ($tokenModel->expires_at === null || $tokenModel->expires_at > now()) &&
                        ($tokenModel->original_token === $token || !$tokenModel->original_token)) {
                        
                        // Find the user
                        $user = User::find($tokenModel->tokenable_id);
                        
                        if ($user) {
                            $this->user = $user;
                            return $user;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return false;
    }

    /**
     * Get the token for the current request.
     *
     * @return string|null
     */
    protected function getTokenFromRequest()
    {
        $token = $this->request->bearerToken();

        if (empty($token)) {
            $token = $this->request->query('api_token');
        }

        if (empty($token)) {
            $token = $this->request->input('api_token');
        }

        if (empty($token)) {
            $token = session('auth_token');
        }

        return $token;
    }
}
