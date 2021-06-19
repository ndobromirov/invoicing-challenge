<?php


namespace App\Entity;

/**
 * Class InvoiceEntry.
 *
 * Represents a single line from the following CSV:
 * ```
 * Customer,Vat number,Document number,Type,Parent document,Currency,Total
 * Vendor 1,123456789,1000000257,1,,USD,400
 * ```
 * @package App\Entity
 */
class InvoiceEntry
{
    const TYPE_INVOICE = 1;
    const TYPE_CREDIT = 2;
    const TYPE_DEBIT = 3;

    public $customer;
    public $vatNumber;
    public $documentNumber;
    public $type;
    public $parentDocumentNumber;
    public $currencyCode;
    public $total;

    public function getTypeMultiplier()
    {
        return $this->type === self::TYPE_CREDIT ? -1 : 1;
    }
}
