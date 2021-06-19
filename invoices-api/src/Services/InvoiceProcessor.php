<?php


namespace App\Services;

use App\Entity\InvoiceEntry;
use App\Entity\CurrencyRateDefinition;

class InvoiceProcessor
{

    /**
     * @var CurrencyConvertor
     */
    private $currencyConvertor;

    /**
     * @var InvoiceFileReader
     */
    private $fileReader;

    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    private $outputCurrency;

    public function __construct(CurrencyConvertor $convertor, InvoiceFileReader $reader, InvoiceRepository $repository)
    {
        $this->currencyConvertor = $convertor;
        $this->fileReader = $reader;
        $this->invoiceRepository = $repository;
    }

    /**
     * @param string $path Absolute path to the file to be imported for processing.
     */
    public function setInputFilePath(string $path)
    {
        $invoices = $this->fileReader->readFile($path);

        $this->invoiceRepository->clear();
        $this->invoiceRepository->ingest($invoices);
    }

    /**
     * @param string $userInput User-provided list of currencies:
     *   For example: `EUR:1,USD:0.987,GBP:0.878`
     */
    public function setCurrencies(string $userInput)
    {
        // Parse User input.
        $asList = array_map('trim', explode(',', $userInput));
        $asObjects = array_map(function ($item) {
            list($code, $rate) = explode(':', $item);
            return new CurrencyRateDefinition($code, $rate);
        }, $asList);
        $this->currencyConvertor->addAllRateDefinitions($asObjects);
    }

    public function setOutputCurrency($code)
    {
        $this->outputCurrency = (new CurrencyRateDefinition($code, 1))
            ->getCode();
    }

    public function getTotals($vat = '')
    {
        $result = [];

        $callback = !$vat ? null : function (InvoiceEntry $entry) use ($vat) {
            return $vat == $entry->vatNumber;
        };

        $rows = $this->invoiceRepository->query($callback);

        foreach ($rows as $document) {
            // Init customer's meta-data.
            if (!isset($result[$document->vatNumber])) {
                $result[$document->vatNumber] = [
                    'name' => $document->customer,
                    'vatNumber' => $document->vatNumber,
                    'total' => 0,
                    'currencyCode' => $this->outputCurrency,
                ];
            }

            $amount = $this->currencyConvertor->convert(
                $this->outputCurrency,
                $document->currencyCode,
                $document->total
            );

            $old = $result[$document->vatNumber]['total'];
            $new = round($old + $amount * $document->getTypeMultiplier(), 2);
            $result[$document->vatNumber]['total'] = $new;
        }

        return $result;
    }
}
