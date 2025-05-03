<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'password' => 'required',
        ]);

        // Get user data from local storage (simulated)
        $storedUsers = session('users', []); // Get from session, default to []

        // Check if the user exists
        if (isset($storedUsers[$request->user_id]) && 
            Hash::check($request->password, $storedUsers[$request->user_id]['password'])) {
            
            // Authentication successful
            $request->session()->regenerate();
            session(['user_id' => $request->user_id]); // Store in session
            return redirect()->intended('/welcome');
        }

        // Authentication failed
        return back()->withErrors([
            'user_id' => 'Invalid User ID or Password',
        ])->withInput($request->only('user_id'));
    }

    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'user_id' => 'required|unique:users,user_id|max:255', //still need unique check
            'password' => 'required|confirmed|min:8',
        ]);

        // Store user data in simulated local storage (session)
        $storedUsers = session('users', []); // Get existing users or default to []

        // Check for duplicate user_id (simulated -  we've added a validation rule, but this is important)
        if (isset($storedUsers[$request->user_id])) {
             return back()->withErrors([
                'user_id' => 'User ID already exists.',
            ])->withInput($request->only('user_id'));
        }
        
        $storedUsers[$request->user_id] = [ // Use user_id as key
            'user_id' => $request->user_id,
            'password' => Hash::make($request->password),
        ];
        session(['users' => $storedUsers]); //save to session

        // Log the user in
        session(['user_id' => $request->user_id]);
        return redirect()->intended('/welcome');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout(); //remove auth

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
