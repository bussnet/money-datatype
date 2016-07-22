<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 12:12
 */

namespace Bnet\Money;


use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;

/**
 * Class Money
 * @package Bnet\Money
 * 
 * @method static Money EUR(int $amount)
 * @method static Money USD(int $amount)
 */
class Money implements \JsonSerializable, Jsonable{

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
		if (!is_int($amount) && !ctype_digit($amount)) {
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
		return new static((int)$units, $currency);
	}

	/**
	 * @return Currency
	 */
	public function currency() {
		return $this->currency;
	}

	/**
	 * @param Money $money
	 * @return bool
	 */
	public function isSameCurrency(Money $money) {
		return $this->currency()->equals($money->currency());
	}

	/**
	 * assertSameCurrency.
	 *
	 * @param Money $other
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function assertSameCurrency(self $other) {
		if (!$this->isSameCurrency($other)) {
			throw new \InvalidArgumentException('Different currencies "' . $this->currency . '" and "' . $other->currency() . '"');
		}
	}

	/**
	 * compare.
	 *
	 * @param Money $other
	 *
	 * @return int
	 *
	 * @throws \InvalidArgumentException
	 */
	public function compare(self $other) {
		$this->assertSameCurrency($other);
		if ($this->amount < $other->amount) {
			return -1;
		}
		if ($this->amount > $other->amount) {
			return 1;
		}
		return 0;
	}

	/**
	 * equals.
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function equals(self $other) {
		return $this->compare($other) == 0;
	}

	/**
	 * greaterThan.
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function greaterThan(self $other) {
		return $this->compare($other) == 1;
	}

	/**
	 * greaterThanOrEqual.
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function greaterThanOrEqual(self $other) {
		return $this->compare($other) >= 0;
	}

	/**
	 * lessThan.
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function lessThan(self $other) {
		return $this->compare($other) == -1;
	}

	/**
	 * lessThanOrEqual.
	 *
	 * @param Money $other
	 *
	 * @return bool
	 */
	public function lessThanOrEqual(self $other) {
		return $this->compare($other) <= 0;
	}

	/**
	 * add.
	 *
	 * @param Money $addend
	 *
	 * @return Money
	 *
	 * @throws \InvalidArgumentException
	 */
	public function add(self $addend) {
		$this->assertSameCurrency($addend);
		return new self($this->amount + $addend->amount, $this->currency);
	}

	/**
	 * subtract.
	 *
	 * @param Money $subtrahend
	 *
	 * @return Money
	 *
	 * @throws \InvalidArgumentException
	 */
	public function subtract(self $subtrahend) {
		$this->assertSameCurrency($subtrahend);
		return new self($this->amount - $subtrahend->amount, $this->currency);
	}

	/**
	 * multiply.
	 *
	 * @param int|float $multiplier
	 * @param int $roundingMode
	 *
	 * @return Money
	 *
	 * @throws \InvalidArgumentException
	 * @throws \OutOfBoundsException
	 */
	public function multiply($multiplier, $roundingMode = PHP_ROUND_HALF_UP) {
		return new self((int)round($this->amount * $multiplier, 0, $roundingMode), $this->currency);
	}

	/**
	 * assertOperand.
	 *
	 * @param int|float $operand
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function assertOperand($operand) {
		if (!is_int($operand) && !is_float($operand)) {
			throw new \InvalidArgumentException('Operand "' . $operand . '" should be an integer or a float');
		}
	}

	/**
	 * divide.
	 *
	 * @param int|float $divisor
	 * @param int $roundingMode
	 *
	 * @return Money
	 *
	 * @throws \InvalidArgumentException
	 * @throws \OutOfBoundsException
	 */
	public function divide($divisor, $roundingMode = PHP_ROUND_HALF_UP) {
		$this->assertOperand($divisor);
		if ($divisor == 0) {
			throw new \InvalidArgumentException('Division by zero');
		}
		return new self((int)round($this->amount / $divisor, 0, $roundingMode), $this->currency);
	}

	/**
	 * allocate.
	 *
	 * @param array $ratios
	 *
	 * @return array
	 */
	public function allocate(array $ratios) {
		$remainder = $this->amount;
		$results = [];
		$total = array_sum($ratios);
		foreach ($ratios as $ratio) {
			$share = (int)floor($this->amount * $ratio / $total);
			$results[] = new self($share, $this->currency);
			$remainder -= $share;
		}
		for ($i = 0; $remainder > 0; $i++) {
			$results[$i]->amount++;
			$remainder--;
		}
		return $results;
	}

	/**
	 * isZero.
	 *
	 * @return bool
	 */
	public function isZero() {
		return $this->amount == 0;
	}

	/**
	 * isPositive.
	 *
	 * @return bool
	 */
	public function isPositive() {
		return $this->amount > 0;
	}

	/**
	 * isNegative.
	 *
	 * @return bool
	 */
	public function isNegative() {
		return $this->amount < 0;
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'amount' => $this->amount,
			'number' => $this->normalize(),
			'format' => $this->format(),
			'currency' => $this->currency->code,
		];
	}

	/**
	 * Convert the object to its JSON representation.
	 *
	 * @param  int $options
	 * @return string
	 */
	public function toJson($options = 0) {
		return json_encode($this->toArray(), $options);
	}

	/**
	 * jsonSerialize.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * Get the evaluated contents of the object.
	 *
	 * @return string
	 */
	public function render() {
		return $this->format();
	}

	/**
	 * __toString.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * __callStatic.
	 *
	 * @param string $method
	 * @param array $arguments
	 *
	 * @return Money
	 */
	public static function __callStatic($method, array $arguments) {
		$convert = (isset($arguments[1]) && is_bool($arguments[1])) ? (bool)$arguments[1] : false;
		return new static($arguments[0], new Currency($method), $convert);
	}

}