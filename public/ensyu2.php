<?php
$redis = new Redis();
$redis->connect('redis', 6379); // Dockerコンテナ名を使う

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    if ($message !== '') {
        $redis->set('latest_post', $message); // 上書き保存
    }
}

$latest = $redis->get('latest_post');
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>掲示板</title>
</head>
<body>
    <h1>投稿フォーム</h1>
    <form method="post">
        <textarea name="message" rows="4" cols="40" placeholder="メッセージを入力してください"></textarea><br>
        <button type="submit">投稿</button>
    </form>

    <h2>最新の投稿</h2>
    <div style="border:1px solid #ccc; padding:10px; width:400px;">
        <?= htmlspecialchars($latest ?? 'まだ投稿はありません。', ENT_QUOTES, 'UTF-8') ?>
    </div>
</body>
</html>

