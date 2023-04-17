<?php

namespace BaseBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;

/**
 * 
 */
class ImportoToStringTransformer extends NumberToLocalizedStringTransformer // implements DataTransformerInterface
{
    private $divisor;

    public function __construct($scale = 2, $grouping = true, $roundingMode = self::ROUND_HALF_UP, $divisor = 1)
    {
        if (null === $grouping) {
            $grouping = true;
        }

        if (null === $scale) {
            $scale = 2;
        }

        parent::__construct($scale, $grouping, $roundingMode);

        if (null === $divisor) {
            $divisor = 1;
        }

        $this->divisor = $divisor;
    }

    /**
     * Transforms a normalized format into a localized money string.
     *
     * @param int|float $value Normalized number
     *
     * @return string Localized money string.
     *
     * @throws TransformationFailedException If the given value is not numeric or
     *                                       if the value can not be transformed.
     */
    public function transform($value)
    {
        if (null !== $value) {
            if (!is_numeric($value)) {
                throw new TransformationFailedException('Expected a numeric.');
            }

            $value /= $this->divisor;
        }

        return parent::transform($value);
    }
	
    /**
     * Returns a preconfigured \NumberFormatter instance.
     *
     * @return \NumberFormatter
     */
    protected function getNumberFormatter()
    {
        $formatter = new ItalianNumberFormatter('en', \NumberFormatter::DECIMAL);

        if (null !== $this->precision) {
            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->precision);
            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, $this->roundingMode);
        }

        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, $this->grouping);

        return $formatter;
    }
	
    /**
     * Transforms a localized money string into a normalized format.
     *
     * @param string $value Localized money string
     *
     * @return int|float Normalized number
     *
     * @throws TransformationFailedException If the given value is not a string
     *                                       or if the value can not be transformed.
     */
    public function reverseTransform($value)
    {
        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        if ('' === $value) {
            return;
        }

        if ('NaN' === $value) {
            throw new TransformationFailedException('"NaN" is not a valid number');
        }

        $position = 0;
        $formatter = $this->getNumberFormatter();
        $groupSep = "."; //$formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
        $decSep = ","; //$formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        if ('.' !== $decSep && (!$this->grouping || '.' !== $groupSep)) {
            $value = str_replace('.', $decSep, $value);
        }

        if (',' !== $decSep && (!$this->grouping || ',' !== $groupSep)) {
            $value = str_replace(',', $decSep, $value);
        }

        $result = $formatter->parse($value, \NumberFormatter::TYPE_DOUBLE, $position);

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new TransformationFailedException($formatter->getErrorMessage());
        }

        if ($result >= PHP_INT_MAX || $result <= -PHP_INT_MAX) {
            throw new TransformationFailedException('I don\'t have a clear idea what infinity looks like');
        }

        if (false !== $encoding = mb_detect_encoding($value, null, true)) {
            $length = mb_strlen($value, $encoding);
            $remainder = mb_substr($value, $position, $length, $encoding);
        } else {
            $length = strlen($value);
            $remainder = substr($value, $position, $length);
        }

        // After parsing, position holds the index of the character where the
        // parsing stopped
        if ($position < $length) {
            // Check if there are unrecognized characters at the end of the
            // number (excluding whitespace characters)
            $remainder = trim($remainder, " \t\n\r\0\x0b\xc2\xa0");

            if ('' !== $remainder) {
                throw new TransformationFailedException(
                    sprintf('The number contains unrecognized characters: "%s"', $remainder)
                );
            }
        }

        // NumberFormatter::parse() does not round
        $value = $this->round($result);	

        if (null !== $value) {
            $value *= $this->divisor;
        }

        return $value;
    }

    /**
     * Rounds a number according to the configured scale and rounding mode.
     *
     * @param int|float $number A number.
     *
     * @return int|float The rounded number.
     */
    protected function round($number)
    {
        if (null !== $this->precision && null !== $this->roundingMode) {
            // shift number to maintain the correct scale during rounding
            $roundingCoef = pow(10, $this->precision);
            $number *= $roundingCoef;

            switch ($this->roundingMode) {
                case self::ROUND_CEILING:
                    $number = ceil($number);
                    break;
                case self::ROUND_FLOOR:
                    $number = floor($number);
                    break;
                case self::ROUND_UP:
                    $number = $number > 0 ? ceil($number) : floor($number);
                    break;
                case self::ROUND_DOWN:
                    $number = $number > 0 ? floor($number) : ceil($number);
                    break;
                case self::ROUND_HALF_EVEN:
                    $number = round($number, 0, PHP_ROUND_HALF_EVEN);
                    break;
                case self::ROUND_HALF_UP:
                    $number = round($number, 0, PHP_ROUND_HALF_UP);
                    break;
                case self::ROUND_HALF_DOWN:
                    $number = round($number, 0, PHP_ROUND_HALF_DOWN);
                    break;
            }

            $number /= $roundingCoef;
        }

        return $number;
    }	

}