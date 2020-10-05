<?php
// ローカル環境
$accessToken = '{キー}';
$db = new PDO('mysql:dbname=axxxis_laravel;host=localhost;charset=utf8','root', 'root');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);

//取得データ
$replyToken = $json_object->{"events"}[0]->{"replyToken"};        //返信用トークン
$message_type = $json_object->{"events"}[0]->{"message"}->{"type"};    //メッセージタイプ
$message_text = $json_object->{"events"}[0]->{"message"}->{"text"};    //メッセージ内容
$timestamp = $json_object->{"events"}[0]->{"timestamp"};      // タイムスタンプ

// ユーザーIDの取得
$user_id = $json_object->{"events"}[0]->{"source"}->{"userId"};

// TimeStampをUTC→JSTに変換
date_default_timezone_set('Asia/Tokyo');

// $timestamp再定義
$timestamp = $timestamp/1000;

// TimeStampをDate型に変換
$timenow = date("Y-m-d\TH:i:s");

// TimeStampをDate型に変換
$timeafter = date("Y-m-d\TH:i:s");

//メッセージタイプが「text」以外のときは何も返さず終了
if($message_type != "text") exit;

// ユーザーIDの探索
$sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
$stmt = $db->query($sql);

// comment_existenceとステータスとID取得
$comment_existence =  NULL; //初期化
$id =  NULL; //初期化
$status_now = NULL; //初期化
foreach ($stmt as $row) {
    $comment_existence = $row['comment_existence'];
}

//返信メッセージ
if(strpos($message_text,'出勤') !== false){

  // データ数を調べる
  $sql = "SELECT * FROM attendance_lines";
  $stmt = $db->query($sql);
  $stmt->execute();
  $count = $stmt->rowCount();

  // ユーザーIDの探索
  $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
  $stmt = $db->query($sql);
  $stmt->execute();
  $register = $stmt->rowCount();

  // ユーザー名取得
  $name = NULL; //初期化
  foreach ($stmt as $row) {
    $name = $row['name'];
  }

  // ユーザーIDの探索
  $sql = "SELECT * FROM attendance_lines WHERE user_id = '" . $user_id . "'";
  $stmt = $db->query($sql);

  // startとfinishとIDの値を取得
  $start = NULL; //初期化
  $finish = NULL; //初期化
  $id = NULL; //初期化
  foreach ($stmt as $row) {
    $start = $row['start'];
    $finish = $row['finish'];
    $id = $row['id'];
  }

  if($register == 0){
    // レスポンス
    $return_message_text = "まだ名前の登録が完了してないよ！ \n@に続けて苗字と名前を書いてね！ \n例「@山田 太郎」";
  }else if($start == NULL & $finish == NULL){
    // データの追加
    $sql = "INSERT INTO attendance_lines (id , name , user_id , start) VALUES (:id , :name , :user_id , :start)";
    $stmt = $db->prepare($sql);
    $params = array(':id' => $count+1 ,':name' => $name , ':user_id' => $user_id , ':start' => $timenow);
    $stmt->execute($params);

    // レスポンス
    $return_message_text = "業務開始ですね！ \n次に勤務場所を入力してください！";

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);

    // comment_existenceとステータスとID取得
    $comment_existence =  NULL; //初期化
    $id =  NULL; //初期化
    $status_now = NULL; //初期化
    foreach ($stmt as $row) {
        $comment_existence = $row['comment_existence'];
        $status_now = $row['status_now'];
        $id = $row['id'];
    }

    // comment_existenceとステータス変更
    $sql = "UPDATE user_lines SET status_now = :status_now , comment_existence = :comment_existence WHERE id = :id";
    $stmt = $db->prepare($sql);
    $params = array(':status_now' => "業務中" , ':comment_existence' => "Place" , ':id' => $id);
    $stmt->execute($params);

    // GoogleCarenderのインポート
    include("axs_Carender_add.php");
  }else if($start != NULL & $finish != NULL){
    // データの追加
    $sql = "INSERT INTO attendance_lines (id , name , user_id , start) VALUES (:id , :name , :user_id , :start)";
    $stmt = $db->prepare($sql);
    $params = array(':id' => $count+1 ,':name' => $name , ':user_id' => $user_id , ':start' => $timenow);
    $stmt->execute($params);

    // レスポンス
    $return_message_text = "業務開始ですね！ \n次に勤務場所を入力してください！";

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);

    // comment_existenceとステータスとID取得
    $comment_existence =  NULL; //初期化
    $id =  NULL; //初期化
    $status_now = NULL; //初期化
    foreach ($stmt as $row) {
        $comment_existence = $row['comment_existence'];
        $status_now = $row['status_now'];
        $id = $row['id'];
    }

    // ステータス変更
    $sql = "UPDATE user_lines SET status_now = :status_now , comment_existence = :comment_existence WHERE id = :id";
    $stmt = $db->prepare($sql);
    $params = array(':status_now' => "業務中", ':comment_existence' => "Place", ':id' => $id);
    $stmt->execute($params);

    // GoogleCarenderのインポート
    include("axs_Carender_add.php");
  }else{
    // レスポンス
    $return_message_text = "既に業務開始してるよ！";
  }

}else if(strpos($message_text,'退勤') !== false){

  // ユーザーIDの探索
  $sql = "SELECT * FROM attendance_lines WHERE user_id = '" . $user_id . "'";
  $stmt = $db->query($sql);
  $stmt->execute();
  $start = $stmt->rowCount();

  // finishとIDの値を取得
  $finish = NULL; //初期化
  $id = NULL; //初期化
  foreach ($stmt as $row) {
    $finish = $row['finish'];
    $id = $row['id'];
  }

  // ユーザーIDの探索
  $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
  $stmt = $db->query($sql);
  $stmt->execute();
  $register = $stmt->rowCount();

  // ユーザー名取得
  $name = NULL; //初期化
  foreach ($stmt as $row) {
    $name = $row['name'];
  }

  if($register == 0){
    // レスポンス
    $return_message_text = "まだ名前の登録が完了してないよ！ \n@に続けて苗字と名前を書いてね！ \n例「@山田 太郎」";
  }else if($finish == NULL && $start != 0){

    // レスポンス
    $return_message_text = "業務終了ですね！ \nそれでは退勤時刻の入力をお願いいたします！ \n例 19:00";

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);

    // comment_existenceとステータスとID取得
    $comment_existence =  NULL; //初期化
    $id =  NULL; //初期化
    $status_now = NULL; //初期化
    foreach ($stmt as $row) {
        $comment_existence = $row['comment_existence'];
        $status_now = $row['status_now'];
        $id = $row['id'];
    }

    // comment_existenceとステータス変更
    $sql = "UPDATE user_lines SET status_now = :status_now , comment_existence = :comment_existence WHERE id = :id";
    $stmt = $db->prepare($sql);
    $params = array(':status_now' => "業務時間外", ':comment_existence' => "Time", ':id' => $id);
    $stmt->execute($params);

    // GoogleCarenderのインポート
    include("axs_Carender_add.php");
  }else{
    // レスポンス
    $return_message_text = "まだ業務開始をしてないよ！";
  }

}else if(strpos($message_text,'@') !== false){

  // @を削除
  $message_text = str_replace('@', '', $message_text);

  // データ数を調べる
  $sql = "SELECT * FROM user_lines";
  $stmt = $db->query($sql);
  $stmt->execute();
  $count=$stmt->rowCount();

  // ユーザーIDの探索
  $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
  $stmt = $db->query($sql);
  $stmt->execute();
  $register = $stmt->rowCount();

  // ユーザー名取得
  $name = NULL; //初期化
  foreach ($stmt as $row) {
    $name = $row['name'];
  }

  if($register == 0){
    // ユーザーの追加
    $sql = "INSERT INTO user_lines (id , name , user_id) VALUES (:id , :name , :user_id)";
    $stmt = $db->prepare($sql);
    $params = array(':id' => $count+1 ,':name' => $message_text , ':user_id' => $user_id);
    $stmt->execute($params);

    // axs_Add_Expense_users.phpのインポート
    include("axs_Add_Expense_users.php");

    // レスポンス
    $return_message_text = "名前の登録が完了したよ！ \nこのチャットの使い方は一番最初に説明してるから、わからなくなったら見返してね！";
  }else{
    $return_message_text = "名前の登録は既に完了しているよ！";
  }

}else if($comment_existence == "Place"){

    // ユーザーIDの探索
    $sql = "SELECT * FROM attendance_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();
    $start = $stmt->rowCount();

    // start_commentとIDの値を取得
    $start_comment = NULL; //初期化
    $id = NULL; //初期化
    foreach ($stmt as $row) {
      $finish = $row['start_comment'];
      $id = $row['id'];
    }

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();
    $register = $stmt->rowCount();

    // ユーザー名取得
    $name = NULL; //初期化
    foreach ($stmt as $row) {
      $name = $row['name'];
    }

    // start_commentの追記
    $sql = "UPDATE attendance_lines SET start_comment = :start_comment WHERE id = :id";
    $stmt = $db->prepare($sql);
    $params = array(':start_comment' => $message_text, ':id' => $id);
    $stmt->execute($params);

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();

    // スタッツ取得
    $status_now = NULL; //初期化
    foreach ($stmt as $row) {
        $status_now = $row['status_now'];
    }

    // ユーザーIDの探索
    $sql = "SELECT * FROM attendance_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();

    // ID取得
    $id = NULL; //初期化
    foreach ($stmt as $row) {
        $id = $row['id'];
    }

    // レスポンス
    $return_message_text = "ありがとうございます！ \n本日も１日頑張りましょう！";

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);

    // comment_existenceとステータスとID取得
    $comment_existence =  NULL; //初期化
    $id =  NULL; //初期化
    foreach ($stmt as $row) {
        $comment_existence = $row['comment_existence'];
        $id = $row['id'];
    }

    // comment_existenceとステータス変更
    $sql = "UPDATE user_lines SET comment_existence = :comment_existence WHERE id = :id";
    $stmt = $db->prepare($sql);
    $params = array(':comment_existence' => "No", ':id' => $id);
    $stmt->execute($params);

    // GoogleCarenderのインポート
    include("axs_Carender_add.php");

}else if($comment_existence == "Time"){

    // ユーザーIDの探索
    $sql = "SELECT * FROM attendance_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();
    $start = $stmt->rowCount();

    // finishとIDの値を取得
    $finish = NULL; //初期化
    $id = NULL; //初期化
    foreach ($stmt as $row) {
      $finish = $row['finish'];
      $id = $row['id'];
    }

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();
    $register = $stmt->rowCount();

    // ユーザー名取得
    $name = NULL; //初期化
    foreach ($stmt as $row) {
      $name = $row['name'];
    }

    // finishの追記
    $sql = "UPDATE attendance_lines SET finish = :finish WHERE id = :id";
    $stmt = $db->prepare($sql);
    $params = array(':finish' => $message_text, ':id' => $id);
    $stmt->execute($params);

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();

    // スタッツ取得
    $status_now = NULL; //初期化
    foreach ($stmt as $row) {
        $status_now = $row['status_now'];
    }

    // ユーザーIDの探索
    $sql = "SELECT * FROM attendance_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();

    // ID取得
    $id = NULL; //初期化
    foreach ($stmt as $row) {
        $id = $row['id'];
    }

    // レスポンス
    $return_message_text = "ありがとうございます！ \n次に本日の実績を入力してください！";

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);

    // comment_existenceとステータスとID取得
    $comment_existence =  NULL; //初期化
    $id =  NULL; //初期化
    foreach ($stmt as $row) {
        $comment_existence = $row['comment_existence'];
        $id = $row['id'];
    }

    // comment_existenceとステータス変更
    $sql = "UPDATE user_lines SET comment_existence = :comment_existence WHERE id = :id";
    $stmt = $db->prepare($sql);
    $params = array(':comment_existence' => "Performance", ':id' => $id);
    $stmt->execute($params);

    // GoogleCarenderのインポート
    include("axs_Carender_add.php");

  }else if($comment_existence == "Performance"){

    // ユーザーIDの探索
    $sql = "SELECT * FROM attendance_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();
    $start = $stmt->rowCount();

    // finishとIDの値を取得
    $finish = NULL; //初期化
    $id = NULL; //初期化
    foreach ($stmt as $row) {
      $finish = $row['finish'];
      $id = $row['id'];
    }

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();
    $register = $stmt->rowCount();

    // ユーザー名取得
    $name = NULL; //初期化
    foreach ($stmt as $row) {
      $name = $row['name'];
    }

    // finishの追記
    $sql = "UPDATE attendance_lines SET finish_comment = :finish_comment WHERE id = :id";
    $stmt = $db->prepare($sql);
    $params = array(':finish_comment' => $message_text, ':id' => $id);
    $stmt->execute($params);

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();

    // スタッツ取得
    $status_now = NULL; //初期化
    foreach ($stmt as $row) {
        $status_now = $row['status_now'];
    }

    // ユーザーIDの探索
    $sql = "SELECT * FROM attendance_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);
    $stmt->execute();

    // ID取得
    $id = NULL; //初期化
    foreach ($stmt as $row) {
        $id = $row['id'];
    }

    // レスポンス
    $return_message_text = "ありがとうございます！ \n本日もお疲れ様でした！";

    // ユーザーIDの探索
    $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
    $stmt = $db->query($sql);

    // comment_existenceとID取得
    $comment_existence =  NULL; //初期化
    $id =  NULL; //初期化
    foreach ($stmt as $row) {
        $comment_existence = $row['comment_existence'];
        $id = $row['id'];
    }

    // comment_existence変更
    $sql = "UPDATE user_lines SET comment_existence = :comment_existence WHERE id = :id";
    $stmt = $db->prepare($sql);
    $params = array(':comment_existence' => "No", ':id' => $id);
    $stmt->execute($params);

    // GoogleCarenderのインポート
    include("axs_Carender_add.php");

}else if(strpos($message_text,'社員情報参照') !== false){

  // ユーザーIDの探索
  $sql = "SELECT * FROM user_lines WHERE user_id = '" . $user_id . "'";
  $stmt = $db->query($sql);
  $stmt->execute();
  $register = $stmt->rowCount();

  $name = NULL; //初期化
  $accessType = NULL; //初期化
  $department = NULL; //初期化
  foreach ($stmt as $row) {
    $name = $row['name'];  // ユーザー名取得
    $accessType = $row['accessType'];  // 役職名取得
    $department = $row['department'];  // 所属名取得
  }

  if($register != 0){
    // レスポンス
    $return_message_text = "登録名: {$name} \n役職名: {$accessType} \n所属名: {$department}";
  }else{
    // レスポンス
    $return_message_text = "まだ名前の登録が完了してないよ！ \n@に続けて苗字と名前を書いてね！ \n例「@山田 太郎」";
  }

}else if(strpos($message_text,'社訓参照') !== false){
  $return_message_text = "「Value(大切にすべき価値観)」 \n
  <前提>利益とは、企業の目的ではなく、企業が事業を継続・発展させていくための条件である。 \n
  ■主体性を発揮する \n
  我々は、決して他人や環境のせいにせず、最も厳しい現実や結果を直視する。
  問題や課題がひとりでに改善することを期待せず、主体的に動き自ら変えていく。
  その問題の中に、未来への機会がある。 \n
  ■スピードは競争力である \n
  我々は、スピードが競争力であることを認識し行動する。
  「他に先んじる早さ」を持ち「何事も早く実行する」ことが、 結果的に多くのビジネスチャンスを掴め、素早い改善を実現し、より良い成果に繋がる。 \n
  ■Win-Win志向である \n
  我々は、顧客をはじめ、上司・同僚・部下・パートナー、すべての関係においてWin-Winの実践をする。
  Win-Winの実践なくして、長期的に良好な関係を築くことは困難である。 \n
  ■結果主義である \n
  我々は結果を重視する。
  計画も実行されなければ価値がない。知識も利用されなければ価値がない。
  問題点を洗い出しても解決されなければ意味がない。
  潜在的能力や潜在的知識などではなく、達成された結果に対して相当する報酬が与えられる。 \n
  ■組織へ貢献する \n
  我々は、個人あるいは部署のために会社全体のパフォーマンスを犠牲にしない。
  我々は、個人の利益ではなく、組織の利益を重んじる。 \n
  ■高い倫理観を持つ \n
  我々は、法律や倫理に反してはならない。
  良識ある社会人として高い倫理観と遵法精神を持ち、 常に何が正しいのかを考え、社会的な良識を持って誠実に行動する。 法律や倫理に反しては長期的な成功は得られない。 \n
  ■アドバタイジングオンセルフとオタクイズムの精神を常に持つ \n
  我々は、商品を売っているわけではなく、人の魅力を売っているという自覚をもってお客様と対峙する。
  また、好きなものを徹底的に極め、誰にも負けないオタクの気持ちを持ち続ける。";

}else if(strpos($message_text,'問い合わせ') !== false){
  $return_message_text = "以下メールアドレスまでご連絡お願いいたします。 \nh.kaji@thenewgate.co.jp";
}else{
  $return_message_text = "素敵ですね！";
}

//レスポンスフォーマット
$response_format_text = ["type" => $message_type , "text" => $return_message_text];

//ポストデータ
$post_data = ["replyToken" => $replyToken , "messages" => [$response_format_text]];

$ch = curl_init("https://api.line.me/v2/bot/message/reply");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charser=UTF-8','Authorization: Bearer ' . $accessToken));
$result = curl_exec($ch);
curl_close($ch);

//データベース接続切断
$db = NULL;

// デバック用
// define("TESTFILE","./TEST.txt");
// $fh = fopen(TESTFILE, "w");
// fwrite($fh,$result);
// fclose($fh);

?>
