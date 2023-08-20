<?php
include "BaleAPIv2.php";






$users=['mohammadreisii'=>['role'=>'admin','name'=>'mohammad'], 'alirp78'=>['role'=>'fin','name'=>'ali']];



 function get_request_details(){
     $token = "1754660216:UbhE2VljgXFVVdLFb5lHRTgitScKXFosSCEveTRE";

// Set session variables

     $bot = new balebot($token);

     return $bot;
}

 function connect_to_db(){}

function check_user_info($users,$username){
    $role= $users[$username];
    return $role;
}

function check_role($role,$message){
     $role_responose=['admin'=>['salam'=>'salam on alaykom','khar'=>'khodeti'],
                        'fin'=>['salam'=>'boro gom sho bi baradar va pedar mage khodet pedar va baradar nadari','khar'=>'afarin']];
    $mes=$role_responose[$role];
     return $mes[$message];
}

function check_message(){}


function send_message($response,$bot){
    $contenttmp = array('chat_id' => $bot->ChatID(), "text" => $response);
    $bot->sendText($contenttmp);
    return;
}



function main($users){
    $bot= get_request_details();
    $chat_id = $bot->ChatID();
    $user_id = $bot->UserID();
    $message = $bot->Text();
    $callback_data = $bot->CallBack_Data();
    $username = $bot->username();
    $message_id = $bot->MessageID();


//    $bot->sendText(array('chat_id' => $chat_id, "text" => "main"));

    $user_info=check_user_info($users,$username);
//    $bot->sendText(array('chat_id' => $chat_id, "text" => "after check info"));

    $response=check_role($user_info['role'],$message);
//    $bot->sendText(array('chat_id' => $chat_id, "text" => "after set response"));
//    $bot->sendText(array('chat_id' => $chat_id, "text" => "role: ".$user_info['role']));
//    $bot->sendText(array('chat_id' => $chat_id, "text" => "message: ".$message));



    send_message($response,$bot);




}




main($users);





//$contenttmp = array('chat_id' => $chat_id, "text" => $bot->MessageID());
//$bot->sendText($contenttmp);
//
//$contenttmp = array('chat_id' => $chat_id, "text" => $Text_orgi);
//$bot->sendText($contenttmp);


//$delete_content = array('chat_id' => $chat_id, "message_id" => 48);
//$bot->deleteMessage($delete_content);
