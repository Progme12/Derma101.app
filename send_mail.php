<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient = $_POST['recipient'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $result = send_mail($recipient, $subject, $message);

    echo json_encode(['success' => $result]);
}

function send_mail($recipient, $subject, $message)
{
    $mail = new PHPMailer();
    // ... (your existing email sending code)

    if (!$mail->Send()) {
        return false;
    } else {
        return true;
    }
}

?>
