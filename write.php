<?php session_start(); require 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SPEAK.ZONE - 온라인 커뮤니티 게시판">
    <meta name="author" content="zephyr">
    <meta property="og:title" content="글 작성 - SPEAK.ZONE">
    <meta property="og:description" content="온라인 커뮤니티 게시판">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://speak-zone.onrender.com/write.php">
    <title>SPEAK.ZONE - Write</title>
    <link rel="stylesheet" href="write.css">
</head>
<body>
    <a href="index.php" class="logo">SPEAK.ZONE</a>
    <div class="container">
        <form method="post" action="write_process.php">
            <div style="grid-area: b1;">
                <label for="title">제목</label>
                <input name="title" type="text" placeholder="글의 제목을 입력하세요." required minlength="1" maxlength="20">
            </div>
            <div style="grid-area: b2;">
                <label for="category">카테고리</label>
                <input name="category" type="text" value="일반" placeholder="글의 카테고리를 입력하세요." required minlength="1" maxlength="20">
            </div>
            <div style="grid-area: b3;">
                <label for="content">본문</label>
                <textarea name="content" placeholder="본문을 입력하세요." required minlength="1" maxlength="4000"></textarea>
                <span>0/4000</span>
            </div>
            <div style="grid-area: b4;">
                <button type="submit" name="action" value="tempSave">임시 저장</button>
                <button type="submit" name="action" value="write">글 작성</button>
            </div>
        </form>
    </div>
    <script>
        document.querySelector("textarea").addEventListener("input", () => {
            let contentLen = document.querySelector("textarea").value.length;

            if (contentLen > 4000) {
                document.querySelector("textarea").value = document.querySelector("textarea").value.slice(0, 4000);
                contentLen = document.querySelector("textarea").value.length;
            }

            document.querySelector("span").textContent = `${contentLen}/4000`;
        })
    </script>
</body>
</html>