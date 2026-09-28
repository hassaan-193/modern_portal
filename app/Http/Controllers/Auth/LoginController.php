<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle post-authentication redirects, prioritizing safe intended URLs (e.g. shared Memos).
     */
    protected function authenticated(Request $request, $user)
    {
        // 1. Honor intended destination if present (e.g. clicking a shared memo link while logged out)
        if ($request->session()->has('url.intended')) {
            $intended = $request->session()->get('url.intended');
            $parsedHost = parse_url($intended, PHP_URL_HOST);

            // Anti-open-redirect security: only allow relative URLs or current host
            if ($parsedHost === null || $parsedHost === $request->getHost()) {
                return redirect()->intended($this->redirectPath());
            }
        }

        // 2. Role-specific default landings
        if ($user->hasRole('AMC Reporter') && !$user->can('projects')) {
            return redirect()->route('projects.showForm');
        }

        if ($user->hasRole('Staff Requester') && !$user->can('stafprofile')) {
            return redirect()->route('own_requests.index');
        }
    }
}
