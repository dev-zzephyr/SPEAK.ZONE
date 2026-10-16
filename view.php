<?php
session_start();
require 'db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    http_response_code(404);
    exit('존재하지 않는 게시글입니다.');
}

$stmt = $conn->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY id DESC");
$stmt->bind_param('i', $id);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$_SESSION['viewed'] ??= [];
if (!in_array($id, $_SESSION['viewed'], true)) {
    $stmt = $conn->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $_SESSION['viewed'][] = $id;
}

$reacted = $_SESSION['reacted'][$id] ?? null;

$stmt = $conn->prepare("SELECT id, title, username, category, created_at FROM posts WHERE id < ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$prev = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT id, title, username, category, created_at FROM posts WHERE id > ? ORDER BY id ASC LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$next = $stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPEAK.ZONE - View</title>
    <link rel="stylesheet" href="view.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css">
</head>
<body>
    <a href="index.php" class="logo">SPEAK.ZONE</a>
    <div class="container">
        <div>
            <div>
                <span># <a href="index.php?query=<?= urlencode($post['category']) ?>"><?= htmlspecialchars($post['category']) ?></a></span>
                <h1><?= htmlspecialchars($post['title']) ?></h1>
            </div>
            <div>
                <ul>
                    <li><?= htmlspecialchars($post['username']) ?></li>
                    <li><?= htmlspecialchars($post['created_at']) ?></li>
                </ul>
                <ul>
                    <li><i class="ph ph-eye"></i><?= htmlspecialchars($post['views']) ?></li>
                    <li>
                        <form method="post" action="react_process.php">
                            <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                            <button type="submit" name="type" value="like" class="<?= $reacted == 'like' ? 'on' : ''?>">
                                <i class="ph<?= $reacted == 'like' ? '-fill' : ''?> ph-thumbs-up"></i><?= (int)$post['likes'] ?>
                            </button>
                            <button type="submit" name="type" value="hate" class="<?= $reacted == 'hate' ? 'on' : ''?>">
                                <i class="ph<?= $reacted == 'hate' ? '-fill' : ''?> ph-thumbs-down"></i><?= (int)$post['hates'] ?>
                            </button>
                        </form>
                    </li>    
                </ul>
            </div>
        </div>
        <p><?= htmlspecialchars($post['content']) ?></p>
        <div id="comments">
            <h3>댓글 (<?= count($comments) ?>개)</h3>
            <?php if (isset($_SESSION['user'])): ?>
                <form method="post" action="comment_process.php">
                    <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                    <textarea name="content" maxlength="300" placeholder="댓글을 작성하세요" required></textarea>
                    <button type="submit">등록</button>
                </form>
            <?php else: ?>
                <span><a href="auth.php">로그인</a> 후 댓글 작성이 가능합니다.</span>
            <?php endif; ?>
            <ul>
                <?php if (!empty($comments)): ?>
                    <?php foreach($comments as $c): ?>
                        <li>
                            <div>
                                <span><?= htmlspecialchars($c['username']) ?></span>
                                <span><?= timeAgo($c['created_at']) ?></span>
                            </div>
                            <p><?= htmlspecialchars($c['content']) ?></p>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span>댓글이 없습니다</span>
                <?php endif; ?>
            </ul>
        </div>
        <div class="others">
            <div>
                <span>이전 글</span>
                <div>
                    <?php if ($prev):?>
                    <div>
                        <a href="view.php?id=<?= $prev['id'] ?>"><?= htmlspecialchars($prev['title']) ?></a>
                    </div>
                    <div>
                        <span><?= htmlspecialchars($prev['username']) ?></span>
                        <span><a href="#"><?= htmlspecialchars($prev['category']) ?></a></span>
                        <span><?= mb_substr($prev['created_at'], 0, 10) ?></span>
                    </div>
                    <?php else: ?>
                    <span>-</span>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <span>다음 글</span>
                <div>
                    <?php if ($next):?>
                    <div>
                        <a href="view.php?id=<?= $next['id'] ?>"><?= htmlspecialchars($next['title']) ?></a>
                    </div>
                    <div>
                        <span><?= htmlspecialchars($next['username']) ?></span>
                        <span><a href="#"><?= htmlspecialchars($next['category']) ?></a></span>
                        <span><?= mb_substr($next['created_at'], 0, 10) ?></span>
                    </div>
                    <?php else: ?>
                    <span>-</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>