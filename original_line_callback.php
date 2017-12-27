<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' lang='ja' xml:lang='ja'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta name='viewport' content='width=device-width,initial-scale=1.0,user-scalable=no' />
<link rel=stylesheet type="text/css" href="style.css">
<title>LINE Loginサンプル</title>
</head>
<body>
<div class='all'>
<div class='main'>
<?php

// LINEのサーバーでログイン処理後とにGETアクセスされるページ

// Composerでインストールしたライブラリを一括読み込み
require_once __DIR__ . '/vendor/autoload.php';

// GETリクエストのみ処理
$unsafe = $_SERVER['REQUEST_METHOD'] == 'POST'
       || $_SERVER['REQUEST_METHOD'] == 'PUT'
       || $_SERVER['REQUEST_METHOD'] == 'DELETE';

$session_factory = new \Aura\Session\SessionFactory;
$session = $session_factory->newInstance($_COOKIE);
$csrf_value = $_GET['state'];
$csrf_token = $session->getCsrfToken();

// リクエストの種類とトークンの同一性を検証
if ($unsafe || !$csrf_token->isValid($csrf_value)) {
  echo '<p>不正なリクエストです。</p>';
  return;
}

$callback = 'https://' . $_SERVER['HTTP_HOST']  . '/line_callback.php';
// ログイン成功時はパラメータにcodeが付与されている
if (isset($_GET['code'])) {
  // APIへのアクセストークンを取得するエンドポイント
  $url = 'https://api.line.me/v2/oauth/accessToken';
  // データ
  $data = array(
    'grant_type' => 'authorization_code',
    'client_id' => getenv('LOGIN_CHANNEL_ID'),
    'client_secret' => getenv('LOGIN_CHANNEL_SECRET'),
    'code' => $_GET['code'],
    'redirect_uri' => $callback
  );
  $data = http_build_query($data, '', '&');
  // ヘッダ
  $header = array(
    'Content-Type: application/x-www-form-urlencoded'
  );
  // リクエストを組み立て。POST
  $context = array(
    'http' => array(
      'method'  => 'POST',
      'header'  => implode('\r\n', $header),
      'content' => $data
    )
  );
  // レスポンスを取得
  $resultString = file_get_contents($url, false, stream_context_create($context));
  // 文字列を連想配列に変換
  $result = json_decode($resultString, true);

  // パラメータにaccess_tokenが付与されていれば
  if(isset($result['access_token'])) {
    // ユーザーのプロフィールを取得するエンドポイント
    $url = 'https://api.line.me/v2/profile';
    // アクセストークンを使ってリクエストを組み立て。GET
    $context = array(
      'http' => array(
      'method'  => 'GET',
      'header'  => 'Authorization: Bearer '. $result['access_token']
      )
    );
    $profileString = file_get_contents($url, false, stream_context_create($context));
    $profile = json_decode($profileString, true);
    // HTMLに出力
    echo '<img src="' . htmlspecialchars($profile["pictureUrl"], ENT_QUOTES) . '" />';
    echo '<p>ようこそ、' . htmlspecialchars($profile["displayName"], ENT_QUOTES) . 'さん！</p>';
    echo '<p>userId : ' . htmlspecialchars($profile["userId"], ENT_QUOTES) . 'さん！</p>';

    // ユーザーにメッセージを送信
    $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
    $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
    $bot->pushMessage($profile["userId"], new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('ブラウザ経由でログインしました。'));
  }
}
// ログイン失敗時
else {
  echo '<p>ログインに失敗しました</p>';
}
?>
</div>
</div>
</body>
</html>
