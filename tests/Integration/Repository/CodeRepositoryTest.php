<?php

namespace CViniciusSDias\RecargaTvExpress\Tests\Integration\Repository;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Model\VO\Email;
use CViniciusSDias\RecargaTvExpress\Repository\CodeRepository;
use PHPUnit\Framework\TestCase;

class CodeRepositoryTest extends TestCase
{
    private static $con;
    /** @var CodeRepository  */
    private $codeRepository;

    public static function setUpBeforeClass(): void
    {
        $con = new \PDO('sqlite::memory:');
        $con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $con->exec('CREATE TABLE serial_codes (
            id INTEGER PRIMARY KEY,
            serial TEXT NOT NULL,
            user_email TEXT DEFAULT NULL,
            product TEXT
        );');

        self::$con = $con;
    }

    public static function tearDownAfterClass(): void
    {
        self::$con = null;
    }

    protected function setUp(): void
    {
        $this->codeRepository = new CodeRepository(self::$con);
    }

    protected function tearDown(): void
    {
        self::$con->exec('DELETE FROM serial_codes;');
    }

    public function testShouldFindOneUnusedCodeForASale()
    {
        // Arrange
        $this->insertCode('4321', 'anual');
        $this->insertCode('1111', 'anual');
        $this->insertCode('1234', 'mensal');

        $sale = new Sale(new Email('email@example.com'), 'mensal');

        // Act
        $code = $this->codeRepository->findUnusedCodeFor($sale);

        // Assert
        self::assertSame('1234', $code->serial);
    }

    public function testShouldNotFindAnyUnusedCodeForASaleIfThereAreNone()
    {
        // Assert
        $this->expectExceptionMessage('No unused code found for this sale');

        // Arrange
        $this->insertCode('4321', 'anual');
        $this->insertCode('1111', 'anual');

        $sale = new Sale(new Email('email@example.com'), 'mensal');

        // Act
        $this->codeRepository->findUnusedCodeFor($sale);
    }

    public function testShouldFindExactNumberOfAvailableCodes()
    {
        // Arrange
        $this->insertCode('1111', 'anual');
        $this->insertCode('2222', 'anual');
        $this->insertCode('3333', 'mensal');
        $this->insertCode('4444', 'mensal');
        $this->insertCode('5555', 'mensal');

        $numberOfAvailableCodes = $this->codeRepository->findNumberOfAvailableCodes();

        self::assertEquals(2, $numberOfAvailableCodes['anual']);
        self::assertEquals(3, $numberOfAvailableCodes['mensal']);
    }

    public function testShouldNotFindAnyAvailableCodesIfThereAreNone()
    {
        $numberOfAvailableCodes = $this->codeRepository->findNumberOfAvailableCodes();

        self::assertEquals(0, $numberOfAvailableCodes['anual']);
        self::assertEquals(0, $numberOfAvailableCodes['mensal']);
    }

    public function testSearchForAllSpecificNumberOfCodesShouldReturnGrouppedArray()
    {
        $this->insertCode('1111', 'anual');
        $this->insertCode('2222', 'anual');
        $this->insertCode('3333', 'mensal');
        $this->insertCode('4444', 'mensal');
        $codes = $this->codeRepository->findUnusedCodes(2, 2);

        self::assertArrayHasKey('anual', $codes);
        self::assertArrayHasKey('mensal', $codes);
        self::assertCount(2, $codes['anual']);
        self::assertCount(2, $codes['mensal']);
        self::assertSame('1111', $codes['anual'][0]->serial);
        self::assertSame('2222', $codes['anual'][1]->serial);
        self::assertSame('3333', $codes['mensal'][0]->serial);
        self::assertSame('4444', $codes['mensal'][1]->serial);
    }

    private function insertCode(string $serial, string $product)
    {
        /** @var \PDOStatement $stm */
        $stm = self::$con->prepare('INSERT INTO serial_codes (serial, product) VALUES (?, ?)');
        $stm->bindValue(1, $serial);
        $stm->bindValue(2, $product);
        $stm->execute();
    }
}
