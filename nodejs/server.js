const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const express = require('express');

const app = express();
const port = 4000;

// Middleware to parse JSON
app.use(express.json());

// Initialize WhatsApp Client
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    }
});

let qrCodeData = '';
let clientReady = false;

// QR Code Event
client.on('qr', (qr) => {
    qrCodeData = qr;  // Save QR code data
});

// Ready Event
client.on('ready', () => {
    clientReady = true;  // Set client as ready
});

// Health Check Endpoint
app.get('/health', (req, res) => {
    res.status(200).send('✅ API server is up and running!');
});

// Status Endpoint
app.get('/status', (req, res) => {
    const response = {
        status: clientReady ? '✅ WhatsApp client is ready!' : '🕒 Waiting for QR code scan...',
        qrCode: qrCodeData ? qrcode.toDataURL(qrCodeData) : null, // Send QR code as data URL
    };
    res.json(response);
});

// Send Message Endpoint
app.post('/send-message', async (req, res) => {
    const { number, message } = req.body;

    if (!number || !message) {
        return res.status(400).json({ error: 'Missing number or message field.' });
    }

    const formattedNumber = number.toString().replace(/[^0-9]/g, '') + '@c.us';

    try {
        await client.sendMessage(formattedNumber, message);
        res.status(200).json({ status: '✅ Message sent successfully' });
    } catch (err) {
        res.status(500).json({ error: `❌ Failed to send message: ${err.message || err}` });
    }
});

// Start Server
app.listen(port, () => {
    console.log(`🚀 API server is running at http://localhost:${port}`);
});

// Start WhatsApp Client
client.initialize();

