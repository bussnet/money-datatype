<?php
/**
 * User: thorsten
 * Date: 22.07.16
 * Time: 16:30
 */

namespace Tests\Bnet\Money;

use Bnet\Money\MoneyGross;
use Bnet\Money\MoneyNet;
use Bnet\Money\TaxedMoney;

/**
 * Class TaxedMoneyTest - extens MoneyTest, so all Tests for MoneyTest have to work for TaxedMoney 
 * @package Tests\Bnet\Money
 */
class TaxedMoneyTest extends MoneyTest {
	
	/**
	 * @param $amount
	 * @param null $currency
	 * @param int $tax
	 * @param int $input_type
	 * @param int $default_return_type
	 * @return TaxedMoney
	 */
	public function money($amount, $currency = null, $tax = 0, $input_type = TaxedMoney::TYPE_NET, $default_return_type = TaxedMoney::TYPE_GROSS) {
		return new TaxedMoney($amount, $currency, $tax, $input_type, $default_return_type);
	}

	public function testTaxCalculation() {
		// Net -> Gross DEFAULT
		$m = $this->money(123456, 'EUR', 19);
		$this->assertEquals(123456, $m->amountWithoutTax(), 'without tax (no calculaction)');
		$this->assertEquals(146913, $m->amountWithTax(), 'with tax');
		$this->assertEquals(146913, $m->amount(), 'with tax (auto detect)');

		// Gross -> Gross
		$m = $this->money(123456, 'EUR', 19, TaxedMoney::TYPE_GROSS);
		$this->assertEquals(103745, $m->amountWithoutTax(), 'without tax');
		$this->assertEquals(123456, $m->amountWithTax(), 'with tax (no calculaction)');
		$this->assertEquals(123456, $m->amount(), 'with tax (auto detect)');

		// Net -> Net
		$m = $this->money(123456, 'EUR', 19, TaxedMoney::TYPE_NET, TaxedMoney::TYPE_NET);
		$this->assertEquals(123456, $m->amountWithoutTax(), 'without tax (no calculaction)');
		$this->assertEquals(146913, $m->amountWithTax(), 'with tax');
		$this->assertEquals(123456, $m->amount(), 'with tax (auto detect)');

		// Gross -> Net
		$m = $this->money(123456, 'EUR', 19, TaxedMoney::TYPE_GROSS, TaxedMoney::TYPE_NET);
		$this->assertEquals(103745, $m->amountWithoutTax(), 'without tax');
		$this->assertEquals(123456, $m->amountWithTax(), 'with tax (no calculaction)');
		$this->assertEquals(103745, $m->amount(), 'with tax (auto detect)');
	}

	public function testAliasFunctions() {
		$amount = 12345;
		$tax = 19;
		$currency = 'EUR';
		$this->assertTrue(TaxedMoney::fromGross($amount, $tax, $currency)->equals(TaxedMoney::fromBrutto($amount, $tax, $currency)), 'gross/brutto equals');
		$this->assertTrue(TaxedMoney::fromNet($amount, $tax, $currency)->equals(TaxedMoney::fromNetto($amount, $tax, $currency)), 'net/netto equals');
	}

	public function testPreciseCaluclations() {
		$m = $this->money(123456, 'EUR', 19.99, TaxedMoney::TYPE_GROSS, TaxedMoney::TYPE_NET);
		$this->assertEquals(102888.57404783732, $m->amountWithoutTax(10), 'without tax');
		$this->assertEquals(102888.57404783732, $m->amount(10), 'without tax (auto detect)');

		$m = $this->money(123456, 'EUR', 19.99999, TaxedMoney::TYPE_NET, TaxedMoney::TYPE_GROSS);
		$this->assertEquals(148147.1876544, $m->amountWithTax(10), 'with tax');
		$this->assertEquals(148147.1876544, $m->amount(10), 'with tax (auto detect)');
	}

	public function testMoneyGrossClass() {
		// Net -> Gross DEFAULT
		$m = MoneyGross::fromNet(123456, 19, 'EUR');
		$this->assertEquals(123456, $m->amountWithoutTax(), 'without tax (no calculaction)');
		$this->assertEquals(146913, $m->amountWithTax(), 'with tax');
		$this->assertEquals(146913, $m->amount(), 'with tax (auto detect)');

		// Gross -> Gross
		$m = MoneyGross::fromGross(123456, 19, 'EUR');
		$this->assertEquals(103745, $m->amountWithoutTax(), 'without tax');
		$this->assertEquals(123456, $m->amountWithTax(), 'with tax (no calculaction)');
		$this->assertEquals(123456, $m->amount(), 'with tax (auto detect)');

	}

	public function testMoneyNetClass() {
		// Net -> Net
		$m = MoneyNet::fromNet(123456, 19, 'EUR');
		$this->assertEquals(123456, $m->amountWithoutTax(), 'without tax (no calculaction)');
		$this->assertEquals(146913, $m->amountWithTax(), 'with tax');
		$this->assertEquals(123456, $m->amount(), 'with tax (auto detect)');

		// Gross -> Net
		$m = MoneyNet::fromGross(123456, 19, 'EUR');
		$this->assertEquals(103745, $m->amountWithoutTax(), 'without tax');
		$this->assertEquals(123456, $m->amountWithTax(), 'with tax (no calculaction)');
		$this->assertEquals(103745, $m->amount(), 'with tax (auto detect)');

	}

	public function testExamplePageCode() {
		$m = MoneyGross::fromNet(1000, 19, 'EUR');
		// return the net: 10EUR
		$this->assertEquals(1000, $m->amountWithoutTax());
		// return the gross: 11.90EUR
		$this->assertEquals(1190, $m->amount());
		$this->assertEquals(1190, $m->amountWithTax());
	}

	public function testMultiplication() {
		$m1 = MoneyGross::fromNet(150, 10);
		$m2 = MoneyGross::fromNet(10, 10);
		$this->assertEquals($m1, $m2->multiply(15));
		$this->assertNotEquals($m1, $m2->multiply(10));
	}

	public function testDivision() {
		$m1 = $this->money(3, new \Bnet\Money\Currency('EUR'));
		$m2 = $this->money(10, new \Bnet\Money\Currency('EUR'));
		$this->assertEquals($m1, $m2->divide(3));
		$this->assertNotEquals($m1, $m2->divide(2));
	}

}
