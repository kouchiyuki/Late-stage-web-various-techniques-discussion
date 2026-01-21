<?php
$redis = new Redis();
$redis->connect('redis', 6379);

$posts = json_decode($redis->get('posts') ?: '[]', true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message'] ?? '');
    if ($msg !== '') {
        array_unshift($posts, ['message' => $msg, 'time' => date('H:i')]);
        $redis->set('posts', json_encode($posts));
    }
}
?>

<form method="post">
    <textarea name="message"></textarea>
    <button>投稿</button>
</form>

<?php foreach ($posts as $p): ?>
    <p><?= htmlspecialchars($p['time']) ?> <?= nl2br(htmlspecialchars($p['message'])) ?></p>
<?php endforeach; ?>

