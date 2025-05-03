<?php
$url = 'http://localhost:3000/send-message';

$data = [
    'number' => '919876543210', // Replace with actual number
    'message' => 'Hello from PHP using WhatsApp Web API.'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "❌ Message sending failed.";
} else {
    echo "✅ Message sent!";
}
?>
