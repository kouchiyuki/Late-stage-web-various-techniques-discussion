<?php
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
$error_message = '';

if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 既に登録済みかチェック
    $check_sth = $dbh->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $check_sth->execute([':email' => $email]);
    $count = $check_sth->fetchColumn();

    if ($count > 0) {
        $error_message = '既にこのメールアドレスは登録されています';
    } else {
        try {
            // INSERT処理
            $insert_sth = $dbh->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $insert_sth->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $password,
            ]);
            // 登録成功後リダイレクト
            header("HTTP/1.1 303 See Other");
            header("Location: ./signup_finish.php");
            exit;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                // 重複エラーが万が一発生した場合の保険
                $error_message = '既にこのメールアドレスは登録されています';
            } else {
                error_log($e->getMessage());
                $error_message = '登録処理中にエラーが発生しました。';
            }
        }
    }
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

<?php if (!empty($error_message)): ?>
    <p style="color: red;"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<form method="POST">
    <label>
        名前:
        <input type="text" name="name" required>
    </label>
    <br>
    <label>
        メールアドレス:
        <input type="email" name="email" required>
    </label>
    <br>
    <label>
        パスワード:
        <input type="password" name="password" minlength="6" required autocomplete="new-password">
    </label>
    <br>
    <button type="submit">決定</button>
</form>

</body>
</html>


