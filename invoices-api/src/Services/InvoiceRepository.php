<?php

namespace App\Services;

use App\Entity\InvoiceEntry;

class InvoiceRepository
{
    /**
     * @var InvoiceEntry[]
     */
    private $dataStorage = [];

    /**
     * Clears the internal storage.
     */
    public function clear()
    {
        $this->dataStorage = [];
    }

    /**
     * Loads data into the internal storage for future queries.
     *
     * @param InvoiceEntry[] $data
     */
    public function ingest(array $data)
    {
        foreach ($data as $item) {
            if (!$item instanceof InvoiceEntry) {
                list($invalidClass, $expectedClass) = [get_class($item), InvoiceEntry::class];
                throw new \InvalidArgumentException(
                    "Invalid entity class provided: {$invalidClass}. Expected: {$expectedClass}"
                );
            }
        }

        $this->dataStorage = array_merge($this->dataStorage, $data);
    }

    /**
     * Performs a query over the internally stored data.
     *
     * @param callable|null $filterCallback Receives a single InvoiceEntry
     *   parameter and decides should it be part of the results set.
     *   When NULL - all data is returned.
     * @return InvoiceEntry[] Results found.
     */
    public function query(callable $filterCallback = null)
    {
        $callback = $filterCallback ?? function (InvoiceEntry $item) {
            return true;
        };
        return array_filter($this->dataStorage, $callback);
    }
}
