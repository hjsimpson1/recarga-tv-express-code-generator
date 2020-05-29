<?php

namespace CViniciusSDias\RecargaTvExpress\Service\EmailParser;

use CViniciusSDias\RecargaTvExpress\Model\Sale;
use CViniciusSDias\RecargaTvExpress\Model\VO\Email;
use PhpImap\IncomingMail;

class WixEmailParser extends EmailParser
{
    protected function canParse(IncomingMail $email): bool
    {
        return $email->fromAddress === 'no-reply@mystore.wix.com';
    }

    protected function parseEmail(IncomingMail $email): ?Sale
    {
        $domDocument = new \DOMDocument();
        libxml_use_internal_errors(true);
        $domDocument->loadHTML($email->textHtml);
        $xPath = new \DOMXPath($domDocument);

        $infoNodes = $xPath
            ->query('/html/body/table[@id="backgroundTable"]//td[@class="section-content"]');

        $emailAddress = $this->retrieveEmailAddress($infoNodes);
        $product = $this->retrieveProduct($infoNodes);

        return new Sale(new Email($emailAddress), $product);
    }

    private function retrieveEmailAddress(\DOMNodeList $infoNodes): string
    {
        $contactInfo = $infoNodes->item(1)
            ->textContent;

        $emailArray = array_filter(
            explode("\n", $contactInfo),
            function (string $line) {
                return filter_var(trim($line), FILTER_VALIDATE_EMAIL);
            }
        );

        return trim(array_values($emailArray)[0]);
    }

    private function retrieveProduct(\DOMNodeList $infoNodes): string
    {
        $productInfo = $infoNodes->item(2)
            ->textContent;
        preg_match('/produto (mensal|anual)/i', $productInfo, $productMatches);

        return $productMatches[1];
    }
}
