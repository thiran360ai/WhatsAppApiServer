<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QRController extends Controller
{
    // Show QR Code page
    public function index()
    {
        return view('qr.show', ['qr' => null, 'error' => null]);
    }

    // Get QR Code for a session
    public function getQr(Request $request)
    {
        $session = $request->query('session'); // session name from query

        if (!$session) {
            return response()->json(['error' => 'Missing user id'], 400);
        }

        try {
            $response = Http::get('http://localhost:4000/get-qr', [
                'session' => $session
            ]);

            if ($response->ok()) {
                $data = $response->json();
                return response()->json($data);
            } else {
                return response()->json(['error' => 'Failed to get QR'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error connecting to Node.js server'], 500);
        }
    }

    // Logout WhatsApp session
    public function logout(Request $request)
    {
        $session = $request->query('session'); // session name from query

        if (!$session) {
            return redirect('/qr')->with('error', 'Missing user id');
        }

        try {
            $response = Http::get('http://localhost:4000/logout-whatsapp', [
                'session' => $session
            ]);

            if ($response->ok()) {
                session()->flush();
                return redirect('/qr')->with('success', 'Logged out successfully');
            } else {
                return redirect('/qr')->with('error', 'Failed to logout from WhatsApp');
            }
        } catch (\Exception $e) {
            return redirect('/qr')->with('error', 'Error connecting to Node.js server');
        }
    }
}
