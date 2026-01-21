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
    // 自己紹介文
    $self_introduction = $_POST['self_introduction'] ?? '';

    // アイコン画像アップロード処理（Base64形式）
    $icon_filename = $user['icon_filename']; // 現状のファイル名を初期値に
    if (!empty($_POST['image_base64'])) {
        $base64 = preg_replace('/^data:.+base64,/', '', $_POST['image_base64']);
        $image_binary = base64_decode($base64);
        $icon_filename = strval(time()) . bin2hex(random_bytes(25)) . '.png';
        $filepath = '/var/www/upload/image/' . $icon_filename;
        file_put_contents($filepath, $image_binary);
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

    // プロフィールページへリダイレクト
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
<h1>プロフィール編集</h1>

<div>
    <?php if(empty($user['icon_filename'])): ?>
        現在アイコン未設定
    <?php else: ?>
        <img src="/image/<?= htmlspecialchars($user['icon_filename']) ?>" 
             style="height:5em;width:5em;border-radius:50%;object-fit:cover;">
    <?php endif; ?>
</div>

<form method="post">
    <div style="margin:1em 0;">
        <input type="file" accept="image/*" id="imageInput">
    </div>
    <input type="hidden" id="imageBase64Input" name="image_base64">
    <canvas id="imageCanvas" style="display:none;"></canvas>

    <div style="margin:1em 0;">
        <label>自己紹介（最大1000文字）</label><br>
        <textarea name="self_introduction" rows="10" cols="50" maxlength="1000"
            placeholder="自己紹介を入力してください"><?= htmlspecialchars($user['self_introduction'] ?? '') ?></textarea>
    </div>

    <button type="submit">保存</button>
</form>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const imageInput = document.getElementById("imageInput");
    imageInput.addEventListener("change", () => {
        if (!imageInput.files.length) return;

        const file = imageInput.files[0];
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        const image = new Image();
        const canvas = document.getElementById("imageCanvas");
        const imageBase64Input = document.getElementById("imageBase64Input");

        reader.onload = () => {
            image.onload = () => {
                const maxLength = 1000;
                let width = image.naturalWidth;
                let height = image.naturalHeight;

                if (width > maxLength || height > maxLength) {
                    if (width > height) {
                        height = maxLength * height / width;
                        width = maxLength;
                    } else {
                        width = maxLength * width / height;
                        height = maxLength;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext("2d");
                ctx.drawImage(image, 0, 0, width, height);
                imageBase64Input.value = canvas.toDataURL();
            };
            image.src = reader.result;
        };
        reader.readAsDataURL(file);
    });
});
</script>
</body>
</html>

