<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send WhatsApp Message</title>
</head>
<body>
    <h1>Send WhatsApp Message</h1>

    <form action="{{ url('/send-message') }}" method="POST">
        @csrf
        <label for="number">Phone Number:</label>
        <input type="text" name="number" id="number" required><br><br>

        <label for="message">Message:</label>
        <textarea name="message" id="message" required></textarea><br><br>

        <button type="submit">Send Message</button>
    </form>
</body>
</html>
