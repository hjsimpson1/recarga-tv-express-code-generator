<?php

namespace CViniciusSDias\RecargaTvExpress\Exception;

use Throwable;

class NotEnoughCodesException extends \Exception
{
    public function __construct(
        int $numerOfAnnualSales,
        int $numberOfAnnualCodes,
        int $numberOfMonthlySales,
        int $numberOfMonthlyCodes
    ) {
        $format = "You don't have enough codes for all your sales.\n";
        $format .= "Number of annual sales: %d. Number of annual codes available: %d.\n";
        $format .= "Number of monthly sales: %d. Number of monthly codes available: %d.\n";
        $message = sprintf($format, ...func_get_args());

        parent::__construct(
            $message,
            0,
            null
        );
    }
}
