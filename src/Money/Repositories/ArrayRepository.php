<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 21:18
 */

namespace Bnet\Money\Repositories;


use Bnet\Money\Repositories\Exception\CurrencyNotFoundException;

class ArrayRepository implements CurrencyRepositoryInterface {

	/**
	 * @var array List of currencies with alpha Iso Code as Key
	 */
	protected $currencies;

	/**
	 * ArrayRepository constructor.
	 * @param array $currencies
	 */
	public function __construct(array $currencies) {
		foreach ($currencies as $currency) {
			$this->currencies[$currency['code']] = $currency;
		}
	}


	/**
	 * load the attributes for this currency and return it
	 * @param string $code the iso alphanumeric currency code
	 * @return array attributes for this currency
	 * @throws CurrencyNotFoundException
	 */
	public function get($code) {
		$code = strtoupper($code);
		$this->assertCurrency($code);
		return $this->currencies[$code];
	}

	/**
	 * check if the currency exists
	 * @param string $code the iso alphanumeric currency code
	 * @return bool currency exists or not
	 */
	public function has($code) {
		return array_key_exists(strtoupper($code), $this->currencies);
	}

	/**
	 * @param $code
	 * @throws CurrencyNotFoundException
	 */
	protected function assertCurrency($code) {
		if (!$this->has($code))
			throw new CurrencyNotFoundException(static::class.": Currency $code not found");
	}

}