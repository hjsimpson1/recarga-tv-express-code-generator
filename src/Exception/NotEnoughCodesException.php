<?php

namespace CViniciusSDias\RecargaTvExpress\Exception;

class NotEnoughCodesException extends \Exception
{
    public function __construct(
        int ...$numberOfSales
    ) {
        $format = "You don't have enough codes for all your sales.\n";
        $format .= "Number of annual sales: %d. Number of annual codes available: %d.\n";
        $format .= "Number of monthly sales: %d. Number of monthly codes available: %d.\n";
        $message = sprintf($format, ...$numberOfSales);

        parent::__construct(
            $message,
            0,
            null
        );
    }
}
