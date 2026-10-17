<?php
session_start();
require 'db.php';

$auth = isset($_SESSION['user']) ? ($_SESSION['user'] == 'admin' ? 2 : 1) : 0;

if($auth == 0) {
    echo "
    <script>
    alert('로그인이 필요합니다.');
    location.href = 'auth.php';
    </script>
    ";
    exit;
}

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "
    <script>
    alert('잘못된 접근입니다.');
    location.href = 'index.php';
    </script>
    ";
    exit;
}

$title = trim(($_POST['title'] ?? ''));
$category = trim(($_POST['category'] ?? '일반'));
$content = trim($_POST['content'] ?? '');
$username = $_SESSION['user'];
$action = $_POST['action'] ?? '';

if (mb_strlen($title) < 1 || mb_strlen($content) < 1) {
    echo "
    <script>
    alert('제목과 본문 모두 최소 1글자여야 합니다.');
    history.back();
    </script>
    ";
    exit;
}

if (mb_strlen($title) > 20) {
    echo "
    <script>
    alert('제목은 최대 20자여야 합니다.');
    history.back();
    </script>
    ";
    exit;
}

if (mb_strlen($category) > 10) {
    echo "
    <script>
    alert('카테고리는 최대 10자여야 합니다.');
    history.back();
    </script>
    ";
    exit;
}

if (mb_strlen($content) > 4000) {
    echo "
    <script>
    alert('본문은 최대 4000자여야 합니다.');
    history.back();
    </script>
    ";
    exit;
}

if (preg_match('/[<>＜＞]/u', $title) || preg_match('/[<>＜＞]/u', $category) ||  preg_match('/[<>＜＞]/u', $content)) {
    echo "<script>alert('제목과 카테고리, 본문에는 <, > 기호를 사용할 수 없습니다.'); history.back();</script>";
    exit;
}

$current_time = date('Y-m-d H:i:s');

if ($action == "write") {
    try {
        $stmt = $conn->prepare("INSERT INTO posts (title, category, content, username, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $title, $category, $content, $username, $current_time);
        $stmt->execute();

        $id = $conn->insert_id;
        $stmt->close();

        header("Location: view.php?id=$id");
        exit;
    } catch (mysqli_sql_exception $e) {
        echo "
        <script>
        alert('게시물 등록에 실패했습니다.');
        history.back();
        </script>
        ";
        exit;
    }
}