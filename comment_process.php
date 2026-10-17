<?php
session_start();
require 'db.php';

$post_id = (int)($_POST['post_id'] ?? 0);
$content = trim((string)($_POST['content'] ?? ''));

if (!isset($_SESSION['user'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href = 'auth.php';</script>";
    exit;
}

if ($post_id < 1) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

if (mb_strlen($content) > 300) {
    echo "<script>alert('댓글은 최대 300자까지 쓸 수 있습니다.'); history.back();</script>";
    exit;
}

if ($content == '') {
    echo "<script>alert('잘못된 입력입니다.'); history.back();</script>";
    exit;
}

$current_time = date('Y-m-d H:i:s');

try {
    $stmt = $conn->prepare("INSERT INTO comments (post_id, username, content, created_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $post_id, $_SESSION['user'], $content, $current_time);
    $stmt->execute();
} catch (mysqli_sql_exception $e) {
    echo "<script>alert('댓글을 등록할 수 없습니다.'); location.href = 'index.php';</script>";
    exit;
}

header("Location: view.php?id=$post_id#comments");
exit;