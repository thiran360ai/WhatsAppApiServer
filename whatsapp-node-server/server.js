const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const express = require('express');

const app = express();
const port = 4000; // Change port to 4000

// Middleware to parse JSON requests
app.use(express.json());

// Create a new WhatsApp client
const client = new Client({
    authStrategy: new LocalAuth()
});

// Event: When QR code is generated
client.on('qr', (qr) => {
    console.log('Scan this QR code with your WhatsApp:');
    qrcode.generate(qr, { small: true });
});

// Event: When the client is ready
client.on('ready', () => {
    console.log('✅ WhatsApp client is ready!');
});

// API endpoint to send a message
app.post('/send-message', async (req, res) => {
    const { number, message } = req.body;
    const formattedNumber = number + '@c.us'; // Add the WhatsApp suffix

    try {
        await client.sendMessage(formattedNumber, message); // Send the message
        res.status(200).json({ status: 'Message sent successfully' });
    } catch (err) {
        console.error('❌ Error sending message:', err);
        res.status(500).json({ error: 'Failed to send message' });
    }
});

// Start the server and listen on port 4000
app.listen(port, () => {
    console.log(`🚀 API server is running at http://localhost:${port}`);
});

// Initialize WhatsApp client
client.initialize();
