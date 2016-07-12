<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 21:26
 */

namespace Bnet\Money\Repositories;


use Bnet\Money\Repositories\Exception\CurrencyNotFoundException;

interface CurrencyRepositoryInterface {

	/**
	 * load the attributes for this currency and return it
	 * @param string $code the iso alphanumeric currency code
	 * @return array attributes for this currency
	 * @throws CurrencyNotFoundException
	 */
	public function get($code);

	/**
	 * check if the currency exists
	 * @param string $code the iso alphanumeric currency code
	 * @return bool currency exists or not
	 */
	public function has($code);

}