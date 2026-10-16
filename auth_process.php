<?php
session_start();
require 'db.php';

$id = trim((string)($_POST['id'] ?? ''));
$pw = (string)($_POST['pw'] ?? '');
$action = $_POST['action'] ?? '';
$success = false;

function checkID($conn, $id, $login) {
    if (mb_strlen($id) < 4) {
        $_SESSION['error'] = '아이디는 최소 4글자여야 합니다';
        return false;
    }
    if (mb_strlen($id) > 15) {
        $_SESSION['error'] = '아이디는 최대 15글자여야 합니다';
        return false;
    }
    if (!preg_match('/^[a-zA-Z0-9]+$/', $id)) {
        $_SESSION['error'] = '아이디에 영어, 숫자 이외의 문자가 들어있습니다';
        return false;
    }

    $stmt = $conn->prepare("SELECT pw FROM users WHERE username = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && !$login) {
        $_SESSION['error'] = '아이디가 이미 존재합니다';
        return false;
    } elseif (!$user && $login) {
        $_SESSION['error'] = '아이디가 존재하지 않습니다';
        return false;
    }
    return true;
}

function checkPW($conn, $pw) {
    if (mb_strlen($pw) < 6) {
        $_SESSION['error'] = '비밀번호는 최소 6글자여야 합니다';
        return false;
    }
    if (mb_strlen($pw) > 15) {
        $_SESSION['error'] = '비밀번호는 최대 15글자여야 합니다';
        return false;
    }
    if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+\[\]{}|;:,.<>?])[a-zA-Z0-9!@#$%^&*()\-_=+\[\]{}|;:,.<>?]+$/', $pw)) {
        $_SESSION['error'] = '비밀번호는 영문, 숫자, 특수문자를 모두 포함해야 합니다';
        return false;
    }

    return true;
}

if (!checkID($conn, $id, ($action == 'login' ? true : false)) || !checkPW($conn, $pw)) {
    header('Location: auth.php');
    exit;
}

$current_time = date('Y-m-d H:i:s');

if ($action == 'signup') {
    try {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, pw, created_at) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $id, $hash, $current_time);
        $stmt->execute();

        $_SESSION['user'] = $id;
        $success = true;
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = '회원가입에 실패했습니다';
    }
} elseif ($action == 'login') {
    $stmt = $conn->prepare("SELECT pw FROM users WHERE username = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($pw, $user['pw'])) {
        $_SESSION['user'] = $id;
        $success = true;
    } else {
        $_SESSION['error'] = '비밀번호가 틀렸습니다';
    }
}

header('Location: '. ($success ? 'index.php' : 'auth.php'));
exit;
?>