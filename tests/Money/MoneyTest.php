<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 20:30
 */

namespace Tests\Bnet\Money;


use Bnet\Money\Money;
use Bnet\Money\MoneyException;
use Bnet\Money\Repositories\ArrayRepository;

class MoneyTest extends \PHPUnit_Framework_TestCase {

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
		\Bnet\Money\Currency::registerCurrencyRepository($r);
	}

	public function testBasicFunctions() {
		$amount = 123456;
		$m = new Money($amount, $this->currency());
		$this->assertEquals($amount, $m->amount(), 'Amount');
		$this->assertEquals($amount, $m->value(), 'Amount');
		$this->assertEquals(1234.56, $m->normalize(), 'Normalize');
		$this->assertEquals('1234,56€', $m->format(), 'default Format');
	}

	protected function currency() {
		$c = new \Bnet\Money\Currency('EUR', [
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
		$m = new Money($amount, $this->currency());

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
		$m = new Money($amount, $this->currency());

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
			new Money(11.1, $this->currency());
			$this->fail('No Exception on float');
		} catch (MoneyException $e) { }

		try {
			new Money('11.1', $this->currency());
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
		$m = new Money($units);
		try {
			$this->assertEquals($m->value(), Money::parse($string)->value(), 'Value: ' . $string);
		} catch (\Exception $e) {
			$this->fail('Exception on Value: ' . $string . ' -> ' . $e->getMessage());
		}
	}

}
