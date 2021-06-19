<?php

namespace App\Entity;

/**
 * Class CurrencyRateDefinition.
 *
 * Used to hold the currency conversion rate for a given currency code.
 *
 * @package App\Entity
 */
class CurrencyRateDefinition
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var int|float
     */
    private $rate;

    /**
     * CurrencyRateDefinition constructor.
     *
     * @param string $code Currency code.
     * @param int|float $rate Rate to of $code compared to the default.
     */
    public function __construct(string $code, $rate)
    {
        $this->code = mb_convert_case(trim($code), MB_CASE_UPPER, 'UTF-8');
        $this->rate = $rate + 0;
    }

    /**
     * Whether this was the default rate form the input.
     *
     * @return bool
     */
    public function isDefault():bool
    {
        return 1 === $this->rate;
    }

    /**
     * Currency code.
     *
     * @return string
     */
    public function getCode():string
    {
        return $this->code;
    }

    /**
     * Conversion rate compared to the default.
     *
     * @return float|int
     */
    public function getRate()
    {
        return $this->rate;
    }
}
