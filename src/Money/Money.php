<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 12:12
 */

namespace Bnet\Money;


use Illuminate\Support\Str;

class Money {

	/**
	 * @var int cents of currency
	 */
	protected $amount;

	/**
	 * @var Currency
	 */
	protected $currency;

	/**
	 * Money constructor.
	 * @param int $amount
	 * @param Currency $currency
	 * @throws MoneyException
	 */
	public function __construct($amount, $currency=null) {
		if (intval($amount) != $amount) {
			throw new MoneyException('Amount must be an integer');
		}

		$this->amount = $amount;
		if (!$currency instanceof Currency)
			$currency = new Currency($currency);
		$this->currency = $currency;
	}


	public function value() {
		return $this->amount();
	}

	/**
	 * @return int
	 */
	public function amount() {
		return $this->amount;
	}

	/**
	 * amount as decimal with . as decPoint
	 * @return float
	 */
	public function normalize() {
		return bcdiv($this->amount(), $this->currency->unit_factor, $this->currency->decimal_place);
	}

	/**
	 * amount as decimal with localized dec and thousand points
	 * @param bool $with_thousand_point
	 * @return string
	 */
	public function localize($with_thousand_point=false) {
		$c = $this->currency;
		return number_format($this->normalize(), $c->decimal_place, $c->decimal_mark, $with_thousand_point?$c->thousands_separator:'');
	}

	/**
	 * amount as decimal with localized dec and thousand points with currency sign or code
	 * @param bool $with_thousand_point
	 * @param bool $code_instead_of_sign
	 * @param bool $swap_left_and_right
	 * @return string
	 */
	public function format($with_thousand_point = false, $code_instead_of_sign=false, $swap_left_and_right=false) {
		$c = $this->currency;
		$amount = $this->localize($with_thousand_point);

		if ($code_instead_of_sign)
			return $swap_left_and_right
				? $c->code . ' ' . $amount
				: $amount .' '.$c->code;
		return $swap_left_and_right
			? $c->symbol_right . $amount . $c->symbol_left
			: $c->symbol_left . $amount . $c->symbol_right;
	}

	/**
	 * amount as decimal with localized dec and thousand points with currency sign or code
	 * @param bool $with_thousand_point
	 * @param bool $code_instead_of_sign
	 * @param bool $swap_left_and_right
	 * @return string
	 */
	public function html($with_thousand_point = false, $code_instead_of_sign=false, $swap_left_and_right=false) {
		$c = $this->currency;

		// prepare parts with html wrapper
		$amount = '<span class="amount">'.$this->localize($with_thousand_point).'</span>';
		$code = '<span class="code">'.$c->code.'</span>';
		$left = empty($c->symbol_left)
			? ''
			: '<span class="symbol">'. $c->symbol_left .'</span>';
		$right = empty($c->symbol_right)
			? ''
			: '<span class="symbol">'. $c->symbol_right .'</span>';

		// code/symbol
		if ($code_instead_of_sign) {
			// check swap
			$html = $swap_left_and_right
				? $code . ' ' . $amount
				: $amount .' '.$code;
		} else {
			// check swap
			$html = $swap_left_and_right
				? $right . $amount . $left
				: $left . $amount . $right;
		}

		return '<span class="money currency_' . Str::lower($c->code) . '">'.$html.'</span>';
	}

	/**
	 * Parse a Moneystring
	 * @param string $money
	 * @param string|Currency $currency
	 * @return static
	 * @throws MoneyException
	 */
	public static function parse($money, $currency=null) {
		if (!is_string($money)) {
			throw new MoneyException('Formatted raw money should be string, e.g. $1.00');
		}
		if (!$currency instanceof Currency)
			$currency = new Currency($currency);

		$sign = "(?P<sign>[-\+])?";
		$digits1 = "(?P<digits1>\d*?)";
		$separator1 = '(?P<separator1>[.,])??';
		$digits2 = "(?P<digits2>\d*)";
		$separator2 = '(?P<separator2>[.,])?';
		$decimals = "(?P<decimal1>\d)?(?P<decimal2>\d)?";
		$pattern = '/^' . $sign . $digits1 . $separator1 .$digits2 . $separator2 . $decimals . '$/';
		if (!preg_match($pattern, trim($money), $matches)) {
			throw new MoneyException('The value could not be parsed as money');
		}
		$units = $matches['sign'] === '-' ? '-' : '';
		$units .= $matches['digits1']. $matches['digits2'];
		$units .= isset($matches['decimal1']) ? $matches['decimal1'] : '0';
		$units .= isset($matches['decimal2']) ? $matches['decimal2'] : '0';
		if ($matches['sign'] === '-') {
			$units = '-' . ltrim(substr($units, 1), '0');
		} else {
			$units = ltrim($units, '0');
		}
		if ($units === '' || $units === '-') {
			$units = '0';
		}
		return new static($units, $currency);
	}
}