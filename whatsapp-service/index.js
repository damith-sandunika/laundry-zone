require('dotenv').config({ path: '../.env' });
const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const pino = require('pino');
const mysql = require('mysql2/promise');
const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(cors());
app.use(express.json());

// State for multi-sessions
const sessions = new Map(); // branch_id => { sock, qr, isConnected, isStarting }
let dbConnection;

const dbConfig = {
    host: process.env.DB_HOST || '127.0.0.1',
    user: process.env.DB_USERNAME || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_DATABASE || 'laundry_saas_db'
};

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
    } else if (clean.startsWith('0') && clean.length === 10) {
        clean = '94' + clean.substring(1);
    } else if (clean.length === 9) {
        clean = '94' + clean;
    }
    return `${clean}@s.whatsapp.net`;
}

function getAuthDir(branchId) {
    return path.join(__dirname, 'auth_info', `branch_${branchId}`);
}

function clearAuth(branchId) {
    try {
        const dir = getAuthDir(branchId);
        if (fs.existsSync(dir)) {
            fs.rmSync(dir, { recursive: true, force: true });
            console.log(`🗑️ Auth cleared for branch ${branchId}`);
        }
    } catch (e) {
        console.error(`Failed to clear auth for branch ${branchId}:`, e.message);
    }
}

async function startWhatsAppService(branchId) {
    let session = sessions.get(branchId) || { sock: null, qr: null, isConnected: false, isStarting: false };
    if (session.isStarting) return;
    
    session.isStarting = true;
    session.qr = null;
    sessions.set(branchId, session);

    try {
        const authDir = getAuthDir(branchId);
        if (!fs.existsSync(authDir)) {
            fs.mkdirSync(authDir, { recursive: true });
        }

        const { state, saveCreds } = await useMultiFileAuthState(authDir);
        const { version } = await fetchLatestBaileysVersion();

        const sock = makeWASocket({
            version,
            auth: state,
            printQRInTerminal: false,
            logger: pino({ level: 'silent' }),
            browser: [`Branch ${branchId}`, 'Chrome', '1.0.0'],
        });

        session.sock = sock;

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                console.log(`📱 QR Generated for branch ${branchId}`);
                session.qr = await qrcode.toDataURL(qr);
                session.isConnected = false;
            }

            if (connection === 'close') {
                session.isConnected = false;
                session.isStarting = false;
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                
                if (statusCode === DisconnectReason.loggedOut || statusCode === 500 || statusCode === 401) {
                    clearAuth(branchId);
                }
                
                setTimeout(() => startWhatsAppService(branchId), 3000);
            } else if (connection === 'open') {
                console.log(`🟢 WhatsApp Connected for branch ${branchId}!`);
                session.qr = null;
                session.isConnected = true;
                session.isStarting = false;
            }
        });

    } catch (err) {
        console.error(`Startup error branch ${branchId}:`, err.message);
        session.isStarting = false;
        setTimeout(() => startWhatsAppService(branchId), 5000);
    }
}

// Queue processor
setInterval(async () => {
    if (!dbConnection) return;
    try {
        // Group messages by branch_id, send 1 per branch per 5 seconds to prevent rate limiting
        const [rows] = await dbConnection.execute(
            "SELECT id, branch_id, phone, message FROM whatsapp_logs WHERE status = 'pending' AND branch_id IS NOT NULL ORDER BY id ASC LIMIT 50"
        );
        
        if (rows.length === 0) return;
        
        // Group by branch
        const byBranch = {};
        for (const row of rows) {
            if (!byBranch[row.branch_id]) byBranch[row.branch_id] = [];
            byBranch[row.branch_id].push(row);
        }

        for (const branchId of Object.keys(byBranch)) {
            const session = sessions.get(Number(branchId));
            if (!session || !session.isConnected || !session.sock) {
                // Try to start session if not started
                if (!session || (!session.isStarting && !session.isConnected && !session.qr)) {
                    startWhatsAppService(Number(branchId));
                }
                continue;
            }
            
            // Send only ONE message per branch per tick to enforce delay
            const log = byBranch[branchId][0];
            const jid = formatPhone(log.phone);
            try {
                await session.sock.sendMessage(jid, { text: log.message });
                console.log(`✅ Sent to ${log.phone} (Branch ${branchId})`);
                await dbConnection.execute("UPDATE whatsapp_logs SET status = 'sent', sent_at = NOW() WHERE id = ?", [log.id]);
            } catch (error) {
                console.error(`❌ Failed ${log.phone} (Branch ${branchId}):`, error.message);
                await dbConnection.execute("UPDATE whatsapp_logs SET status = 'failed', error_message = ? WHERE id = ?", [error.message, log.id]);
            }
        }
    } catch (e) {
        console.error('Queue error:', e.message);
    }
}, 10000); // 10 second delay tick

// ─── API Routes ───────────────────────────────────────────

app.get('/api/whatsapp/status/:branch_id', (req, res) => {
    const branchId = Number(req.params.branch_id);
    const session = sessions.get(branchId);
    
    if (!session) {
        return res.json({ connected: false, qr: null, starting: false });
    }
    
    res.json({
        connected: session.isConnected,
        qr: session.qr,
        starting: session.isStarting
    });
});

app.post('/api/whatsapp/start/:branch_id', (req, res) => {
    const branchId = Number(req.params.branch_id);
    startWhatsAppService(branchId);
    res.json({ success: true, message: 'Starting session' });
});

app.post('/api/whatsapp/logout/:branch_id', async (req, res) => {
    const branchId = Number(req.params.branch_id);
    const session = sessions.get(branchId);
    
    try {
        if (session && session.sock) {
            try { await session.sock.logout(); } catch(e) {}
        }
        clearAuth(branchId);
        sessions.delete(branchId);
        res.json({ success: true });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

const PORT = 3001;
app.listen(PORT, async () => {
    console.log(`🚀 Multi-Session WhatsApp Service running on http://localhost:${PORT}`);
    await initDB();
});
