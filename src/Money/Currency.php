<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 12:14
 */

namespace Bnet\Money;


use Bnet\Money\Repositories\CurrencyRepositoryInterface;
use Bnet\Money\Repositories\Exception\CurrencyRepositoryException;

class Currency {

	public static $default_currency = 'EUR';

	/**
	 * @var string iso code
	 */
	public $code;

	/**
	 * @var int numeric iso code
	 */
	public $iso;

	public $name;
	public $symbol_left;
	public $symbol_right;
	public $decimal_place;
	public $decimal_mark;
	public $thousands_separator;

	/**
	 * @var float the value relative to USD to convert amounts
	 */
	public $value;

	public $unit_factor = 100;


	/**
	 * @var CurrencyRepositoryInterface
	 */
	protected static $repository;

	/**
	 * Currency constructor.
	 * @param string $code
	 * @param array $attributes
	 */
	public function __construct($code = null, array $attributes = []) {
		$this->code = @$code ?: self::$default_currency;
		$this->loadAttributes($this->code, $attributes);
	}

	/**
	 * register a repository for loading the currency attributes
	 * @param CurrencyRepositoryInterface $repo
	 */
	public static function registerCurrencyRepository(CurrencyRepositoryInterface $repo) {
		self::$repository = $repo;
	}

	/**
	 * @param $code
	 * @param $attributes
	 * @return mixed
	 */
	protected function loadAttributes($code, $attributes=[]) {
		if (empty($attributes)) {
			$this->assertRepository();
			$attributes = self::$repository->get($code);
		}
		foreach ($attributes as $k => $v) {
			if (property_exists(self::class, $k))
				$this->{$k} = $v;
		}
	}

	/**
	 * @return string
	 */
	public static function getDefaultCurrency() {
		return self::$default_currency;
	}

	/**
	 * @param string $currency
	 */
	public static function setDefaultCurrency($currency) {
		self::$default_currency = $currency;
	}

	/**
	 * @throws CurrencyRepositoryException
	 */
	protected function assertRepository() {
		if (!self::$repository instanceof CurrencyRepositoryInterface)
			throw new CurrencyRepositoryException;
	}

}