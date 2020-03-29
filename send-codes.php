<?php

require_once __DIR__ . '/bootstrap.php';

use CViniciusSDias\RecargaTvExpress\Exception\NotEnoughCodesException;
use CViniciusSDias\RecargaTvExpress\Repository\SalesRepository;
use CViniciusSDias\RecargaTvExpress\Service\EmailSalesReader;
use CViniciusSDias\RecargaTvExpress\Service\SerialCodeSender;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/** @var ContainerInterface $container */
$container = require_once __DIR__ . '/config/dependencies.php';

try {
    /** @var SalesRepository $salesFinder */
    $salesFinder = $container->get(SalesRepository::class);
    /** @var SerialCodeSender $codeSender */
    $codeSender = $container->get(SerialCodeSender::class);

    $sales = $salesFinder->salesWithCodes();

    foreach ($sales as $sale) {
        $codeSender->sendCodeTo($sale);
    }
} catch (NotEnoughCodesException $exception) {
    /** @var LoggerInterface $logger */
    $logger = $container->get(LoggerInterface::class);
    /** @var EmailSalesReader $emailReader */
    $emailReader = $container->get(EmailSalesReader::class);
    $emailReader->markEmailsAsUnread();

    $logger->error('Erro ao enviar códigos: ' . $exception->getMessage());
} catch (\Throwable $error) {
    /** @var LoggerInterface $logger */
    $logger = $container->get(LoggerInterface::class);
    $context = [
        'mensagem' => $error->getMessage(),
        'erro' => $error
    ];

    $logger->error('Erro desconhecido ao enviar códigos.', $context);
}
