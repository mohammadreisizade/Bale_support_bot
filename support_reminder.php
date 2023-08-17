<?php
include "BaleAPIv2.php";
include "jdf.php";

$token = "879112410:dTpufLCe78gh27C5mli5iEZSmFbpM9mqcStBi99V";

// Set session variables

$bot = new balebot($token);
$chat_id = $bot->ChatID();
$user_id = $bot->UserID();
$Text_orgi = $bot->Text();
$callback_data = $bot->CallBack_Data();

$username = $bot->username();

// -----------------------------------------           date and time          --------------------------------------------------

date_default_timezone_set('Asia/Tehran');
$today_date = gregorian_to_jalali(date("Y"), date("m"), date("d"), "-");
$time_now = date('H:i:s');

// ---------------------------------------------------------********************************----------------------------------------------------------------------------
// ---------------------------------------------------------      DATABASE INFORMATIONS     ----------------------------------------------------------------------------
// ---------------------------------------------------------********************************----------------------------------------------------------------------------

$servername = "localhost";
$usern = "balebtir_dev";
$password = "1P,2Hs!xan).";
// Create connection
$conn = new mysqli($servername, $usern, $password, "balebtir_support_bot");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
mysqli_set_charset($conn, "utf8mb4");


$sql = "SELECT unique_id FROM Persons WHERE position=4";
if ($result = $conn->query($sql)) {
    if ($result->num_rows != 0) {
        while ($ro = $result->fetch_assoc()) {
            $data[] = $ro['unique_id'];
        }
    } else {
        $data = [];
    }
}
foreach ($data as $u) {
    $contenttmp = array('chat_id' => $u, "text" => "شروع پردازش درخواست های تأیید شده:");
    $bot->sendText($contenttmp);

    $sql = "SELECT * FROM Requests WHERE is_closed=3 AND req_status=3 AND unit=4 ORDER BY accept_date DESC, accept_time DESC LIMIT 1000";
    if ($result = $conn->query($sql)) {
        if ($result->num_rows == 0) {
            $contenttmp = array('chat_id' => $u, "text" => "موردی وجود ندارد.");
            $bot->sendText($contenttmp);
        } else {
            while ($row = $result->fetch_assoc()) {
                $title = $row['title'];
                $id = $row['id'];
                $description = $row['description'];

                $date = $row['register_date'];
                $time = $row['register_time'];

                $accept_date = $row['accept_date'];
                $accept_time = $row['accept_time'];

                $predict_date = $row['predict_date'];
                $name = $row['name'];

                $uni = "پشتیبانی";

                $status = "تأیید شده";
                $cbdatadone = "d$id";

                $inlineKeyboardoption = [
                    [$bot->buildInlineKeyBoardButton("انجام شده", '', "$cbdatadone")],
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $content = array("chat_id" => $u, "text" => "وضعیت : $status\nنام درخواست دهنده : $name\nواحد مربوط : $uni\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nزمان پیشبینی انجام درخواست : $predict_date\nتاریخ تأیید درخواست : $accept_date\nساعت تأیید درخواست : $accept_time", 'reply_markup' => $Keyboard);
                $bot->sendText($content);

                sleep(2);
            }
        }

        $contenttmp = array('chat_id' => $u, "text" => "پایان گزارش.");
        $bot->sendText($contenttmp);

    }
}
?>