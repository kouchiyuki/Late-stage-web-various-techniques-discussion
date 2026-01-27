<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
    header("Location: ../login2.php");
    exit;
}

$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

if (!empty($_POST['cover_base64'])) {
    $base64 = preg_replace('/^data:.+base64,/', '', $_POST['cover_base64']);
    $image_binary = base64_decode($base64);
    $filename = time() . bin2hex(random_bytes(25)) . '.png';
    $filepath = '/var/www/upload/image/' . $filename;
    file_put_contents($filepath, $image_binary);

    // DB更新
    $update = $dbh->prepare("UPDATE users SET cover_filename = :cover WHERE id = :id");
    $update->execute([
        ':cover' => $filename,
        ':id' => $_SESSION['login_user_id']
    ]);

    header("Location: /profile.php?user_id=" . $_SESSION['login_user_id']);
    exit;
}
?>

<h1>カバー画像設定</h1>
<form method="POST">
    <input type="file" id="coverInput" accept="image/*">
    <input type="hidden" name="cover_base64" id="coverBase64">
    <canvas id="coverCanvas" style="display:none;"></canvas>
    <button type="submit">アップロード</button>
</form>
<div style="margin-top: 1em;">
  <a href="/setting/index.php" style="display: inline-block; padding: 0.5em 1em; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9em;">
    戻る
  </a>
</div>

<script>
document.getElementById("coverInput").addEventListener("change", function(){
    if (!this.files[0]) return;
    const file = this.files[0];
    if (!file.type.startsWith('image/')) return;

    const reader = new FileReader();
    const image = new Image();
    const canvas = document.getElementById("coverCanvas");
    const input = document.getElementById("coverBase64");

    reader.onload = () => {
        image.onload = () => {
            // 縮小例：幅1000px以内
            const maxWidth = 1000;
            if(image.width > maxWidth){
                canvas.width = maxWidth;
                canvas.height = maxWidth * image.height / image.width;
            } else {
                canvas.width = image.width;
                canvas.height = image.height;
            }
            const ctx = canvas.getContext("2d");
            ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
            input.value = canvas.toDataURL();
        }
        image.src = reader.result;
    }
    reader.readAsDataURL(file);
});
</script>

