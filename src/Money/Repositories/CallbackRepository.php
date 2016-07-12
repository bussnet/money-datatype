<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 22:05
 */

namespace Bnet\Money\Repositories;


use Bnet\Money\Repositories\Exception\CurrencyNotFoundException;
use Bnet\Money\Repositories\Exception\CurrencyRepositoryException;

class CallbackRepository extends ArrayRepository{

	/**
	 * @var \Closure
	 */
	protected $resource;

	/**
	 * CallbackRepository constructor.
	 * @param \Closure $resource
	 */
	public function __construct($resource) {
		$this->resource = $resource;
	}

	/**
	 * load the attributes for this currency and return it
	 * @param string $code the iso alphanumeric currency code
	 * @return array attributes for this currency
	 * @throws CurrencyNotFoundException
	 * @throws CurrencyRepositoryException
	 */
	public function get($code) {
		$code = strtoupper($code);
		if (is_callable($this->resource)) {
			$func = $this->resource;
			$attributes = $func($code);
		} else
			throw new CurrencyRepositoryException;
		if (empty($attributes))
			throw new CurrencyNotFoundException;
		return $attributes;
	}

	/**
	 * check if the currency exists
	 * @param string $code the iso alphanumeric currency code
	 * @return bool currency exists or not
	 */
	public function has($code) {
		try {
			$this->get($code);
			return true;
		} catch (CurrencyNotFoundException $e) {
			return false;
		}
	}

}