<?php
//ローカル環境
$accessToken = '{キー}';
$db = new PDO('mysql:dbname=axxxis_laravel;host=localhost;charset=utf8','root', 'root');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);

$headers = [
	'Authorization: Bearer ' . $accessToken,
	'Content-Type: application/json; charset=utf-8',
];

// ユーザーIDの探索
$sql = "SELECT * FROM user_lines";
$stmt = $db->query($sql);

// status_nowとIDの値を取得
$id = []; //初期化
foreach ($stmt as $row) {
  if($row['status_now'] == "業務時間外"){
    array_push($id,$row['user_id']);
  }
}

foreach ($id as $row) {

  // POSTデータを設定してJSONにエンコード
  $post = [
    'to' => $row,  //ID
    'messages' => [
      [
        'type' => 'text',
        'text' => "おはようございます。 \n出勤処理をお忘れでないですか？ \n業務開始次第、「出勤」ボタンを押してください！",
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
