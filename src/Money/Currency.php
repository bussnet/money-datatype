<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 12:14
 */

namespace Bnet\Money;


use Bnet\Money\Repositories\CurrencyRepositoryInterface;
use Bnet\Money\Repositories\Exception\CurrencyRepositoryException;

/**
 * Class Currency
 * @package Bnet\Money
 * @method static Currency EUR()
 * @method static Currency USD()
 */
class Currency {

	public static $default_currency = 'EUR';

	/**
	 * @var bool use a default repository with the main currencies if no repository is defined
	 */
	protected static $use_default_currency_repository = true;

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
	 * @param $code
	 * @param array $attributes
	 * @return static
	 */
	public function getInstance($code, array $attributes = []) {
		return new static($code, $attributes);
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
			if (self::$use_default_currency_repository && file_exists(__DIR__.'/../../currencies.php')) {
				self::registerCurrencyRepository(new Repositories\ArrayRepository(
					include(__DIR__ . '/../../currencies.php')
				));
			} else {
				throw new CurrencyRepositoryException;
			}
	}

	/**
	 * use a default repository with the main currencies if no repository is defined
	 * @param bool $use
	 * @return $this
	 * @internal param bool $use_default_currency_repository
	 */
	public static function useDefaultCurrencyRepository($use=true) {
		self::$use_default_currency_repository = $use;
	}

	/**
	 * equals.
	 *
	 * @param Currency $currency
	 *
	 * @return bool
	 */
	public function equals(self $currency) {
		return $this->code == $currency->code;
	}

	/**
	 * __callStatic.
	 *
	 * @param string $method
	 * @param array $arguments
	 *
	 * @return Currency
	 */
	public static function __callStatic($method, array $arguments) {
		return new static($method);
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray() {
		return [$this->code => [
				'iso' => $this->iso,
				'symbol_left' => $this->symbol_left,
				'symbol_right' => $this->symbol_right,
				'decimal_place' => $this->decimal_place,
				'decimal_mark' => $this->decimal_mark,
				'thousands_separator' => $this->thousands_separator,
				'unit_factor' => $this->unit_factor
			]
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
		return $this->code . ' (' . $this->name . ')';
	}

	/**
	 * __toString.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}
}