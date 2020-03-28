<?php

namespace CViniciusSDias\RecargaTvExpress\Repository;

use CViniciusSDias\RecargaTvExpress\Exception\NotEnoughCodesException;
use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Service\EmailSalesReader;

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
     * @throws \PDOException
     * @throws NotEnoughCodesException
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
        if (count($annualSales) > count($grouppedCodes['anual']) || count($monthlySales) > $grouppedCodes['mensal']) {
            throw new NotEnoughCodesException(
                count($annualSales),
                count($grouppedCodes['anual']),
                count($monthlySales),
                count($grouppedCodes['mensal']),
            );
        }

        $this->con->beginTransaction();
        try {
            $this->attachCodesToSales($grouppedCodes, $annualSales, $monthlySales);
            $this->con->commit();
        } catch (\PDOException $e) {
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
        foreach ($grouppedCodes['anual'] as $i => $code) {
            $annualSales[$i]->attachCode($code);
            $this->codeRepository->attachCodeToSale($code, $annualSales[$i]);
        }


        foreach ($grouppedCodes['mensal'] as $i => $code) {
            $monthlySales[$i]->attachCode($code);
            $this->codeRepository->attachCodeToSale($code, $monthlySales[$i]);
        }
    }
}
