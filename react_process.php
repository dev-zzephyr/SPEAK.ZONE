<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    echo "
    <script>
    alert('로그인이 필요합니다.');
    location.href = 'auth.php';
    </script>
    ";
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$type = $_POST['type'] ?? '';
$cols = ['like' => 'likes', 'hate' => 'hates'];

if ($id <= 0 || !isset($cols[$type])) {
    echo "
    <script>
    alert('잘못된 접근입니다.');
    location.href = 'index.php';
    </script>
    ";
    exit;
}

function change(mysqli $conn, string $col, int $delta, int $id) {
    $stmt = $conn->prepare("UPDATE posts SET $col = GREATEST($col + ?, 0) WHERE id = ?");
    $stmt->bind_param('ii', $delta, $id);
    $stmt->execute();
}

$_SESSION['reacted'] ??= [];
$prev = $_SESSION['reacted'][$id] ?? null;

if ($prev == $type) { // 같은 것
    change($conn, $cols[$type], -1, $id);
    unset($_SESSION['reacted'][$id]);
}
else {
    if ($prev != null) { // 다른 것
        change($conn, $cols[$prev], -1, $id);
    }
    change($conn, $cols[$type], 1, $id);
    $_SESSION['reacted'][$id] = $type;
}

header("Location: view.php?id=$id");
exit;
?>