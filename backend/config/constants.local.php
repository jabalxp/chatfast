<?php
// Database configuration - LOCAL (XAMPP)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sua senha do MySQL local, geralmente vazio no XAMPP
define('DB_NAME', 'rede_social');

// JWT Secret Key - Mesma chave para consistência
define('JWT_SECRET', 'rS9k4L#mP2xQ8wN@vB6jT5hY!fG3cD7aE1uZ0iO-redesocial-2025-secure-key');

// JWT expiration time (24 hours)
define('JWT_EXPIRATION', 86400);

// Base URL - LOCAL
define('BASE_URL', 'http://localhost:8000');

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('AVATARS_DIR', UPLOAD_DIR . 'avatars/');
define('POSTS_DIR', UPLOAD_DIR . 'posts/');
define('STORIES_DIR', UPLOAD_DIR . 'stories/');

// Allowed file types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm']);
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/wav', 'audio/ogg']);

// Max file sizes (in bytes)
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_VIDEO_SIZE', 50 * 1024 * 1024); // 50MB
define('MAX_AUDIO_SIZE', 10 * 1024 * 1024); // 10MB

// Create uploads directories if they don't exist
if (!file_exists(AVATARS_DIR)) {
    mkdir(AVATARS_DIR, 0755, true);
}
if (!file_exists(POSTS_DIR)) {
    mkdir(POSTS_DIR, 0755, true);
}
if (!file_exists(STORIES_DIR)) {
    mkdir(STORIES_DIR, 0755, true);
}
?>
