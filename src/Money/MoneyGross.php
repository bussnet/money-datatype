<?php
/**
 * User: thorsten
 * Date: 22.07.16
 * Time: 20:50
 */

namespace Bnet\Money;


class MoneyGross extends TaxedMoney{

	/**
	 * MoneyGross constructor.
	 * @param int $amount
	 * @param Currency|string $currency
	 * @param float|int $tax
	 * @param int $input_type
	 * @throws MoneyException
	 */
	public function __construct($amount, $currency, $tax, $input_type) {
		parent::__construct($amount, $currency, $tax, $input_type, self::TYPE_GROSS);
	}

}