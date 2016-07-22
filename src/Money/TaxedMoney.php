<?php
/**
 * User: thorsten
 * Date: 22.07.16
 * Time: 15:57
 */

namespace Bnet\Money;


class TaxedMoney extends Money {

	/**
	 * amount is gross/Brutto
	 */
	const TYPE_GROSS = 1;

	/**
	 * amount is net/Netto
	 */
	const TYPE_NET = 2;

	/**
	 * @var float|int the tax percentage for amount
	 */
	protected $tax;

	/**
	 * @var self::TYPE_GROSS|self::TYPE_NET which type is the amount field
	 */
	protected $amount_type;

	/**
	 * @var self::TYPE_GROSS|self::TYPE_NET which type is returned as amount() for default
	 */
	protected $default_return_type;

	/**
	 * Money constructor.
	 * TaxedMoney constructor.
	 * @param int $amount
	 * @param Currency|string $currency
	 * @param float|int $tax
	 * @param int $input_type
	 * @param int $default_return_type
	 * @throws MoneyException
	 */
	public function __construct($amount, $currency = null, $tax = 0, $input_type = self::TYPE_NET, $default_return_type = self::TYPE_GROSS) {
		$this->tax = $tax;
		$this->amount_type = $input_type;
		$this->default_return_type = $default_return_type;
		parent::__construct($amount, $currency);
	}

	/**
	 * alias for fromGross
	 * @param int $amount
	 * @param float $tax tax percentage of the given amount
	 * @param Currency|string $currency
	 * @return static
	 */
	public static function fromBrutto($amount, $tax, $currency=null) {
		return static::fromGross($amount, $tax, $currency);
	}

	/**
	 * create a MoneyObject with the given Amount/Tax as Gross
	 * @param int $amount
	 * @param float $tax tax percentage of the given amount
	 * @param Currency|string $currency
	 * @return static
	 */
	public static function fromGross($amount, $tax, $currency = null) {
		return new static($amount, $currency, $tax, self::TYPE_GROSS);
	}

	/**
	 * alias for fromNet
	 * @param int $amount
	 * @param float $tax tax percentage of the given amount
	 * @param Currency|string $currency
	 * @return static
	 */
	public static function fromNetto($amount, $tax, $currency = null) {
		return static::fromNet($amount, $tax, $currency);
	}

	/**
	 * create a MoneyObject with the given Amount/Tax as Net
	 * @param int $amount
	 * @param float $tax tax percentage of the given amount
	 * @param Currency|string $currency
	 * @return static
	 */
	public static function fromNet($amount, $tax, $currency = null) {
		return new static($amount, $currency, $tax, self::TYPE_NET);
	}

	/**
	 * has this MoneyObj tax options
	 * @return bool
	 */
	public function hasTax() {
		return true;
	}

	/**
	 * return the gross/net amount as defined in $this->default_return_type
	 * @param int $precision the number of precision positions for better calucations with the amount
	 * @return int
	 */
	public function amount($precision = 0) {
		if ($this->amount_type == $this->default_return_type) {
			$amount = parent::amount();
		} elseif ($this->amount_type == self::TYPE_NET) {
			$amount = $this->amountWithTax($precision);
		} elseif ($this->amount_type == self::TYPE_GROSS) {
			$amount = $this->amountWithoutTax($precision);
		} else {
			throw new MoneyException('Problems with defined types in TaxedMoney');
		}
		// cast to int if the precision is 0 for internal calculations that need and int
		return $precision == 0
			?(int)$amount
			: $amount;
	}

	/**
	 * return the amount with tax
	 * @param int $precision the number of precision positions for better calucations with the amount
	 * @return float|int
	 */
	public function amountWithTax($precision=0) {
		return $this->amount_type == self::TYPE_GROSS
			? $this->amount
			: $this->calcAddTax($this->amount, $precision);
	}

	/**
	 * return the amount without tax
	 * @param int $precision the number of precision positions for better calucations with the amount
	 * @return float|int
	 */
	public function amountWithoutTax($precision=0) {
		return $this->amount_type == self::TYPE_NET
			? $this->amount
			: $this->calcSubTax($this->amount, $precision);
	}

	/**
	 * subtract the percentage of tax from the given amount
	 * @param float $amount
	 * @param int $precision the number of precision positions for better calucations with the amount
	 * @return float|int int if precision=0
	 */
	protected function calcSubTax($amount, $precision=0) {
		return $this->round($amount / (1 + $this->tax/100), $precision);
	}

	/**
	 * add the percentage of tax to the given amount
	 * @param float|int $amount
	 * @param int $precision the number of precision positions for better calucations with the amount
	 * @return float|int int if precision=0
	 */
	protected function calcAddTax($amount, $precision=0) {
		return $this->round($amount * (1 + $this->tax/100), $precision);
	}

	/**
	 * round the given float value from calulations as int
	 * @param float $amount
	 * @param int $precision the number of precision positions for better calucations with the amount
	 * @return float|int
	 */
	protected function round($amount, $precision=0) {
		return round($amount, $precision);
	}
}