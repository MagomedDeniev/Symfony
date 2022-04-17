<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer
{
    private $mailer;
    private $from;
    private $to;
    private $subject;
    private $template;
    public $variables;

    public function __construct(MailerInterface $mailer, $email)
    {
        $this->mailer = $mailer;
        $this->from = $email;
    }

    public function notify()
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->getFrom(), 'ShovdanYist'))
            ->to($this->getTo())
            ->subject($this->getSubject())
            ->htmlTemplate($this->getTemplate())
            ->context($this->getVariables());

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
        }
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($from): Mailer
    {
        $this->from = $from;

        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo($to): Mailer
    {
        $this->to = $to;

        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template): Mailer
    {
        $this->template = $template;

        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject): Mailer
    {
        $this->subject = $subject;

        return $this;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function setVariables($variables): Mailer
    {
        $this->variables = $variables;

        return $this;
    }
}
