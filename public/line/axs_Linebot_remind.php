<?php

// ローカル環境
$accessToken = '{キー}';
$db = new PDO('mysql:dbname=axxxis_laravel;host=localhost;charset=utf8','root', 'root');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);

$headers = [
	'Authorization: Bearer ' . $accessToken,
	'Content-Type: application/json; charset=utf-8',
];

// 昨月
$last_month = date('n', strtotime('-1 month'));


// ユーザーIDの取得
$sql = "SELECT user_id,url FROM expense_users";
$stmt = $db->query($sql);
$stmt->execute();

// ユーザー名取得
$name = NULL; //初期化

foreach ($stmt as $row) {

  // POSTデータを設定してJSONにエンコード
  $post = [
    'to' => $row["user_id"],  //ID
    'messages' => [
      [
        'type' => 'text',
        'text' => "【リマインド】 \n{$last_month}月分の交通費精算をお忘れずに行ってください \n{$row["url"]}",
      ],
    ],
  ];
  $post = json_encode($post);

  // HTTPリクエストを設定
  $ch = curl_init('https://api.line.me/v2/bot/message/push');
  $options = [
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_BINARYTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_POSTFIELDS => $post,
  ];
  curl_setopt_array($ch, $options);

  // 実行
  $result = curl_exec($ch);

  // エラーチェック
  $errno = curl_errno($ch);
  if ($errno) {
    return;
  }

  // HTTPステータスを取得
  $info = curl_getinfo($ch);
  $httpStatus = $info['http_code'];

  $responseHeaderSize = $info['header_size'];
  $body = substr($result, $responseHeaderSize);

  // 200 だったら OK
  echo $httpStatus . ' ' . $body;

}
