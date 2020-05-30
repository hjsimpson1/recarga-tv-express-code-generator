<?php

namespace CViniciusSDias\RecargaTvExpress\Tests\Integration\Service;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Service\EmailParser\{EmailParser, WixEmailParser};
use CViniciusSDias\RecargaTvExpress\Service\EmailSalesReader;
use PhpImap\IncomingMail;
use PhpImap\Mailbox;
use PHPUnit\Framework\TestCase;

/**
 * Test class for integration between SalesFinder and EmailFinders
 */
class SalesFinderTest extends TestCase
{
    public function testSalesFinderShouldReturnEmptyArrayWhenNoParseableEmailIsFound()
    {
        $incomingMail = $this->createStub(IncomingMail::class);
        $incomingMail->fromAddress = 'info@mercadopago.com';
        $invalidEmailSubject = 'Você recebeu um pagamento por Combo MFC + TVE anual';
        $incomingMail->subject = $invalidEmailSubject;

        $mailbox = $this->createStub(Mailbox::class);
        $mailbox->method('searchMailbox')->willReturn([1]);
        $mailbox->method('getMail')->willReturn($incomingMail);

        $salesFinder = new EmailSalesReader($mailbox, $this->emailParser());

        $sales = $salesFinder->findSales();

        $this->assertEmpty($sales);
    }

    /**
     * @todo Implement tests for WixEmailParser
     */
    public function testSalesFinderShouldOnlyReturnSalesFromParseableEmails()
    {
        // arrange

        // valid mercado pago e-mail
        $incomingMailMock1 = $this->createStub(IncomingMail::class);
        $incomingMailMock1->subject = 'Você recebeu um pagamento por P 2';
        $incomingMailMock1->fromAddress = 'info@mercadopago.com';
        $incomingMailMock1->method('__get')
            ->willReturn(file_get_contents(__DIR__ . '/../../data/email-without-phone.html'));

        // valid paypal e-mail
        $incomingMailMock2 = $this->createStub(IncomingMail::class);
        $incomingMailMock2->subject = 'Item nº 12345';
        $incomingMailMock2->fromAddress = 'service@paypal.com.br';
        $incomingMailMock2->method('__get')
            ->willReturn(file_get_contents(__DIR__ . '/../../data/email-with-payment-from-paypal.html'));

        // invalid e-mail
        $incomingMailMock3 = $this->createStub(IncomingMail::class);
        $incomingMailMock3->fromAddress = 'wrong-email@example.com';
        $incomingMailMock3->subject = 'Você recebeu um pagamento por Combo MFC + TVE anual';

        // valid wix e-mail
        $incomingMailMock4 = $this->createStub(IncomingMail::class);
        $incomingMailMock4->subject = 'ÓTIMO! VOCÊ ACABOU DE RECEBER UM PEDIDO (#10001)';
        $incomingMailMock4->fromAddress = 'no-reply@mystore.wix.com';
        $incomingMailMock4->method('__get')
            ->willReturn(file_get_contents(__DIR__ . '/../../data/email-from-wix.html'));

        // valid wix e-mail with 2 sales
        $incomingMailMock5 = $this->createStub(IncomingMail::class);
        $incomingMailMock5->subject = 'ÓTIMO! VOCÊ ACABOU DE RECEBER UM PEDIDO (#10001)';
        $incomingMailMock5->fromAddress = 'no-reply@mystore.wix.com';
        $incomingMailMock5->method('__get')
            ->willReturn(file_get_contents(__DIR__ . '/../../data/email-from-wix-with-three-sales.html'));

        $mailbox = $this->createStub(Mailbox::class);
        $mailbox->method('searchMailbox')->willReturn([1, 2, 3, 4, 5]);
        $mailbox->method('getMail')->willReturnOnConsecutiveCalls(
            $incomingMailMock1,
            $incomingMailMock2,
            $incomingMailMock3,
            $incomingMailMock4,
            $incomingMailMock5,
        );

        $salesFinder = new EmailSalesReader($mailbox, $this->emailParser());

        // act
        $sales = $salesFinder->findSales();

        // assert
        $this->assertCount(4, $sales);
        $this->assertContainsOnlyInstancesOf(Sale::class, $sales);
    }

    private function emailParser(): EmailParser
    {
        $nullParser = new class extends EmailParser
        {
            protected function parseEmail(IncomingMail $email): array
            {
                return [];
            }

            protected function canParse(IncomingMail $email): bool
            {
                return true;
            }
        };

        return new WixEmailParser($nullParser);
    }
}
