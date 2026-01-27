<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: ../login.php");
    exit;
}

$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['birthday'])) {
    $sth = $dbh->prepare("UPDATE users SET birthday = :birthday WHERE id = :id");
    $sth->execute([
        ':birthday' => $_POST['birthday'],
        ':id' => $_SESSION['login_user_id']
    ]);
    header("Location: index.php"); // 設定一覧に戻る
    exit;
}

// 現在の生年月日を取得
$sth = $dbh->prepare("SELECT birthday FROM users WHERE id = :id");
$sth->execute([':id' => $_SESSION['login_user_id']]);
$user = $sth->fetch();
$birthday = $user['birthday'] ?? '';
?>

<h1>生年月日設定</h1>
<form method="POST">
    <label>生年月日:
        <input type="date" name="birthday" value="<?= htmlspecialchars($birthday) ?>">
    </label>
    <button type="submit">保存</button>
</form>
<div style="margin-top: 1em;">
  <a href="/setting/index.php" style="display: inline-block; padding: 0.5em 1em; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9em;">
    戻る
  </a>
</div>
