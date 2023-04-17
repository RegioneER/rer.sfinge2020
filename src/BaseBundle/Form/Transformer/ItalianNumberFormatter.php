<?php

namespace BaseBundle\Form\Transformer;

use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;
use Symfony\Component\Intl\Exception\MethodArgumentNotImplementedException;
use Symfony\Component\Intl\Exception\MethodArgumentValueNotImplementedException;
use Symfony\Component\Intl\Globals\IntlGlobals;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Intl\Locale\Locale;

/**
 * Replacement for PHP's native {@link \NumberFormatter} class.
 *
 * The only methods currently supported in this class are:
 *
 *  - {@link __construct}
 *  - {@link create}
 *  - {@link formatCurrency}
 *  - {@link format}
 *  - {@link getAttribute}
 *  - {@link getErrorCode}
 *  - {@link getErrorMessage}
 *  - {@link getLocale}
 *  - {@link parse}
 *  - {@link setAttribute}
 *
 * @author Eriksen Costa <eriksen.costa@infranology.com.br>
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @internal
 */
class ItalianNumberFormatter extends \Symfony\Component\Intl\NumberFormatter\NumberFormatter
{
	
    /**
     * @var int
     */
    protected $style;
	
    /**
     * The maximum values of the integer type in 32 bit platforms.
     *
     * @var array
     */
    protected static $int32Range = array(
        'positive' => 2147483647,
        'negative' => -2147483648,
    );

    /**
     * The maximum values of the integer type in 64 bit platforms.
     *
     * @var array
     */
    protected static $int64Range = array(
        'positive' => 9223372036854775807,
        'negative' => -9223372036854775808,
    );	

    /**
     * Parse a number.
     *
     * @param string $value    The value to parse
     * @param int    $type     Type of the formatting, one of the format type constants. NumberFormatter::TYPE_DOUBLE by default
     * @param int    $position Offset to begin the parsing on return this value will hold the offset at which the parsing ended
     *
     * @return bool|string The parsed value of false on error
     *
     * @see http://www.php.net/manual/en/numberformatter.parse.php
     */
    public function parse($value, $type = self::TYPE_DOUBLE, &$position = 0)
    {
        if ($type == self::TYPE_DEFAULT || $type == self::TYPE_CURRENCY) {
            trigger_error(__METHOD__.'(): Unsupported format type '.$type, \E_USER_WARNING);

            return false;
        }

        $groupSep = $this->getAttribute(self::GROUPING_USED) ? '.' : '';

        // Any string before the numeric value causes error in the parsing
        if (preg_match("/^-?(?:\.\d++|([\d{$groupSep}]++)(?:\,\d++)?)/", $value, $matches)) {
            $value = $matches[0];
            $position = strlen($value);
            if ($error = $groupSep && isset($matches[1]) && !preg_match('/^\d{1,3}+(?:(?:.\d{3})++|\d*+)$/', $matches[1])) {
                $position -= strlen(preg_replace('/^\d{1,3}+(?:(?:.\d++)++|\d*+)/', '', $matches[1]));
            }
        } else {
            $error = 1;
            $position = 0;
        }

        if ($error) {
            IntlGlobals::setError(IntlGlobals::U_PARSE_ERROR, 'Number parsing failed');
            $this->errorCode = IntlGlobals::getErrorCode();
            $this->errorMessage = IntlGlobals::getErrorMessage();

            return false;
        }

        $value = str_replace('.', '', $value);
		$value = str_replace(',', '.', $value);
        $value = $this->convertValueDataType($value, $type);

        // behave like the intl extension
        $this->resetError();

        return $value;
    }
	
    /**
     * Format a number.
     *
     * @param number $value The value to format
     * @param int    $type  Type of the formatting, one of the format type constants.
     *                      Only type NumberFormatter::TYPE_DEFAULT is currently supported.
     *
     * @return bool|string The formatted value or false on error
     *
     * @see http://www.php.net/manual/en/numberformatter.format.php
     *
     * @throws NotImplementedException                    If the method is called with the class $style 'CURRENCY'
     * @throws MethodArgumentValueNotImplementedException If the $type is different than TYPE_DEFAULT
     */
    public function format($value, $type = self::TYPE_DEFAULT)
    {
        // The original NumberFormatter does not support this format type
        if ($type == self::TYPE_CURRENCY) {
            trigger_error(__METHOD__.'(): Unsupported format type '.$type, \E_USER_WARNING);

            return false;
        }

        if ($this->style == self::CURRENCY) {
            throw new NotImplementedException(sprintf(
                '%s() method does not support the formatting of currencies (instance with CURRENCY style). %s',
                __METHOD__, NotImplementedException::INTL_INSTALL_MESSAGE
            ));
        }

        // Only the default type is supported.
        if ($type != self::TYPE_DEFAULT) {
            throw new MethodArgumentValueNotImplementedException(__METHOD__, 'type', $type, 'Only TYPE_DEFAULT is supported');
        }

        $fractionDigits = $this->getAttribute(self::FRACTION_DIGITS);

        $value = $this->round($value, $fractionDigits);
        $value = $this->formatNumber($value, $fractionDigits);

        // behave like the intl extension
        $this->resetError();

        return $value;
    }	

    /**
     * Formats a number.
     *
     * @param int|float $value     The numeric value to format
     * @param int       $precision The number of decimal digits to use
     *
     * @return string The formatted number
     */
    protected function formatNumber($value, $precision)
    {
		
		$temp = $this->getUninitializedPrecision($value, $precision);
		
        $precision = $temp < $precision ? $precision : $temp;

        return number_format($value, $precision, ',', $this->getAttribute(self::GROUPING_USED) ? '.' : '');
    }
	
	/**
     * Rounds a value.
     *
     * @param int|float $value     The value to round
     * @param int       $precision The number of decimal digits to round to
     *
     * @return int|float The rounded value
     */
    protected function round($value, $precision)
    {
        $precision = $this->getUninitializedPrecision($value, $precision);

        $roundingModeAttribute = $this->getAttribute(self::ROUNDING_MODE);
        if (isset(self::$phpRoundingMap[$roundingModeAttribute])) {
            $value = round($value, $precision, self::$phpRoundingMap[$roundingModeAttribute]);
        } elseif (isset(self::$customRoundingList[$roundingModeAttribute])) {
            $roundingCoef = pow(10, $precision);
            $value *= $roundingCoef;

            switch ($roundingModeAttribute) {
                case self::ROUND_CEILING:
                    $value = ceil($value);
                    break;
                case self::ROUND_FLOOR:
                    $value = floor($value);
                    break;
                case self::ROUND_UP:
                    $value = $value > 0 ? ceil($value) : floor($value);
                    break;
                case self::ROUND_DOWN:
                    $value = $value > 0 ? floor($value) : ceil($value);
                    break;
            }

            $value /= $roundingCoef;
        }

        return $value;
    }
	
	/**
     * Returns the precision value if the DECIMAL style is being used and the FRACTION_DIGITS attribute is uninitialized.
     *
     * @param int|float $value     The value to get the precision from if the FRACTION_DIGITS attribute is uninitialized
     * @param int       $precision The precision value to returns if the FRACTION_DIGITS attribute is initialized
     *
     * @return int The precision value
     */
    protected function getUninitializedPrecision($value, $precision)
    {
        if (self::CURRENCY == $this->style) {
            return $precision;
        }

        if (!$this->isInitializedAttribute(self::FRACTION_DIGITS)) {
            preg_match('/.*\.(.*)/', (string) $value, $digits);
            if (isset($digits[1])) {
                $precision = strlen($digits[1]);
            }
        }

        return $precision;
    }
	
	 /**
     * Check if the attribute is initialized (value set by client code).
     *
     * @param string $attr The attribute name
     *
     * @return bool true if the value was set by client, false otherwise
     */
    protected function isInitializedAttribute($attr)
    {
        return isset($this->initializedAttributes[$attr]);
    }
	
	/**
	  * Returns the numeric value using the $type to convert to the right data type.
     *
     * @param mixed $value The value to be converted
     * @param int   $type  The type to convert. Can be TYPE_DOUBLE (float) or TYPE_INT32 (int)
     *
     * @return int|float|false The converted value
     */
    protected function convertValueDataType($value, $type)
    {
        if (self::TYPE_DOUBLE == $type) {
            $value = (float) $value;
        } elseif (self::TYPE_INT32 == $type) {
            $value = $this->getInt32Value($value);
        } elseif (self::TYPE_INT64 == $type) {
            $value = $this->getInt64Value($value);
        }

        return $value;
    }

}
