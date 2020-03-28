<?php

namespace CViniciusSDias\RecargaTvExpress\Tests\Unit\Model;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Model\VO\Email;
use PHPUnit\Framework\TestCase;

class SaleTest extends TestCase
{
    public function testCreatingASaleWithAnInvalidProductMustThrowException()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('invalid is not a valid product');

        new Sale(new Email('email@example.com'), 'invalid');
    }

    public function testCreatingASaleWithAValidProductMustWork()
    {
        $aSale = new Sale(new Email('email@example.com'), 'mensal');
        $anotherSale = new Sale(new Email('email@example.com'), 'anual');

        self::assertSame('mensal', $aSale->product);
        self::assertSame('anual', $anotherSale->product);
    }
}
