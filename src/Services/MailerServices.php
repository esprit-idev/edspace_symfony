<?php

namespace App\Services;

class MailerServices
{

    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }


    public function sendEmail($to, $subject, $texto) {
        $message = (new \Swift_Message($subject))
            ->setFrom('meriamesprittest@gmail.com')
            ->setTo($to)
            ->setBody(($texto),'text/html');
        return $this->mailer->send($message);
    }
}