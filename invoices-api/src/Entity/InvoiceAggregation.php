<?php

namespace App\Entity;

class InvoiceAggregation
{
    /**
     * @var string
     */
    public $invoicesFile;

    /**
     * @var string
     */
    public $currencyRates;

    /**
     * @var string
     */
    public $outputCurrency;

    /**
     * @var string
     */
    public $vatNumber;
}
