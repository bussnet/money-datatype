<?php
/**
 * User: thorsten
 * Date: 22.07.16
 * Time: 21:48
 */

namespace Bnet\Money\Updater;


use Bnet\Money\Updater\Exception\CurrencyUpdaterException;

class CurrencyUpdater {
	
	const DEFAULT_CURRENCY_FILE = 'https://raw.github.com/RubyMoney/money/master/config/currency_iso.json';

	protected $file;

	/**
	 * CurrencyUpdater constructor.
	 * @param null $file
	 */
	public function __construct($file=null) {
		$this->file = $file ?: self::DEFAULT_CURRENCY_FILE;
	}


	/**
	 * Update the currencyTable from a file/url with a specific parser
	 * @param \Closure $itemParser
	 * @param \Closure $fileParser
	 * @return bool
	 */
	public function update_currency_table($itemSaver, \Closure $itemParser = null, \Closure $fileParser = null) {
		$file = $this->file;
		$fileParser = @$fileParser ?: function ($file) {
			return json_decode(file_get_contents($file), true);
		};

		$err = array();

		$arr = $fileParser($file);
		foreach ($arr as $item) {
			try {
				if (is_callable($itemParser))
					$itemParser($item, $itemSaver);
				else
					$this->parseItem($item, $itemSaver);
			} catch (CurrencyUpdaterException $e) {
				if ($e->getPrevious())
					$err[$item['iso_code']] = get_class($e->getPrevious()) . '::' . $e->getPrevious()->getMessage();
				else
					$err[$item['iso_code']] = get_class($e) . '::' . $e->getMessage();
			}
		}

		// return errorList or true if no errors
		return $err ?: true;
	}


	/**
	 * Read Currency Item and parse it with the $parser
	 * parse an item with the given parser and return the result of the $parser
	 * @param array $raw_item
	 * @param \Closure $saver
	 * @return mixed
	 * @throws CurrencyUpdaterException
	 */
	protected function parseItem($raw_item, \Closure $saver) {
		/* $raw_item
		"eur": {
			"priority": 2,
			"iso_code": "EUR",
			"name": "Euro",
			"symbol": "€",
			"alternate_symbols": [],
			"subunit": "Cent",
			"subunit_to_unit": 100,
			"symbol_first": true,
			"html_entity": "&#x20AC;",
			"decimal_mark": ",",
			"thousands_separator": ".",
			"iso_numeric": "978",
			"smallest_denomination": 1
		},
		 */
		$item = [
			'code' => strtoupper($raw_item['iso_code']),
			'iso' => $raw_item['iso_numeric'],
			'name' => $raw_item['name'],
			'symbol_left' => '',
			'symbol_right' => '',
			'decimal_mark' => $raw_item['decimal_mark'],
			'thousands_separator' => $raw_item['thousands_separator'],
			'unit_factor' => $raw_item['subunit_to_unit']
		];

		if ($raw_item['symbol_first']) {
			$item['symbol_left'] = $raw_item['symbol'];
		} else {
			$item['symbol_right'] = $raw_item['symbol'];
		}

		// calculate the decimal places
		$item['decimal_place'] = strlen(preg_replace('/1/', '', $raw_item['subunit_to_unit']));

		try {
			$ret = $saver($item);
		} catch (\Exception $e) {
			if ($e instanceof CurrencyUpdaterException)
				throw new CurrencyUpdaterException("", 0, $e);
		}
		return $ret;
	}


}