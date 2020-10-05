<?php
// 【axs_Linebot.phpからincludeでこのファイルを参照しています】

// ライブラリの読み込み
require_once __DIR__. '/vendor/autoload.php';
// 秘密鍵.jsonまでのパス
$json_path = __DIR__. '/axxxis-calender-869de224e573.json';
// データの開始日
$start = date(date('Y'). '-01-01\T00:00:00\Z');
// データの終了日
$end = date(date('Y'). '-12-31\T00:00:00\Z');
// カレンダーID
$calendar_id = 'thenewgate.staff@gmail.com';

$client = new Google_Client();
// ※ 注意ポイント: 権限の指定
// 予定を取得する時は
// $client -> setScopes(Google_Service_Calendar::CALENDAR_READONLY);
// 予定を追加する時は Google_Service_Calendar::CALENDAR_EVENTS
$client -> setScopes(Google_Service_Calendar::CALENDAR_EVENTS);

// 認証設定
$client -> setAuthConfig($json_path);

// カレンダーサービスの作成
$service = new Google_Service_Calendar($client);


if($return_message_text == "業務開始ですね！ \n次に勤務場所を入力してください！"){

  $event = new Google_Service_Calendar_Event(array(
    'summary' => "【{$name}】業務開始", //予定のタイトル
    'start' => array(
        'dateTime' => "{$timenow}+09:00",// 開始日時
        'timeZone' => 'Asia/Tokyo',
    ),
    'end' => array(
        'dateTime' => "{$timenow}+09:00", // 終了日時
        'timeZone' => 'Asia/Tokyo',
    ),
  ));

  $event = $service->events->insert($calendar_id, $event);


}else if($return_message_text == "ありがとうございます！ \n次に本日の実績を入力してください！"){


  $option = [
    'timeMin' => $start,
    'timeMax' => $end,
    'maxResults' => 50,
    'orderBy' => 'startTime',
    'singleEvents' => 'true'
  ];
  // データの取得
  $response = $service->events->listEvents($calendar_id, $option);
  $events = $response->getItems();

  $results = [];
  if (!empty($events)) {
      foreach ($events as $event) {
          // 終日予定はdate、時刻指定はdateTimeにデータが入り、もう片方にはNULLが入っている
          $start = new DateTime(!is_null($event->start->date)?$event->start->date:$event->start->dateTime);
          $description = $event->getDescription();
          $end   = new DateTime(!is_null($event->end->date)?$event->end->date:$event->end->dateTime);
          $eventId = $event->getId();
          $results[] = [
              'start' => $start->format("Y-m-d\TH:i:s"),
              'title' => (string)$event->summary,
              'description' => $description,
              'eventId' => $eventId
          ];
      }
  }

  foreach($results as $result){

    if($result['title'] == "【{$name}】業務開始"){
      $timenow = $result['start'];

      $service->events->delete($calendar_id, $result['eventId']);

      $event = new Google_Service_Calendar_Event(array(
      'summary' => "【{$name}】業務完了", //予定のタイトル
      'start' => array(
          'dateTime' => "{$timenow}+09:00",// 開始日時
          'timeZone' => 'Asia/Tokyo',
      ),
      'end' => array(
          'dateTime' => "{$timeafter}+09:00", // 終了日時
          'timeZone' => 'Asia/Tokyo',
      ),
        'description' => "{$description} \n \n◆業務終了時刻 \n{$message_text}", //説明
    ));
      $event = $service->events->insert($calendar_id, $event);
    }
  }


}else if($return_message_text == "ありがとうございます！ \n本日も１日頑張りましょう！"){

    $option = [
        'timeMin' => $start,
        'timeMax' => $end,
        'maxResults' => 50,
        'orderBy' => 'startTime',
        'singleEvents' => 'true'
    ];

    // データの取得
    $response = $service->events->listEvents($calendar_id, $option);
    $events = $response->getItems();

    $results = [];
    if (!empty($events)) {
        foreach ($events as $event) {
            // 終日予定はdate、時刻指定はdateTimeにデータが入り、もう片方にはNULLが入っている
            $start = new DateTime(!is_null($event->start->date)?$event->start->date:$event->start->dateTime);
            $description = $event->getDescription();
            $end   = new DateTime(!is_null($event->end->date)?$event->end->date:$event->end->dateTime);
            $eventId = $event->getId();
            $results[] = [
                'start' => $start->format("Y-m-d\TH:i:s"),
                'end' => $end->format("Y-m-d\TH:i:s"),
                'title' => (string)$event->summary,
                'description' => $description,
                'eventId' => $eventId
            ];
        }
    }

    $result = end($results);

    if($result['title'] == "【{$name}】業務開始"){
        $timenow = $result['start'];

        $service->events->delete($calendar_id, $result['eventId']);

        $event = new Google_Service_Calendar_Event(array(
        'summary' => "【{$name}】業務開始", //予定のタイトル
        'start' => array(
            'dateTime' => "{$timenow}+09:00",// 開始日時
            'timeZone' => 'Asia/Tokyo',
        ),
        'end' => array(
            'dateTime' => "{$timenow}+09:00", // 終了日時
            'timeZone' => 'Asia/Tokyo',
        ),
        'description' => "◆勤務場所 \n{$message_text}", //説明
    ));
        $event = $service->events->insert($calendar_id, $event);
    }

}else if($return_message_text == "ありがとうございます！ \n本日もお疲れ様でした！"){

    $option = [
        'timeMin' => $start,
        'timeMax' => $end,
        'maxResults' => 50,
        'orderBy' => 'startTime',
        'singleEvents' => 'true'
    ];

    // データの取得
    $response = $service->events->listEvents($calendar_id, $option);
    $events = $response->getItems();

    $results = [];
    if (!empty($events)) {
        foreach ($events as $event) {
            // 終日予定はdate、時刻指定はdateTimeにデータが入り、もう片方にはNULLが入っている
            $start = new DateTime(!is_null($event->start->date)?$event->start->date:$event->start->dateTime);
            $description = $event->getDescription();
            $end   = new DateTime(!is_null($event->end->date)?$event->end->date:$event->end->dateTime);
            $eventId = $event->getId();
            $results[] = [
                'start' => $start->format("Y-m-d\TH:i:s"),
                'end' => $end->format("Y-m-d\TH:i:s"),
                'title' => (string)$event->summary,
                'description' => $description,
                'eventId' => $eventId
            ];
        }
    }

    $result = end($results);

    if(strpos($description,'◆業務終了時コメント') !== false){
        $description = strstr($description, "\n \n◆業務終了時コメント", true);
    }

    if($result['title'] == "【{$name}】業務完了"){
        $timenow = $result['start'];

        $service->events->delete($calendar_id, $result['eventId']);

        $event = new Google_Service_Calendar_Event(array(
        'summary' => "【{$name}】業務完了", //予定のタイトル
        'start' => array(
            'dateTime' => "{$timenow}+09:00",// 開始日時
            'timeZone' => 'Asia/Tokyo',
        ),
        'end' => array(
            'dateTime' => "{$timeafter}+09:00", // 終了日時
            'timeZone' => 'Asia/Tokyo',
        ),
        'description' => "{$description} \n \n◆実績コメント \n{$message_text}", //説明
    ));
        $event = $service->events->insert($calendar_id, $event);
    }
}
