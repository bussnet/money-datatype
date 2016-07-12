<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 20:55
 */

namespace Tests\Bnet\Money;


class CurrencyTest extends \PHPUnit_Framework_TestCase {


	public function testManualAttributes() {
		$a = [
			'code' => 'EUR',
			'iso_numeric' => 123,
			'name' => 'Euro',
			'symbol_left' => '',
			'symbol_right' => '€',
			'decimal_place' => 2,
			'decimal_mark' => ',',
			'thousands_separator' => '.',
			'unit_factor' => 100
		];
		$c = new \Bnet\Money\Currency('EUR', $a);

		$this->assertEquals($a['code'], $c->code, 'code');
		$this->assertEquals($a['iso'], $c->iso, 'iso');
		$this->assertEquals($a['name'], $c->name, 'name');
		$this->assertEquals($a['symbol_left'], $c->symbol_left, 'symbol_left');
		$this->assertEquals($a['symbol_right'], $c->symbol_right, 'symbol_right');
		$this->assertEquals($a['decimal_place'], $c->decimal_place, 'decimal_place');
		$this->assertEquals($a['decimal_mark'], $c->decimal_mark, 'decimal_mark');
		$this->assertEquals($a['thousands_separator'], $c->thousands_separator, 'thousands_separator');
		$this->assertEquals($a['unit_factor'], $c->unit_factor, 'unit_factor');
	}

	/**
	 * @expectedException \Bnet\Money\Repositories\Exception\CurrencyRepositoryException
	 */
	public function testNotFoundRepository() {
		new \Bnet\Money\Currency('EUR');
	}


}
