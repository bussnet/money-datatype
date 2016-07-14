<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 20:55
 */

namespace Tests\Bnet\Money;


use Bnet\Money\Currency;

class CurrencyTest extends \PHPUnit_Framework_TestCase {


	public function testManualAttributes() {
		$a = [
			'code' => 'EUR',
			'iso' => 123,
			'name' => 'Euro',
			'symbol_left' => '',
			'symbol_right' => '€',
			'decimal_place' => 2,
			'decimal_mark' => ',',
			'thousands_separator' => '.',
			'unit_factor' => 100
		];
		$c = new Currency('EUR', $a);

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
	 *
	 */
	public function testFactoryMethods() {
		$this->assertEquals(Currency::EUR(), new Currency('EUR'));
		$this->assertEquals(Currency::USD(), new Currency('USD'));
	}

	/**
	 * @expectedException \Bnet\Money\Repositories\Exception\CurrencyNotFoundException
	 */
	public function testCantInstantiateUnknownCurrency() {
		new Currency('unknown');
	}

	public function testComparison() {
		$c1 = new Currency('EUR');
		$c2 = new Currency('USD');
		$this->assertTrue($c1->equals(new Currency('EUR')));
		$this->assertTrue($c2->equals(new Currency('USD')));
		$this->assertFalse($c1->equals($c2));
		$this->assertFalse($c2->equals($c1));
	}

	public function testGetters() {
		$c = new Currency('EUR');
		$this->assertEquals('EUR', $c->code, 'code');
		$this->assertEquals('Euro', $c->name, 'name');
		$this->assertEquals(2, $c->decimal_place, 'decimal_place');
		$this->assertEquals(100, $c->unit_factor, 'unit_factor');
		$this->assertEquals('€', $c->symbol_left, 'symbol_left');
		$this->assertEquals('', $c->symbol_right, 'symbol_right');
		$this->assertEquals(',', $c->decimal_mark, 'decimal_mark');
		$this->assertEquals('.', $c->thousands_separator, 'thousands_separator');
	}

	public function testToString() {
		$this->assertEquals('EUR (Euro)', (string)new Currency('EUR'));
		$this->assertEquals('USD (U.S. Dollar)', (string)new Currency('USD'));
	}
}
