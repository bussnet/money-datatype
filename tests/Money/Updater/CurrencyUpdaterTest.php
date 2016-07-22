<?php
/**
 * User: thorsten
 * Date: 22.07.16
 * Time: 23:00
 */

namespace Tests\Bnet\Money\Updater;


class CurrencyUpdaterTest extends \PHPUnit_Framework_TestCase {


	public function testCurrencyUpdaterDefaultJsonParser() {
		$tmpFile = '/tmp/currencyFile.json';
		$data = '{
	"eur": {
		"priority": 2,
		"iso_code": "EUR",
		"name": "Euro",
		"symbol": "€",
		"alternate_symbols": [

		],
		"subunit": "Cent",
		"subunit_to_unit": 100,
		"symbol_first": true,
		"html_entity": "7",
		"decimal_mark": ",",
		"thousands_separator": ".",
		"iso_numeric": "978",
		"smallest_denomination": 1
	}
}';
		file_put_contents($tmpFile, $data);
		$updater = new \Bnet\Money\Updater\CurrencyUpdater($tmpFile);

		$a = null;
		$updater->update_currency_table(function($item) use(&$a) {
			$a = $item;
		});

		$this->assertEquals('EUR', $a['code'], 'code');
		$this->assertEquals('978', $a['iso'], 'iso');
		$this->assertEquals('Euro', $a['name'], 'name');
		$this->assertEquals("€", $a['symbol_left'], 'symbol_left');
		$this->assertEquals("", $a['symbol_right'], 'symbol_right');
		$this->assertEquals(2, $a['decimal_place'], 'decimal_place');
		$this->assertEquals(',', $a['decimal_mark'], 'decimal_mark');
		$this->assertEquals('.', $a['thousands_separator'], 'thousands_separator');
		$this->assertEquals(100, $a['unit_factor'], 'unit_factor');

		@unlink($tmpFile);
	}

	public function testCurrencyUpdaterCustomParser() {
		$tmpFile = '/tmp/currencyFile';
		$data = 'T1:Test2';
		file_put_contents($tmpFile, $data);
		$updater = new \Bnet\Money\Updater\CurrencyUpdater($tmpFile);

		$a = null;
		$updater->update_currency_table(function($item) use(&$a) {
			$a = $item;
		}, function($item, $saver) {
			return $saver($item);
		}, function($file) {
			return [explode(':', file_get_contents($file))];
		});

		$this->assertEquals('T1', $a[0], 'first');
		$this->assertEquals('Test2', $a[1], 'second');

		@unlink($tmpFile);
	}
}
