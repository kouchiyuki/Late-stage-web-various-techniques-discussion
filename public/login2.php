<?php
session_start();
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

if (!empty($_POST['email']) && !empty($_POST['password'])) {
    // メールアドレスからユーザー情報取得
    $select_sth = $dbh->prepare("SELECT * FROM users WHERE email = :email ORDER BY id DESC LIMIT 1");
    $select_sth->execute([
        ':email' => $_POST['email'],
    ]);
    $user = $select_sth->fetch();

    if (empty($user)) {
        // ユーザーが存在しない
        header("HTTP/1.1 303 See Other");
        header("Location: ./login.php?error=1");
        exit;
    }

    // ソルトを取得し、パスワード + ソルトで再度ハッシュ
    $input_hashed_password = hash('sha256', $_POST['password'] . $user['salt']);

    // ハッシュが一致するか確認
    if ($input_hashed_password !== $user['password']) {
        // パスワード不一致
        header("HTTP/1.1 303 See Other");
        header("Location: ./login.php?error=1");
        exit;
    }

    // セッション処理
    $_SESSION['login_user_id'] = $user['id'];

    header("HTTP/1.1 303 See Other");
    header("Location: ./login_finish.php");
    exit;
}
?>

初めての人は<a href="/signup.php">会員登録</a>しましょう。
<hr>
<h1>ログイン</h1>
<form method="POST">
    <label>
        メールアドレス:
        <input type="email" name="email" required>
    </label>
    <br>
    <label>
        パスワード:
        <input type="password" name="password" minlength="6" required>
    </label>
    <br>
    <button type="submit">決定</button>
</form>

<?php if (!empty($_GET['error'])): ?>
<div style="color: red;">
    メールアドレスかパスワードが間違っています。
</div>
<?php endif; ?>
