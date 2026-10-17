<?php
session_start();

require 'db.php';
require 'get_posts.php';

$stmt = $conn->prepare("SELECT category, COUNT(*) AS post_count FROM posts WHERE created_at >= NOW() - INTERVAL 7 DAY GROUP BY category ORDER BY post_count DESC LIMIT 5");
$stmt->execute();
$trendingCategories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT * FROM posts WHERE (likes > 0 AND created_at >= NOW() - INTERVAL 7 DAY) ORDER BY likes DESC, views DESC LIMIT 5");
$stmt->execute();
$trendingPosts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT username, SUM(likes) AS total_likes FROM posts WHERE created_at >= NOW() - INTERVAL 7 DAY GROUP BY username ORDER BY total_likes DESC LIMIT 5");
$stmt->execute();
$popularUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SPEAK.ZONE - 온라인 커뮤니티 게시판">
    <meta name="author" content="zephyr">
    <meta property="og:title" content="SPEAK.ZONE">
    <meta property="og:description" content="온라인 커뮤니티 게시판">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://speak-zone.onrender.com/">
    <title>SPEAK.ZONE</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css">
</head>
<body>
<main>
    <a href="index.php" class="logo">SPEAK.ZONE</a>
    <?php if (isset($_SESSION['user']) && $_SESSION['user'] == 'admin'): ?>
        <div>
            <h1>환영합니다, <a href="#" title="내 계정"><?php echo htmlspecialchars($_SESSION['user']) ?></a>님!</h1>
            <a href="logout_process.php" title="로그아웃"><i class="ph ph-sign-out"></i></a>
        </div>
        <h3>공지사항을 작성하고 유저들과 게시물을 검토하세요.</h3>
    <?php elseif (isset($_SESSION['user'])): ?>
        <div>
            <h1>환영합니다, <a href="#" title="내 계정"><?php echo htmlspecialchars($_SESSION['user']) ?></a>님!</h1>
            <a href="logout_process.php" title="로그아웃"><i class="ph ph-sign-out"></i></a>
        </div>
        <h3>게시물을 자유롭게 작성하고 읽어보세요.</h3>
    <?php else: ?>
        <h1>환영합니다!</h1>
        <h3>게시물을 보거나 작성하기 전, <a href="auth.php" title="로그인">로그인</a>이 필요합니다.</h3>
    <?php endif; ?>
    <div class="ranking">
        <ol>
            <h4>가장 인기있는 카테고리</h4>
            <?php if (!empty($trendingCategories)): ?>
                <?php foreach($trendingCategories as $row): ?>
                    <li><a href="index.php?query=<?= urlencode($row['category']) ?>"><?= htmlspecialchars($row['category']) ?></a><span>(<?= $row['post_count'] ?>건)</span></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>아직 등록된 카테고리가 없습니다</li>
            <?php endif; ?>
        </ol>
        <ol>
            <h4>가장 인기있는 글</h4>
            <?php if (!empty($trendingPosts)): ?>
                <?php foreach($trendingPosts as $row): ?>
                    <li>
                        <a href="view.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a>
                        <!-- <span>(<i class="ph ph-thumbs-up"></i> <?= $row['likes'] ?>)</span> -->
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>아직 인기있는 글이 없습니다</li>
            <?php endif; ?>
        </ol>
        <ol>
            <h4>가장 인기있는 사용자</h4>
            <?php if (!empty($popularUsers)): ?>
                <?php foreach($popularUsers as $row): ?>
                    <li>
                        <?php if($row['username'] == 'admin'): ?>
                            <a href="#" class="by_admin"><?= htmlspecialchars($row['username']) ?></a>
                        <?php else: ?>
                            <a href="#"><?= htmlspecialchars($row['username']) ?></a>
                        <?php endif; ?>
                        <!-- <span>(<i class="ph ph-thumbs-up"></i> <?= $row['total_likes'] ?>)</span> -->
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>아직 인기있는 사용자가 없습니다</li>
            <?php endif; ?>
        </ol>
    </div>
    <div class="actions">
        <?php if (isset($_SESSION['user']) && $_SESSION['user'] == 'admin'): ?>
            <button onclick="openAdminPanel()">관리자 제어 패널 열기</button>
        <?php else: ?>
            <button onclick="viewAdminNotification()">관리자 공지 보기</button>
        <?php endif; ?>
        <button onclick="goWrite()">글 작성</button>
        <button onclick="viewNotification()">알림</button>
        <button onclick="viewMyAccount()">내 계정</button>
    </div>
    <span class="copyright">
        ⓒ 2026. <a href="https://dev-zzephyr.github.io/" target="_blank">zephyr</a> All rights reserved.
    </span>
</main>
<div class="articleArea">
    <form method="get" action="index.php">
        <input name="query" id="query" type="text" placeholder="검색어를 입력하세요" value="<?= htmlspecialchars($_GET['query'] ?? '') ?>">
        <select name="type" id="type">
            <option value="post">게시물</option>
            <!-- <option value="user">사용자</option> -->
        </select>
        <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="latest">최신순</option>
            <option value="views">조회수 순</option>
            <option value="likes">인기 순</option>
        </select>
    </form>
    <?php if(!$posts): ?>
        <span>글이 없습니다.</span>
    <?php endif; ?>
    <?php foreach ($posts as $index => $post) : ?>
        <a href="view.php?id=<?= (int) $post['id'] ?>" class="article">
            <div>
                <div>
                    <?php if ($post['username'] == 'admin'): ?>
                        <span class="by_admin"><?= htmlspecialchars($post['username']) ?></span>
                    <?php else: ?>
                        <span><?= htmlspecialchars($post['username']) ?></span>
                    <?php endif; ?>
                    <ul>
                        <li><?= mb_substr(htmlspecialchars($post['created_at']), 0, 10) ?></li>
                    </ul>
                </div>
                <h4 class="bold"><?= htmlspecialchars(mb_substr($post['title'], 0, 10)).(mb_strlen($post['title']) > 10 ? '...' : '') ?></h4>
            </div>
            <ul class="abs">
                <li><i class="ph ph-eye"></i><?= htmlspecialchars($post['views']) ?></li>
                <li><i class="ph ph-thumbs-up"></i><?= htmlspecialchars($post['likes']) ?></li>
                <li><i class="ph ph-thumbs-down"></i><?= htmlspecialchars($post['hates']) ?></li>
            </ul>
        </a>
    <?php endforeach; ?>
</div>
<script>
    const auth = <?php echo isset($_SESSION['user']) ? ($_SESSION['user'] == 'admin' ? 2 : 1) : 0; ?>;
    const currentQuery = '<?php echo $_GET['query'] ?? ''; ?>';
    const currentType = '<?php echo $_GET['type'] ?? 'post'; ?>';
    const currentSort = '<?php echo $_SESSION['sort'] ?? 'latest'; ?>';
    
    document.querySelector("input#query").value = currentQuery;
    document.querySelector("select#type").value = currentType;
    document.querySelector("select#sort").value = currentSort;
</script>
<script src="main.js"></script>
</body>
</html>
