<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$cfg = file_exists(__DIR__ . '/config.local.php')
    ? require __DIR__ . '/config.local.php'
    : [
        'host' => getenv('DB_HOST'),
        'port' => getenv('DB_PORT'),
        'name' => getenv('DB_NAME'),
        'user' => getenv('DB_USER'),
        'pass' => getenv('DB_PASS'),
    ];

$ca = file_exists('/etc/secrets/ca.pem')
    ? '/etc/secrets/ca.pem'
    : __DIR__ . '/ca.pem';

$conn = mysqli_init();
$conn->ssl_set(null, null, $ca, null, null);
$conn->real_connect(
    $cfg['host'], $cfg['user'], $cfg['pass'],
    $cfg['name'], (int)$cfg['port'], null, MYSQLI_CLIENT_SSL
);
$conn->set_charset('utf8mb4');
date_default_timezone_set('Asia/Seoul');

if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $currentTime = time();
        $diff = $currentTime - $timestamp;

        if ($diff < 60) {
            return "방금 전";
        }
        
        $diff = floor($diff / 60);
        if ($diff < 60) {
            return $diff . "분 전";
        }
        
        $diff = floor($diff / 60);
        if ($diff < 24) {
            return $diff . "시간 전";
        }
        
        $diff = floor($diff / 24);
        if ($diff < 30) {
            return $diff . "일 전";
        }
        
        $diff = floor($diff / 30);
        if ($diff < 12) {
            return $diff . "개월 전";
        }
        
        $diff = floor($diff / 12);
        return $diff . "년 전";
    }
}
?>