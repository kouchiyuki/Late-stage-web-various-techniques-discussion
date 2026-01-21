<?php
session_start();

// ログインしていなければリダイレクト
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// DB接続
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
$error_message = '';
$success_message = '';

// ユーザー情報の取得
$select_sth = $dbh->prepare("SELECT name FROM users WHERE id = :id");
$select_sth->execute([':id' => $_SESSION['user_id']]);
$user = $select_sth->fetch();

if (!$user) {
    // 万が一ユーザーが存在しない場合
    $error_message = 'ユーザーが見つかりませんでした。';
}

// フォームが送信されたとき
if (!empty($_POST['name'])) {
    $new_name = trim($_POST['name']);

    if ($new_name === '') {
        $error_message = '名前を入力してください。';
    } else {
        // 名前を更新
        $update_sth = $dbh->prepare("UPDATE users SET name = :name WHERE id = :id");
        $update_sth->execute([
            ':name' => $new_name,
            ':id' => $_SESSION['user_id']
        ]);
        $success_message = '名前を変更しました。';
        $user['name'] = $new_name; // 表示用に更新
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>名前の変更</title>
</head>
<body>
    <h1>名前の変更</h1>

    <?php if ($error_message): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
    <?php elseif ($success_message): ?>
        <p style="color: green;"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>
            新しい名前:
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </label>
        <br>
        <button type="submit">変更する</button>
    </form>
</body>
</html>
