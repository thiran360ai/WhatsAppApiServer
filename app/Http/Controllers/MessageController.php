<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MessageController extends Controller
{
    public function sendBulkFromExcelWithImage(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif,mp4,mp3,pdf,doc,docx|max:16384',
            'session' => 'required|string',
        ]);

        $imagePath = null;
        $fullImagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $fullImagePath = storage_path('app/public/' . $imagePath);
        }

        $spreadsheet = IOFactory::load($request->file('excel_file')->getPathname());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $results = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header

            $number = trim($row[0]);
            $message = trim($row[1]);

            if (empty($number) || empty($message)) {
                $results[] = "❌ Skipped row " . ($index + 1) . " (missing number or message)";
                continue;
            }

            try {
                if ($fullImagePath && file_exists($fullImagePath)) {
                    $response = Http::asMultipart()
                        ->attach('file', file_get_contents($fullImagePath), basename($fullImagePath))
                        ->post('http://localhost:4000/send-media', [
                            ['name' => 'number', 'contents' => $number],
                            ['name' => 'caption', 'contents' => $message],
                            ['name' => 'session', 'contents' => $request->session],
                        ]);
                } else {
                    $response = Http::post('http://localhost:4000/send-message', [
                        'number' => $number,
                        'message' => $message,
                        'session' => $request->session,
                    ]);
                }

                if ($response->successful()) {
                    $results[] = "✅ Sent to $number";
                } else {
                    $results[] = "❌ Failed to send to $number - " . $response->body();
                }
            } catch (\Exception $e) {
                $results[] = "❌ Error sending to $number: " . $e->getMessage();
            }
        }

        return back()->with('results', $results);
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

            if ($response->successful()) {
                return back()->with('status', '✅ Session logged out successfully.');
            } else {
                return back()->with('error', '❌ Failed to logout session: ' . $response->body());
            }
        } catch (\Exception $e) {
            return back()->with('error', '❌ Error logging out session: ' . $e->getMessage());
        }
    }

    // Check if a specific session is active
    public function checkSession(Request $request)
    {
        $request->validate([
            'session' => 'required|string',
        ]);

        try {
            $response = Http::get('http://localhost:4000/check-session', [
                'session' => $request->session,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'active' => $data['active'] ?? false
                ]);
            } else {
                return response()->json([
                    'active' => false,
                    'error' => $response->body(),
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'active' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // 🔥 New: Get all active sessions
    public function getAllActiveSessions()
    {
        try {
            $response = Http::get('http://localhost:4000/list-sessions');

            if ($response->successful()) {
                $sessions = $response->json();
                return response()->json([
                    'sessions' => $sessions
                ]);
            } else {
                return response()->json([
                    'sessions' => [],
                    'error' => $response->body(),
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'sessions' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
