<?php
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

// エラーメッセージ表示用変数
$error_message = '';

// POSTされたデータが揃っていたら処理開始
if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {

    // 既にメールアドレスが登録済みかチェック
    $check_sth = $dbh->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $check_sth->execute([':email' => $_POST['email']]);
    $count = $check_sth->fetchColumn();

    if ($count > 0) {
        // 既に登録されていた場合のエラーメッセージ
        $error_message = '既にこのメールアドレスは登録されています';
    } else {
        // トランザクション開始（複数リクエストによる競合を少しでも減らすため）
        $dbh->beginTransaction();
        try {
            // insert実行
            $insert_sth = $dbh->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $insert_sth->execute([
                ':name' => $_POST['name'],
                ':email' => $_POST['email'],
                ':password' => $_POST['password'],
            ]);

            // コミット
            $dbh->commit();

            // 完了画面へリダイレクト
            header("HTTP/1.1 303 See Other");
            header("Location: ./signup_finish.php");
            exit;
        } catch (PDOException $e) {
            // エラー時はロールバック
            $dbh->rollBack();

            // 重複エラーの場合はユーザーにわかりやすく伝える
            if ($e->errorInfo[1] == 1062) { // 1062 = duplicate entry
                $error_message = '既にこのメールアドレスは登録されています';
            } else {
                // その他の例外はログに残すなどしてユーザーには一般エラー表示
                error_log($e->getMessage());
                $error_message = '登録処理中にエラーが発生しました。しばらくしてから再度お試しください。';
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

<!-- エラーメッセージがあれば赤文字で表示 -->
<?php if (!empty($error_message)): ?>
  <p style="color: red;"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<!-- 登録フォーム -->
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
