<?php
session_start();
require 'db.php';

if (isset($_SESSION['user'])) {
    echo "<script>alert('로그아웃을 먼저 해 주세요'); location.href = 'index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPEAK.ZONE - Login</title>
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <a href="index.php" class="logo">SPEAK.ZONE</a>
    <div class="container">
        <form method="post" action="auth_process.php">
            <input name="id" type="text" placeholder="ID" required minlength="4" maxlength="15">
            <input name="pw" type="password" placeholder="PW" required minlength="6" maxlength="15">
            <div>
                <button type="submit" name="action" value="signup">회원가입</button>
                <button type="submit" name="action" value="login">로그인</button>
            </div>
        </form>
        <?php if (isset($_SESSION['error'])): ?>
            <div style="color: red; margin-bottom: 15px;">
                <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>