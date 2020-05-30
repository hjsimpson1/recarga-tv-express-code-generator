<?php

namespace CViniciusSDias\RecargaTvExpress\Service\EmailParser;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use PhpImap\IncomingMail;

abstract class EmailParser
{
    /** @var EmailParser */
    protected $next;

    public function __construct(EmailParser $next = null)
    {
        $this->next = $next;
    }

    /** @return Sale[] */
    public function parse(IncomingMail $email): array
    {
        if (!$this->canParse($email)) {
            return $this->next->parse($email);
        }

        return $this->parseEmail($email);
    }
    /** @return Sale[] */
    abstract protected function parseEmail(IncomingMail $email): array;
    abstract protected function canParse(IncomingMail $email): bool;
}
