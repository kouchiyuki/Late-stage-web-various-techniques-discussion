<?php
session_start();

// 未ログインならログインページへ
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

// DB接続
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 現在のユーザー情報取得
$select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$select_sth->execute([':id' => $_SESSION['login_user_id']]);
$user = $select_sth->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $self_introduction = $_POST['self_introduction'] ?? '';

  // ファイルアップロード処理（アイコン）
  $icon_filename = $user['icon_filename']; // 既存のを初期値に
  if (!empty($_FILES['icon']['tmp_name'])) {
    $uploaded_path = './image/';
    $filename = sha1(uniqid(mt_rand(), true)) . '.' . pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION);
    move_uploaded_file($_FILES['icon']['tmp_name'], $uploaded_path . $filename);
    $icon_filename = $filename;
  }

  // DB更新
  $update_sth = $dbh->prepare(
    "UPDATE users SET icon_filename = :icon, self_introduction = :intro WHERE id = :id"
  );
  $update_sth->execute([
    ':icon' => $icon_filename,
    ':intro' => $self_introduction,
    ':id' => $_SESSION['login_user_id']
  ]);

  header("Location: /1022/profile.php?user_id=" . $_SESSION['login_user_id']);
  exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>プロフィール編集</title>
</head>
<body>
  <h1>プロフィールを編集</h1>
  <form method="post" enctype="multipart/form-data">
    <div>
      <label>アイコン画像:</label><br>
      <?php if (!empty($user['icon_filename'])): ?>
        <img src="./image/<?= htmlspecialchars($user['icon_filename']) ?>" width="100"><br>
      <?php endif; ?>
      <input type="file" name="icon">
    </div>
    <div>
      <label>自己紹介文 (最大1000文字)</label><br>
      <textarea name="self_introduction" rows="10" cols="60" maxlength="1000"><?= htmlspecialchars($user['self_introduction'] ?? '') ?></textarea>
    </div>
    <div>
      <button type="submit">保存する</button>
    </div>
  </form>
  <p><a href="/1022/profile.php?user_id=<?= htmlspecialchars($user['id']) ?>">戻る</a></p>
</body>
</html>

