<?php

namespace CViniciusSDias\RecargaTvExpress\Service;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Service\EmailParser\EmailParser;
use PhpImap\Mailbox;

class EmailSalesReader
{
    private $mailbox;
    /** @var int[] */
    private $mailIds;
    private $emailParser;

    public function __construct(Mailbox $mailbox, EmailParser $emailParser)
    {
        $this->mailbox = $mailbox;
        $this->mailIds = [];
        $this->emailParser = $emailParser;
    }

    /** @return Sale[] */
    public function findSales(): array
    {
        $this->mailIds = $this->mailbox
            ->searchMailbox('UNSEEN');

        if (empty($this->mailIds)) {
            return [];
        }

        $sales = [];
        foreach ($this->mailIds as $mailId) {
            $mail = $this->mailbox->getMail($mailId);

            $sales[] = $this->emailParser->parse($mail);
        }

        return array_filter($sales);
    }
}
