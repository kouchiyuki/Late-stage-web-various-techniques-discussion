<?php
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
    // 既に同じメールアドレスで登録された会員が存在しないか確認する
    $select_sth = $dbh->prepare("SELECT * FROM users WHERE email = :email ORDER BY id DESC LIMIT 1");
    $select_sth->execute([
        ':email' => $_POST['email'],
    ]);
    $user = $select_sth->fetch();

    if (!empty($user)) {
        // メールアドレスが重複していた場合
        header("HTTP/1.1 303 See Other");
        header("Location: ./signup_1105.php?duplicate_email=1");
        exit;
    }

    //php標準のハッシュ関数を使用する
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $insert_sth = $dbh->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $insert_sth->execute([
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':password' => $hashed_password,
    ]);

    // 登録完了後リダイレクト
    header("HTTP/1.1 303 See Other");
    header("Location: ./signup_finish.php");
    exit;
}
?>

<h1>会員登録</h1>
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

<?php if (!empty($_GET['duplicate_email'])): ?>
<div style="color: red;">
    入力されたメールアドレスは既に使われています。
</div>
<?php endif; ?>



