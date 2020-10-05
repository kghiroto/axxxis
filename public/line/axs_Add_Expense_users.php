<?php
// axs_Linebot.phpのインポート先

// データ数を調べる
$sql = "SELECT * FROM expense_users";
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

// expense_usersテーブルにユーザーの追加($user_idは事前に取得済み)
$sql = "INSERT INTO expense_users (id , user_id , name , url) VALUES (:id , :user_id , :name , :url)";
$stmt = $db->prepare($sql);
$params = array(':id' => $count+1 ,':user_id' => $user_id , ':name' => $name ,':url' => "");
$stmt->execute($params);
