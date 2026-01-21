<?php
session_start(); // セッション開始

// DB接続
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

// エラーメッセージ用変数
$error_message = '';

// ログインしていない場合、ログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// POSTで名前が送られてきたら処理
if (!empty($_POST['new_name'])) {
    try {
        // ユーザーIDをセッションから取得
        $user_id = $_SESSION['user_id'];
        $new_name = $_POST['new_name'];

        // ユーザー名の更新
        $update_sth = $dbh->prepare("UPDATE users SET name = :name WHERE id = :id");
        $update_sth->execute([
            ':name' => $new_name,
            ':id' => $user_id,
        ]);

        // 名前変更後、確認メッセージ
        $success_message = "名前が変更されました。";

    } catch (PDOException $e) {
        // エラー発生時
        $error_message = '名前変更に失敗しました。';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>名前変更</title>
</head>
<body>

<h1>名前変更</h1>

<!-- エラーメッセージ -->
<?php if (!empty($error_message)): ?>
    <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>

<!-- 成功メッセージ -->
<?php if (!empty($success_message)): ?>
    <p style="color: green;"><?= htmlspecialchars($success_message) ?></p>
<?php endif; ?>

<!-- 名前変更フォーム -->
<form method="POST">
    <label>
        新しい名前:
        <input type="text" name="new_name" required>
    </label>
    <br>
    <button type="submit">変更する</button>
</form>

</body>
</html>

