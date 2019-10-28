<?php
/*
 * Class modules\base\mailer\Mailer
 *
 * Отвечает за отправку почты
 *
 * @author Alexander Ladonin <ladonin85@mail.ru>
 */
namespace modules\base\mailer;

use \modules\base\mailer\classes;

final class Mailer extends classes\Mailer
{
    /*
     * Объект PHPMailer
     */
    protected $mailer;

    protected function __construct()
    {
        $mailer = new classes\PHPMailer;
        $mailer->CharSet = 'UTF-8';
        /*
          $mailer->isSMTP();                                      // Set mailer to use SMTP
          $mailer->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
          $mailer->SMTPAuth = true;                               // Enable SMTP authentication
          $mailer->Username = 'user@example.com';                 // SMTP username
          $mailer->Password = 'secret';                           // SMTP password
          $mailer->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
          $mailer->Port = 587;                                    // TCP port to connect to
         */

        $this->mailer = $mailer;
    }


}
