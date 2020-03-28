<?php

namespace CViniciusSDias\RecargaTvExpress\Service;

use CViniciusSDias\RecargaTvExpress\Model\Code;
use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Repository\CodeRepository;

class SalesRepository
{
    private $emailSalesReader;
    private $codeRepository;
    private $con;

    public function __construct(EmailSalesReader $emailSalesReader, CodeRepository $codeRepository, \PDO $con)
    {
        $this->emailSalesReader = $emailSalesReader;
        $this->codeRepository = $codeRepository;
        $this->con = $con;
    }

    /**
     * @return Sale[]
     * @throws \Throwable
     */
    public function salesWithCodes(): array
    {
        $salesWithoutCode = $this->emailSalesReader->findSales();
        $annualSales = array_values(array_filter($salesWithoutCode, function (Sale $sale) {
            return $sale->product === 'anual';
        }));
        $monthlySales = array_values(array_filter($salesWithoutCode, function (Sale $sale) {
            return $sale->product === 'mensal';
        }));
        $grouppedCodes = $this->codeRepository->findUnusedCodes(count($annualSales), count($monthlySales));

        $this->con->beginTransaction();
        try {
            $this->attachCodesToSales($grouppedCodes, $annualSales, $monthlySales);
            $this->con->commit();
        } catch (\Throwable $e) {
            $this->con->rollBack();
            throw $e;
        }

        return array_merge($annualSales, $monthlySales);
    }

    /**
     * @param array $grouppedCodes
     * @param Sale[] $annualSales
     * @param Sale[] $monthlySales
     */
    private function attachCodesToSales(array $grouppedCodes, array $annualSales, array $monthlySales): void
    {
        foreach ($grouppedCodes['anual'] as $annualCodes) {
            $this->attachCodes($annualCodes, $annualSales);
        }

        foreach ($grouppedCodes['mensal'] as $monthlyCodes) {
            $this->attachCodes($monthlyCodes, $monthlySales);
        }
    }

    /**
     * @param Code[] $codes
     * @param Sale[] $sales
     */
    private function attachCodes($codes, array $sales)
    {
        foreach ($codes as $i => $code) {
            $sales[$i]->attachCode($code);
            $this->codeRepository->attachCodeToSale($code, $sales[$i]);
        }
    }
}
