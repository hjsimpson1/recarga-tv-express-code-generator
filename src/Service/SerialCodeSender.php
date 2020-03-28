<?php

namespace CViniciusSDias\RecargaTvExpress\Service;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\NamedAddress;

class SerialCodeSender
{
    /**
     * @var MailerInterface
     */
    private $mailer;
    /**
     * @var string
     */
    private $from;

    public function __construct(MailerInterface $mailer, string $from)
    {
        $this->mailer = $mailer;
        $this->from = $from;
    }

    public function sendCodeTo(Sale $sale): void
    {
        $emailBody = <<<EMAIL
        Olá!
        Obrigado pela compra
        Segue abaixo seu codigo de recarga:
        {$sale->code}
        
        Dúvidas de como recarregar acesse o site https://www.recargatvexpress.com/como-recarregar
        EMAIL;

        $email = (new Email())
            ->from(new NamedAddress($this->from, 'TV Express'))
            ->to((string) $sale->costumerEmail)
            ->subject('Recarga TV express ')
            ->text($emailBody);
        $this->mailer->send($email);
    }
}
