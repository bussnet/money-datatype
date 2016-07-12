<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 23:03
 */

namespace Bnet\Money\Repositories;


use Bnet\Money\Repositories\Exception\CurrencyNotFoundException;

class CachedCallbackRepository extends CallbackRepository {

	/**
	 * @var string prefix for cache key
	 */
	protected $cache_prefix = 'bnet.currency.';

	/**
	 * load the attributes for this currency and return it
	 * @param string $code the iso alphanumeric currency code
	 * @return array attributes for this currency
	 * @throws CurrencyNotFoundException
	 */
	public function get($code) {
		$code = strtoupper($code);
		$this->assertCurrency($code);
		return \Cache::rememberForever($this->cache_prefix . $code, function () use ($code) {
			return parent::get($code);
		});
	}

	/**
	 * check if the currency exists
	 * @param string $code the iso alphanumeric currency code
	 * @return bool currency exists or not
	 */
	public function has($code) {
		$code = strtoupper($code);
		return \Cache::has($this->cache_prefix . $code) || parent::has($code);
	}

}