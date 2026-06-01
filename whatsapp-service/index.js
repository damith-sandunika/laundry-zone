require('dotenv').config({ path: '../.env' });
const { makeWASocket, useMultiFileAuthState, DisconnectReason } = require('@whiskeysockets/baileys');
const pino = require('pino');
const mysql = require('mysql2/promise');
const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const fs = require('fs');

const app = express();
app.use(cors());
app.use(express.json());

// State variables
let currentQR = null;
let isConnected = false;
let sock = null;

// Database Connection
let dbConfig = {
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USERNAME || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_DATABASE || 'laudry_db'
};

let dbConnection;

async function initDB() {
    try {
        dbConnection = await mysql.createConnection(dbConfig);
        console.log('✅ Connected to MySQL Database');
    } catch (err) {
        console.error('❌ Failed to connect to database:', err.message);
    }
}

// Format Sri Lanka phone numbers (handles 07x, +94x, 94x, 7x)
function formatPhone(phone) {
    let clean = phone.replace(/\D/g, ''); // strip all non-digits
    if (clean.startsWith('94') && clean.length === 11) {
        // already 94xxxxxxxxx
    } else if (clean.startsWith('0') && clean.length === 10) {
        clean = '94' + clean.substring(1); // 07x -> 94x
    } else if (clean.length === 9) {
        clean = '94' + clean; // 7xxxxxxxx -> 947xxxxxxxx
    }
    return `${clean}@s.whatsapp.net`;
}

async function startWhatsAppService() {
    const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys');

    sock = makeWASocket({
        auth: state,
        printQRInTerminal: true,
        logger: pino({ level: 'silent' })
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;
        
        if (qr) {
            console.log('New QR generated...');
            currentQR = await qrcode.toDataURL(qr); // Save QR as base64 image
            isConnected = false;
        }

        if (connection === 'close') {
            isConnected = false;
            const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
            console.log('WhatsApp connection closed. Reconnecting:', shouldReconnect);
            if (shouldReconnect) {
                setTimeout(startWhatsAppService, 2000); // add delay and don't leak stack
            } else {
                console.log('Device Logged Out. Clearing auth folder...');
                currentQR = null;
                fs.rmSync('auth_info_baileys', { recursive: true, force: true });
                setTimeout(startWhatsAppService, 2000); // Restart to generate new QR
            }
        } else if (connection === 'open') {
            console.log('🟢 WhatsApp Service Connected & Ready!');
            currentQR = null;
            isConnected = true;
            startQueueProcessor();
        }
    });
}

// Queue Processor Loop
let queueInterval = null;
async function startQueueProcessor() {
    if (queueInterval) clearInterval(queueInterval);
    
    console.log('⏳ Starting WhatsApp Log Queue Processor...');
    
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
                console.log(`📤 Sending to ${log.phone} -> JID: ${jid}`);
                
                try {
                    // Check if number exists on WhatsApp
                    const [result] = await sock.onWhatsApp(jid);
                    if (!result || !result.exists) {
                        console.error(`❌ Number not on WhatsApp: ${log.phone}`);
                        await markAs(log.id, 'failed', 'Number not registered on WhatsApp');
                        continue;
                    }

                    await sock.sendMessage(jid, { text: log.message });
                    console.log(`✅ Sent WhatsApp to ${log.phone}`);
                    await markAs(log.id, 'sent', null);
                } catch (error) {
                    console.error(`❌ Failed to send to ${log.phone}:`, error.message);
                    await markAs(log.id, 'failed', error.message);
                }

                // Small delay between messages
                await new Promise(r => setTimeout(r, 1500));
            }
        } catch (dbError) {
            console.error('Database Error in Loop:', dbError.message);
        }

    }, 10000);
}

async function markAs(id, status, errorMsg) {
    try {
        await dbConnection.execute(
            "UPDATE whatsapp_logs SET status = ?, error_message = ?, sent_at = NOW() WHERE id = ?",
            [status, errorMsg, id]
        );
        console.log(`📝 Log ${id} marked as: ${status}`);
    } catch (err) {
        console.error('Failed to update log status:', err.message);
    }
}

// === EXPRESS API ENDPOINTS ===

// Check Status
app.get('/api/whatsapp/status', (req, res) => {
    res.json({
        connected: isConnected,
        qr: currentQR
    });
});

// Logout
app.post('/api/whatsapp/logout', async (req, res) => {
    try {
        if (sock) {
            sock.logout();
        } else {
            fs.rmSync('auth_info_baileys', { recursive: true, force: true });
        }
        isConnected = false;
        res.json({ success: true, message: 'Logged out successfully.' });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

const PORT = 3001;
app.listen(PORT, async () => {
    console.log(`🚀 Express API running on http://localhost:${PORT}`);
    await initDB();
    await startWhatsAppService();
});
