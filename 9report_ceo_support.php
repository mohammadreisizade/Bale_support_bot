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


$sql = "SELECT unique_id FROM Persons WHERE position LIKE CONCAT('%', '6', '%')";
if ($result = $conn->query($sql)) {
    if ($result->num_rows != 0) {
        while ($ro = $result->fetch_assoc()) {
            $data[] = $ro['unique_id'];
        }
    } else {
        $data = [];
    }
}
$counter = 0;
foreach ($data as $u) {
    $contenttmp = array('chat_id' => $u, "text" => "گزارشات روزانه:");
    $bot->sendText($contenttmp);

    $sql = "SELECT * FROM Requests WHERE is_closed=4 ORDER BY done_date DESC, done_time DESC LIMIT 1000";
    if ($result = $conn->query($sql)) {
        if ($result->num_rows == 0) {
            $contenttmp = array('chat_id' => $u, "text" => "موردی وجود ندارد.");
            $bot->sendText($contenttmp);
        } else {
            while ($row = $result->fetch_assoc()) {
                $done_date = $row['done_date'];

                // تفکیک سال، ماه و روز از تاریخ جلالی
                list($year, $month, $day) = explode('-', $done_date);

                $year = intval($year);
                $month = intval($month);
                $day = intval($day);
                $done_date = jalali_to_gregorian($year, $month, $day, "-");

                $done_time = $row['done_time'];
                $timestampFromDB = strtotime($done_date . " " . $done_time);
                // محاسبه فاصله زمانی بین زمان کنونی و زمان دریافتی از دیتابیس (به صورت ثانیه)
                $timeDiffInSeconds = time() - $timestampFromDB;

                // محاسبه فاصله زمانی به صورت ساعت
                $timeDiffInHours = $timeDiffInSeconds / 3600;

                if ($timeDiffInHours <= 24) {
                    $counter++;
                    $title = $row['title'];
                    $id = $row['id'];
                    $description = $row['description'];

                    $date = $row['register_date'];
                    $time = $row['register_time'];

                    $accept_date = $row['accept_date'];
                    $accept_time = $row['accept_time'];

                    $predict_date = $row['predict_date'];
                    $name = $row['name'];
                    $requestor_unit = $row['requestor_unit'];

                    $unit = $row['unit'];
                    if ($unit == 2) {
                        $uni = "انفورماتیک";
                    } elseif ($unit == 3) {
                        $uni = "مالی";
                    } elseif ($unit == 4) {
                        $uni = "پشتیبانی";
                    } elseif ($unit == 5) {
                        $uni = "خدمات";
                    } else {
                        $uni = "نامشخص";
                    }

                    $rate = $row['rate'];
                    if ($rate == 1) {
                        $r = "عدم رضایت";
                    } elseif ($rate == 2) {
                        $r = "رضایت کم";
                    } elseif ($rate == 3) {
                        $r = "رضایت نسبی";
                    } elseif ($rate == 4) {
                        $r = "رضایت متوسط";
                    } elseif ($rate == 5) {
                        $r = "رضایت کامل";
                    } else {
                        $r = "ثبت نشده";
                    }

                    $status = "انجام شده";

                    $content = array("chat_id" => $u, "text" => "وضعیت : $status\nنام درخواست دهنده : $name\nواحد درخواست دهنده : $requestor_unit\nواحد خدمات دهنده : $uni\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nزمان پیشبینی انجام درخواست : $predict_date\nتاریخ تأیید درخواست : $accept_date\nساعت تأیید درخواست : $accept_time\nتاریخ تغییر وضعیت درخواست به انجام شده : $done_date\nساعت تغییر وضعیت درخواست به انجام شده : $done_time\nمیزان رضایت : $r");
                    $bot->sendText($content);

                } else {
                    continue;
                }
                sleep(2);
            }
        }
        if ($counter == 0){
            $contenttmp = array('chat_id' => $u, "text" => "موردی وجود ندارد.");
            $bot->sendText($contenttmp);
        }else{
            $contenttmp = array('chat_id' => $u, "text" => "پایان گزارش.");
            $bot->sendText($contenttmp);
        }
    }
}
?>