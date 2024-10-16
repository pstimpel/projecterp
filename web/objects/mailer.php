<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../external/phpmailer/Exception.php';
require_once '../external/phpmailer/PHPMailer.php';
require_once '../external/phpmailer/SMTP.php';


class Mail {

    private $mailtext;

    public function __construct($mailtext)
    {
        $this->mailtext = $mailtext;
    }

    public function sendmail() {
        try {
            $mail = new PHPMailer(true);
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
            $mail->isSMTP();      
            // TODO: change mail server address and mail addresses                                      //Send using SMTP
            $mail->Host       = 'a.b.c.d';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = false;                                   //Enable SMTP authentication
            $mail->Port       = 25;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            //Recipients
            $mail->setFrom('xxx@yyy', 'ERP');
            $mail->addAddress('yyy@xxx', 'yyy');     //Add a recipient

            //Content
            $mail->isHTML(false);                                  //Set email format to HTML
            $mail->Subject = 'ERP->handling error';
            $mail->Body    = $this->mailtext;

            $mail->send();
            //echo 'Message has been sent';
            return true;
        } catch (Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
}