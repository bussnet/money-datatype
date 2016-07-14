<?php
/**
 * User: thorsten
 * Date: 08.07.16
 * Time: 14:05
 */

namespace Tests\Bnet\Money\Repositories;


use Bnet\Money\Currency;
use Bnet\Money\Repositories\CallbackRepository;
use Bnet\Money\Repositories\Exception\CurrencyRepositoryException;

class CallbackRepositoryTest extends \PHPUnit_Framework_TestCase {


	public function testClosureWork() {
		$r = new CallbackRepository(function($code) {
			if ($code !== 'EUR')
				throw new CurrencyRepositoryException;
			return ['name' => 'TestCurrency'];
		});
		$this->assertEquals('TestCurrency', $r->get('EUR')['name'], 'Loading successfull');
	}

	public function testWithCurrency() {
		$r = new CallbackRepository(function($code) {
			if ($code !== 'EUR')
				throw new CurrencyRepositoryException;
			return ['name' => 'TestCurrency'];
		});

		Currency::registerCurrencyRepository($r);
		$c = new Currency('EUR');

		$this->assertEquals('TestCurrency', $c->name, 'Name matches');
	}
}
