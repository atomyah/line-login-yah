<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitonal//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://w3.org/1999/xhtml' lang="ja" xml:lang='ja'>
  <head>
    <meta http-equiv="Content-type" content="text/html: charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <link rel=stylesheet type="text/css" href="style.css">
      <title>LINE LOGINサンプル</title>
  </head>
  <body>
    <div class="all">
      <div class="main">
        <p>下のボタンをタップしてログインしてください</p>
        
<?php
//LINEログインへのリンクを表示するページ

require_once __DIR__ . '/vendor/autoload.php';

//セッション管理クラスをインスタンス化
$session_factory = new \Aura\Session\SegmentFactory;
//セッションのインスタンスを取得
$session = $session_factory->newInstance($_SESSION);
//Segmentオブジェクトを取得 文字列は任意のものに変更
$segment = $session->getSegment('Vendor\Package\ClassName');
// CSRFトークン
$csrf_value = $session->getCsrfToken()->getValue();

//コールバックURL
$callback = urlencode('https://' . $_SERVER['HTTP_HOST'] . '/line_callback.php');
$url = 'https://access.line.me/dialog/oauth/weblogin?response_type=code&client_id=' . getenv('LOGIN_CHANNEL_ID') . '&redirect_url=' . $callback . '&state=' . $csrf_value;
//リンクを出力
echo '<a href=' . $url . '><button class="contact">LINEログイン</button></a>';

?>
      
      
      </div>
    </div>    
  </body> 
</html>