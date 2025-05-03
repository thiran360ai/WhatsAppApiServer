<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load("messages.xlsx");
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();

foreach ($rows as $index => $row) {
    if ($index === 0) continue; // Skip the header row (phone number, message)
    
    $number = preg_replace('/\s+/', '', trim($row[0]));  // Clean whitespace
    $message = trim($row[1]);

    if (empty($number) || empty($message)) {
        echo "❌ Skipping empty row at line ".($index+1)."\n";
        continue;
    }

    $data = [
        "number" => $number,
        "message" => $message
    ];

    $options = [
        "http" => [
            "header"  => "Content-type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents("http://localhost:4000/send-message", false, $context);  // Changed port to 4000

    if ($result === FALSE) {
        echo "❌ Failed to send message to $number\n";
    } else {
        echo "✅ Message sent to $number\n";
    }
}
?>
