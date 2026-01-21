<?php
session_start(); // セッション開始

// ログインしていない場合は、ログイン画面にリダイレクト
if (empty($_SESSION['login_user_id'])) {
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php");
    exit;
}

$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

// 検索処理
$search_query = ''; // 名前検索用の初期値
$start_year = ''; // 生まれ年開始の初期値
$end_year = ''; // 生まれ年終了の初期値

// 検索フォームが送信された場合
if (!empty($_GET['search']) && !empty($_GET['start_year']) && !empty($_GET['end_year'])) {
    $search_query = $_GET['search']; // 名前検索用
    $start_year = $_GET['start_year']; // 生まれ年開始
    $end_year = $_GET['end_year']; // 生まれ年終了

    // 名前検索と生まれ年範囲検索を組み合わせたクエリ
    $select_sth = $dbh->prepare('SELECT * FROM users WHERE name LIKE :search_query AND birth_year BETWEEN :start_year AND :end_year ORDER BY id DESC');
    $select_sth->execute([
        ':search_query' => '%' . $search_query . '%',
        ':start_year' => $start_year,
        ':end_year' => $end_year
    ]);
} elseif (!empty($_GET['search'])) {
    // 名前検索のみ
    $search_query = $_GET['search'];
    $select_sth = $dbh->prepare('SELECT * FROM users WHERE name LIKE :search_query ORDER BY id DESC');
    $select_sth->execute([':search_query' => '%' . $search_query . '%']);
} elseif (!empty($_GET['start_year']) && !empty($_GET['end_year'])) {
    // 生まれ年範囲検索のみ
    $start_year = $_GET['start_year'];
    $end_year = $_GET['end_year'];
    $select_sth = $dbh->prepare('SELECT * FROM users WHERE birth_year BETWEEN :start_year AND :end_year ORDER BY id DESC');
    $select_sth->execute([
        ':start_year' => $start_year,
        ':end_year' => $end_year
    ]);
} else {
    // 検索条件が送信されていない場合、すべての会員を表示
    $select_sth = $dbh->prepare('SELECT * FROM users ORDER BY id DESC');
    $select_sth->execute();
}

// フォロー処理
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['followee_user_id'])) {
    $follower_user_id = $_SESSION['login_user_id']; // ログインユーザーID
    $followee_user_id = $_POST['followee_user_id']; // フォローされる側のID

    // フォロー状態を確認
    $check_sth = $dbh->prepare("SELECT * FROM user_relationships WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id");
    $check_sth->execute([
        ':follower_user_id' => $follower_user_id,
        ':followee_user_id' => $followee_user_id
    ]);
    $existing_relationship = $check_sth->fetch();

    if (empty($existing_relationship)) {
        // フォローがまだであれば、フォロー関係をDBに追加
        $insert_sth = $dbh->prepare("INSERT INTO user_relationships (follower_user_id, followee_user_id) VALUES (:follower_user_id, :followee_user_id)");
        $insert_sth->execute([
            ':follower_user_id' => $follower_user_id,
            ':followee_user_id' => $followee_user_id
        ]);
        $message = 'フォローしました。';
    } else {
        $message = '既にフォローしています。';
    }
}
?>

<body>
    <h1>会員一覧</h1>

    <div style="margin-bottom: 1em;">
        <a href="/setting/index.php">設定画面</a> /
        <a href="/timeline.php">タイムライン</a>
    </div>

    <!-- 検索フォーム（名前検索と生まれ年範囲検索） -->
    <form method="GET" style="margin-bottom: 1em;">
        <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="名前で検索" style="padding: 0.5em;">
        <input type="number" name="start_year" value="<?= htmlspecialchars($start_year) ?>" placeholder="年" style="padding: 0.5em;">~
        <input type="number" name="end_year" value="<?= htmlspecialchars($end_year) ?>" placeholder="年" style="padding: 0.5em;">
        <button type="submit" style="background-color: #007bff; color: white; padding: 0.5em 1em; border: none; cursor: pointer;">検索</button>
    </form>

    <?php if (!empty($message)): ?>
        <div style="color: green;"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($select_sth->rowCount() > 0): ?>
        <!-- 検索結果が存在する場合 -->
        <?php foreach($select_sth as $user): ?>
            <div style="display: flex; justify-content: start; align-items: center; padding: 1em 2em;">
                <?php if(empty($user['icon_filename'])): ?>
                    <!-- アイコン無い場合は同じ大きさの空白を表示して揃えておく -->
                    <div style="height: 2em; width: 2em;"></div>
                <?php else: ?>
                    <img src="/image/<?= $user['icon_filename'] ?>"
                         style="height: 2em; width: 2em; border-radius: 50%; object-fit: cover;">
                <?php endif; ?>
                <a href="/profile.php?user_id=<?= $user['id'] ?>" style="margin-left: 1em;">
                    <?= htmlspecialchars($user['name']) ?>
                </a>

                <?php
                // フォロー状態を確認
                $check_sth = $dbh->prepare("SELECT * FROM user_relationships WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id");
                $check_sth->execute([
                    ':follower_user_id' => $_SESSION['login_user_id'], // ログインユーザーID
                    ':followee_user_id' => $user['id'] // フォローされる側
                ]);
                $existing_relationship = $check_sth->fetch();
                ?>

                <?php if (empty($existing_relationship)): ?>
                    <!-- フォローしていない場合、フォローボタンを表示 -->
                    <form method="POST" style="margin-left: 1em;">
                        <input type="hidden" name="followee_user_id" value="<?= $user['id'] ?>">
                        <button type="submit" style="background-color: #007bff; color: white; padding: 0.5em 1em; border: none; cursor: pointer;">
                            フォローする
                        </button>
                    </form>
                <?php else: ?>
                    <!-- フォロー済みの場合、メッセージを表示 -->
                    <span style="margin-left: 1em; color: green; font-weight: bold;">フォロー済み</span>
                <?php endif; ?>
            </div>
            <hr style="border: none; border-bottom: 1px solid gray;">
        <?php endforeach; ?>
    <?php else: ?>
        <div>検索結果は見つかりませんでした。</div>
    <?php endif; ?>
</body>

