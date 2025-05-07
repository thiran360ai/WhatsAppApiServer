const express = require('express');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const fs = require('fs');
const path = require('path');
const QRCode = require('qrcode');
const cors = require('cors');
const fileUpload = require('express-fileupload');

const app = express();
const port = 4000;

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(fileUpload());

const clients = new Map();

// Create a new WhatsApp client
function createClient(sessionId) {
    const client = new Client({
        authStrategy: new LocalAuth({ clientId: sessionId }),
        puppeteer: {
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        }
    });

    client.qrCode = null;

    client.initialize();

    client.on('qr', (qr) => {
        console.log(`🆗 QR Code generated for session [${sessionId}]`);
        QRCode.toDataURL(qr, (err, url) => {
            if (!err) {
                client.qrCode = url;
            }
        });
    });

    client.on('ready', () => {
        console.log(`✅ Client [${sessionId}] is ready!`);
        client.qrCode = null;
    });

    client.on('authenticated', () => {
        console.log(`🔒 Authenticated [${sessionId}]`);
    });

    client.on('auth_failure', (msg) => {
        console.error(`❌ Auth failure [${sessionId}]:`, msg);
    });

    clients.set(sessionId, client);
}

// Optimized QR retrieval with wait logic
app.get('/get-qr', async (req, res) => {
    const sessionId = req.query.session;
    if (!sessionId) return res.status(400).send('Missing session parameter');

    let client = clients.get(sessionId);

    if (!client) {
        console.log(`🆕 Creating new session: ${sessionId}`);
        createClient(sessionId);
        client = clients.get(sessionId);
    }

    // Wait for QR code or authentication
    const waitForQr = () => {
        return new Promise((resolve) => {
            const interval = setInterval(() => {
                if (client.qrCode) {
                    clearInterval(interval);
                    resolve({ qr: client.qrCode });
                } else if (client.info) {
                    clearInterval(interval);
                    resolve({ status: 'authenticated' });
                }
            }, 500);

            setTimeout(() => {
                clearInterval(interval);
                resolve({ status: 'connecting' });
            }, 10000);
        });
    };

    const response = await waitForQr();
    res.json(response);
});

// Send Text Message
app.post('/send-message', async (req, res) => {
    const { number, message, session } = req.body;
    const client = clients.get(session);
    if (!client) return res.status(400).send('Invalid session');

    try {
        await client.sendMessage(`${number}@c.us`, message);
        res.send('✅ Message sent');
    } catch (err) {
        console.error('❌ Failed to send message:', err);
        res.status(500).send('Failed to send message');
    }
});

// Send Media File
app.post('/send-media', async (req, res) => {
    const { number, caption, session } = req.body;
    const file = req.files?.file;

    const client = clients.get(session);
    if (!client) return res.status(400).send('Invalid session');
    if (!file) return res.status(400).send('No file uploaded');

    const uploadPath = path.join(__dirname, 'uploads');
    if (!fs.existsSync(uploadPath)) {
        fs.mkdirSync(uploadPath);
    }

    const filePath = path.join(uploadPath, file.name);
    await file.mv(filePath);

    const media = MessageMedia.fromFilePath(filePath);

    try {
        await client.sendMessage(`${number}@c.us`, media, { caption });
        res.send('✅ Media sent');
    } catch (err) {
        console.error('❌ Failed to send media:', err);
        res.status(500).send('Failed to send media');
    }
});

// Logout session
app.post('/logout', async (req, res) => {
    const { session } = req.body;
    if (!session) return res.status(400).send('Missing session parameter');

    const client = clients.get(session);
    if (!client) return res.status(400).send('Invalid session');

    try {
        await client.logout();
        clients.delete(session);
        console.log(`👋 Client [${session}] logged out and removed`);
        res.send('✅ Logged out successfully');
    } catch (err) {
        console.error(`❌ Failed to logout [${session}]:`, err);
        res.status(500).send('Failed to logout');
    }
});

// List all active sessions
app.get('/sessions', (req, res) => {
    const activeSessions = Array.from(clients.keys());
    res.json({ sessions: activeSessions });
});

// Start the server
app.listen(port, () => {
    console.log(`🚀 Server running at http://localhost:${port}`);
});
