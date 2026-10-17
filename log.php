<?php
function client_ip() {
    $ip = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        $n = count($parts);
        if ($n >= 3) $ip = $parts[$n - 3];
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}

function log_access(mysqli $conn) {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method == 'HEAD' || $method == 'OPTIONS') return;

    try {
        $username = $_SESSION['user'] ?? null;
        $ip = client_ip();
        $path = mb_substr(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', 0, 255);
        $ua = mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
        $current_time = date('Y-m-d H:i:s');

        $stmt = $conn->prepare('INSERT INTO access_logs (username, ip, method, path, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssss', $username, $ip, $method, $path, $ua, $current_time);
        $stmt->execute();
        $stmt->close();
        if (random_int(1, 200) === 1) cleanup_logs($conn);
    } catch (Throwable $e) {
        error_log('log_access failed ' . $e->getMessage());
    }
}

function log_login(mysqli $conn, $username, string $action, bool $success, ?string $reason = null) {
    try {
        $ip = client_ip();
        $ua = mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
        $name = mb_substr((string)$username, 0, 50);
        $ok = $success ? 1 : 0;
        $current_time = date('Y-m-d H:i:s');

        $stmt = $conn->prepare('INSERT INTO login_logs (username, action, success, reason, ip, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssissss', $name, $action, $ok, $reason, $ip, $ua, $current_time);
        $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        error_log('log_login failed: ' . $e->getMessage());
    }
}

function cleanup_logs(mysqli $conn, int $days = 30)  {
    try {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        foreach (['access_logs', 'login_logs'] as $table) {
            $stmt = $conn->prepare("DELETE FROM $table WHERE created_at < ?");
            $stmt->bind_param('s', $cutoff);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Throwable $e) {
        error_log('cleanup_logs failed: ' . $e->getMessage());
    }
}