<?php
/**
 * Handles all of the formatting onto pages, such as safe strings.
 *
 * @copyright   2012 Christopher Hill <cjhill@gmail.com>
 * @author      Christopher Hill <cjhill@gmail.com>
 * @since       15/09/2012
 */
class Core_Format
{
	/**
	 * Make sure that anything outputted to the browser is safe.
	 *
	 * @access public
	 * @param  string $string
	 * @return string
	 * @static
	 */
	public static function safeHtml($string) {
		return htmlentities($string, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Provides a single access point for numbers.
	 *
	 * @access public
	 * @param  int    $number
     * @param  int    $decimals
     * @param  string $decimalPoint
     * @param  string $thousandsSeparator
	 * @return string
     * @static
	 */
	public static function number($number, $decimals = 0, $decimalPoint = '.', $thousandsSeparator = ',') {
		return number_format($number, $decimals, $decimalPoint, $thousandsSeparator);
	}
}