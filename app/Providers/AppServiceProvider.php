<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Using Laravel HTTP client (Guzzle under the hood)

class WhatsAppController extends Controller
{
    /**
     * Send a WhatsApp message via Node.js server
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(Request $request)
    {
        $number = $request->input('number');
        $message = $request->input('message');

        // Validate inputs
        if (!$number || !$message) {
            return response()->json(['error' => 'Missing number or message'], 400);
        }

        try {
            // Send a POST request to the Node.js server
            $response = Http::post('http://localhost:4000/send-message', [
                'number' => $number,
                'message' => $message,
            ]);

            // Check if the response status is successful
            if ($response->successful()) {
                return response()->json(['status' => '✅ Message sent successfully']);
            } else {
                return response()->json(['error' => '❌ Failed to send message from Node.js server'], 500);
            }

        } catch (\Exception $e) {
            // Handle any errors that occur while sending the request
            return response()->json(['error' => '❌ Error: ' . $e->getMessage()], 500);
        }
    }
}
