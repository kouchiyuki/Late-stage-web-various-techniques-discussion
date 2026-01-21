<?php
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

$access_count = isset($session_values['access_count']) ? $session_values['access_count'] : 0;
$access_count++;

$session_values['access_count'] = $access_count;
$redis->set($redis_session_key, json_encode($session_values));

echo "このセッションでの{$access_count}回目のアクセスです！";
