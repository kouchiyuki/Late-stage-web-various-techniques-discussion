<?php
session_start(); // セッション開始

if (empty($_SESSION['login_user_id'])) {
    header("HTTP/1.1 302 Found");
    header("Location: ./login2.php");
    exit;
}

$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

//検索処理
$search_query = ''; // 名前検索用の初期値
$start_year = '';   // 生まれ年開始の初期値
$end_year = '';     // 生まれ年終了の初期値

//名前と生まれ年の両方が入力されている場合
if (!empty($_GET['search']) && !empty($_GET['start_year']) && !empty($_GET['end_year'])) {
    $search_query = $_GET['search'];
    $start_year = $_GET['start_year'];
    $end_year = $_GET['end_year'];

    $select_sth = $dbh->prepare('SELECT * FROM users WHERE name LIKE :search_query AND birth_year BETWEEN :start_year AND :end_year ORDER BY id DESC');
    $select_sth->execute([
        ':search_query' => '%' . $search_query . '%',
        ':start_year' => $start_year,
        ':end_year' => $end_year
    ]);

//名前検索のみの場合
} elseif (!empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $select_sth = $dbh->prepare('SELECT * FROM users WHERE name LIKE :search_query ORDER BY id DESC');
    $select_sth->execute([':search_query' => '%' . $search_query . '%']);

//生まれ年範囲検索のみの場合
} elseif (!empty($_GET['start_year']) && !empty($_GET['end_year'])) {
    $start_year = $_GET['start_year'];
    $end_year = $_GET['end_year'];
    $select_sth = $dbh->prepare('SELECT * FROM users WHERE birth_year BETWEEN :start_year AND :end_year ORDER BY id DESC');
    $select_sth->execute([
        ':start_year' => $start_year,
        ':end_year' => $end_year
    ]);

//検索条件がない場合
} else {
    $select_sth = $dbh->prepare('SELECT * FROM users ORDER BY id DESC');
    $select_sth->execute();
}

//フォロー・解除処理
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['followee_user_id'])) {
    $follower_user_id = $_SESSION['login_user_id'];
    $followee_user_id = $_POST['followee_user_id'];

    // 解除ボタンが押された場合
    if (isset($_POST['action']) && $_POST['action'] === 'unfollow') {
        $delete_sth = $dbh->prepare("DELETE FROM user_relationships WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id");
        $delete_sth->execute([
            ':follower_user_id' => $follower_user_id,
            ':followee_user_id' => $followee_user_id
        ]);
        $message = 'フォローを解除しました。';
    } else {
        //フォローボタンが押された場合
        $insert_sth = $dbh->prepare("INSERT IGNORE INTO user_relationships (follower_user_id, followee_user_id) VALUES (:follower_user_id, :followee_user_id)");
        $insert_sth->execute([
            ':follower_user_id' => $follower_user_id,
            ':followee_user_id' => $followee_user_id
        ]);
        $message = 'フォローしました。';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>会員一覧</title>
</head>
<body>
    <h1>会員一覧</h1>

    <div style="margin-bottom: 1em;">
        <a href="/setting/index.php">設定画面</a> /
        <a href="/timeline.php">タイムライン</a>
    </div>

    <form method="GET" style="margin-bottom: 2em; padding: 10px; background: #f9f9f9; border-radius: 5px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="名前で検索" style="padding: 0.5em;">
        <input type="number" name="start_year" value="<?= htmlspecialchars($start_year) ?>" placeholder="開始年" style="padding: 0.5em;"> ~
        <input type="number" name="end_year" value="<?= htmlspecialchars($end_year) ?>" placeholder="終了年" style="padding: 0.5em;">
        <button type="submit" style="background-color: #007bff; color: white; padding: 0.5em 1em; border: none; cursor: pointer; border-radius: 4px;">検索</button>
    </form>

    <?php if (!empty($message)): ?>
        <div style="color: green; margin-bottom: 1em; font-weight: bold;"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($select_sth->rowCount() > 0): ?>
        <?php foreach($select_sth as $user): ?>
            <div style="display: flex; align-items: center; padding: 1em 0;">
                
                <?php if(empty($user['icon_filename'])): ?>
                    <div style="height: 2.5em; width: 2.5em; background: #ccc; border-radius: 50%;"></div>
                <?php else: ?>
                    <img src="/image/<?= $user['icon_filename'] ?>" style="height: 2.5em; width: 2.5em; border-radius: 50%; object-fit: cover;">
                <?php endif; ?>

                <a href="/profile.php?user_id=<?= $user['id'] ?>" style="margin-left: 1em; text-decoration: none; color: #333; font-weight: bold;">
                    <?= htmlspecialchars($user['name']) ?>
                </a>

                <?php
                $check_sth = $dbh->prepare("SELECT * FROM user_relationships WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id");
                $check_sth->execute([
                    ':follower_user_id' => $_SESSION['login_user_id'],
                    ':followee_user_id' => $user['id']
                ]);
                $existing_relationship = $check_sth->fetch();
                ?>

                <div style="margin-left: auto;">
                    <?php if (empty($existing_relationship)): ?>
                        <form method="POST">
                            <input type="hidden" name="followee_user_id" value="<?= $user['id'] ?>">
                            <button type="submit" style="background-color: #007bff; color: white; padding: 0.4em 1em; border: none; cursor: pointer; border-radius: 20px;">
                                フォローする
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" style="display: flex; align-items: center; gap: 10px;">
                            <span style="color: green; font-size: 0.9em;">フォロー済み</span>
                            <input type="hidden" name="followee_user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="action" value="unfollow">
                            <button type="submit" style="color: #666; border: 1px solid #ccc; background: white; padding: 0.3em 0.8em; cursor: pointer; border-radius: 20px; font-size: 0.8em;">
                                解除
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <hr style="border: none; border-top: 1px solid #eee;">
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #666;">検索結果は見つかりませんでした。</p>
    <?php endif; ?>

</body>
</html>
