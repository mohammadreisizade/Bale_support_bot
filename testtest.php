<?php
include "BaleAPIv2.php";

$token = "1754660216:UbhE2VljgXFVVdLFb5lHRTgitScKXFosSCEveTRE";

// Set session variables

$bot = new balebot($token);
$chat_id = $bot->ChatID();
$user_id = $bot->UserID();
$Text_orgi = $bot->Text();
$callback_data = $bot->CallBack_Data();
$username = $bot->username();
$message_id = $bot->MessageID();

$contenttmp = array('chat_id' => $chat_id, "text" => $bot->messa);
$bot->sendText($contenttmp);

$contenttmp = array('chat_id' => $chat_id, "text" => $Text_orgi);
$x = $bot->sendText($contenttmp);

$contenttmp = array('chat_id' => $chat_id, "text" => $x);
$bot->sendText($contenttmp);

//$delete_content = array('chat_id' => $chat_id, "message_id" => 9);
//$bot->deleteMessage($delete_content);
