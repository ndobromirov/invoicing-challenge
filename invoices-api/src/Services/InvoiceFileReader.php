<?php

namespace App\Services;

use App\Entity\InvoiceEntry;

class InvoiceFileReader
{
    /**
     * Read an invoice CSV into memory.
     *
     * @param string $path
     * @param bool $skipHeaders
     * @return InvoiceEntry[]
     */
    public function readFile(string $path, bool $skipHeaders = true):array
    {
        /** @var InvoiceEntry[] $result */
        $result = [];
        try {
            if (false === ($handler = fopen($path, 'r'))) {
                throw new \InvalidArgumentException("Can not open file for read: $path");
            }

            if ($skipHeaders) {
                fgetcsv($handler);
            }

            while ($line = fgetcsv($handler)) {
                // TODO: Per row validation on the entity level...
                //   Inject the validation service.
                $invoice = $this->createEntity($line);
                $result[$invoice->documentNumber] = $invoice;
            }
        } finally {
            fclose($handler);
        }

        // Validate references.
        foreach ($result as $item) {
            if ($item->parentDocumentNumber && !isset($result[$item->parentDocumentNumber])) {
                throw new \InvalidArgumentException("Invalid parent reference in file: {$item->parentDocumentNumber}!");
            }
        }

        return  $result;
    }

    /**
     * Internal factory to improve readability.
     *
     * @param array $line Line as read from the CSV file.
     * @return InvoiceEntry A newly created instance based on the line's data.
     */
    protected function createEntity(array $line)
    {
        $entity = new InvoiceEntry();

        // Typecasts and safety for missing values in rows.
        $entity->customer = $line[0] ?? '';
        $entity->vatNumber = intval($line[1] ?? 0);
        $entity->documentNumber = intval($line[2] ?? 0);
        $entity->type = intval($line[3] ?? 0);
        $entity->parentDocumentNumber = intval($line[4] ?? 0) ?: null;
        $entity->currencyCode = $line[5] ?? "";
        $entity->total = ($line[6] ?? 0) + 0;

        return $entity;
    }
}
