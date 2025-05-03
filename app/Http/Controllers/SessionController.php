<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SessionController extends Controller
{
    public function getQrCode(Request $request)
    {
        $request->validate([
            'session' => 'required|string'
        ]);

        try {
            $response = Http::get('http://localhost:4000/get-qr', [
                'session' => $request->session,
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function listSessions()
    {
        try {
            $response = Http::get('http://localhost:4000/list-sessions');

            return response()->json([
                'success' => true,
                'sessions' => $response->json()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function logoutSession(Request $request)
    {
        $request->validate([
            'session' => 'required|string',
        ]);

        try {
            $response = Http::post('http://localhost:4000/logout', [
                'session' => $request->session,
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
