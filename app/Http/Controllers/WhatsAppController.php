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
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // Retrieve users stored in session (in-memory storage)
        $storedUsers = session('users', []);

        if (
            isset($storedUsers[$request->user_id]) &&
            Hash::check($request->password, $storedUsers[$request->user_id]['password'])
        ) {
            // Login success
            $request->session()->regenerate();
            session(['user_id' => $request->user_id]);

            return redirect()->intended('/msg');
        }

        // Login failed
        return back()->withErrors([
            'user_id' => 'Invalid User ID or Password.',
        ])->withInput($request->only('user_id'));
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string|max:255',
            'password' => 'required|confirmed|min:8',
        ]);

        $storedUsers = session('users', []);

        // Check if user already exists
        if (isset($storedUsers[$request->user_id])) {
            return back()->withErrors([
                'user_id' => 'User ID already exists.',
            ])->withInput($request->only('user_id'));
        }

        // Save new user
        $storedUsers[$request->user_id] = [
            'user_id' => $request->user_id,
            'password' => Hash::make($request->password),
        ];

        session(['users' => $storedUsers]);

        return redirect('/login')->with('status', 'Registration successful. Please log in.');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
