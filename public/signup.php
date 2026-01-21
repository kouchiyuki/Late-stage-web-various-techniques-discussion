<?php
$dbh = new PDO(
  'mysql:host=mysql;dbname=example_db;charset=utf8mb4',
  'root',
  '',
  [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]
);

if (
  !empty($_POST['name']) &&
  !empty($_POST['email']) &&
  !empty($_POST['password'])
) {
  // メール重複チェック
  $select_sth = $dbh->prepare(
    "SELECT id FROM users WHERE email = :email LIMIT 1"
  );
  $select_sth->execute([
    ':email' => $_POST['email'],
  ]);
  $user = $select_sth->fetch();

  if ($user) {
    header("HTTP/1.1 303 See Other");
    header("Location: ./signup.php?duplicate_email=1");
    exit;
  }

  // salt生成
  $salt = bin2hex(random_bytes(16));

  // パスワードを salt 付きでハッシュ
  $hashedPassword = hash('sha256', $_POST['password'] . $salt);

  // ユーザー登録
  $insert_sth = $dbh->prepare(
    "INSERT INTO users (name, email, password, salt)
     VALUES (:name, :email, :password, :salt)"
  );

  $insert_sth->execute([
    ':name'     => $_POST['name'],
    ':email'    => $_POST['email'],
    ':password' => $hashedPassword,
    ':salt'     => $salt,
  ]);

  // 完了画面へ
  header("HTTP/1.1 303 See Other");
  header("Location: ./signup_finish.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>会員登録</title>
</head>
<body>

<h1>会員登録</h1>

<form method="POST">
  <label>
    名前：
    <input type="text" name="name" required>
  </label>
  <br>

  <label>
    メールアドレス：
    <input type="email" name="email" required>
  </label>
  <br>

  <label>
    パスワード：
    <input type="password" name="password" minlength="6" required>
  </label>
  <br>

  <button type="submit">登録</button>
</form>

<?php if (!empty($_GET['duplicate_email'])): ?>
  <p style="color:red;">このメールアドレスは既に登録されています。</p>
<?php endif; ?>
</body>
</html>
