<?php 
define('API_KEY','330865504:AAEkUjT1YoA6LEHbybmbkemGn_K0OjDWFSc');
$fname = $message->chat->first_name;
function apiRequest($method, $parameters) {
    foreach($parameters as $key => &$val) {
      if(is_array($val)) {
        $val = json_encode($val);
      }
    }
    $ch = curl_init("https://api.telegram.org/bot".API_KEY."/".$method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    curl_exec($ch);
    curl_close($ch);
}
function randomMail() {
 $list = json_decode(file_get_contents("https://api.temp-mail.org/request/domains/format/json"));
    return substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", ceil(5/34))), 1, 5).$list[mt_rand(0, count($list)-1)];
}
function processMessage($message) {
  if(isset($message["text"])) {
   $chat_id = $message['chat']['id'];
   $message_id = $message["message_id"];
    $text = $message["text"];
    if($text === "/start") {
      apiRequest("sendMessage", ["chat_id" => $chat_id, "text" => "hello!", 'reply_markup'=>["keyboard"=>[[["text"=>"➕Create New Email"]]],"resize_keyboard"=>true]]);
    }
	if($text === "/creator") {
      apiRequest("sendMessage", ["chat_id" => $chat_id, "text" => "hello!", 'reply_markup'=>["inline_keyboard"=>[[['text'=>'ربات خود را بسازید','url'=>"http://t.me/Phpcr_bot"]]],"resize_keyboard"=>true]]);
    }
    elseif($text === "➕Create New Email") {
      $mail = randomMail();
   apiRequest("sendMessage", ["chat_id" => $chat_id, "text" => "✉️Your Email Address:\n\n".$mail, 'reply_markup'=>['inline_keyboard'=>[[['text'=>"📮Message Received", 'callback_data'=>$mail."-".md5($mail)]]]]]);
    }
  }
}
function processQuery($query) {
  $chat_id = $query["message"]['chat']['id'];
  $id = $query['id'];
  $exploded = explode("-", $query['data']); 
  $ch = curl_init("https://api.temp-mail.org/request/mail/id/".$exploded[1]."/format/json");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  $list = curl_exec($ch);
  curl_close($ch);
  if($list === '{"error":"There are no emails yet"}') {
 apiRequest("sendMessage", ["chat_id" => $chat_id, "text" => "📬Email Messages ".$exploded[0]."\n➖➖➖➖➖➖➖➖➖➖➖➖➖\n❌There are no Emails"]);
  }
  else {
 foreach (json_decode($list, true) as $email) {
   apiRequest("sendMessage", ["chat_id" => $chat_id, "text" => "📬Email Messages: ".$exploded[0]."\n\n📍From: ".$email["mail_from"]."\n📌Subject: ".$email["mail_subject"]."\n\n➖➖➖➖➖🏳Start➖➖➖➖➖\n\n".$email["mail_text"]."\n➖➖➖➖➖🏴End➖➖➖➖➖"]);
 }
  }
  apiRequest("answerCallbackQuery", ['callback_query_id' => $id]);
}
$update = json_decode(file_get_contents("php://input"), true);
if(isset($update["message"])) {
 processMessage($update["message"]);
}
elseif(isset($update["callback_query"])) {
 processQuery($update["callback_query"]);
}
?>