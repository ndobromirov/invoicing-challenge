<?php

namespace App\Services;

use App\Entity\CurrencyRateDefinition;

class CurrencyConvertor
{
    private $conversionMap;

    /**
     * @param CurrencyRateDefinition[] $rateDefinitions
     */
    public function addAllRateDefinitions(array $rateDefinitions)
    {
        /* @var CurrencyRateDefinition[] $rateDefinitions  */

        $default = null;
        $this->conversionMap = [];

        // Input validations.
        foreach ($rateDefinitions as $definition) {
            if (!$definition instanceof CurrencyRateDefinition) {
                throw new \InvalidArgumentException("Invalid item in input list!");
            }

            // Aggregate whether we have found a default in the input.
            if ($definition->isDefault()) {
                $default = $definition;
            }
        }

        if (!$default) {
            throw new \InvalidArgumentException("Missing default in the input!");
        }

        foreach ($rateDefinitions as $definition) {
            // Fill definitions.
            $key = $this->getKey($default, $definition);
            $rate = $this->getRate($default, $definition);
            $this->conversionMap[$key] = $rate;

            // Fill inverted definitions.
            $key = $this->getKey($definition, $default);
            $rate = $this->getRate($definition, $default);
            $this->conversionMap[$key] = $rate;
        }

        // Fill two-step conversions through the default.
        foreach ($rateDefinitions as $definitionFrom) {
            foreach ($rateDefinitions as $definitionTo) {
                $key = $this->getKey($definitionFrom, $definitionTo);

                // Skip conversions that are already computed.
                if (isset($this->conversionMap[$key])) {
                    continue;
                }

                // USD to GBP & EUR as default, results in (USD -> EUR) * (EUR -> GBP).
                $rateToDefault = $this->conversionMap[$this->getKey($definitionFrom, $default)];
                $rateFromDefault = $this->conversionMap[$this->getKey($default, $definitionTo)];
                $rate = $rateToDefault * $rateFromDefault;

                $this->conversionMap[$key] = $rate;
            }
        }

        // All set!
    }

    /**
     * Computes a key for the conversion rate between $from and $to.
     *
     * @param CurrencyRateDefinition $from
     * @param CurrencyRateDefinition $to
     * @return string
     */
    private function getKey(CurrencyRateDefinition $from, CurrencyRateDefinition $to)
    {
        return "{$from->getCode()}:{$to->getCode()}";
    }

    /**
     * Computes the conversion rate between $from and $to.
     *
     * @param CurrencyRateDefinition $from
     * @param CurrencyRateDefinition $to
     * @return float|int
     */
    private function getRate(CurrencyRateDefinition $from, CurrencyRateDefinition $to)
    {
        // Conversion rate between the same currency is always 1.
        return $from->getCode() !== $to->getCode()
            ? $this->round($from->getRate() / $to->getRate())
            : 1;
    }

    /**
     * Internally managed rounding behavior.
     *
     * This can be improved in future or moved outside and injected.
     * The method here is to allow for easy future extension.
     *
     * @param int|float $value
     * @return int|float The rounded value (if it needed rounding).
     */
    private function round($value)
    {
        return is_int($value) ? $value : round($value, 6);
    }

    /**
     * Currency conversion method.
     *
     * To work as expected the internal state of the service needs the be
     * initialized with a set of conversion rates for the different currencies.
     *
     * @see CurrencyRateDefinition
     * @see CurrencyConvertor::addAllRateDefinitions()
     *
     * @param string $fromCode Convert from currency code.
     * @param string $toCode Convert to currency code.
     * @param int|float $amount Amount of source currency to be converted.
     * @return float|int Amount of resulting currency after conversion.
     */
    public function convert(string $fromCode, string $toCode, $amount)
    {
        $from = new CurrencyRateDefinition($fromCode, 1);
        $to = new CurrencyRateDefinition($toCode, 1);
        $key = $this->getKey($from, $to);

        if (!isset($this->conversionMap[$key])) {
            throw new \InvalidArgumentException("Unsupported conversion: $key!");
        }

        return $this->round($this->conversionMap[$key] * $amount);
    }
}
