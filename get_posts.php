<?php
require 'db.php';

if (isset($_GET['sort'])) {
    $_SESSION['sort'] = $_GET['sort'];
}

$sort = $_SESSION['sort'] ?? 'latest';

$allowed_sorts = [
    'latest' => 'id DESC',
    'likes'  => 'likes DESC, id DESC',
    'views'  => 'views DESC, id DESC'
];

$order_sql = $allowed_sorts[$sort] ?? 'id DESC';

$query = trim($_GET['query'] ?? '');
$type = $_GET['type'] ?? 'post';

$posts = [];
$param = '%'.$query.'%';

try {
    if ($type == 'post') {
        if ($query != '') {
            $stmt = $conn->prepare("SELECT * FROM posts WHERE (title LIKE ? OR content LIKE ? OR category LIKE ?) ORDER BY $order_sql");
            $stmt->bind_param('sss', $param, $param, $param);
        } else {
            $stmt = $conn->prepare("SELECT * FROM posts ORDER BY $order_sql");
        }
    }
    else {
        if ($query != '') {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ? ORDER BY $order_sql");
            $stmt->bind_param('s', $param);
        }
        else {
            $stmt = $conn->prepare("SELECT * FROM users ORDER BY $order_sql"); # users일때 select의 option 수정 필요
        }
    }

    $stmt->execute();
    $posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

} catch (mysqli_sql_exception $e) {

}
?>