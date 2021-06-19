<?php

namespace App\Controller;

use App\Entity\InvoiceAggregation;
use App\Services\InvoiceProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    public function index(Request $request, InvoiceProcessor $processor): Response
    {
        // TODO: Improve validations.
        /* @var $invoicesFile UploadedFile|null */
        $invoicesFile = $request->files->get('invoicesFile');
        $currencyRates = $request->request->get('currencyRates');
        $outputCurrency = $request->request->get('outputCurrency');
        $vatNumber = $request->request->get('vatNumber', '');

//        return new JsonResponse([get_class($invoicesFile), $currencyRates, $outputCurrency, $vatNumber]);

        if ($invoicesFile === null || $currencyRates === null || $outputCurrency === null) {
            return new JsonResponse(['error' => 'Missing required inputs!'], 400);
        }

        try {
            $processor->setInputFilePath($invoicesFile->getPathname());
            $processor->setCurrencies($currencyRates);
            $processor->setOutputCurrency($outputCurrency);

            $totals = $processor->getTotals($vatNumber);

            return new JsonResponse(['totals' => array_values($totals)]);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        }
    }
}
