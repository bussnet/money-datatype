<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 20:30
 */

namespace Tests\Bnet\Money;


use Bnet\Money\Currency;
use Bnet\Money\Money;
use Bnet\Money\MoneyException;
use Bnet\Money\Repositories\ArrayRepository;

class MoneyTest extends \PHPUnit_Framework_TestCase {


	/**
	 * @param $amount
	 * @param null $currency
	 * @return Money
	 */
	public function money($amount, $currency = null) {
		return new Money($amount, $currency);
	}

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();
		$r = new ArrayRepository([
			'EUR' => [
				'code' => 'EUR',
				'iso' => 978,
				'name' => 'Euro',
				'symbol_left' => '',
				'symbol_right' => '€',
				'decimal_place' => 2,
				'decimal_mark' => ',',
				'thousands_separator' => '.',
				'unit_factor' => 100
			],
			'USD' => [
				'code' => 'USD',
				'decimal_place' => 2,
				'decimal_mark' => '.',
				'thousands_separator' => ',',
			]
		]);
		Currency::registerCurrencyRepository($r);
	}

	public function testBasicFunctions() {
		$amount = 123456;
		$m = $this->money($amount, $this->currency());
		$this->assertEquals($amount, $m->amount(), 'Amount');
		$this->assertEquals($amount, $m->value(), 'Amount');
		$this->assertEquals("1234.56", $m->normalize(), 'Normalize');
		$this->assertEquals('1234,56€', $m->format(), 'default Format');
	}

	protected function currency() {
		$c = new Currency('EUR', [
			'code' => 'EUR',
			'iso' => 978,
			'name' => 'Euro',
			'symbol_left' => '',
			'symbol_right' => '€',
			'decimal_place' => 2,
			'decimal_mark' => ',',
			'thousands_separator' => '.',
			'unit_factor' => 100
		]);
		return $c;
	}

	public function testFormat() {
		$amount = 123456;
		$m = $this->money($amount, $this->currency());

		$this->assertEquals('1234,56€', $m->format(), 'default Format');
		$this->assertEquals('1.234,56€', $m->format(true), 'Format +thPt');

		$this->assertEquals('€1234,56', $m->format(false, false, true), 'Format Swap');
		$this->assertEquals('€1.234,56', $m->format(true, false, true), 'Format +thPt Swap');

		$this->assertEquals('1234,56 EUR', $m->format(false, true), 'Format code');
		$this->assertEquals('1.234,56 EUR', $m->format(true, true), 'Format code +thPt');

		$this->assertEquals('EUR 1234,56', $m->format(false, true, true), 'Format code swap');
		$this->assertEquals('EUR 1.234,56', $m->format(true, true, true), 'Format code swap +thPt');

	}

	public function testHtml() {
		$amount = 123456;
		$m = $this->money($amount, $this->currency());

		$this->assertEquals('<span class="money currency_eur"><span class="amount">1234,56</span><span class="symbol">€</span></span>', $m->html(), 'default Html');
		$this->assertEquals('<span class="money currency_eur"><span class="amount">1.234,56</span><span class="symbol">€</span></span>', $m->html(true), 'Html +thPt');

		$this->assertEquals('<span class="money currency_eur"><span class="symbol">€</span><span class="amount">1234,56</span></span>', $m->html(false, false, true), 'Html Swap');
		$this->assertEquals('<span class="money currency_eur"><span class="symbol">€</span><span class="amount">1.234,56</span></span>', $m->html(true, false, true), 'Html +thPt Swap');

		$this->assertEquals('<span class="money currency_eur"><span class="amount">1234,56</span> <span class="code">EUR</span></span>', $m->html(false, true), 'Html code');
		$this->assertEquals('<span class="money currency_eur"><span class="amount">1.234,56</span> <span class="code">EUR</span></span>', $m->html(true, true), 'Html code +thPt');

		$this->assertEquals('<span class="money currency_eur"><span class="code">EUR</span> <span class="amount">1234,56</span></span>', $m->html(false, true, true), 'Html code swap');
		$this->assertEquals('<span class="money currency_eur"><span class="code">EUR</span> <span class="amount">1.234,56</span></span>', $m->html(true, true, true), 'Html code swap +thPt');

	}

	public function testNoNumber() {
		try {
			$this->money(11.1, $this->currency());
			$this->fail('No Exception on float');
		} catch (MoneyException $e) { }

		try {
			$this->money('11.1', $this->currency());
			$this->fail('No Exception on float-string');
		} catch (MoneyException $e) { }
	}

	public static function provideStringsMoneyParsing() {
		return array(
			array("1000", 100000),
			array("1000.0", 100000),
			array("1000.00", 100000),
			array("1000.1", 100010),
			array("1000.11", 100011),
			array("1000,0", 100000),
			array("1000,00", 100000),
			array("1000,1", 100010),
			array("1000,11", 100011),
			array("1.000,11", 100011),
			array("1.000.11", 100011),
			array("1,000,11", 100011),
			array("1,000.11", 100011),
			array("0.01", 1),
			array("0,01", 1),
			array("1", 100),
			array("-1000", -100000),
			array("-1000.0", -100000),
			array("-1000.00", -100000),
			array("-0.01", -1),
			array("-1000,0", -100000),
			array("-1000,00", -100000),
			array("-0,01", -1),
			array("-1", -100),
			array("+1000", 100000),
			array("+1000.0", 100000),
			array("+1000.00", 100000),
			array("+0.01", 1),
			array("+1000,0", 100000),
			array("+1000,00", 100000),
			array("+0,01", 1),
			array("+1", 100)
		);
	}

	/**
	 * test parsing of money strings
	 * @param $string
	 * @param $units
	 * @dataProvider provideStringsMoneyParsing
	 */
	public function testMoneyParsing($string, $units) {
		$m = $this->money($units);
		try {
			$this->assertEquals($m->value(), Money::parse($string)->value(), 'Value: ' . $string);
		} catch (\Exception $e) {
			$this->fail('Exception on Value: ' . $string . ' -> ' . $e->getMessage());
		}
	}


	public function testFactoryMethods() {
		$this->assertEquals(Money::EUR(25), Money::EUR(10)->add(Money::EUR(15)));
		$this->assertEquals(Money::USD(25), Money::USD(10)->add(Money::USD(15)));
	}

	/**
	 * @expectedException \Bnet\Money\MoneyException
	 */
	public function testStringThrowsException() {
		$this->money('foo', new Currency('EUR'));
	}

	public function testGetters() {
		$m = $this->money(100, new Currency('EUR'));
		$this->assertEquals(100, $m->amount());
		$this->assertEquals(1, $m->normalize());
		$this->assertEquals(new Currency('EUR'), $m->currency());
	}

	public function testSameCurrency() {
		$m = $this->money(100, new Currency('EUR'));
		$this->assertTrue($m->isSameCurrency($this->money(100, new Currency('EUR'))));
		$this->assertFalse($m->isSameCurrency($this->money(100, new Currency('USD'))));
	}

	public function testComparison() {
		$m1 = $this->money(50, new Currency('EUR'));
		$m2 = $this->money(100, new Currency('EUR'));
		$m3 = $this->money(200, new Currency('EUR'));
		$this->assertEquals(-1, $m2->compare($m3));
		$this->assertEquals(1, $m2->compare($m1));
		$this->assertEquals(0, $m2->compare($m2));
		$this->assertTrue($m2->equals($m2));
		$this->assertFalse($m3->equals($m2));
		$this->assertTrue($m3->greaterThan($m2));
		$this->assertFalse($m2->greaterThan($m3));
		$this->assertTrue($m2->greaterThanOrEqual($m2));
		$this->assertFalse($m2->greaterThanOrEqual($m3));
		$this->assertTrue($m2->lessThan($m3));
		$this->assertFalse($m3->lessThan($m2));
		$this->assertTrue($m2->lessThanOrEqual($m2));
		$this->assertFalse($m3->lessThanOrEqual($m2));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testDifferentCurrenciesCannotBeCompared() {
		$m1 = $this->money(100, new Currency('EUR'));
		$m2 = $this->money(100, new Currency('USD'));
		$m1->compare($m2);
	}

	public function testAddition() {
		$m1 = $this->money(1100101, new Currency('EUR'));
		$m2 = $this->money(1100021, new Currency('EUR'));
		$sum = $m1->add($m2);
		$this->assertEquals($this->money(2200122, new Currency('EUR')), $sum);
		$this->assertNotEquals($sum, $m1);
		$this->assertNotEquals($sum, $m2);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testDifferentCurrenciesCannotBeAdded() {
		$m1 = $this->money(100, new Currency('EUR'));
		$m2 = $this->money(100, new Currency('USD'));
		$m1->add($m2);
	}

	public function testSubtraction() {
		$m1 = $this->money(10010, new Currency('EUR'));
		$m2 = $this->money(10002, new Currency('EUR'));
		$diff = $m1->subtract($m2);
		$this->assertEquals($this->money(8, new Currency('EUR')), $diff);
		$this->assertNotSame($diff, $m1);
		$this->assertNotSame($diff, $m2);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testDifferentCurrenciesCannotBeSubtracted() {
		$m1 = $this->money(100, new Currency('EUR'));
		$m2 = $this->money(100, new Currency('USD'));
		$m1->subtract($m2);
	}

	public function testMultiplication() {
		$m1 = $this->money(15, new Currency('EUR'));
		$m2 = $this->money(1, new Currency('EUR'));
		$this->assertEquals($m1, $m2->multiply(15));
		$this->assertNotEquals($m1, $m2->multiply(10));
	}

	public function testDivision() {
		$m1 = $this->money(3, new Currency('EUR'));
		$m2 = $this->money(10, new Currency('EUR'));
		$this->assertEquals($m1, $m2->divide(3));
		$this->assertNotEquals($m1, $m2->divide(2));
	}

	public function testAllocation() {
		$m1 = $this->money(100, new Currency('EUR'));
		list($part1, $part2, $part3) = $m1->allocate([1, 1, 1]);
		$this->assertEquals($this->money(34, new Currency('EUR')), $part1);
		$this->assertEquals($this->money(33, new Currency('EUR')), $part2);
		$this->assertEquals($this->money(33, new Currency('EUR')), $part3);
		$m2 = $this->money(101, new Currency('EUR'));
		list($part1, $part2, $part3) = $m2->allocate([1, 1, 1]);
		$this->assertEquals($this->money(34, new Currency('EUR')), $part1);
		$this->assertEquals($this->money(34, new Currency('EUR')), $part2);
		$this->assertEquals($this->money(33, new Currency('EUR')), $part3);
	}

	public function testAllocationOrderIsImportant() {
		$m = $this->money(5, new Currency('EUR'));
		list($part1, $part2) = $m->allocate([3, 7]);
		$this->assertEquals($this->money(2, new Currency('EUR')), $part1);
		$this->assertEquals($this->money(3, new Currency('EUR')), $part2);
		list($part1, $part2) = $m->allocate([7, 3]);
		$this->assertEquals($this->money(4, new Currency('EUR')), $part1);
		$this->assertEquals($this->money(1, new Currency('EUR')), $part2);
	}

	public function testComparators() {
		$m1 = $this->money(0, new Currency('EUR'));
		$m2 = $this->money(-1, new Currency('EUR'));
		$m3 = $this->money(1, new Currency('EUR'));
		$m4 = $this->money(1, new Currency('EUR'));
		$m5 = $this->money(1, new Currency('EUR'));
		$m6 = $this->money(-1, new Currency('EUR'));
		$this->assertTrue($m1->isZero());
		$this->assertTrue($m2->isNegative());
		$this->assertTrue($m3->isPositive());
		$this->assertFalse($m4->isZero());
		$this->assertFalse($m5->isNegative());
		$this->assertFalse($m6->isPositive());
	}
	 
}
