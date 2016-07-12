<?php
/**
 * User: thorsten
 * Date: 08.07.16
 * Time: 14:05
 */

namespace Tests\Bnet\Money\Repositories;


class CallbackRepositoryTest extends \PHPUnit_Framework_TestCase {


	public function testClosureWork() {
		$r = new \Bnet\Money\Repositories\CallbackRepository(function($code) {
			if ($code !== 'EUR')
				throw new \Bnet\Money\Repositories\Exception\CurrencyRepositoryException;
			return ['name' => 'TestCurrency'];
		});
		$this->assertEquals('TestCurrency', $r->get('EUR')['name'], 'Loading successfull');
	}

	public function testWithCurrency() {
		$r = new \Bnet\Money\Repositories\CallbackRepository(function($code) {
			if ($code !== 'EUR')
				throw new \Bnet\Money\Repositories\Exception\CurrencyRepositoryException;
			return ['name' => 'TestCurrency'];
		});

		\Bnet\Money\Currency::registerCurrencyRepository($r);
		$c = new \Bnet\Money\Currency('EUR');

		$this->assertEquals('TestCurrency', $c->name, 'Name matches');
	}
}
