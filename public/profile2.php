<?php
session_start();

// ログイン確認
if (empty($_SESSION['login_user_id'])) {
    header("Location: ../1022/login.php");
    exit;
}
$login_user_id = $_SESSION['login_user_id'];

// 年齢計算関数
function calculateAge($birthday) {
    if (!$birthday) return '';
    $birth = new DateTime($birthday);
    $today = new DateTime();
    $age = $today->diff($birth)->y;
    return $age;
}

// DB接続
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 表示対象ユーザーID（URLから取得、なければ自分のプロフィール）
$user_id = $_GET['user_id'] ?? $login_user_id;

// ユーザー情報取得
$sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$sth->execute([':id' => $user_id]);
$user = $sth->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("HTTP/1.1 404 Not Found");
    echo "そのようなユーザーは存在しません";
    exit;
}

// 投稿一覧取得
$sth_posts = $dbh->prepare("SELECT * FROM bbs_entries WHERE user_id = :id ORDER BY created_at DESC");
$sth_posts->execute([':id' => $user_id]);
$posts = $sth_posts->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($user['name']) ?> のプロフィール</title>
<style>
.cover {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: #ccc;
}
.icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-top: -50px;
    border: 3px solid #fff;
}
.profile-header {
    text-align: center;
    margin-bottom: 1em;
}
.post {
    border-bottom: 1px solid #ccc;
    padding: 1em 0;
}
</style>
</head>
<body>
<a href="/bbs.php">掲示板に戻る</a>

<div class="profile-header">
    <?php if (!empty($user['cover_filename'])): ?>
        <img src="/image/<?= htmlspecialchars($user['cover_filename']) ?>" class="cover">
    <?php else: ?>
        <div class="cover"></div>
    <?php endif; ?>

    <?php if (!empty($user['icon_filename'])): ?>
        <img src="/image/<?= htmlspecialchars($user['icon_filename']) ?>" class="icon">
    <?php else: ?>
        <div class="icon"></div>
    <?php endif; ?>

    <h1><?= htmlspecialchars($user['name']) ?></h1>

    <?php if (!empty($user['birthday'])): ?>
        <p>年齢: <?= calculateAge($user['birthday']) ?>歳</p>
    <?php endif; ?>

    <p><?= nl2br(htmlspecialchars($user['introduction'] ?? '')) ?></p>
</div>

<?php if ($user_id == $login_user_id): ?>
<h2>プロフィール編集</h2>
<form method="POST" enctype="multipart/form-data" action="profile_edit.php">
    <p>アイコン: <input type="file" name="icon"></p>
    <p>カバー画像: <input type="file" name="cover"></p>
    <p>生年月日: <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>"></p>
    <p>紹介文:<br>
        <textarea name="introduction" rows="3" cols="50"><?= htmlspecialchars($user['introduction'] ?? '') ?></textarea>
    </p>
    <button type="submit">保存</button>
</form>
<?php endif; ?>

<h2>投稿一覧</h2>
<?php foreach ($posts as $post): ?>
    <div class="post">
        <p>ID: <?= htmlspecialchars($post['id']) ?></p>
        <p>日時: <?= htmlspecialchars($post['created_at']) ?></p>
        <p><?= nl2br(htmlspecialchars($post['body'])) ?></p>
        <?php if (!empty($post['image_filename'])): ?>
            <img src="/image/<?= htmlspecialchars($post['image_filename']) ?>" style="max-height:150px;">
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</body>
</html>

