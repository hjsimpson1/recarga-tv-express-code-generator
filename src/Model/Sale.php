<?php

namespace CViniciusSDias\RecargaTvExpress\Model;

use CViniciusSDias\RecargaTvExpress\Model\VO\Email;

/**
 * @property-read Email $costumerEmail
 * @property-read string $product
 */
class Sale
{
    use PropertyAccess;

    private $costumerEmail;
    private $product;

    public function __construct(Email $costumerEmail, string $product)
    {
        $this->costumerEmail = $costumerEmail;
        $this->setProduct($product);
    }

    private function setProduct(string $product): void
    {
        $productName = trim($product);
        if (!in_array($productName, ['anual', 'mensal'])) {
            throw new \DomainException("$productName is not a valid product");
        }

        $this->product = $productName;
    }
}
