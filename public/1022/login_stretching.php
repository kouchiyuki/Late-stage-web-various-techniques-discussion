<?php
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

    // ソルトを取得し、パスワード + ソルトで再度ハッシュストレッチングを10万回追加for文
    $input_hashed_password = hash('sha256', $_POST['password'] . $user['salt']);
    for ($i = 0; $i < 100000; $i++) {
        $input_hashed_password = hash('sha256', $input_hashed_password);
    }

    // ハッシュが一致するか確認
    if ($input_hashed_password !== $user['password']) {
        // パスワード不一致
        header("HTTP/1.1 303 See Other");
        header("Location: ./login.php?error=1");
        exit;
    }

    // セッション処理（Redisを使う例）
    $session_cookie_name = 'session_id';
    $session_id = $_COOKIE[$session_cookie_name] ?? base64_encode(random_bytes(64));
    if (!isset($_COOKIE[$session_cookie_name])) {
        setcookie($session_cookie_name, $session_id);
    }

    $redis = new Redis();
    $redis->connect('redis', 6379);
    $redis_session_key = "session-" . $session_id;

    $session_values = $redis->exists($redis_session_key)
        ? json_decode($redis->get($redis_session_key), true)
        : [];

    $session_values["login_user_id"] = $user['id'];
    $redis->set($redis_session_key, json_encode($session_values));

    // ログイン完了画面へリダイレクト
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
