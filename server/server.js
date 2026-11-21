const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const http = require('http');
const { Server } = require('socket.io');

const app = express();
const PORT = process.env.PORT || 3001;

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const dbConfig = {
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'vphone',
  charset: 'utf8mb4'
};

let pool;

// Create upload directories
const uploadDirMessages = path.join(__dirname, '..', 'uploads', 'messages');
const uploadDirPosts = path.join(__dirname, '..', 'uploads', 'posts');
const uploadDirGroups = path.join(__dirname, '..', 'uploads', 'groups');
fs.mkdirSync(uploadDirMessages, { recursive: true });
fs.mkdirSync(uploadDirPosts, { recursive: true });
fs.mkdirSync(uploadDirGroups, { recursive: true });

// Multer for messages
const storageMessages = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadDirMessages),
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname) || '';
    cb(null, Date.now() + '-' + Math.round(Math.random() * 1e9) + ext);
  }
});
const uploadMessage = multer({
  storage: storageMessages,
  limits: { fileSize: 50 * 1024 * 1024 }
});

// Multer for posts
const storagePosts = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadDirPosts),
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname) || '';
    cb(null, Date.now() + '-' + Math.round(Math.random() * 1e9) + ext);
  }
});
const uploadPost = multer({
  storage: storagePosts,
  limits: { fileSize: 50 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    if (file.mimetype.startsWith('image/') || file.mimetype.startsWith('video/')) {
      cb(null, true);
      return;
    }
    cb(new Error('Only image and video uploads are supported'));
  }
});

// Multer for group avatars
const storageGroups = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadDirGroups),
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname) || '';
    cb(null, Date.now() + '-' + Math.round(Math.random() * 1e9) + ext);
  }
});
const uploadGroupAvatar = multer({
  storage: storageGroups,
  limits: { fileSize: 10 * 1024 * 1024 }
});

async function getDb() {
  if (!pool) {
    pool = mysql.createPool(dbConfig);
  }
  return pool;
}

async function addColumnIfMissing(conn, tableName, columnName, definitionSql) {
  try {
    const [rows] = await conn.execute(
      'SELECT COUNT(*) AS column_count FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
      [tableName, columnName]
    );
    if (rows[0].column_count === 0) {
      await conn.execute(`ALTER TABLE ${tableName} ADD COLUMN ${definitionSql}`);
    }
  } catch (error) {
    if (!/Duplicate column|already exists/i.test(error.message)) {
      console.error(`Column check failed for ${tableName}.${columnName}:`, error.message);
    }
  }
}

async function ensureTables() {
  const conn = await getDb();

  // Users
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(100) NOT NULL UNIQUE,
      password VARCHAR(255) NOT NULL,
      profile_image VARCHAR(255) DEFAULT NULL,
      last_seen TIMESTAMP NULL DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
  `);

  // Chat messages
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS chat_messages (
      id INT AUTO_INCREMENT PRIMARY KEY,
      sender_id INT NOT NULL,
      receiver_id INT NOT NULL,
      message TEXT NOT NULL,
      message_type VARCHAR(20) DEFAULT 'text',
      media_url VARCHAR(255) DEFAULT NULL,
      reply_to INT DEFAULT NULL,
      reactions TEXT DEFAULT NULL,
      edited_at TIMESTAMP NULL DEFAULT NULL,
      deleted_at TIMESTAMP NULL DEFAULT NULL,
      is_read TINYINT(1) DEFAULT 0,
      is_seen TINYINT(1) DEFAULT 0,
      seen_at TIMESTAMP NULL DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_sender_receiver (sender_id, receiver_id),
      INDEX idx_receiver_read (receiver_id, is_read)
    )
  `);

  // Chat stars, blocks, hidden
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS chat_stars (
      user_id INT NOT NULL,
      message_id INT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (user_id, message_id)
    )
  `);
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS chat_blocks (
      blocker_id INT NOT NULL,
      blocked_id INT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (blocker_id, blocked_id)
    )
  `);
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS chat_hidden (
      user_id INT NOT NULL,
      message_id INT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (user_id, message_id)
    )
  `);

  // ---- SOCIAL TABLES ----
  // Posts
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS posts (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      content TEXT,
      media_url VARCHAR(255) DEFAULT NULL,
      media_type VARCHAR(20) DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_user (user_id)
    )
  `);

  // Comments
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS post_comments (
      id INT AUTO_INCREMENT PRIMARY KEY,
      post_id INT NOT NULL,
      user_id INT NOT NULL,
      comment TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_post (post_id)
    )
  `);

  // Likes
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS post_likes (
      post_id INT NOT NULL,
      user_id INT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (post_id, user_id)
    )
  `);

  // Views
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS post_views (
      post_id INT NOT NULL,
      user_id INT NOT NULL,
      viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (post_id, user_id)
    )
  `);

  // Groups
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS groups (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(100) NOT NULL,
      created_by INT NOT NULL,
      avatar VARCHAR(255) DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
  `);

  // Group members (includes invites)
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS group_members (
      group_id INT NOT NULL,
      user_id INT NOT NULL,
      status ENUM('pending', 'active', 'declined') DEFAULT 'pending',
      invited_by INT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (group_id, user_id)
    )
  `);

  // ---- FRIEND SYSTEM ----
  await conn.execute(`
    CREATE TABLE IF NOT EXISTS friend_requests (
      id INT AUTO_INCREMENT PRIMARY KEY,
      sender_id INT NOT NULL,
      receiver_id INT NOT NULL,
      status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY unique_request (sender_id, receiver_id)
    )
  `);
}

// ----- Real-time layer (Socket.io) -----
const server = http.createServer(app);
const io = new Server(server, { cors: { origin: '*' } });

server.on('error', (error) => {
  if (error.code === 'EADDRINUSE') {
    console.error(`Port ${PORT} is already in use. The chat server may already be running.`);
  } else {
    console.error('The chat server could not start:', error.message);
  }
  process.exit(1);
});

const onlineUsers = new Map();

function emitToUser(userId, event, payload) {
  const sockets = onlineUsers.get(String(userId));
  if (sockets) sockets.forEach((socketId) => io.to(socketId).emit(event, payload));
}

function currentPresenceSnapshot() {
  const presence = {};
  onlineUsers.forEach((sockets, userId) => { presence[userId] = sockets.size > 0; });
  return presence;
}

async function isBlockedPair(conn, userA, userB) {
  const [rows] = await conn.execute(
    'SELECT 1 FROM chat_blocks WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?) LIMIT 1',
    [userA, userB, userB, userA]
  );
  return rows.length > 0;
}

async function areFriends(conn, userA, userB) {
  const [rows] = await conn.execute(
    `SELECT 1 FROM friend_requests
     WHERE status = 'accepted'
       AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
     LIMIT 1`,
    [userA, userB, userB, userA]
  );
  return rows.length > 0;
}

io.on('connection', (socket) => {
  socket.on('identify', async (userId) => {
    if (!userId) return;
    const key = String(userId);
    socket.userId = key;
    if (!onlineUsers.has(key)) onlineUsers.set(key, new Set());
    onlineUsers.get(key).add(socket.id);
    try {
      const conn = await getDb();
      await conn.execute('UPDATE users SET last_seen = CURRENT_TIMESTAMP WHERE id = ?', [userId]);
    } catch (error) { console.error(error); }
    socket.emit('presence_update', currentPresenceSnapshot());
    io.emit('presence_update', currentPresenceSnapshot());
  });

  socket.on('send_message', async (data, callback) => {
    try {
      const { sender_id, receiver_id, message, reply_to } = data || {};
      if (!sender_id || !receiver_id || !message || !String(message).trim()) {
        if (callback) callback({ success: false, message: 'Message cannot be empty' });
        return;
      }
      if (String(socket.userId) !== String(sender_id)) {
        if (callback) callback({ success: false, message: 'Identify your account before sending messages' });
        return;
      }
      const conn = await getDb();
      if (!(await areFriends(conn, sender_id, receiver_id))) {
        if (callback) callback({ success: false, message: 'Accept the friend request before chatting' });
        return;
      }
      if (await isBlockedPair(conn, sender_id, receiver_id)) {
        if (callback) callback({ success: false, message: 'Messaging is blocked between these users' });
        return;
      }
      const [result] = await conn.execute(
        'INSERT INTO chat_messages (sender_id, receiver_id, message, reply_to) VALUES (?, ?, ?, ?)',
        [sender_id, receiver_id, message, reply_to || null]
      );
      const [rows] = await conn.execute('SELECT * FROM chat_messages WHERE id = ?', [result.insertId]);
      const saved = rows[0];
      emitToUser(sender_id, 'new_message', saved);
      emitToUser(receiver_id, 'new_message', saved);
      if (callback) callback({ success: true, message: saved });
    } catch (error) {
      console.error(error);
      if (callback) callback({ success: false, message: 'Server error' });
    }
  });

  socket.on('typing', (data) => {
    const { user_id, receiver_id } = data || {};
    if (!user_id || !receiver_id) return;
    emitToUser(receiver_id, 'typing', { from: user_id });
  });

  socket.on('stop_typing', (data) => {
    const { user_id, receiver_id } = data || {};
    if (!user_id || !receiver_id) return;
    emitToUser(receiver_id, 'stop_typing', { from: user_id });
  });

  socket.on('disconnect', () => {
    if (socket.userId && onlineUsers.has(socket.userId)) {
      onlineUsers.get(socket.userId).delete(socket.id);
      if (onlineUsers.get(socket.userId).size === 0) {
        onlineUsers.delete(socket.userId);
        getDb().then((conn) => conn.execute('UPDATE users SET last_seen = CURRENT_TIMESTAMP WHERE id = ?', [socket.userId])).catch(() => {});
      }
      io.emit('presence_update', currentPresenceSnapshot());
    }
  });

  // ---- Friend request events ----
  socket.on('friend_request', (data) => {
    const { from, to, message } = data;
    emitToUser(to, 'friend_request', { from, message });
  });

  socket.on('friend_request_responded', (data) => {
    const { request_id, status } = data;
    // We'll broadcast to the other user, but we need to know who they are.
    // We'll send a generic event; the frontend will refresh friend data.
    io.emit('friend_request_responded', { request_id, status });
  });
});

// ----- ROUTES -----

// Health
app.get('/health', (req, res) => res.json({ ok: true }));

// Users
app.get('/api/users', async (req, res) => {
  try {
    const conn = await getDb();
    const [rows] = await conn.execute(`
      SELECT id, username, profile_image, last_seen, created_at
      FROM users ORDER BY username ASC
    `);
    res.json({
      success: true,
      users: rows.map(user => ({
        ...user,
        profile_image: user.profile_image ? `/virtual-phone/uploads/profile/${user.profile_image}` : null
      }))
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not load users' });
  }
});

// Login
app.post('/api/login', async (req, res) => {
  try {
    const { username, password } = req.body;
    if (!username || !password) return res.status(400).json({ success: false, message: 'Missing credentials' });
    const conn = await getDb();
    const [rows] = await conn.execute(
      'SELECT id, username, profile_image FROM users WHERE username = ? AND password = ?',
      [username, password]
    );
    if (rows.length === 0) {
      return res.status(401).json({ success: false, message: 'Invalid username or password' });
    }
    await conn.execute('UPDATE users SET last_seen = CURRENT_TIMESTAMP WHERE id = ?', [rows[0].id]);
    res.json({ success: true, user: rows[0] });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Login failed' });
  }
});

// Logout
app.post('/api/logout', async (req, res) => {
  try {
    const { user_id } = req.body;
    if (!user_id) return res.status(400).json({ success: false, message: 'Missing user id' });
    const conn = await getDb();
    await conn.execute('UPDATE users SET last_seen = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE id = ?', [user_id]);
    onlineUsers.delete(String(user_id));
    io.emit('presence_update', currentPresenceSnapshot());
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Logout failed' });
  }
});

// Presence snapshot
app.get('/api/presence', async (req, res) => {
  res.json({ success: true, presence: currentPresenceSnapshot() });
});

// ----- CHAT MESSAGES (existing) -----

// Send message with file
app.post('/api/messages', uploadMessage.single('file'), async (req, res) => {
  try {
    const { sender_id, receiver_id, message = '', reply_to = null, media_url: forwardedMediaUrl, message_type: forwardedType } = req.body;
    if (!sender_id || !receiver_id) {
      return res.status(400).json({ success: false, message: 'Missing required fields' });
    }
    const conn = await getDb();
    if (!(await areFriends(conn, sender_id, receiver_id))) {
      return res.status(403).json({ success: false, message: 'Accept the friend request before chatting' });
    }
    if (await isBlockedPair(conn, sender_id, receiver_id)) {
      return res.status(403).json({ success: false, message: 'Messaging is blocked between these users' });
    }
    let messageType = 'text';
    let mediaUrl = null;
    if (req.file) {
      const mimetype = req.file.mimetype;
      if (mimetype.startsWith('image/')) messageType = 'image';
      else if (mimetype.startsWith('video/')) messageType = 'video';
      else if (mimetype.startsWith('audio/')) messageType = 'audio';
      else messageType = 'file';
      mediaUrl = `/virtual-phone/uploads/messages/${req.file.filename}`;
    } else if (forwardedMediaUrl) {
      mediaUrl = forwardedMediaUrl;
      messageType = forwardedType || 'file';
    }
    const textContent = message || '';
    if (!mediaUrl && !textContent.trim()) {
      return res.status(400).json({ success: false, message: 'Message cannot be empty' });
    }
    await conn.execute(
      `INSERT INTO chat_messages (sender_id, receiver_id, message, message_type, media_url, reply_to)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [sender_id, receiver_id, textContent, messageType, mediaUrl, reply_to || null]
    );
    const [rows] = await conn.execute(
      'SELECT * FROM chat_messages WHERE sender_id = ? AND receiver_id = ? ORDER BY id DESC LIMIT 1',
      [sender_id, receiver_id]
    );
    const saved = rows[0];
    emitToUser(sender_id, 'new_message', saved);
    emitToUser(receiver_id, 'new_message', saved);
    res.json({ success: true, message: saved });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not send message' });
  }
});

// Get messages between two users
app.get('/api/messages/:senderId/:receiverId', async (req, res) => {
  try {
    const { senderId, receiverId } = req.params;
    const { q } = req.query;
    const conn = await getDb();
    if (!(await areFriends(conn, senderId, receiverId))) {
      return res.status(403).json({ success: false, message: 'You can only view chats with accepted friends' });
    }
    let sql = `
      SELECT cm.*
      FROM chat_messages cm
      LEFT JOIN chat_hidden ch ON ch.message_id = cm.id AND ch.user_id = ?
      WHERE cm.deleted_at IS NULL
        AND ch.message_id IS NULL
        AND ((cm.sender_id = ? AND cm.receiver_id = ?) OR (cm.sender_id = ? AND cm.receiver_id = ?))
    `;
    const params = [senderId, senderId, receiverId, receiverId, senderId];
    if (q && q.trim()) {
      sql += ' AND cm.message LIKE ?';
      params.push('%' + q.trim() + '%');
    }
    sql += ' ORDER BY cm.id ASC';
    const [rows] = await conn.execute(sql, params);

    const [starRows] = await conn.execute('SELECT message_id FROM chat_stars WHERE user_id = ?', [senderId]);
    const starredIds = new Set(starRows.map(r => r.message_id));
    const messages = rows.map(m => ({ ...m, is_starred: starredIds.has(m.id) }));

    await conn.execute(`
      UPDATE chat_messages
      SET is_read = 1, is_seen = 1, seen_at = CURRENT_TIMESTAMP
      WHERE receiver_id = ? AND sender_id = ? AND is_read = 0
    `, [senderId, receiverId]);

    emitToUser(receiverId, 'messages_seen', { by: senderId });

    res.json({ success: true, messages });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not load messages' });
  }
});

// Edit message
app.post('/api/messages/:messageId/edit', async (req, res) => {
  try {
    const { messageId } = req.params;
    const { message } = req.body;
    if (!message || !message.trim()) return res.status(400).json({ success: false, message: 'Message cannot be empty' });
    const conn = await getDb();
    await conn.execute('UPDATE chat_messages SET message = ?, edited_at = CURRENT_TIMESTAMP WHERE id = ?', [message, messageId]);
    const [rows] = await conn.execute('SELECT * FROM chat_messages WHERE id = ?', [messageId]);
    const updated = rows[0];
    if (updated) {
      emitToUser(updated.sender_id, 'message_updated', updated);
      emitToUser(updated.receiver_id, 'message_updated', updated);
    }
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not edit message' });
  }
});

// Delete for everyone
app.post('/api/messages/:messageId/delete', async (req, res) => {
  try {
    const { messageId } = req.params;
    const conn = await getDb();
    const [existing] = await conn.execute('SELECT * FROM chat_messages WHERE id = ?', [messageId]);
    await conn.execute('UPDATE chat_messages SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?', [messageId]);
    if (existing[0]) {
      emitToUser(existing[0].sender_id, 'message_deleted', { id: Number(messageId) });
      emitToUser(existing[0].receiver_id, 'message_deleted', { id: Number(messageId) });
    }
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not delete message' });
  }
});

// Delete for me only
app.post('/api/messages/:messageId/delete-me', async (req, res) => {
  try {
    const { messageId } = req.params;
    const { user_id } = req.body;
    if (!user_id) return res.status(400).json({ success: false, message: 'Missing user id' });
    const conn = await getDb();
    await conn.execute('INSERT IGNORE INTO chat_hidden (user_id, message_id) VALUES (?, ?)', [user_id, messageId]);
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not hide message' });
  }
});

// React to message
app.post('/api/messages/:messageId/react', async (req, res) => {
  try {
    const { messageId } = req.params;
    const { user_id, emoji } = req.body;
    if (!user_id || !emoji) return res.status(400).json({ success: false, message: 'Missing fields' });
    const conn = await getDb();
    const [rows] = await conn.execute('SELECT * FROM chat_messages WHERE id = ?', [messageId]);
    if (!rows.length) return res.status(404).json({ success: false, message: 'Message not found' });
    const msg = rows[0];
    const reactions = msg.reactions ? JSON.parse(msg.reactions) : {};
    if (reactions[user_id] === emoji) delete reactions[user_id];
    else reactions[user_id] = emoji;
    await conn.execute('UPDATE chat_messages SET reactions = ? WHERE id = ?', [JSON.stringify(reactions), messageId]);
    emitToUser(msg.sender_id, 'reaction_update', { message_id: Number(messageId), reactions });
    emitToUser(msg.receiver_id, 'reaction_update', { message_id: Number(messageId), reactions });
    res.json({ success: true, reactions });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not react to message' });
  }
});

// Star message
app.post('/api/messages/:messageId/star', async (req, res) => {
  try {
    const { messageId } = req.params;
    const { user_id } = req.body;
    const conn = await getDb();
    await conn.execute('INSERT IGNORE INTO chat_stars (user_id, message_id) VALUES (?, ?)', [user_id, messageId]);
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not star message' });
  }
});

// Unstar message
app.post('/api/messages/:messageId/unstar', async (req, res) => {
  try {
    const { messageId } = req.params;
    const { user_id } = req.body;
    const conn = await getDb();
    await conn.execute('DELETE FROM chat_stars WHERE user_id = ? AND message_id = ?', [user_id, messageId]);
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not unstar message' });
  }
});

// Get starred messages
app.get('/api/starred/:userId', async (req, res) => {
  try {
    const { userId } = req.params;
    const conn = await getDb();
    const [rows] = await conn.execute(`
      SELECT cm.* FROM chat_stars cs
      JOIN chat_messages cm ON cm.id = cs.message_id
      WHERE cs.user_id = ? AND cm.deleted_at IS NULL
      ORDER BY cs.created_at DESC
    `, [userId]);
    res.json({ success: true, messages: rows });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not load starred messages' });
  }
});

// Block user
app.post('/api/block', async (req, res) => {
  try {
    const { blocker_id, blocked_id } = req.body;
    if (!blocker_id || !blocked_id) return res.status(400).json({ success: false, message: 'Missing fields' });
    const conn = await getDb();
    await conn.execute('INSERT IGNORE INTO chat_blocks (blocker_id, blocked_id) VALUES (?, ?)', [blocker_id, blocked_id]);
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not block user' });
  }
});

// Unblock user
app.post('/api/unblock', async (req, res) => {
  try {
    const { blocker_id, blocked_id } = req.body;
    const conn = await getDb();
    await conn.execute('DELETE FROM chat_blocks WHERE blocker_id = ? AND blocked_id = ?', [blocker_id, blocked_id]);
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not unblock user' });
  }
});

// Get blocked users
app.get('/api/blocked/:userId', async (req, res) => {
  try {
    const { userId } = req.params;
    const conn = await getDb();
    const [rows] = await conn.execute('SELECT blocked_id FROM chat_blocks WHERE blocker_id = ?', [userId]);
    res.json({ success: true, blocked: rows.map(r => r.blocked_id) });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not load blocked users' });
  }
});

// Unread count
app.get('/api/unread/:userId', async (req, res) => {
  try {
    const { userId } = req.params;
    const conn = await getDb();
    const [unread] = await conn.execute(`
      SELECT cm.sender_id, COUNT(*) AS unread_count, u.username, u.profile_image
      FROM chat_messages cm
      JOIN users u ON u.id = cm.sender_id
      WHERE cm.receiver_id = ? AND cm.is_read = 0 AND cm.deleted_at IS NULL
      GROUP BY cm.sender_id, u.username, u.profile_image
    `, [userId]);
    const [notifications] = await conn.execute(`
      SELECT cm.id, cm.sender_id, cm.message, cm.message_type, cm.created_at,
             u.username AS sender_name, u.profile_image
      FROM chat_messages cm
      JOIN users u ON u.id = cm.sender_id
      WHERE cm.receiver_id = ? AND cm.is_read = 0 AND cm.deleted_at IS NULL
      ORDER BY cm.created_at DESC, cm.id DESC
      LIMIT 100
    `, [userId]);
    res.json({
      success: true,
      unread,
      notifications: notifications.map(notification => ({
        ...notification,
        profile_image: notification.profile_image
          ? `/virtual-phone/uploads/profile/${notification.profile_image}`
          : null
      }))
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not load unread messages' });
  }
});

// ============================================================
//  SOCIAL: POSTS
// ============================================================

// Get all posts (with likes, comments, views)
app.get('/api/posts', async (req, res) => {
  try {
    const conn = await getDb();
    const [posts] = await conn.execute(`
      SELECT p.*, u.username, u.profile_image
      FROM posts p
      LEFT JOIN users u ON p.user_id = u.id
      ORDER BY p.created_at DESC
    `);

    const postIds = posts.map(p => p.id);
    let likesMap = {}, commentsMap = {}, viewsMap = {};
    if (postIds.length) {
      const postPlaceholders = postIds.map(() => '?').join(', ');
      // Likes
      const [likes] = await conn.execute(
        `SELECT post_id, user_id FROM post_likes WHERE post_id IN (${postPlaceholders})`,
        postIds
      );
      likes.forEach(l => {
        if (!likesMap[l.post_id]) likesMap[l.post_id] = [];
        likesMap[l.post_id].push(l.user_id);
      });

      // Comments
      const [comments] = await conn.execute(`
        SELECT c.*, u.username, u.profile_image
        FROM post_comments c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.post_id IN (${postPlaceholders})
        ORDER BY c.created_at ASC
      `, postIds);
      comments.forEach(c => {
        if (!commentsMap[c.post_id]) commentsMap[c.post_id] = [];
        commentsMap[c.post_id].push(c);
      });

      // Views
      const [views] = await conn.execute(
        `SELECT post_id, user_id FROM post_views WHERE post_id IN (${postPlaceholders})`,
        postIds
      );
      views.forEach(v => {
        if (!viewsMap[v.post_id]) viewsMap[v.post_id] = [];
        viewsMap[v.post_id].push(v.user_id);
      });
    }

    const result = posts.map(p => ({
      ...p,
      likes: likesMap[p.id] || [],
      comments: commentsMap[p.id] || [],
      views: viewsMap[p.id] ? viewsMap[p.id].length : 0,
      user: { username: p.username, profile_image: p.profile_image }
    }));

    res.json({ success: true, posts: result });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not fetch posts' });
  }
});

app.get('/api/posts/:postId', async (req, res) => {
  try {
    const conn = await getDb();
    const [posts] = await conn.execute(`
      SELECT p.*, u.username, u.profile_image
      FROM posts p
      LEFT JOIN users u ON u.id = p.user_id
      WHERE p.id = ?
      LIMIT 1
    `, [req.params.postId]);
    if (!posts.length) return res.status(404).json({ success: false, message: 'Post not found' });

    const post = posts[0];
    const [likes] = await conn.execute('SELECT user_id FROM post_likes WHERE post_id = ?', [post.id]);
    const [comments] = await conn.execute(`
      SELECT c.*, u.username, u.profile_image
      FROM post_comments c
      LEFT JOIN users u ON u.id = c.user_id
      WHERE c.post_id = ?
      ORDER BY c.created_at ASC
    `, [post.id]);
    const [views] = await conn.execute('SELECT COUNT(*) AS count FROM post_views WHERE post_id = ?', [post.id]);
    res.json({
      success: true,
      post: { ...post, likes: likes.map(like => like.user_id), comments, views: views[0].count }
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not fetch post' });
  }
});

app.delete('/api/posts/:postId', async (req, res) => {
  try {
    const { user_id } = req.body;
    if (!user_id) return res.status(400).json({ success: false, message: 'User ID required' });
    const conn = await getDb();
    const [ownedPosts] = await conn.execute(
      'SELECT media_url FROM posts WHERE id = ? AND user_id = ?',
      [req.params.postId, user_id]
    );
    if (!ownedPosts.length) {
      return res.status(403).json({ success: false, message: 'You can only delete your own posts' });
    }
    await conn.execute('DELETE FROM post_likes WHERE post_id = ?', [req.params.postId]);
    await conn.execute('DELETE FROM post_comments WHERE post_id = ?', [req.params.postId]);
    await conn.execute('DELETE FROM post_views WHERE post_id = ?', [req.params.postId]);
    await conn.execute('DELETE FROM posts WHERE id = ? AND user_id = ?', [req.params.postId, user_id]);
    const mediaUrl = ownedPosts[0].media_url || '';
    const mediaFileName = path.basename(mediaUrl);
    if (mediaUrl.startsWith('/virtual-phone/uploads/posts/') && mediaFileName) {
      try {
        await fs.promises.unlink(path.join(uploadDirPosts, mediaFileName));
      } catch (error) {
        if (error.code !== 'ENOENT') throw error;
      }
    }
    io.emit('post_deleted', { post_id: Number(req.params.postId) });
    res.json({ success: true, message: 'Post deleted' });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not delete post' });
  }
});

// Get comments for a specific post
app.get('/api/posts/:postId/comments', async (req, res) => {
  try {
    const { postId } = req.params;
    const conn = await getDb();
    const [rows] = await conn.execute(`
      SELECT c.*, u.username, u.profile_image
      FROM post_comments c
      LEFT JOIN users u ON c.user_id = u.id
      WHERE c.post_id = ?
      ORDER BY c.created_at ASC
    `, [postId]);
    res.json({ success: true, comments: rows });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not fetch comments' });
  }
});

// Create a post
app.post('/api/posts', uploadPost.single('media'), async (req, res) => {
  try {
    const { user_id, content } = req.body;
    if (!user_id) return res.status(400).json({ success: false, message: 'User ID required' });
    if (!content && !req.file) return res.status(400).json({ success: false, message: 'Content or media required' });

    let media_url = null, media_type = null;
    if (req.file) {
      const mimetype = req.file.mimetype;
      if (mimetype.startsWith('image/')) media_type = 'image';
      else if (mimetype.startsWith('video/')) media_type = 'video';
      else media_type = 'file';
      media_url = `/virtual-phone/uploads/posts/${req.file.filename}`;
    }

    const conn = await getDb();
    const [result] = await conn.execute(
      'INSERT INTO posts (user_id, content, media_url, media_type) VALUES (?, ?, ?, ?)',
      [user_id, content || '', media_url, media_type]
    );
    const [newPost] = await conn.execute('SELECT * FROM posts WHERE id = ?', [result.insertId]);
    // Emit new post event
    const postData = newPost[0];
    const [userRow] = await conn.execute('SELECT username, profile_image FROM users WHERE id = ?', [user_id]);
    if (userRow.length) {
      postData.username = userRow[0].username;
      postData.profile_image = userRow[0].profile_image;
    }
    io.emit('new_post', postData);
    res.json({ success: true, post: postData });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not create post' });
  }
});

// Like a post
app.post('/api/posts/:postId/like', async (req, res) => {
  try {
    const { postId } = req.params;
    const { user_id } = req.body;
    if (!user_id) return res.status(400).json({ success: false, message: 'User ID required' });
    const conn = await getDb();
    await conn.execute('INSERT IGNORE INTO post_likes (post_id, user_id) VALUES (?, ?)', [postId, user_id]);
    io.emit('post_liked', { post_id: postId, user_id });
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not like post' });
  }
});

// Unlike a post
app.post('/api/posts/:postId/unlike', async (req, res) => {
  try {
    const { postId } = req.params;
    const { user_id } = req.body;
    if (!user_id) return res.status(400).json({ success: false, message: 'User ID required' });
    const conn = await getDb();
    await conn.execute('DELETE FROM post_likes WHERE post_id = ? AND user_id = ?', [postId, user_id]);
    io.emit('post_unliked', { post_id: postId, user_id });
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not unlike post' });
  }
});

// Comment on a post
app.post('/api/posts/:postId/comment', async (req, res) => {
  try {
    const { postId } = req.params;
    const { user_id, comment } = req.body;
    if (!user_id || !comment || !comment.trim()) {
      return res.status(400).json({ success: false, message: 'User and comment required' });
    }
    const conn = await getDb();
    await conn.execute(
      'INSERT INTO post_comments (post_id, user_id, comment) VALUES (?, ?, ?)',
      [postId, user_id, comment.trim()]
    );
    // Fetch the inserted comment with user info
    const [rows] = await conn.execute(`
      SELECT c.*, u.username, u.profile_image
      FROM post_comments c
      LEFT JOIN users u ON c.user_id = u.id
      WHERE c.id = LAST_INSERT_ID()
    `);
    const commentData = rows[0];
    io.emit('post_commented', { post_id: postId, comment: commentData });
    res.json({ success: true, comment: commentData });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not add comment' });
  }
});

// Record a view
app.post('/api/posts/:postId/view', async (req, res) => {
  try {
    const { postId } = req.params;
    const { user_id } = req.body;
    if (!user_id) return res.status(400).json({ success: false, message: 'User ID required' });
    const conn = await getDb();
    await conn.execute('INSERT IGNORE INTO post_views (post_id, user_id) VALUES (?, ?)', [postId, user_id]);
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not record view' });
  }
});

app.use((error, req, res, next) => {
  if (error instanceof multer.MulterError) {
    const message = error.code === 'LIMIT_FILE_SIZE'
      ? 'Media must be 50 MB or smaller'
      : 'Could not upload this media';
    return res.status(400).json({ success: false, message });
  }
  if (error.message === 'Only image and video uploads are supported') {
    return res.status(400).json({ success: false, message: error.message });
  }
  next(error);
});

// ============================================================
//  SOCIAL: GROUPS
// ============================================================

// Get groups for current user (includes pending invites)
app.get('/api/groups', async (req, res) => {
  try {
    const userId = req.query.user_id;
    if (!userId) return res.status(400).json({ success: false, message: 'User ID required' });

    const conn = await getDb();

    const [groupRows] = await conn.execute(`
      SELECT g.*,
             (SELECT COUNT(*) FROM group_members WHERE group_id = g.id AND status = 'active') AS active_members
      FROM groups g
      WHERE EXISTS (
        SELECT 1 FROM group_members gm
        WHERE gm.group_id = g.id
          AND gm.user_id = ?
          AND gm.status IN ('active', 'pending')
      )
      ORDER BY g.created_at DESC
    `, [userId]);

    const groupIds = groupRows.map(g => g.id);
    let membersMap = {};
    if (groupIds.length) {
      const [members] = await conn.execute(`
        SELECT gm.group_id, gm.user_id, gm.status, gm.invited_by
        FROM group_members gm
        WHERE gm.group_id IN (?)
      `, [groupIds]);
      members.forEach(m => {
        if (!membersMap[m.group_id]) membersMap[m.group_id] = [];
        membersMap[m.group_id].push({
          user_id: m.user_id,
          status: m.status,
          invited_by: m.invited_by
        });
      });
    }

    const groups = groupRows.map(row => {
      const members = membersMap[row.id] || [];
      return {
        ...row,
        members: members.map(m => m.user_id),
        invites: members,
        active_member_count: row.active_members
      };
    });

    res.json({ success: true, groups });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not fetch groups' });
  }
});

// Create a group (with avatar upload)
app.post('/api/groups', uploadGroupAvatar.single('avatar'), async (req, res) => {
  let connection;
  try {
    const { name, creator_id, members } = req.body;
    if (!name || !creator_id) {
      return res.status(400).json({ success: false, message: 'Name and creator required' });
    }
    const pool = await getDb();
    connection = await pool.getConnection();
    await connection.beginTransaction();

    let avatarUrl = null;
    if (req.file) {
      avatarUrl = `/virtual-phone/uploads/groups/${req.file.filename}`;
    }

    const [result] = await connection.execute(
      'INSERT INTO groups (name, created_by, avatar) VALUES (?, ?, ?)',
      [name, creator_id, avatarUrl]
    );
    const groupId = result.insertId;

    // Add creator as active member
    await connection.execute(
      'INSERT INTO group_members (group_id, user_id, status, invited_by) VALUES (?, ?, ?, ?)',
      [groupId, creator_id, 'active', creator_id]
    );

    let memberIds = [];
    try {
      memberIds = members ? JSON.parse(members) : [];
    } catch(e) {}

    if (memberIds && memberIds.length) {
      for (const uid of memberIds) {
        if (uid !== creator_id) {
          await connection.execute(
            'INSERT INTO group_members (group_id, user_id, status, invited_by) VALUES (?, ?, ?, ?)',
            [groupId, uid, 'pending', creator_id]
          );
        }
      }
    }

    await connection.commit();
    connection.release();

    // Emit group created event
    const groupData = { id: groupId, name, created_by: creator_id, created_at: new Date().toISOString() };
    io.emit('group_created', groupData);
    // Also emit invites
    memberIds.forEach(uid => {
      if (uid !== creator_id) {
        io.emit('group_invite', {
          user_id: uid,
          group_id: groupId,
          group_name: name,
          created_by: creator_id,
          created_by_username: 'User' // ideally fetch username
        });
      }
    });

    res.json({ success: true, group_id: groupId });
  } catch (error) {
    console.error(error);
    if (connection) {
      try {
        await connection.rollback();
        connection.release();
      } catch (rollbackError) {
        console.error('Rollback failed:', rollbackError);
      }
    }
    res.status(500).json({ success: false, message: 'Could not create group' });
  }
});

// Get group details with members
app.get('/api/groups/:groupId', async (req, res) => {
  try {
    const { groupId } = req.params;
    const conn = await getDb();
    const [groupRows] = await conn.execute('SELECT * FROM groups WHERE id = ?', [groupId]);
    if (!groupRows.length) return res.status(404).json({ success: false, message: 'Group not found' });
    const group = groupRows[0];

    const [members] = await conn.execute(`
      SELECT gm.user_id, gm.status, gm.invited_by, u.username, u.profile_image
      FROM group_members gm
      LEFT JOIN users u ON gm.user_id = u.id
      WHERE gm.group_id = ?
      ORDER BY gm.created_at ASC
    `, [groupId]);

    res.json({ success: true, group: { ...group, members_details: members } });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not fetch group details' });
  }
});

// Respond to group invite (accept/decline)
app.post('/api/groups/:groupId/respond', async (req, res) => {
  try {
    const { groupId } = req.params;
    const { user_id, status } = req.body;
    if (!user_id || !status || !['active', 'declined'].includes(status)) {
      return res.status(400).json({ success: false, message: 'Invalid status or missing user' });
    }
    const conn = await getDb();
    const [existing] = await conn.execute(
      'SELECT status FROM group_members WHERE group_id = ? AND user_id = ?',
      [groupId, user_id]
    );
    if (!existing.length) {
      return res.status(400).json({ success: false, message: 'User not invited to this group' });
    }
    await conn.execute(
      'UPDATE group_members SET status = ? WHERE group_id = ? AND user_id = ?',
      [status, groupId, user_id]
    );
    io.emit('group_invite_responded', { group_id: groupId, user_id, status });
    res.json({ success: true });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not respond to invite' });
  }
});

// ============================================================
//  FRIEND SYSTEM
// ============================================================

// Get friends of a user (accepted friend requests)
app.get('/api/friends/:userId', async (req, res) => {
  try {
    const userId = req.params.userId;
    const conn = await getDb();
    const [rows] = await conn.execute(`
      SELECT 
        CASE 
          WHEN sender_id = ? THEN receiver_id 
          ELSE sender_id 
        END AS friend_id
      FROM friend_requests
      WHERE (sender_id = ? OR receiver_id = ?) AND status = 'accepted'
    `, [userId, userId, userId]);
    const friends = rows.map(r => r.friend_id);
    res.json({ success: true, friends });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not fetch friends' });
  }
});

// Get friend statuses for a user (for each other user)
app.get('/api/friend-statuses/:userId', async (req, res) => {
  try {
    const userId = req.params.userId;
    const conn = await getDb();
    const [rows] = await conn.execute(`
      SELECT 
        CASE 
          WHEN sender_id = ? THEN receiver_id 
          ELSE sender_id 
        END AS other_user,
        status,
        sender_id,
        receiver_id
      FROM friend_requests
      WHERE sender_id = ? OR receiver_id = ?
    `, [userId, userId, userId]);
    const statuses = {};
    rows.forEach(row => {
      const other = row.other_user;
      if (row.sender_id == userId && row.status === 'pending') {
        statuses[other] = 'pending'; 
      } else if (row.receiver_id == userId && row.status === 'pending') {
        statuses[other] = 'pending'; 
      } else if (row.status === 'accepted') {
        statuses[other] = 'friend';
      } else if (row.status === 'declined') {
        statuses[other] = 'none';
      }
    });
    res.json({ success: true, statuses });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not fetch friend statuses' });
  }
});

// Get friend requests for a user (incoming pending)
app.get('/api/friend-requests/:userId', async (req, res) => {
  try {
    const userId = req.params.userId;
    const conn = await getDb();
    const [rows] = await conn.execute(`
      SELECT id, sender_id, receiver_id, status, created_at
      FROM friend_requests
      WHERE receiver_id = ? AND status = 'pending'
    `, [userId]);
    res.json({ success: true, requests: rows });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not fetch friend requests' });
  }
});

// Send a friend request
app.post('/api/friend-request', async (req, res) => {
  try {
    const { sender_id, receiver_id } = req.body;
    if (!sender_id || !receiver_id) {
      return res.status(400).json({ success: false, message: 'Missing sender or receiver' });
    }
    if (sender_id === receiver_id) {
      return res.status(400).json({ success: false, message: 'Cannot send request to yourself' });
    }
    const conn = await getDb();
    const [existing] = await conn.execute(
      'SELECT status FROM friend_requests WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)',
      [sender_id, receiver_id, receiver_id, sender_id]
    );
    if (existing.length) {
      if (existing[0].status === 'accepted') {
        return res.status(400).json({ success: false, message: 'Already friends' });
      } else if (existing[0].status === 'pending') {
        return res.status(400).json({ success: false, message: 'Request already pending' });
      } else {
        // If declined, update to pending
        await conn.execute(
          'UPDATE friend_requests SET status = "pending", updated_at = CURRENT_TIMESTAMP WHERE id = ?',
          [existing[0].id]
        );
        return res.json({ success: true, message: 'Request resent' });
      }
    }
    await conn.execute(
      'INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, ?)',
      [sender_id, receiver_id, 'pending']
    );
    // Emit real-time event
    const [userRow] = await conn.execute('SELECT username FROM users WHERE id = ?', [sender_id]);
    const username = userRow.length ? userRow[0].username : 'Someone';
    io.emit('friend_request', {
      from: sender_id,
      to: receiver_id,
      from_username: username,
      message: `${username} sent you a friend request 💌`
    });
    res.json({ success: true, message: 'Friend request sent' });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not send friend request' });
  }
});

// Respond to a friend request (accept/decline)
app.post('/api/friend-request/:requestId/respond', async (req, res) => {
  try {
    const requestId = req.params.requestId;
    const { status } = req.body; // 'accepted' or 'declined'
    if (!status || !['accepted', 'declined'].includes(status)) {
      return res.status(400).json({ success: false, message: 'Invalid status' });
    }
    const conn = await getDb();
    const [rows] = await conn.execute('SELECT * FROM friend_requests WHERE id = ?', [requestId]);
    if (!rows.length) {
      return res.status(404).json({ success: false, message: 'Request not found' });
    }
    await conn.execute(
      'UPDATE friend_requests SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?',
      [status, requestId]
    );
    io.emit('friend_request_responded', { request_id: requestId, status });
    res.json({ success: true, message: 'Request updated' });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not respond to friend request' });
  }
});

// Unfriend (remove accepted friend request)
app.post('/api/friends/remove', async (req, res) => {
  try {
    const { user_id, friend_id } = req.body;
    if (!user_id || !friend_id) {
      return res.status(400).json({ success: false, message: 'Missing user_id or friend_id' });
    }
    const conn = await getDb();
    await conn.execute(
      `DELETE FROM friend_requests 
       WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) 
       AND status = 'accepted'`,
      [user_id, friend_id, friend_id, user_id]
    );
    res.json({ success: true, message: 'Unfriended' });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: 'Could not unfriend' });
  }
});

// ============================================================
//  STATIC FILES
// ============================================================
app.use(express.static(path.join(__dirname, '..')));

// Start server
ensureTables()
  .then(() => {
    server.listen(PORT, () => {
      console.log(`Virtual Phone server (with Socket.IO) running on http://localhost:${PORT}`);
    });
  })
  .catch(async (error) => {
    console.error('Database initialization failed; server was not started:', error);
    if (pool) await pool.end().catch(poolError => console.error('Could not close the database pool:', poolError));
    process.exitCode = 1;
  });