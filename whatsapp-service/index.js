require('dotenv').config({ path: '../.env' });
const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const pino = require('pino');
const mysql = require('mysql2/promise');
const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const fs = require('fs');

const app = express();
app.use(cors());
app.use(express.json());

// State
let currentQR = null;
let isConnected = false;
let isStarting = false;
let sock = null;
let queueInterval = null;

// DB Config
const dbConfig = {
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USERNAME || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_DATABASE || 'laudry_db'
};
let dbConnection;

async function initDB() {
    try {
        dbConnection = await mysql.createConnection(dbConfig);
        console.log('✅ Connected to MySQL');
    } catch (err) {
        console.error('❌ DB Error:', err.message);
    }
}

function formatPhone(phone) {
    let clean = phone.replace(/\D/g, '');
    if (clean.startsWith('94') && clean.length === 11) {
        // ok
    } else if (clean.startsWith('0') && clean.length === 10) {
        clean = '94' + clean.substring(1);
    } else if (clean.length === 9) {
        clean = '94' + clean;
    }
    return `${clean}@s.whatsapp.net`;
}

function clearAuth() {
    try {
        if (fs.existsSync('auth_info_baileys')) {
            fs.rmSync('auth_info_baileys', { recursive: true, force: true });
            console.log('🗑️ Auth cleared');
        }
    } catch (e) {
        console.error('Failed to clear auth:', e.message);
    }
}

async function startWhatsAppService() {
    if (isStarting) return;
    isStarting = true;
    currentQR = null;

    try {
        const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys');

        const { version, isLatest } = await fetchLatestBaileysVersion();
        console.log(`Using WA v${version.join('.')}, isLatest: ${isLatest}`);

        sock = makeWASocket({
            version,
            auth: state,
            printQRInTerminal: true,
            logger: pino({ level: 'silent' }),
            browser: ['SmartLaundry', 'Chrome', '1.0.0'],
            generateHighQualityLinkPreview: true,
            syncFullHistory: false
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                console.log('📱 QR Generated - scan now!');
                currentQR = await qrcode.toDataURL(qr);
                isConnected = false;
            }

            if (connection === 'close') {
                isConnected = false;
                isStarting = false;
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                console.log('🔴 Connection closed. Code:', statusCode);

                if (statusCode === DisconnectReason.loggedOut) {
                    console.log('Logged out - clearing auth');
                    clearAuth();
                } else if (statusCode === 500 || statusCode === 401) {
                    console.log('Session corrupted (Bad Session) - clearing auth');
                    clearAuth();
                } else if (statusCode === 515) {
                    console.log('Restart required - restarting without clearing auth');
                }
                setTimeout(startWhatsAppService, 3000);

            } else if (connection === 'open') {
                console.log('🟢 WhatsApp Connected!');
                currentQR = null;
                isConnected = true;
                isStarting = false;
                startQueueProcessor();
            }
        });

    } catch (err) {
        console.error('Startup error:', err.message);
        isStarting = false;
        setTimeout(startWhatsAppService, 5000);
    }
}

function startQueueProcessor() {
    if (queueInterval) clearInterval(queueInterval);
    console.log('⏳ Queue processor started');

    queueInterval = setInterval(async () => {
        if (!dbConnection || !isConnected || !sock) return;
        try {
            const [rows] = await dbConnection.execute(
                "SELECT * FROM whatsapp_logs WHERE status = 'pending' LIMIT 5"
            );
            for (const log of rows) {
                if (!log.phone || !log.message) {
                    await markAs(log.id, 'failed', 'Missing phone or message');
                    continue;
                }
                const jid = formatPhone(log.phone);
                try {
                    await sock.sendMessage(jid, { text: log.message });
                    console.log(`✅ Sent to ${log.phone}`);
                    await markAs(log.id, 'sent', null);
                } catch (error) {
                    console.error(`❌ Failed ${log.phone}:`, error.message);
                    await markAs(log.id, 'failed', error.message);
                }
                await new Promise(r => setTimeout(r, 1500));
            }
        } catch (e) {
            console.error('Queue error:', e.message);
        }
    }, 10000);
}

async function markAs(id, status, errorMsg) {
    try {
        await dbConnection.execute(
            "UPDATE whatsapp_logs SET status = ?, error_message = ?, sent_at = NOW() WHERE id = ?",
            [status, errorMsg, id]
        );
    } catch (e) {
        console.error('markAs error:', e.message);
    }
}

// ─── API Routes ───────────────────────────────────────────

app.get('/api/whatsapp/status', (req, res) => {
    res.json({
        connected: isConnected,
        qr: currentQR,
        starting: isStarting
    });
});

app.post('/api/whatsapp/logout', async (req, res) => {
    try {
        if (sock) { try { await sock.logout(); } catch (e) {} }
        isConnected = false;
        currentQR = null;
        isStarting = false;
        if (queueInterval) clearInterval(queueInterval);
        clearAuth();
        res.json({ success: true });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// NEW: Reset endpoint - clears auth and regenerates QR
app.post('/api/whatsapp/reset', async (req, res) => {
    try {
        if (sock) { try { sock.end(); } catch (e) {} }
        isConnected = false;
        currentQR = null;
        isStarting = false;
        if (queueInterval) clearInterval(queueInterval);
        clearAuth();
        setTimeout(startWhatsAppService, 1500);
        res.json({ success: true, message: 'Reset done. QR will appear in ~5 seconds.' });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

const PORT = 3001;
app.listen(PORT, async () => {
    console.log(`🚀 WhatsApp Service running on http://localhost:${PORT}`);
    await initDB();
    await startWhatsAppService();
});
