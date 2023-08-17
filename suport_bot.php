<?php
include "BaleAPIv2.php";
include "jdf.php";

//require 'vendor/autoload.php';
require_once 'Classes/PHPExcel.php';

//use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// -------------------------------------------------------------------------------------------------------------------------------
// ---------------------------------------------------------- FUNCTIONS -----------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------
// کنسل کردن تغییر سمت
function stop_changing($conn)
{
    $sql = "SELECT * FROM Persons WHERE status='changing'";
    if ($result = $conn->query($sql)) {
        if ($result->num_rows != 0) {
            $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
            $conn->query($q_up);
        }
    }
    $sql = "SELECT * FROM Persons WHERE status='change'";
    if ($result = $conn->query($sql)) {
        if ($result->num_rows != 0) {
            $q_up = "DELETE FROM Persons WHERE status='change'";
            $conn->query($q_up);
        }
    }
}

// کنسل کردن عملیات رد درخواست
function stop_reason_message($conn, $bb)
{
    $query_for_reject_message = "SELECT * FROM Requests WHERE rejector=$bb AND (reason='setreasonforreject' OR predict_date='setpredictdate')";
    if ($result = $conn->query($query_for_reject_message)) {
        if ($result->num_rows != 0) {
            $row = $result->fetch_assoc();
            $id = $row['id'];
            $q_up = "UPDATE Requests SET reason=NULL, rejector=NULL, predict_date=NULL WHERE id=$id";
            $conn->query($q_up);
        }
    }

}

// کنسل کردن ساخت کاربر جدید و کنسل کردن تغییر یوزر نیم
function delete_half_made_user($conn)
{
    $sql = "SELECT * FROM Persons WHERE status='choosing' OR status='getname' OR status='getuser' OR status='getpos' OR 
                            status='changeus' OR status='changeuss'";
    if ($result = $conn->query($sql)) {
        if ($result->num_rows != 0) {
            $sql = "DELETE FROM Persons WHERE status='choosing' OR status='getname' OR status='getuser' OR
                          status='getpos' OR status='changeus' OR status='changeuss'";
            $conn->query($sql);
        }
    }
}

// کنسل کردن دریافت تعداد برای نمایش درخواست ها
function delete_get_num($conn, $bb)
{
    $q_exists = "SELECT id FROM Requests WHERE req_status=8 AND created_by=$bb";
    if ($result = $conn->query($q_exists)) {
        if ($result->num_rows != 0) {
            $row = $result->fetch_assoc();
            $ccc = $row['id'];
            settype($ccc, "integer");
            $d_q = "DELETE FROM Requests WHERE id=$ccc";
            $conn->query($d_q);
        }
    }
}

// حذف درخواست های نیمه کاره
function delete_undone_request($conn, $bb)
{
    $q_exists = "SELECT id FROM Requests WHERE created_by=$bb AND is_closed=1";
    if ($result = $conn->query($q_exists)) {
        if ($result->num_rows != 0) {
            $row = $result->fetch_assoc();
            $ccc = $row['id'];
            settype($ccc, "integer");
            $d_q = "DELETE FROM Requests WHERE id=$ccc";
            $conn->query($d_q);
        }
    }
}

function accounting_clipboard($bot, $chat_id)
{
    $inlineKeyboardoption = [
        $bot->buildInlineKeyBoardButton("ثبت درخواست", '', "newreqacc"),
        $bot->buildInlineKeyBoardButton("درخواست های من", '', "myreq"),
        $bot->buildInlineKeyBoardButton("درخواست های بررسی نشده", '', "openreqacc"),
        $bot->buildInlineKeyBoardButton("درخواست های تأیید شده", '', "confirmed_requests"),
        $bot->buildInlineKeyBoardButton("همه درخواست های مربوط به من", '', "everything"),
    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' => $Keyboard);
    $bot->sendText($contenttmp);
}

function projectmanager_clipboard($bot, $chat_id)
{
    $inlineKeyboardoption = [
        $bot->buildInlineKeyBoardButton("ثبت درخواست جدید", '', "paymentreq"),
        $bot->buildInlineKeyBoardButton("درخواست های من", '', "myreq"),
    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' => $Keyboard);
    $bot->sendText($contenttmp);
}

function admin_clipboard($bot, $chat_id)
{
    $inlineKeyboardoption = [
        $bot->buildInlineKeyBoardButton("تنظیمات", '', "setting"),
        $bot->buildInlineKeyBoardButton("ثبت درخواست جدید", '', "admin_new_req"),
        $bot->buildInlineKeyBoardButton("همه درخواست ها", '', "everything"),
        $bot->buildInlineKeyBoardButton("درخواست های من", '', "myreq"),
        $bot->buildInlineKeyBoardButton("کاربران سامانه", '', "admin_users_list"),
        $bot->buildInlineKeyBoardButton("خروجی اکسل", '', "adminexcel"),

    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' => $Keyboard);
    $bot->sendText($contenttmp);
}

function users_list($conn, $bot, $chat_id)
{
    $sql = "SELECT name, username, position FROM Persons WHERE status IS null";
    $result = $conn->query($sql);
    function convertToText($rows)
    {
        $text = "";
        foreach ($rows as $row) {
            $name = $row['name'];
            $username = $row['username'];
            $position = $row['position'];
            if ($position == 1) {
                $position = "ادمین";
            } elseif ($position == 2) {
                $position = "انفورماتیک";
            } elseif ($position == 3) {
                $position = "مالی";
            } elseif ($position == 4) {
                $position = "پشتیبانی";
            } elseif ($position == 5) {
                $position = "خدمات";
            } elseif ($position == 6) {
                $position = "مدیر عامل";
            } elseif ($position == 7) {
                $position = "مدیر واحد";
            }
            $text .= "نام: $name \n یوزرنیم:\n @$username \n سمت: $position \n**********\n";
        }
        return $text;
    }

    $batchSize = 4;
    $count = 0;
    $rows = array();

    while ($row = $result->fetch_assoc()) {
        $count++;
        $rows[] = $row;

        if ($count % $batchSize === 0) {
            $content = array("chat_id" => $chat_id, "text" => convertToText($rows));
            $bot->sendText($content);
            $rows = array();
        }
        sleep(2);
    }
    if (!empty($rows)) {
        $content = array("chat_id" => $chat_id, "text" => convertToText($rows));
        $bot->sendText($content);
    }
}

function create_new_req($conn, $bot, $bb, $chat_id)
{
    $qu = "INSERT INTO Requests (is_closed, req_status, created_by)
      VALUES (1, 7,$bb)";
    $conn->query($qu);
    $inlineKeyboardoption = [
        $bot->buildInlineKeyBoardButton("انفورماتیک", '', "choose_informatic"),
        $bot->buildInlineKeyBoardButton("پشتیبانی", '', "choose_support"),
        $bot->buildInlineKeyBoardButton("خدمات", '', "choose_services"),
        $bot->buildInlineKeyBoardButton("مالی", '', "choose_financial"),
    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $contenttmp = array('chat_id' => $chat_id, "text" => "لطفا واحد درخواست دهنده را از گزینه های زیر انتخاب کنید.", 'reply_markup' => $Keyboard);
    $bot->sendText($contenttmp);
}

function req_status_process($conn, $bot, $chat_id, $bb, $Text_orgi)
{
    $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=1";
    if ($result = $conn->query($q_exists)) {

        if ($result->num_rows != 0) {
            $row = $result->fetch_assoc();
            $ccc = $row['req_status'];

            if ($ccc == 4) {
                if (strlen($Text_orgi) >= 200) {
                    $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                    $bot->sendText($contenttmp);
                } else {
                    $q_up = "UPDATE Requests SET title='$Text_orgi', req_status=5 WHERE created_by=$bb AND req_status=4";
                    $conn->query($q_up);
                    $contenttmp = array('chat_id' => $chat_id, "text" => "توضیحات انجام درخواست را وارد کنید_حداکثر 300 کاراکتر:");
                    $bot->sendText($contenttmp);
                }

            } elseif ($ccc == 5) {
                if (strlen($Text_orgi) >= 300) {
                    $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                    $bot->sendText($contenttmp);
                } else {
                    $q_up = "UPDATE Requests SET description='$Text_orgi' WHERE created_by=$bb AND req_status=5";
                    $conn->query($q_up);
                    $all = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=1";
                    if ($result = $conn->query($all)) {
                        $row = $result->fetch_assoc();
                        $unit = $row['unit'];
                        if ($unit == 2) {
                            $u = "انفورماتیک";
                        } elseif ($unit == 3) {
                            $u = "مالی";
                        } elseif ($unit == 4) {
                            $u = "پشتیبانی";
                        } elseif ($unit == 5) {
                            $u = "خدمات";
                        } else {
                            $u = "نامشخص";
                        }
                        $t = $row['title'];
                        $d = $row['description'];

                        $content = array("chat_id" => $chat_id, "text" => "واحد مورد درخواست : $u\nعنوان : $t\nتوضیحات : $d");
                        $bot->sendText($content);

                        $q_up = "UPDATE Requests SET req_status=6 WHERE created_by=$bb AND is_closed=1";
                        $conn->query($q_up);

                        $inlineKeyboardoption = [
                            $bot->buildInlineKeyBoardButton("تأیید", '', "conf_pm"),
                            $bot->buildInlineKeyBoardButton("انصراف", '', "cancle_pm"),
                        ];
                        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "ثبت درخواست", 'reply_markup' => $Keyboard);
                        $bot->sendText($contenttmp);
                    }
                }
            }
        }
    }
}

function my_req($conn, $bot, $chat_id, $bb)
{
    $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND (is_closed=2 OR is_closed=3 OR is_closed=4) ORDER BY register_date DESC, register_time DESC LIMIT 500";
    if ($result = $conn->query($q_exists)) {
        if ($result->num_rows == 0) {
            $contenttmp = array('chat_id' => $chat_id, "text" => "موردی وجود ندارد.");
            $bot->sendText($contenttmp);
        } else {
            while ($row = $result->fetch_assoc()) {
                if ($row['is_closed'] == 4) {
                    $accept_date = $row['accept_date'];

                    // تفکیک سال، ماه و روز از تاریخ جلالی
                    list($year, $month, $day) = explode('-', $accept_date);

                    $year = intval($year);
                    $month = intval($month);
                    $day = intval($day);
                    $accept_date = jalali_to_gregorian($year, $month, $day, "-");

                    $accept_time = $row['accept_time'];
                    $timestampFromDB = strtotime($accept_date . " " . $accept_time);
                    // محاسبه فاصله زمانی بین زمان کنونی و زمان دریافتی از دیتابیس (به صورت ثانیه)
                    $timeDiffInSeconds = time() - $timestampFromDB;

                    // محاسبه فاصله زمانی به صورت ساعت
                    $timeDiffInHours = $timeDiffInSeconds / 3600;
                    if ($timeDiffInHours <= 48) {
                        $title = $row['title'];
                        $id = $row['id'];
                        $description = $row['description'];

                        $date = $row['register_date'];
                        $time = $row['register_time'];

                        $done_date = $row['done_date'];
                        $done_time = $row['done_time'];

                        $predict_date = $row['predict_date'];

                        $unit = $row['unit'];
                        if ($unit == 2) {
                            $u = "انفورماتیک";
                        } elseif ($unit == 3) {
                            $u = "مالی";
                        } elseif ($unit == 4) {
                            $u = "پشتیبانی";
                        } elseif ($unit == 5) {
                            $u = "خدمات";
                        } else {
                            $u = "نامشخص";
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

                        $state = $row['req_status'];
                        if ($state == 9) {
                            $status = "انجام شده";
                        }
                        if ($state == 9) {
                            $cbdatasubmitrate = "j$id";

                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("ثبت میزان رضایت", '', "$cbdatasubmitrate"),
                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nواحد مربوط : $u\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nزمان پیشبینی انجام درخواست : $predict_date\nتاریخ تأیید درخواست : $accept_date\nساعت تأیید درخواست : $accept_time\nتاریخ تغییر وضعیت درخواست به انجام شده : $done_date\nساعت تغییر وضعیت درخواست به انجام شده : $done_time\nمیزان رضایت : $r", 'reply_markup' => $Keyboard);
                            $bot->sendText($content);
                        }
                    } else {
                        continue;
                    }
                } elseif ($row['is_closed'] == 2 or $row['is_closed'] == 3) {
                    $title = $row['title'];
                    $description = $row['description'];
                    $reason = $row['reason'];

                    $date = $row['register_date'];
                    $time = $row['register_time'];

                    $date_reject = $row['reject_date'];
                    $time_reject = $row['reject_time'];

                    $predict_date = $row['predict_date'];

                    $accept_date = $row['accept_date'];
                    $accept_time = $row['accept_time'];

                    $unit = $row['unit'];
                    if ($unit == 2) {
                        $u = "انفورماتیک";
                    } elseif ($unit == 3) {
                        $u = "مالی";
                    } elseif ($unit == 4) {
                        $u = "پشتیبانی";
                    } elseif ($unit == 5) {
                        $u = "خدمات";
                    } else {
                        $u = "نامشخص";
                    }

                    $state = $row['req_status'];
                    if ($state == 1) {
                        $status = "در انتظار بررسی";
                    } elseif ($state == 2) {
                        $status = "رد شده توسط واحد مربوط";
                    } elseif ($state == 3) {
                        $status = "تأیید شده";
                    } else {
                        $status = "نامشخص";
                    }

                    if ($state == 2) {
                        $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nواحد مربوط : $u\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nدلیل رد شدن درخواست : $reason\nتاریخ رد شدن درخواست : $date_reject\nساعت رد شدن درخواست : $time_reject");
                        $bot->sendText($content);
                    } elseif ($state == 1) {
                        $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nواحد مربوط : $u\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time");
                        $bot->sendText($content);
                    } elseif ($state == 3) {
                        $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nواحد مربوط : $u\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nزمان پیشبینی انجام درخواست : $predict_date\nتاریخ تأیید درخواست : $accept_date\n ساعت تأیید درخواست : $accept_time");
                        $bot->sendText($content);
                    }
                }
                sleep(2);
            }
        }
    }
}

function manage_get_num($conn, $bot, $bb, $Text_orgi, $chat_id, $position)
{
    $sql = "SELECT * FROM Requests WHERE req_status=8 AND created_by=$bb";
    if ($result = $conn->query($sql)) {
        if ($result->num_rows != 0) {
            $thenum = $Text_orgi;

            if (is_numeric($thenum)) {
                settype($thenum, "integer");
                if ($position == 1) {
                    $q_exists = "SELECT * FROM Requests WHERE is_closed=2 OR is_closed=3 OR is_closed=4 ORDER BY register_date DESC, register_time DESC LIMIT $thenum";
                } else {
                    $q_exists = "SELECT * FROM Requests WHERE (is_closed=2 OR is_closed=3 OR is_closed=4) AND unit='$position' ORDER BY register_date DESC, register_time DESC LIMIT $thenum";
                }
                if ($result = $conn->query($q_exists)) {
                    if ($result->num_rows == 0) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "موردی وجود ندارد.");
                        $bot->sendText($contenttmp);
                    } else {
                        $max_rows = $result->num_rows;
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد انتخاب شده: $thenum\nحداکثر درخواست های موجود: $max_rows");
                        $bot->sendText($contenttmp);
                        while ($row = $result->fetch_assoc()) {

                            $title = $row['title'];
                            $description = $row['description'];
                            $reason = $row['reason'];

                            $date = $row['register_date'];
                            $time = $row['register_time'];

                            $date_reject = $row['reject_date'];
                            $time_reject = $row['reject_time'];

                            $date_accept = $row['accept_date'];
                            $time_accept = $row['accept_time'];

                            $done_date = $row['done_date'];
                            $done_time = $row['done_time'];

                            $predict_date = $row['predict_date'];

                            $name = $row['name'];

                            $unit = $row['unit'];
                            if ($unit == 2) {
                                $u = "انفورماتیک";
                            } elseif ($unit == 3) {
                                $u = "مالی";
                            } elseif ($unit == 4) {
                                $u = "پشتیبانی";
                            } elseif ($unit == 5) {
                                $u = "خدمات";
                            } else {
                                $u = "نامشخص";
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

                            $state = $row['req_status'];
                            if ($state == 1) {
                                $status = "در انتظار بررسی";
                            } elseif ($state == 2) {
                                $status = "رد شده توسط واحد مربوط";
                            } elseif ($state == 3) {
                                $status = "تأیید شده";
                            } elseif ($state == 9) {
                                $status = "انجام شده";
                            } else {
                                $status = "نامشخص";
                            }
                            if ($state == 2) {
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nنام درخواست دهنده : $name\nواحد مربوط : $u\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nدلیل رد شدن درخواست : $reason\nتاریخ رد شدن درخواست : $date_reject\nساعت رد شدن درخواست : $time_reject");
                                $bot->sendText($content);
                            } elseif ($state == 1) {
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nنام درخواست دهنده : $name\nواحد مربوط : $u\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time");
                                $bot->sendText($content);
                            } elseif ($state == 3) {
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nنام درخواست دهنده : $name\nواحد مربوط : $u\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nزمان پیشبینی انجام درخواست : $predict_date\nتاریخ تأیید درخواست : $date_accept\nساعت تأیید درخواست : $time_accept");
                                $bot->sendText($content);
                            } elseif ($state == 9) {
                                $content = array("chat_id" => $chat_id, "text" => "وضعیت : $status\nنام درخواست دهنده : $name\nواحد مربوط : $u\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\n ساعت ثبت درخواست : $time\nزمان پیشبینی انجام درخواست : $predict_date\nتاریخ تأیید درخواست : $date_accept\nساعت تأیید درخواست : $time_accept\nتاریخ تغییر وضعیت درخواست به انجام شده : $done_date\nساعت تغییر وضعیت درخواست به انجام شده : $done_time\nمیزان رضایت : $r");
                                $bot->sendText($content);
                            }
                            sleep(2);
                        }
                        $content = array("chat_id" => $chat_id, "text" => "پایان پردازش");
                        $bot->sendText($content);
                    }
                }
                delete_get_num($conn, $bb);

            } else {
                $contenttmp = array('chat_id' => $chat_id, "text" => "لطفا عدد وارد کنید:");
                $bot->sendText($contenttmp);
            }
        }
    }
}

function export_excel_all($conn, $bot, $chat_id)
{

    $sql = "SELECT * FROM Requests WHERE is_closed = 2 OR is_closed = 3 OR is_closed = 4 ORDER BY register_date DESC, register_time DESC LIMIT 1000";
    $result = $conn->query($sql);

    if ($result->num_rows != 0) {
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->setActiveSheetIndex(0);

// اضافه کردن عنوان‌ها به اکسل
        $sheet->setCellValue('A1', 'نام');
        $sheet->setCellValue('B1', 'عنوان');
        $sheet->setCellValue('C1', 'توضیحات');
        $sheet->setCellValue('D1', 'واحد مربوط');
        $sheet->setCellValue('E1', 'وضعیت');
        $sheet->setCellValue('F1', 'تاریخ ثبت درخواست');
        $sheet->setCellValue('G1', 'ساعت ثبت درخواست');
        $sheet->setCellValue('H1', 'تاریخ تأیید درخواست');
        $sheet->setCellValue('I1', 'ساعت تأیید درخواست');
        $sheet->setCellValue('J1', 'زمان پیشبینی انجام درخواست');
        $sheet->setCellValue('K1', 'تاریخ تغییر وضعیت به انجام شده');
        $sheet->setCellValue('L1', 'ساعت تغییر وضعیت به انجام شده');
        $sheet->setCellValue('M1', 'میزان رضایت');
        $sheet->setCellValue('N1', 'دلیل رد درخواست');
        $sheet->setCellValue('O1', 'تاریخ رد درخواست');
        $sheet->setCellValue('P1', 'ساعت رد درخواست');
        $rowNumber = 2; // از ردیف دوم شروع می‌کنیم (بعد از عنوان‌ها)
        while ($row = $result->fetch_assoc()) {

            if ($row['req_status'] == 9) {
                $req_status = "انجام شده";
            } elseif ($row['req_status'] == 3) {
                $req_status = "تأیید شده";
            } elseif ($row['req_status'] == 2) {
                $req_status = "رد شده";
            } elseif ($row['req_status'] == 1) {
                $req_status = "در انتظار بررسی";
            } else {
                $req_status = "---";
            }

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
                $uni = "---";
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

            $name = $row['name'];
            $title = $row['title'];
            $description = $row['description'];

            $sheet->setCellValue('A' . $rowNumber, $name);
            $sheet->setCellValue('B' . $rowNumber, $title);
            $sheet->setCellValue('C' . $rowNumber, $description ?? "---");
            $sheet->setCellValue('D' . $rowNumber, $uni);
            $sheet->setCellValue('E' . $rowNumber, $req_status);
            $sheet->setCellValue('F' . $rowNumber, $row['register_date']);
            $sheet->setCellValue('G' . $rowNumber, $row['register_time']);
            $sheet->setCellValue('H' . $rowNumber, $row['accept_date'] ?? "---");
            $sheet->setCellValue('I' . $rowNumber, $row['accept_time'] ?? "---");
            $sheet->setCellValue('J' . $rowNumber, $row['predict_date'] ?? "---");
            $sheet->setCellValue('K' . $rowNumber, $row['done_date'] ?? "---");
            $sheet->setCellValue('L' . $rowNumber, $row['done_time'] ?? "---");
            $sheet->setCellValue('M' . $rowNumber, $r);
            $sheet->setCellValue('N' . $rowNumber, $row['reason'] ?? "---");
            $sheet->setCellValue('O' . $rowNumber, $row['reject_date'] ?? "---");
            $sheet->setCellValue('P' . $rowNumber, $row['reject_time'] ?? "---");
            $rowNumber++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        $objPHPExcel->setActiveSheetIndex(0);
        $temp_name = date('Y-m-d--H-i-s');
// ذخیره کردن فایل اکسل
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $temp_path = 'public_html/reports_all_' . $temp_name . '.xlsx';
        $objWriter->save($temp_path);
        $contentdoc = array('chat_id' => $chat_id, "document" => 'http://balebot.balebt.ir/' . $temp_path);
        $bot->sendDocument($contentdoc);
        unlink($temp_path);
    } else {
        $content = array("chat_id" => $chat_id, "text" => "موردی برای گزارش یافت نشد.");
        $bot->sendText($content);
    }
}

function export_excel_reject($conn, $bot, $chat_id)
{

    $sql = "SELECT * FROM Requests WHERE is_closed = 2 AND req_status = 2 ORDER BY reject_date DESC, reject_time DESC LIMIT 1000";
    $result = $conn->query($sql);

    if ($result->num_rows != 0) {
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->setActiveSheetIndex(0);

// اضافه کردن عنوان‌ها به اکسل
        $sheet->setCellValue('A1', 'نام');
        $sheet->setCellValue('B1', 'عنوان');
        $sheet->setCellValue('C1', 'توضیحات');
        $sheet->setCellValue('D1', 'واحد مربوط');
        $sheet->setCellValue('E1', 'وضعیت');
        $sheet->setCellValue('F1', 'تاریخ ثبت درخواست');
        $sheet->setCellValue('G1', 'ساعت ثبت درخواست');
        $sheet->setCellValue('H1', 'دلیل رد درخواست');
        $sheet->setCellValue('I1', 'تاریخ رد درخواست');
        $sheet->setCellValue('J1', 'ساعت رد درخواست');
        $rowNumber = 2; // از ردیف دوم شروع می‌کنیم (بعد از عنوان‌ها)
        while ($row = $result->fetch_assoc()) {

            $req_status = "رد شده";

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
                $uni = "---";
            }

            $name = $row['name'];
            $title = $row['title'];
            $description = $row['description'];

            $sheet->setCellValue('A' . $rowNumber, $name);
            $sheet->setCellValue('B' . $rowNumber, $title);
            $sheet->setCellValue('C' . $rowNumber, $description ?? "---");
            $sheet->setCellValue('D' . $rowNumber, $uni);
            $sheet->setCellValue('E' . $rowNumber, $req_status);
            $sheet->setCellValue('F' . $rowNumber, $row['register_date']);
            $sheet->setCellValue('G' . $rowNumber, $row['register_time']);
            $sheet->setCellValue('H' . $rowNumber, $row['reason'] ?? "---");
            $sheet->setCellValue('I' . $rowNumber, $row['reject_date'] ?? "---");
            $sheet->setCellValue('J' . $rowNumber, $row['reject_time'] ?? "---");
            $rowNumber++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        $objPHPExcel->setActiveSheetIndex(0);
        $temp_name = date('Y-m-d--H-i-s');
// ذخیره کردن فایل اکسل
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $temp_path = 'public_html/reports_rejected_' . $temp_name . '.xlsx';
        $objWriter->save($temp_path);
        $contentdoc = array('chat_id' => $chat_id, "document" => 'http://balebot.balebt.ir/' . $temp_path);
        $bot->sendDocument($contentdoc);
        unlink($temp_path);
    } else {
        $content = array("chat_id" => $chat_id, "text" => "موردی برای گزارش یافت نشد.");
        $bot->sendText($content);
    }
}

function export_excel_accept($conn, $bot, $chat_id)
{

    $sql = "SELECT * FROM Requests WHERE is_closed = 3 ORDER BY accept_date DESC, accept_time DESC LIMIT 1000";
    $result = $conn->query($sql);

    if ($result->num_rows != 0) {

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->setActiveSheetIndex(0);

// اضافه کردن عنوان‌ها به اکسل
        $sheet->setCellValue('A1', 'نام');
        $sheet->setCellValue('B1', 'عنوان');
        $sheet->setCellValue('C1', 'توضیحات');
        $sheet->setCellValue('D1', 'واحد مربوط');
        $sheet->setCellValue('E1', 'وضعیت');
        $sheet->setCellValue('F1', 'تاریخ ثبت درخواست');
        $sheet->setCellValue('G1', 'ساعت ثبت درخواست');
        $sheet->setCellValue('H1', 'تاریخ تأیید درخواست');
        $sheet->setCellValue('I1', 'ساعت تأیید درخواست');
        $sheet->setCellValue('J1', 'زمان پیشبینی انجام درخواست');

        $rowNumber = 2; // از ردیف دوم شروع می‌کنیم (بعد از عنوان‌ها)
        while ($row = $result->fetch_assoc()) {

            $req_status = "تأیید شده";

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
                $uni = "---";
            }

            $name = $row['name'];
            $title = $row['title'];
            $description = $row['description'];

            $sheet->setCellValue('A' . $rowNumber, $name);
            $sheet->setCellValue('B' . $rowNumber, $title);
            $sheet->setCellValue('C' . $rowNumber, $description ?? "---");
            $sheet->setCellValue('D' . $rowNumber, $uni);
            $sheet->setCellValue('E' . $rowNumber, $req_status);
            $sheet->setCellValue('F' . $rowNumber, $row['register_date']);
            $sheet->setCellValue('G' . $rowNumber, $row['register_time']);
            $sheet->setCellValue('H' . $rowNumber, $row['accept_date'] ?? "---");
            $sheet->setCellValue('I' . $rowNumber, $row['accept_time'] ?? "---");
            $sheet->setCellValue('J' . $rowNumber, $row['predict_date'] ?? "---");

            $rowNumber++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        $objPHPExcel->setActiveSheetIndex(0);
        $temp_name = date('Y-m-d--H-i-s');
// ذخیره کردن فایل اکسل
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $temp_path = 'public_html/reports_accepted_' . $temp_name . '.xlsx';
        $objWriter->save($temp_path);
        $contentdoc = array('chat_id' => $chat_id, "document" => 'http://balebot.balebt.ir/' . $temp_path);
        $bot->sendDocument($contentdoc);
        unlink($temp_path);

    } else {
        $content = array("chat_id" => $chat_id, "text" => "موردی برای گزارش یافت نشد.");
        $bot->sendText($content);
    }
}

function export_excel_open($conn, $bot, $chat_id)
{

    $sql = "SELECT * FROM Requests WHERE is_closed = 2 AND req_status = 1 ORDER BY register_date DESC, register_time DESC LIMIT 1000";
    $result = $conn->query($sql);

    if ($result->num_rows != 0) {

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->setActiveSheetIndex(0);

// اضافه کردن عنوان‌ها به اکسل
        $sheet->setCellValue('A1', 'نام');
        $sheet->setCellValue('B1', 'عنوان');
        $sheet->setCellValue('C1', 'توضیحات');
        $sheet->setCellValue('D1', 'واحد مربوط');
        $sheet->setCellValue('E1', 'وضعیت');
        $sheet->setCellValue('F1', 'تاریخ ثبت درخواست');
        $sheet->setCellValue('G1', 'ساعت ثبت درخواست');

        $rowNumber = 2; // از ردیف دوم شروع می‌کنیم (بعد از عنوان‌ها)
        while ($row = $result->fetch_assoc()) {

            $req_status = "در انتظار بررسی";

            $name = $row['name'];
            $title = $row['title'];
            $description = $row['description'];

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
                $uni = "---";
            }

            $sheet->setCellValue('A' . $rowNumber, $name);
            $sheet->setCellValue('B' . $rowNumber, $title);
            $sheet->setCellValue('C' . $rowNumber, $description);
            $sheet->setCellValue('D' . $rowNumber, $uni);
            $sheet->setCellValue('E' . $rowNumber, $req_status);
            $sheet->setCellValue('F' . $rowNumber, $row['register_date']);
            $sheet->setCellValue('G' . $rowNumber, $row['register_time']);

            $rowNumber++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        $objPHPExcel->setActiveSheetIndex(0);
        $temp_name = date('Y-m-d--H-i-s');
// ذخیره کردن فایل اکسل
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $temp_path = 'public_html/reports_registered_' . $temp_name . '.xlsx';
        $objWriter->save($temp_path);
        $contentdoc = array('chat_id' => $chat_id, "document" => 'http://balebot.balebt.ir/' . $temp_path);
        $bot->sendDocument($contentdoc);
        unlink($temp_path);

    } else {
        $content = array("chat_id" => $chat_id, "text" => "موردی برای گزارش یافت نشد.");
        $bot->sendText($content);
    }

}

function export_excel_done($conn, $bot, $chat_id)
{

    $sql = "SELECT * FROM Requests WHERE is_closed = 4 ORDER BY done_date DESC, done_time DESC LIMIT 1000";
    $result = $conn->query($sql);

    if ($result->num_rows != 0) {

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->setActiveSheetIndex(0);

// اضافه کردن عنوان‌ها به اکسل
        $sheet->setCellValue('A1', 'نام');
        $sheet->setCellValue('B1', 'عنوان');
        $sheet->setCellValue('C1', 'توضیحات');
        $sheet->setCellValue('D1', 'واحد مربوط');
        $sheet->setCellValue('E1', 'وضعیت');
        $sheet->setCellValue('F1', 'تاریخ ثبت درخواست');
        $sheet->setCellValue('G1', 'ساعت ثبت درخواست');
        $sheet->setCellValue('H1', 'تاریخ تأیید درخواست');
        $sheet->setCellValue('I1', 'ساعت تأیید درخواست');
        $sheet->setCellValue('J1', 'زمان پیشبینی انجام درخواست');
        $sheet->setCellValue('K1', 'تاریخ تغییر وضعیت به انجام شده');
        $sheet->setCellValue('L1', 'ساعت تغییر وضعیت به انجام شده');
        $sheet->setCellValue('M1', 'میزان رضایت');

        $rowNumber = 2; // از ردیف دوم شروع می‌کنیم (بعد از عنوان‌ها)
        while ($row = $result->fetch_assoc()) {

            $req_status = "انجام شده";

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
                $uni = "---";
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

            $name = $row['name'];
            $title = $row['title'];
            $description = $row['description'];

            $sheet->setCellValue('A' . $rowNumber, $name);
            $sheet->setCellValue('B' . $rowNumber, $title);
            $sheet->setCellValue('C' . $rowNumber, $description ?? "---");
            $sheet->setCellValue('D' . $rowNumber, $uni);
            $sheet->setCellValue('E' . $rowNumber, $req_status);
            $sheet->setCellValue('F' . $rowNumber, $row['register_date']);
            $sheet->setCellValue('G' . $rowNumber, $row['register_time']);
            $sheet->setCellValue('H' . $rowNumber, $row['accept_date'] ?? "---");
            $sheet->setCellValue('I' . $rowNumber, $row['accept_time'] ?? "---");
            $sheet->setCellValue('J' . $rowNumber, $row['predict_date'] ?? "---");
            $sheet->setCellValue('K' . $rowNumber, $row['done_date'] ?? "---");
            $sheet->setCellValue('L' . $rowNumber, $row['done_time'] ?? "---");
            $sheet->setCellValue('M' . $rowNumber, $r);

            $rowNumber++;
        }

        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        $objPHPExcel->setActiveSheetIndex(0);
        $temp_name = date('Y-m-d--H-i-s');
// ذخیره کردن فایل اکسل
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $temp_path = 'public_html/reports_done_' . $temp_name . '.xlsx';
        $objWriter->save($temp_path);
        $contentdoc = array('chat_id' => $chat_id, "document" => 'http://balebot.balebt.ir/' . $temp_path);
        $bot->sendDocument($contentdoc);
        unlink($temp_path);

    } else {
        $content = array("chat_id" => $chat_id, "text" => "موردی برای گزارش یافت نشد.");
        $bot->sendText($content);
    }

}

function reset_admin($conn, $bb){
    delete_undone_request($conn, $bb);
    delete_half_made_user($conn);
    stop_changing($conn);
//    stop_reason_message($conn, $bb);
    delete_get_num($conn, $bb);
}
function reset_unit($conn, $bb){
    delete_undone_request($conn, $bb);
    stop_reason_message($conn, $bb);
    delete_get_num($conn, $bb);
}
// ---------------------------------------------------------------------------------------------------------------------------

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


//----------------------------------------------- ابتدای حروف کالبک های اشغال شده ------------------------------------------------------

// a, c, p, x, d, j, m, c, n, o, e, r

//f g h i k     برای rate

// موجود b, l, q, s, t, u, v, w, y , z
//----------------------------------------------- حالت های req_status ------------------------------------------------------

//   1  در انتظار تأیید
//   2   رد شده
//   3   تأیید شده
//   4   دریافت عنوان درخواست
//   5   دریافت توضیحات درخواست
//   6   در انتظار ثبت یا لغو درخواست
//   7   دریافت واحد
//   8   دریافت شماره برای تعداد درخواست هایی که میخواهیم نمایش داده شود
//   9   انجام شده

//---------------------------------------------------- حالت های status -----------------------------------------------------------

//change دریافت یوزر نیم برای تغییر سمت
//changing  هنگام تغییر سمت برای دریافت سمت مورد نظر یوزری که وارد شده، این وضعیت برای شخص مورد نظر تعیین می شود
//changeus وضعیت برای دریافت یوزر نیم برای تغییر یوزر نیم
//changeuss وضعیت برای دریافت یوزرنیم جدید برای تغییر یوزر نیم
//getname دریافت نام برای ساخت حساب کاربری
//getuser دریافت یوزرنیم برای ساخت حساب کاربری
//getpos وضعیت درخواست پرداخت شده
//choosing تأیید ساخت یک حساب کاربری جدید برای بات

//---------------------------------------------------- حالت های is_closed -----------------------------------------------------------
// 1 درخواست در حال ساخته شدن است.
// 2   درخواست ثبت شده و منتظر تأیید است.
// 3 درخواست تأیید شده است.
// 4 درخواست پرداخت شده است.

//---------------------------------------------------- حالت های rate -----------------------------------------------------------
// null وارد نشده.
// 1 عدم رضابت
// 2   رضایت کم
// 3 رضایت نسبی
// 4 رضایت متوسط
// 5 رضایت کامل
//---------------------------------------------------- حالت های دسترسی -----------------------------------------------------------
// 1 ادمین
// 2 انفورماتیک
// 3 مالی
// 4 پشتیبانی
// 5 خدمات(تشریفات)
// 6 مدیر عامل
// 7 مدید واحد (مدیر پروژه سابق)

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

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------UPDATE UNIQUE ID INSTEAD OF USERNAME----------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------


// دریافت همه یوزر نیم های ثبت شده در دیتابیس
$q = "SELECT username FROM Persons";
if ($result = $conn->query($q)) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['username'];
    }
}
// بررسی این که آیا شخصی که وارد بات شده، قبلا یوزرنیم آن ثبت شده یا نه
if (in_array($username, $data)) {
    $sql = "SELECT * FROM Persons WHERE username='$username'";

    if ($result = $conn->query($sql)) {
        {
            $row = $result->fetch_assoc();
            $id = $row['unique_id'];

            if (!isset($id)) {

                // اگر قبلا وارد بات شده باشد آیدی یونیک دریافت شده و گرنه با دستور زیر، آیدی یونیک او را ذخیره می کنیم.
                $query = "UPDATE Persons SET unique_id='$user_id' WHERE username='$username'";
                $result = $conn->query($query);
            }
        }
    }
} else {
    //اگر آیدی یونیک ثبت نشده باشد ، پیغام زیر داده میشود
    $q = "SELECT unique_id FROM Persons";
    if ($result = $conn->query($q)) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row['unique_id'];
        }
    }
    if (!in_array($user_id, $data)) {
        $contenttmp = array('chat_id' => $chat_id, "text" => "آیدی شما در ربات تعریف نشده است!");
        $bot->sendText($contenttmp);
    }
}

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------- get current user id in database -------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

// نکته : bb یعنی یوزرنیم آیدی کاربری که دارد استفاده میکند در دیتابیس

$bb = null;
$q_id = "SELECT id FROM Persons WHERE unique_id=$user_id";
if ($result = $conn->query($q_id)) {
    $row = $result->fetch_assoc();
    $bb = $row['id'];
    settype($bb, "integer");
}

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------- get current user position in database -------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

$position = null;
$q_id = "SELECT position FROM Persons WHERE unique_id=$user_id";
if ($result = $conn->query($q_id)) {
    $row = $result->fetch_assoc();
    $position = $row['position'];
    settype($position, "integer");
}


// -------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------- ADMIN --------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// دریافت ادمین
$sql = "SELECT unique_id FROM Persons WHERE position=1";
if ($result = $conn->query($sql)) {
    $row = $result->fetch_assoc();
    $admin = $row['unique_id'];
}
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------- CEOs --------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// دریافت مدیر عامل ها
$sql = "SELECT unique_id FROM Persons WHERE position=6";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $ceoceo[] = $row['unique_id'];
    }
}
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------- INFORMATICs ---------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

//دریافت حسابدار ها

$sql = "SELECT unique_id FROM Persons WHERE position=2";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $informatics_ids[] = $row['unique_id'];
    }
}

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------- FINANCIALs ---------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

//دریافت حسابدار ها

$sql = "SELECT unique_id FROM Persons WHERE position=3";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $financials_ids[] = $row['unique_id'];
    }
}
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------- SUPPORTs ---------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

//دریافت حسابدار ها

$sql = "SELECT unique_id FROM Persons WHERE position=4";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $supports_ids[] = $row['unique_id'];
    }
}
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------- SERVICESs ---------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

//دریافت حسابدار ها

$sql = "SELECT unique_id FROM Persons WHERE position=5";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $services_ids[] = $row['unique_id'];
    }
}
//-------------------------------------------------------------------------------------------------------------------------------------------------------

$all_units = (in_array($chat_id, $financials_ids) or in_array($chat_id, $services_ids) or in_array($chat_id, $supports_ids) or in_array($chat_id, $informatics_ids));

//-------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------- UNIT MANAGER (PROJECT MANAGERSs) -------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------------------------------------------

//مدیر پروژه ها را دریافت می کند
$sql = "SELECT unique_id FROM Persons WHERE position=7";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $project_managers[] = $row['unique_id'];
    }
}


// -------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------- CODE FOR CEO --------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

if (in_array($chat_id, $ceoceo)) {
    if ($Text_orgi == "/start") {
        delete_undone_request($conn, $bb);
        $inlineKeyboardoption = [
            $bot->buildInlineKeyBoardButton("ثبت درخواست", '', "newreqceo"),
        ];
        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
        $contenttmp = array('chat_id' => $chat_id, "text" => "انتخاب کنید:", 'reply_markup' => $Keyboard);
        $bot->sendText($contenttmp);
    } else {
        req_status_process($conn, $bot, $chat_id, $bb, $Text_orgi);
    }
}

// -------------------------------------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------ Callback for reject or accept request ------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------
// بررسی پیغام برای رد درخواست
if ($all_units) {
    if ($Text_orgi != "/start") {
        $query_for_reject_message = "SELECT * FROM Requests WHERE rejector='$bb' AND reason='setreasonforreject'";
        $query_for_set_prediction_date = "SELECT * FROM Requests WHERE rejector='$bb' AND predict_date='setpredictdate'";
        if ($result = $conn->query($query_for_reject_message)) {
            if ($result->num_rows != 0) {
                if (strlen($Text_orgi) >= 300) {
                    $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                    $bot->sendText($contenttmp);
                } else {
                    $row = $result->fetch_assoc();
                    $ccc = $row['id'];
                    $title = $row['title'];
                    $q_up = "UPDATE Requests SET req_status=2, is_closed=2, reason='$Text_orgi', reject_date='$today_date', reject_time='$time_now' WHERE id='$ccc'";
                    $result = $conn->query($q_up);
                    $content = array("chat_id" => $chat_id, "text" => "درخواست: ** $title ** با موفقیت رد شد.");
                    $bot->sendText($content);
                }
            }
        }
        if ($result = $conn->query($query_for_set_prediction_date)) {
            if ($result->num_rows != 0) {
                if (strlen($Text_orgi) > 15) {
                    $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                    $bot->sendText($contenttmp);
                } else {
                    $row = $result->fetch_assoc();
                    $ccc = $row['id'];
                    $created_by = $row['created_by'];
                    $title = $row['title'];
                    $q_up = "UPDATE Requests SET req_status=3, is_closed=3, predict_date='$Text_orgi', accept_date='$today_date', accept_time='$time_now' WHERE id='$ccc'";
                    $result = $conn->query($q_up);

                    $content = array("chat_id" => $chat_id, "text" => "درخواست : $title تأیید شد.");
                    $bot->sendText($content);
                }
            }
        }
    }
}
//اگر کال بک، حرف اول آن، x باشد، یعنی درخواست مورد نظر ، پرداخت شده است، آیدی درخواست مورد نظر در ادامه x وجود دارد
if ($callback_data[0] == "x") {
    $getcbdata = str_replace("x", "", $callback_data);
    settype($getcbdata, "integer");

    $q_up = "UPDATE Requests SET predict_date='setpredictdate', rejector='$bb' WHERE id=$getcbdata";
    if ($result = $conn->query($q_up)) {
        $content = array("chat_id" => $chat_id, "text" => "زمان پیشبینی انجام درخواست (به فرمت تاریخ) را وارد کنید.\n برای مثال : 1402-05-24");
        $bot->sendText($content);
    }

    //اگر کال بک، حرف اول آن، r باشد، یعنی درخواست مورد نظر ، عدم تأیید است، آیدی درخواست مورد نظر در ادامه r وجود دارد
} elseif ($callback_data[0] == 'r') {
    $getcbdata = str_replace('r', '', $callback_data);
    settype($getcbdata, "integer");
    $q_up = "UPDATE Requests SET reason='setreasonforreject', rejector='$bb' WHERE id=$getcbdata";
    if ($result = $conn->query($q_up)) {
        $content = array("chat_id" => $chat_id, "text" => "متن توضیح برای عدم تأیید درخواست را وارد کنید(حداکثر 300 کاراکتر):");
        $bot->sendText($content);
    }
} elseif ($callback_data[0] == 'd') {
    $getcbdata = str_replace('d', '', $callback_data);
    settype($getcbdata, "integer");
    $q_up = "UPDATE Requests SET req_status=9, is_closed=4, done_date='$today_date', done_time='$time_now' WHERE id='$getcbdata'";
    if ($result = $conn->query($q_up)) {

        $q_up = "SELECT * FROM Requests WHERE id='$getcbdata'";
        $result = $conn->query($q_up);
        $row = $result->fetch_assoc();
        $created_by = $row['created_by'];
        $title = $row['title'];

        $content = array("chat_id" => $chat_id, "text" => "وضعیت درخواست با عنوان : ** $title ** به انجام شده تغییر داده شد.");
        $bot->sendText($content);


        $sql = "SELECT unique_id FROM Persons WHERE id=$created_by";
        if ($result = $conn->query($sql)) {
            if ($result->num_rows != 0) {
                $ro = $result->fetch_assoc();
                $requestor = $ro['unique_id'];
            }
        }

        $date = $row['register_date'];
        $time = $row['register_time'];
        $description = $row['description'];
        $name = $row['name'];
        $id = $row['id'];
        $unit = $row['unit'];
        if ($unit == 2) {
            $u = "انفورماتیک";
        } elseif ($unit == 3) {
            $u = "مالی";
        } elseif ($unit == 4) {
            $u = "پشتیبانی";
        } elseif ($unit == 5) {
            $u = "خدمات";
        } else {
            $u = "نامشخص";
        }
        $predict_date = $row['predict_date'];
        $accept_date = $row['accept_date'];
        $accept_time = $row['accept_time'];

        $done_date = $row['done_date'];
        $done_time = $row['done_time'];

        $cbdatasubmitrate = "j$id";
        $inlineKeyboardoption = [
            $bot->buildInlineKeyBoardButton("ثبت میزان رضایت", '', "$cbdatasubmitrate"),
        ];
        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
        $content = array("chat_id" => $requestor, "text" => "درخواست شما با مشخصات زیر توسط واحد $u انجام شد:\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nزمان پیشبینی انجام درخواست : $predict_date\nتاریخ تأیید درخواست : $accept_date\nساعت تأیید درخواست : $accept_time\nتاریخ تغییر وضعیت درخواست به انجام شده : $done_date\nساعت تغییر وضعیت درخواست به انجام شده : $done_time", 'reply_markup' => $Keyboard);
        $bot->sendText($content);
    }

} elseif ($callback_data[0] == 'j') {

    $getcbdata = str_replace('j', '', $callback_data);
    settype($getcbdata, "integer");
    $no = "f$getcbdata";
    $low = "g$getcbdata";
    $kinda = "h$getcbdata";
    $average = "i$getcbdata";
    $full = "k$getcbdata";

    $inlineKeyboardoption = [
        $bot->buildInlineKeyBoardButton("عدم رضایت", '', "$no"),
        $bot->buildInlineKeyBoardButton("رضایت کم", '', "$low"),
        $bot->buildInlineKeyBoardButton("رضایت نسبی", '', "$kinda"),
        $bot->buildInlineKeyBoardButton("رضایت متوسط", '', "$average"),
        $bot->buildInlineKeyBoardButton("رضایت کامل", '', "$full"),
    ];
    $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
    $content = array("chat_id" => $chat_id, "text" => "لطفا میزان رضایت خود را از بین گزینه های زیر انتخاب کنید.", 'reply_markup' => $Keyboard);
    $bot->sendText($content);

} elseif ($callback_data[0] == 'f') {
    $getcbdata = str_replace('f', '', $callback_data);
    settype($getcbdata, "integer");

    $q_up = "UPDATE Requests SET rate=1 WHERE id='$getcbdata'";
    if ($result = $conn->query($q_up)) {
        $content = array("chat_id" => $chat_id, "text" => "عدم رضایت برای این درخواست ثبت شد.");
        $bot->sendText($content);
    }
} elseif ($callback_data[0] == 'g') {
    $getcbdata = str_replace('g', '', $callback_data);
    settype($getcbdata, "integer");

    $q_up = "UPDATE Requests SET rate=2 WHERE id='$getcbdata'";
    if ($result = $conn->query($q_up)) {
        $content = array("chat_id" => $chat_id, "text" => "رضایت کم برای این درخواست ثبت شد.");
        $bot->sendText($content);
    }
} elseif ($callback_data[0] == 'h') {
    $getcbdata = str_replace('h', '', $callback_data);
    settype($getcbdata, "integer");

    $q_up = "UPDATE Requests SET rate=3 WHERE id='$getcbdata'";
    if ($result = $conn->query($q_up)) {
        $content = array("chat_id" => $chat_id, "text" => "رضایت نسبی برای این درخواست ثبت شد.");
        $bot->sendText($content);
    }
} elseif ($callback_data[0] == 'i') {
    $getcbdata = str_replace('i', '', $callback_data);
    settype($getcbdata, "integer");

    $q_up = "UPDATE Requests SET rate=4 WHERE id='$getcbdata'";
    if ($result = $conn->query($q_up)) {
        $content = array("chat_id" => $chat_id, "text" => "رضایت متوسط برای این درخواست ثبت شد.");
        $bot->sendText($content);
    }
} elseif ($callback_data[0] == 'k') {
    $getcbdata = str_replace('k', '', $callback_data);
    settype($getcbdata, "integer");

    $q_up = "UPDATE Requests SET rate=5 WHERE id='$getcbdata'";
    if ($result = $conn->query($q_up)) {
        $content = array("chat_id" => $chat_id, "text" => "رضایت کامل برای این درخواست ثبت شد.");
        $bot->sendText($content);
    }
}

// ---------------------------------------------------------********************************----------------------------------------------------------------------------
// ---------------------------------------------------------********************************----------------------------------------------------------------------------
// ---------------------------------------------------------********************************----------------------------------------------------------------------------


// -------------------------------------------------------------------------------------------------------------------------------------------------------
// -------------------------------------------------------------------- CODE FOR ADMIN --------------------------------------------------------------------------
// -------------------------------------------------------------------------------------------------------------------------------------------------------

if ($chat_id == $admin) {
    if ($Text_orgi == "/start") {
        reset_admin($conn, $bb);
        admin_clipboard($bot, $chat_id);

    } else {
        req_status_process($conn, $bot, $chat_id, $bb, $Text_orgi);
        manage_get_num($conn, $bot, $bb, $Text_orgi, $chat_id, $position);

        $q_id = "SELECT * FROM Persons WHERE status='getname' OR status='getuser' OR status='change' OR status='changeus' OR status='changeuss'";
        if ($result = $conn->query($q_id)) {
            if ($result->num_rows != 0) {
                $row = $result->fetch_assoc();
                if ($row['status'] == 'getname') {
                    if (strlen($Text_orgi) >= 30) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Persons SET name='$Text_orgi', status='getuser' WHERE status='getname'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "یوزرنیم شخص مورد نظر را بدون علامت @ وارد کنید_حداکثر 30 کاراکتر:");
                        $bot->sendText($contenttmp);
                    }
                } elseif ($row['status'] == 'getuser') {
                    if (strlen($Text_orgi) >= 30) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $sql = "SELECT * FROM Persons WHERE username='$Text_orgi'";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            delete_half_made_user($conn);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "امکان پذیر نیست! این یوزرنیم در حال حاضر در ربات موجود است.");
                            $bot->sendText($contenttmp);
                            admin_clipboard($bot, $chat_id);
                        } else {
                            $q_up = "UPDATE Persons SET username='$Text_orgi', status='getpos' WHERE status='getuser'";
                            $result = $conn->query($q_up);
                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("مدیر واحد", '', "ppm"),
                                $bot->buildInlineKeyBoardButton("انفورماتیک", '', "p_anformatic"),
                                $bot->buildInlineKeyBoardButton("مالی", '', "p_financial"),
                                $bot->buildInlineKeyBoardButton("پشتیبانی", '', "p_support"),
                                $bot->buildInlineKeyBoardButton("خدمات", '', "p_service"),
                                $bot->buildInlineKeyBoardButton("مدیر عامل", '', "pseo"),

                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "سمت شخص مورد نظر را از بین گزینه های زیر انتخاب کنید:", 'reply_markup' => $Keyboard);
                            $bot->sendText($contenttmp);
                        }
                    }

                } elseif ($row['status'] == 'change') {
                    $sql = "SELECT * FROM Persons WHERE username='$Text_orgi'";
                    if ($res = $conn->query($sql)) {
                        if ($res->num_rows == 0) {
                            $d_q = "DELETE FROM Persons WHERE status='change'";
                            $result = $conn->query($d_q);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "این یوزرنیم در این ربات تعریف نشده است!");
                            $bot->sendText($contenttmp);
                            admin_clipboard($bot, $chat_id);

                        } else {
                            $row = $res->fetch_assoc();
                            if ($row['position'] == 'admin') {
                                stop_changing($conn);
                                $contenttmp = array('chat_id' => $chat_id, "text" => "این کار امکان پذیر نیست. یوزرنیم وارد شده متعلق به ادمین است!");
                                $bot->sendText($contenttmp);
                                admin_clipboard($bot, $chat_id);
                            } else {
                                if ($row['position'] == 7) {
                                    $stat = "مدیر پروژه";
                                } elseif ($row['position'] == 2) {
                                    $stat = "انفورماتیک";
                                } elseif ($row['position'] == 3) {
                                    $stat = "مالی";
                                } elseif ($row['position'] == 4) {
                                    $stat = "پشتیبانی";
                                } elseif ($row['position'] == 5) {
                                    $stat = "خدمات";
                                } elseif ($row['position'] == 6) {
                                    $stat = "مدیر عامل";
                                } else {
                                    $stat = "نامشخص";
                                }
                                $q_up = "UPDATE Persons SET status='changing' WHERE username='$Text_orgi'";
                                $result = $conn->query($q_up);
                                $sql = "SELECT * FROM Persons WHERE status='change'";
                                if ($result = $conn->query($sql)) {
                                    if ($result->num_rows != 0) {
                                        $d_q = "DELETE FROM Persons WHERE status='change'";
                                        $result = $conn->query($d_q);
                                    }
                                }
                                $inlineKeyboardoption = [
                                    $bot->buildInlineKeyBoardButton("مدیر واحد", '', "changetopm"),
                                    $bot->buildInlineKeyBoardButton("انفورماتیک", '', "changetoinformatic"),
                                    $bot->buildInlineKeyBoardButton("مالی", '', "changetofinancial"),
                                    $bot->buildInlineKeyBoardButton("پشتیبانی", '', "changetosupport"),
                                    $bot->buildInlineKeyBoardButton("خدمات", '', "changetoservice"),
                                    $bot->buildInlineKeyBoardButton("مدیر عامل", '', "changetoceo"),
                                    $bot->buildInlineKeyBoardButton("حذف این شخص", '', "changeremove"),
                                ];
                                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                                $contenttmp = array('chat_id' => $chat_id, "text" => "سمت این شخص $stat است.سمت مدنظر خود را برای این شخص انتخاب کنید:", 'reply_markup' => $Keyboard);
                                $bot->sendText($contenttmp);
                            }
                        }
                    }
                } elseif ($row['status'] == 'changeus') {
                    $sql = "SELECT * FROM Persons WHERE username='$Text_orgi'";
                    if ($res = $conn->query($sql)) {
                        if ($res->num_rows == 0) {
                            $d_q = "DELETE FROM Persons WHERE status='changeus'";
                            $result = $conn->query($d_q);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "این یوزرنیم در این ربات تعریف نشده است!");
                            $bot->sendText($contenttmp);
                            admin_clipboard($bot, $chat_id);

                        } else {
                            $q_up = "UPDATE Persons SET status='changeuss' WHERE username='$Text_orgi'";
                            $result = $conn->query($q_up);
                            $sql = "SELECT * FROM Persons WHERE status='changeus'";
                            if ($result = $conn->query($sql)) {
                                if ($result->num_rows != 0) {
                                    $d_q = "DELETE FROM Persons WHERE status='changeus'";
                                    $result = $conn->query($d_q);
                                }
                            }
                            $contenttmp = array('chat_id' => $chat_id, "text" => "یوزرنیم جدید را بدون علامت @ وارد کنید_حداکثر 30 کاراکتر:");
                            $bot->sendText($contenttmp);
                        }
                    }
                } elseif ($row['status'] == 'changeuss') {
                    if (strlen($Text_orgi) >= 30) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد کاراکتر ها بیش از حد مجاز است، متن کوتاه تری را وارد کنید:");
                        $bot->sendText($contenttmp);
                    } else {
                        $q_up = "UPDATE Persons SET username='$Text_orgi', status=NULL WHERE status='changeuss'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "یوزرنیم ثبت شده شخص مورد نظر با موفقیت تغییر داده شد.");
                        $bot->sendText($contenttmp);
                        admin_clipboard($bot, $chat_id);
                    }
                }
            }
        }
    }
}


// برای واحد ها
// --------------------------------------------------------------------------------------------------------------------------------------------------------------
//اگر start فراخوانی شود، وضعیت درخواست ها و کاربر ها مثل قبل میشود و عملیات کنسل می شود
if ($all_units) {
    if ($Text_orgi == "/start") {
        reset_unit($conn, $bb);
        accounting_clipboard($bot, $chat_id);
    } else {
        req_status_process($conn, $bot, $chat_id, $bb, $Text_orgi);
        manage_get_num($conn, $bot, $bb, $Text_orgi, $chat_id, $position);
    }
}


// برای مدیر واحد
// ------------------------------------------------------------------------------------------------------------------------------------
if (in_array($chat_id, $project_managers)) {

    if ($Text_orgi == "/start") {
        delete_undone_request($conn, $bb);

        projectmanager_clipboard($bot, $chat_id);
    } else {
        req_status_process($conn, $bot, $chat_id, $bb, $Text_orgi);
    }
}

switch ($callback_data) {
//    درخواست پرداخت
    case "paymentreq":
        if (in_array($chat_id, $project_managers)) {
            delete_undone_request($conn, $bb);
            create_new_req($conn, $bot, $bb, $chat_id);
        }
        break;
//  درخواست های من
    case "myreq":
        if ($all_units or in_array($chat_id, $project_managers) or $chat_id == $admin) {
            if ($all_units){
                reset_unit($conn, $bb);
            }elseif(in_array($chat_id, $project_managers)){
                delete_undone_request($conn, $bb);
            }elseif ($chat_id == $admin){
                reset_admin($conn, $bb);
            }
            my_req($conn, $bot, $chat_id, $bb);
            if (in_array($chat_id, $project_managers)) {
                projectmanager_clipboard($bot, $chat_id);
            }
            if ($chat_id == $admin) {
                admin_clipboard($bot, $chat_id);
            }
            if ($all_units) {
                accounting_clipboard($bot, $chat_id);
            }
        }
        break;
//انتخاب واحد انفورماتیک
    case "choose_informatic":
        if (in_array($chat_id, $project_managers) || $all_units || in_array($chat_id, $ceoceo) || $chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            stop_reason_message($conn, $bb);
            $q_up = "UPDATE Requests SET unit=2, req_status=4 WHERE created_by=$bb AND req_status=7";
            $result = $conn->query($q_up);

            $content = array("chat_id" => $chat_id, "text" => "لطفا عنوان درخواست خود را با رعایت اصول حفاظتی وارد کنید_حداکثر 200 کاراکتر:");
            $bot->sendText($content);
        }
        break;

    //انتخاب واحد خدمات
    case "choose_services":
        if (in_array($chat_id, $project_managers) || $all_units || in_array($chat_id, $ceoceo) || $chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            stop_reason_message($conn, $bb);
            $q_up = "UPDATE Requests SET unit=5, req_status=4 WHERE created_by=$bb AND req_status=7";
            $result = $conn->query($q_up);

            $content = array("chat_id" => $chat_id, "text" => "لطفا عنوان درخواست خود را با رعایت اصول حفاظتی وارد کنید_حداکثر 200 کاراکتر:");
            $bot->sendText($content);
        }
        break;

    //انتخاب واحد پشتیبانی
    case "choose_support":
        if (in_array($chat_id, $project_managers) || $all_units || in_array($chat_id, $ceoceo) || $chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            stop_reason_message($conn, $bb);
            $q_up = "UPDATE Requests SET unit=4, req_status=4 WHERE created_by=$bb AND req_status=7";
            $result = $conn->query($q_up);

            $content = array("chat_id" => $chat_id, "text" => "لطفا عنوان درخواست خود را با رعایت اصول حفاظتی وارد کنید_حداکثر 200 کاراکتر:");
            $bot->sendText($content);
        }
        break;

    //انتخاب واحد مالی
    case "choose_financial":
        if (in_array($chat_id, $project_managers) || $all_units || in_array($chat_id, $ceoceo) || $chat_id == $admin) {
            delete_half_made_user($conn);
            stop_changing($conn);
            stop_reason_message($conn, $bb);
            $q_up = "UPDATE Requests SET unit=3, req_status=4 WHERE created_by=$bb AND req_status=7";
            $result = $conn->query($q_up);

            $content = array("chat_id" => $chat_id, "text" => "لطفا عنوان درخواست خود را با رعایت اصول حفاظتی وارد کنید_حداکثر 200 کاراکتر:");
            $bot->sendText($content);
        }
        break;

//تأیید درخواست پرداخت
    case "conf_pm":
        if (in_array($chat_id, $project_managers) || $all_units || in_array($chat_id, $ceoceo) || $chat_id == $admin) {
            $q_id = "SELECT * FROM Persons WHERE unique_id=$user_id";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $n = $row['name'];
            }else{
                $n = "نامشخص";
            }
            $q_exists = "SELECT * FROM Requests WHERE created_by=$bb AND is_closed=1";
            if ($result = $conn->query($q_exists)) {
                if ($result->num_rows != 0) {
                    $row = $result->fetch_assoc();
                    if ($row['req_status'] == 6) {
                        $unit = $row['unit'];
                        $req_id = $row['id'];
                        $q_up = "UPDATE Requests SET req_status=1, register_date='$today_date', is_closed=2,
                    register_time='$time_now', name='$n' WHERE created_by=$bb AND is_closed=1";
                        $result = $conn->query($q_up);

                        // دریافت کسانی که واحد مورد نظر هستند
                        $sql = "SELECT unique_id FROM Persons WHERE position=$unit";
                        if ($result = $conn->query($sql)) {
                            if ($result->num_rows != 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $data[] = $row['unique_id'];
                                }
                            } else {
                                $data = [];
                            }
                        }
                        $content = array("chat_id" => $chat_id, "text" => "درخواست شما با موفقیت ثبت شد.");
                        $bot->sendText($content);
                        // فرستادن درخواست به واحد مورد نظر
                        foreach ($data as $u) {
                            $q_exists = "SELECT * FROM Requests WHERE id='$req_id'";
                            if ($result = $conn->query($q_exists)) {
                                $row = $result->fetch_assoc();
                                $title = $row['title'];
                                $date = $row['register_date'];
                                $time = $row['register_time'];
                                $description = $row['description'];
                                $created_by = $row['created_by'];
                                $name = $row['name'];
                                $id = $row['id'];
                                $cbdataaccept = "x$id";
                                $rejectreq = "r$id";
                                $inlineKeyboardoption = [
                                    $bot->buildInlineKeyBoardButton("عدم تأیید", '', "$rejectreq"),
                                    $bot->buildInlineKeyBoardButton("تأیید", '', "$cbdataaccept"),
                                ];
                                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                                $content = array("chat_id" => $u, "text" => "درخواست جدید:\nنام : $name\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time", 'reply_markup' => $Keyboard);
                                $bot->sendText($content);
                            }
                            sleep(1);
                        }
                    } else {
                        $content = array("chat_id" => $chat_id, "text" => "درخواست ناقص است!");
                        $bot->sendText($content);
                    }
                } else {
                    $content = array("chat_id" => $chat_id, "text" => "موردی وجود ندارد");
                    $bot->sendText($content);
                }
            }
            if (in_array($chat_id, $project_managers)) {
                projectmanager_clipboard($bot, $chat_id);
            } elseif (in_array($chat_id, $ceoceo)) {
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("ثبت درخواست", '', "newreqceo"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            } elseif ($chat_id == $admin) {
                admin_clipboard($conn, $chat_id);
            } elseif ($all_units) {
                accounting_clipboard($bot, $chat_id);
            }
        }
        break;
//لغو درخواست پرداخت
    case "cancle_pm":
        if (in_array($chat_id, $project_managers) || $all_units || in_array($chat_id, $ceoceo) || $chat_id == $admin) {
            $q_exists = "SELECT id FROM Requests WHERE created_by=$bb AND is_closed=1";
            if ($result = $conn->query($q_exists)) {
                $row = $result->fetch_assoc();
                if (isset($row)) {
                    $ccc = $row['id'];
                    settype($ccc, "integer");
                    $d_q = "DELETE FROM Requests WHERE id=$ccc";
                    if ($conn->query($d_q) === TRUE) {
                        $contenttmp = array('chat_id' => $chat_id, "text" => "درخواست شما لغو شد.");
                        $bot->sendText($contenttmp);
                        if (in_array($chat_id, $project_managers)) {
                            projectmanager_clipboard($bot, $chat_id);
                        } elseif ($all_units) {
                            accounting_clipboard($bot, $chat_id);
                        } elseif ($chat_id == $admin) {
                            admin_clipboard($bot, $chat_id);
                        } elseif (in_array($chat_id, $ceoceo)) {
                            $inlineKeyboardoption = [
                                $bot->buildInlineKeyBoardButton("ثبت درخواست", '', "newreqceo"),
                            ];
                            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                            $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید", 'reply_markup' => $Keyboard);
                            $bot->sendText($contenttmp);
                        }
                    }
                }
            }
        }
        break;
//درخواست پرداخت برای حسابداری
    case "newreqacc":
        if ($all_units) {
            reset_unit($conn, $bb);
            create_new_req($conn, $bot, $bb, $chat_id);
        }
        break;

    //نمایش درخواست های بررسی نشده
    case "openreqacc":
        if ($all_units) {
            reset_unit($conn, $bb);

            $q_exists = "SELECT * FROM Requests WHERE req_status=1 AND is_closed=2 AND unit='$position' ORDER BY register_date DESC, register_time DESC LIMIT 30";

            if ($result = $conn->query($q_exists)) {
                if ($result->num_rows == 0) {
                    $contenttmp = array('chat_id' => $chat_id, "text" => "موردی وجود ندارد.");
                    $bot->sendText($contenttmp);
                } else {
                    $num = $result->num_rows;
                    $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
                    $bot->sendText($contenttmp);
                    $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد درخواست های در حال پردازش (حداکثر 30)  : $num");
                    $bot->sendText($contenttmp);
                    sleep(1);

                    while ($row = $result->fetch_assoc()) {
                        $title = $row['title'];
                        $date = $row['register_date'];
                        $time = $row['register_time'];
                        $description = $row['description'];
                        $created_by = $row['created_by'];
                        $name = $row['name'];
                        $id = $row['id'];

                        $cbdataaccept = "x$id";
                        $rejectreq = "r$id";

                        $inlineKeyboardoption = [
                            $bot->buildInlineKeyBoardButton("عدم تأیید", '', "$rejectreq"),
                            $bot->buildInlineKeyBoardButton("تأیید", '', "$cbdataaccept"),
                        ];
                        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                        $content = array("chat_id" => $chat_id, "text" => "نام : $name\nعنوان : $title \nتوضیحات : $description\nتاریخ درخواست : $date\nساعت درخواست : $time", 'reply_markup' => $Keyboard);
                        $bot->sendText($content);
                        sleep(1);
                    }
                    $contenttmp = array('chat_id' => $chat_id, "text" => "پایان پردازش");
                    $bot->sendText($contenttmp);
                }
            }
        }
        break;

    //نمایش درخواست های تأیید شده
    case "confirmed_requests":
        if ($all_units) {
            reset_unit($conn, $bb);

            $q_exists = "SELECT * FROM Requests WHERE req_status=3 AND is_closed=3 AND unit='$position' ORDER BY register_date DESC, register_time DESC LIMIT 30";

            if ($result = $conn->query($q_exists)) {
                if ($result->num_rows == 0) {
                    $contenttmp = array('chat_id' => $chat_id, "text" => "موردی وجود ندارد.");
                    $bot->sendText($contenttmp);
                } else {
                    $num = $result->num_rows;
                    $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
                    $bot->sendText($contenttmp);
                    $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد درخواست های در حال پردازش (حداکثر 30)  : $num");
                    $bot->sendText($contenttmp);
                    sleep(1);

                    while ($row = $result->fetch_assoc()) {
                        $title = $row['title'];

                        $date = $row['register_date'];
                        $time = $row['register_time'];

                        $accept_date = $row['accept_date'];
                        $accept_time = $row['accept_time'];

                        $predict_date = $row['predict_date'];

                        $description = $row['description'];
                        $created_by = $row['created_by'];

                        $name = $row['name'];
                        $id = $row['id'];

                        $cbdatadone = "d$id";

                        $inlineKeyboardoption = [
                            $bot->buildInlineKeyBoardButton("انجام شده", '', "$cbdatadone"),
                        ];
                        $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                        $content = array("chat_id" => $chat_id, "text" => "نام : $name\nعنوان : $title \nتوضیحات : $description\nتاریخ ثبت درخواست : $date\nساعت ثبت درخواست : $time\nزمان پیشبینی انجام درخواست : $predict_date\nتاریخ تأیید درخواست : $accept_date\nساعت تأیید درخواست : $accept_time", 'reply_markup' => $Keyboard);
                        $bot->sendText($content);
                        sleep(1);
                    }
                    $contenttmp = array('chat_id' => $chat_id, "text" => "پایان پردازش");
                    $bot->sendText($contenttmp);
                }
            }
        }
        break;

//نمایش تمام درخواست ها
    case "everything":
        if ($all_units or $chat_id == $admin) {
            delete_undone_request($conn, $bb);
            if ($chat_id == $admin) {
                delete_half_made_user($conn);
                stop_changing($conn);
            }
            stop_reason_message($conn, $bb);

            $qu = "INSERT INTO Requests (req_status, created_by)
      VALUES (8,$bb)";
            $conn->query($qu);
            $contenttmp = array('chat_id' => $chat_id, "text" => "تعداد درخواست هایی که میخواهید نمایش داده شود را به صورت عدد وارد کنید:(کیبورد خود را به انگلیسی تغییر دهید)");
            $bot->sendText($contenttmp);
        }
        break;
//مدیریت حساب ها(تنظیمات)
    case "setting":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $inlineKeyboardoption = [
                $bot->buildInlineKeyBoardButton("افزودن سمت", '', "newpost"),
                $bot->buildInlineKeyBoardButton("تغییر سمت", '', "changepost"),
                $bot->buildInlineKeyBoardButton("تغییر یوزرنیم", '', "changeusername"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id, "text" => "یکی از گزینه های زیر را انتخاب کنید:", 'reply_markup' => $Keyboard);
            $bot->sendText($contenttmp);
        }
        break;
//افزودن یک شخص جدید به ربات
    case "newpost":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $contenttmp = array('chat_id' => $chat_id, "text" => "نام کامل شخص مورد نظر را وارد کنید");
            $bot->sendText($contenttmp);
            $qu = "INSERT INTO Persons (status) VALUES ('getname')";
            $result = $conn->query($qu);
        }
        break;
//تغییر سمت شخص مورد نظر
    case "changepost":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $qu = "INSERT INTO Persons (status) VALUES ('change')";
            $result = $conn->query($qu);
            $contenttmp = array('chat_id' => $chat_id, "text" => "یوزرنیم شخص مورد نظر را بدون علامت @ وارد کنید_حداکثر 30 کاراکتر:");
            $bot->sendText($contenttmp);
        }
        break;
//تغییر یوزرنیم یک شخص
    case "changeusername":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $qu = "INSERT INTO Persons (status) VALUES ('changeus')";
            $result = $conn->query($qu);
            $contenttmp = array('chat_id' => $chat_id, "text" => "یوزرنیم قبلی شخص مورد نظر را بدون علامت @ وارد کنید:");
            $bot->sendText($contenttmp);
        }
        break;
//نمایش وضعیت مدیر پروژه که در حال اضافه شدن است
    case "ppm":
        if ($chat_id == $admin) {
            $q_up = "UPDATE Persons SET position=7 WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_id = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $nam = $row['name'];
                $un = $row['username'];
                $content = array("chat_id" => $chat_id, "text" => "نام : $nam\nیوزرنیم : $un\nسمت : مدیر واحد");
                $bot->sendText($content);
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("تأیید", '', "confcreate"),
                    $bot->buildInlineKeyBoardButton("انصراف", '', "canclecreate"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "تأیید می کنید؟", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            }
        }

        break;
//نمایش وضعیت انفورماتیک که در حال اضافه شدن است
    case "p_anformatic":
        if ($chat_id == $admin) {
            $q_up = "UPDATE Persons SET position=2 WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_id = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $nam = $row['name'];
                $un = $row['username'];
                $content = array("chat_id" => $chat_id, "text" => "نام : $nam\nیوزرنیم : $un\nسمت : انفورماتیک");
                $bot->sendText($content);
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("تأیید", '', "confcreate"),
                    $bot->buildInlineKeyBoardButton("انصراف", '', "canclecreate"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "تأیید می کنید؟", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            }
        }
        break;
    //نمایش وضعیت مالی که در حال اضافه شدن است
    case "p_financial":
        if ($chat_id == $admin) {
            $q_up = "UPDATE Persons SET position=3 WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_id = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $nam = $row['name'];
                $un = $row['username'];
                $content = array("chat_id" => $chat_id, "text" => "نام : $nam\nیوزرنیم : $un\nسمت : بخش مالی");
                $bot->sendText($content);
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("تأیید", '', "confcreate"),
                    $bot->buildInlineKeyBoardButton("انصراف", '', "canclecreate"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "تأیید می کنید؟", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            }
        }
        break;
    //نمایش وضعیت پشتیبانی که در حال اضافه شدن است
    case "p_support":
        if ($chat_id == $admin) {
            $q_up = "UPDATE Persons SET position=4 WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_id = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $nam = $row['name'];
                $un = $row['username'];
                $content = array("chat_id" => $chat_id, "text" => "نام : $nam\nیوزرنیم : $un\nسمت : پشتیبانی");
                $bot->sendText($content);
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("تأیید", '', "confcreate"),
                    $bot->buildInlineKeyBoardButton("انصراف", '', "canclecreate"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "تأیید می کنید؟", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            }
        }
        break;
    //نمایش وضعیت خدمات که در حال اضافه شدن است
    case "p_service":
        if ($chat_id == $admin) {
            $q_up = "UPDATE Persons SET position=5 WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_id = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $nam = $row['name'];
                $un = $row['username'];
                $content = array("chat_id" => $chat_id, "text" => "نام : $nam\nیوزرنیم : $un\nسمت : خدمات");
                $bot->sendText($content);
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("تأیید", '', "confcreate"),
                    $bot->buildInlineKeyBoardButton("انصراف", '', "canclecreate"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "تأیید می کنید؟", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            }
        }
        break;
//نمایش وضعیت مدیر عامل که در حال اضافه شدن است
    case "pseo":
        if ($chat_id == $admin) {
            $q_up = "UPDATE Persons SET position=6 WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_up = "UPDATE Persons SET status='choosing' WHERE status='getpos'";
            $result = $conn->query($q_up);
            $q_id = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($q_id)) {
                $row = $result->fetch_assoc();
                $nam = $row['name'];
                $un = $row['username'];
                $content = array("chat_id" => $chat_id, "text" => "نام : $nam\nیوزرنیم : $un\nسمت : مدیر عامل");
                $bot->sendText($content);
                $inlineKeyboardoption = [
                    $bot->buildInlineKeyBoardButton("تأیید", '', "confcreate"),
                    $bot->buildInlineKeyBoardButton("انصراف", '', "canclecreate"),
                ];
                $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
                $contenttmp = array('chat_id' => $chat_id, "text" => "تأیید می کنید؟", 'reply_markup' => $Keyboard);
                $bot->sendText($contenttmp);
            }
        }
        break;
// تأیید ساخت حساب
    case "confcreate":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='choosing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $row = $result->fetch_assoc();
                    $q_up = "UPDATE Persons SET status=NULL WHERE status='choosing'";
                    $result = $conn->query($q_up);
                    $contenttmp = array('chat_id' => $chat_id, "text" => "کاربر با موفقیت اضافه شد.");
                    $bot->sendText($contenttmp);
                }
            }
            admin_clipboard($bot, $chat_id);
        }
        break;
//لغو ساخت حساب جدید
    case "canclecreate":
        if ($chat_id == $admin) {
            delete_half_made_user($conn);
            $contenttmp = array('chat_id' => $chat_id, "text" => "درخواست شما لغو شد.");
            $bot->sendText($contenttmp);
            admin_clipboard($bot, $chat_id);
        }
        break;
//حذف یک کاربر موجود در ربات
    case "changeremove":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $sql = "DELETE FROM Persons WHERE status='changing'";
                    $result = $conn->query($sql);
                    $sql = "DELETE FROM Persons WHERE status='change'";
                    $result = $conn->query($sql);
                }
            }
            $contenttmp = array('chat_id' => $chat_id, "text" => "کاربر مورد نظر از لیست کاربران مجاز این ربات حذف شد.");
            $bot->sendText($contenttmp);
            admin_clipboard($bot, $chat_id);
        }
        break;
//تغییر سمت به مدیر عامل
    case "changetoceo":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $q_up = "UPDATE Persons SET position=6 WHERE status='changing'";
                    if ($result = $conn->query($q_up)) {
                        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت کاربر مورد نظر به مدیر عامل تغییر یافت.");
                        $bot->sendText($contenttmp);
                        $sql = "DELETE FROM Persons WHERE status='change'";
                        $result = $conn->query($sql);
                    }
                }
            }
            admin_clipboard($bot, $chat_id);
        }
        break;
//تغییر سمت به مدیر پروژه
    case "changetopm":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $q_up = "UPDATE Persons SET position=7 WHERE status='changing'";
                    if ($result = $conn->query($q_up)) {
                        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت کاربر مورد نظر به مدیر واحد تغییر یافت.");
                        $bot->sendText($contenttmp);
                        $sql = "DELETE FROM Persons WHERE status='change'";
                        $result = $conn->query($sql);
                    }
                }
            }
            admin_clipboard($bot, $chat_id);
        }
        break;

//تغییر سمت به انفورماتیک
    case "changetoinformatic":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $q_up = "UPDATE Persons SET position=2 WHERE status='changing'";
                    if ($result = $conn->query($q_up)) {
                        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت کاربر مورد نظر به انفورماتیک تغییر یافت.");
                        $bot->sendText($contenttmp);
                        $sql = "DELETE FROM Persons WHERE status='change'";
                        $result = $conn->query($sql);
                    }
                }
            }
            admin_clipboard($bot, $chat_id);
        }
        break;
    //تغییر سمت به مالی
    case "changetofinancial":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $q_up = "UPDATE Persons SET position=3 WHERE status='changing'";
                    if ($result = $conn->query($q_up)) {
                        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت کاربر مورد نظر به بخش مالی تغییر یافت.");
                        $bot->sendText($contenttmp);
                        $sql = "DELETE FROM Persons WHERE status='change'";
                        $result = $conn->query($sql);
                    }
                }
            }
            admin_clipboard($bot, $chat_id);
        }
        break;
    //تغییر سمت به پشتیبانی
    case "changetosupport":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $q_up = "UPDATE Persons SET position=4 WHERE status='changing'";
                    if ($result = $conn->query($q_up)) {
                        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت کاربر مورد نظر به پشتیبانی تغییر یافت.");
                        $bot->sendText($contenttmp);
                        $sql = "DELETE FROM Persons WHERE status='change'";
                        $result = $conn->query($sql);
                    }
                }
            }
            admin_clipboard($bot, $chat_id);
        }
        break;
    //تغییر سمت به خدمات
    case "changetoservice":
        if ($chat_id == $admin) {
            $sql = "SELECT * FROM Persons WHERE status='changing'";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows != 0) {
                    $q_up = "UPDATE Persons SET position=5 WHERE status='changing'";
                    if ($result = $conn->query($q_up)) {
                        $q_up = "UPDATE Persons SET status=NULL WHERE status='changing'";
                        $result = $conn->query($q_up);
                        $contenttmp = array('chat_id' => $chat_id, "text" => "سمت کاربر مورد نظر به خدمات تغییر یافت.");
                        $bot->sendText($contenttmp);
                        $sql = "DELETE FROM Persons WHERE status='change'";
                        $result = $conn->query($sql);
                    }
                }
            }
            admin_clipboard($bot, $chat_id);
        }
        break;

//ثبت درخواست جدید مدیر عامل
    case "newreqceo":
        if (in_array($chat_id, $ceoceo)) {
            delete_undone_request($conn, $bb);
            create_new_req($conn, $bot, $bb, $chat_id);
        }
        break;
//لیست کاربران
    case "admin_users_list":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
            sleep(1);
            $bot->sendText($contenttmp);
            users_list($conn, $bot, $chat_id);
            sleep(2);
            admin_clipboard($bot, $chat_id);
        }
        break;
//        درخواست جدید برای ادمین
    case "admin_new_req":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            create_new_req($conn, $bot, $bb, $chat_id);
        }
        break;
    //        خروجی گرفتن اکسل از گزارشات
    case "adminexcel":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $inlineKeyboardoption = [
                $bot->buildInlineKeyBoardButton("درخواست های انجام شده", '', "adminexceldone"),
                $bot->buildInlineKeyBoardButton("درخواست های تأیید شده", '', "adminexcelaccept"),
                $bot->buildInlineKeyBoardButton("درخواست های رد شده", '', "adminexcelreject"),
                $bot->buildInlineKeyBoardButton("درخواست های بررسی نشده", '', "adminexcelopen"),
                $bot->buildInlineKeyBoardButton("تمام درخواست ها", '', "adminexcelall"),
            ];
            $Keyboard = $bot->buildInlineKeyBoard($inlineKeyboardoption);
            $contenttmp = array('chat_id' => $chat_id, "text" => "گزارش مورد نظر خود را انتخاب کنید(1000 مورد آخر خروجی گرفته میشود):", 'reply_markup' => $Keyboard);
            $bot->sendText($contenttmp);
        }
        break;
//اکسل از درخواست های بررسی نشده
    case "adminexcelopen":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
            $bot->sendText($contenttmp);
            export_excel_open($conn, $bot, $chat_id);

            admin_clipboard($bot, $chat_id);
        }
        break;
//اکسل از درخواست های انجام شده
    case "adminexceldone":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
            $bot->sendText($contenttmp);
            export_excel_done($conn, $bot, $chat_id);

            admin_clipboard($bot, $chat_id);
        }
        break;
//اکسل از همه درخواست ها
    case "adminexcelall":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
            $bot->sendText($contenttmp);
            export_excel_all($conn, $bot, $chat_id);

            admin_clipboard($bot, $chat_id);
        }
        break;
//        درخواست های تأیید شده
    case "adminexcelaccept":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
            $bot->sendText($contenttmp);
            export_excel_accept($conn, $bot, $chat_id);

            admin_clipboard($bot, $chat_id);
        }
        break;
    //        درخواست های رد شده
    case "adminexcelreject":
        if ($chat_id == $admin) {
            reset_admin($conn, $bb);

            $contenttmp = array('chat_id' => $chat_id, "text" => "منتظر بمانید...");
            $bot->sendText($contenttmp);
            export_excel_reject($conn, $bot, $chat_id);

            admin_clipboard($bot, $chat_id);
        }
        break;
}
$conn->close();
?>

