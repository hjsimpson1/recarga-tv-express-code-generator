<?php

namespace CViniciusSDias\RecargaTvExpress\Tests\Unit\Service;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Service\EmailParser\WixEmailParser;
use PhpImap\IncomingMail;
use PHPUnit\Framework\TestCase;

class WixEmailParserTest extends TestCase
{
    public function testShouldParseCorrectlyASaleFromWixEmail()
    {
        // arrange
        $emailBody = file_get_contents(__DIR__ . '/../../data/email-from-wix.html');
        $parser = new WixEmailParser();

        $incomingMailMock = $this->createStub(IncomingMail::class);
        $incomingMailMock->fromAddress = 'no-reply@mystore.wix.com';
        $incomingMailMock->subject = 'ÓTIMO! VOCÊ ACABOU DE RECEBER UM PEDIDO (#10001)';
        $incomingMailMock->method('__get')
            ->willReturn($emailBody);

        // act
        $sale = $parser->parse($incomingMailMock);

        // assert
        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertSame('mensal', $sale->product);
        $this->assertEquals('email@example.com', $sale->costumerEmail);
    }

    public function testShouldRaiseErrorWhenTryingToParseUnsupportedEmail()
    {
        $this->expectException(\Error::class);

        // arrange
        $parser = new WixEmailParser();
        $incomingMailMock = $this->createStub(IncomingMail::class);
        $incomingMailMock->fromAddress = 'email@example.com';

        // act
        $parser->parse($incomingMailMock);
    }
}
