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
        header("Location: ./login_1105.php?error=1");
        exit;
    }

    var_dump($_POST['password']);   // フォームから送信された値
    var_dump($user['password']);    // DBに保存されているハッシュ
    var_dump(password_verify($_POST['password'], $user['password'])); // true になるか確認
    exit;

    //php標準のやつを使用する
    if (!password_verify($_POST['password'], $user['password'])) {
        // パスワード不一致
        header("HTTP/1.1 303 See Other");
        header("Location: ./login_1105.php?error=1");
        exit;
    }

    // セッション処理
    $_SESSION['login_user_id'] = $user['id'];

    header("HTTP/1.1 303 See Other");
    header("Location: ./login_finish.php");
    exit;
}
?>

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
