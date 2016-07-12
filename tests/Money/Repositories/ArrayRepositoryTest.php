<?php
/**
 * User: thorsten
 * Date: 07.07.16
 * Time: 21:44
 */

namespace Tests\Bnet\Money\Repositories;


use Bnet\Money\Repositories\ArrayRepository;

class ArrayRepositoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \Bnet\Money\Repositories\CurrencyRepositoryInterface
	 */
	protected $repository;

	public function testGetCurrency() {
		$a = $this->repository->get('EUR');
		$this->assertEquals(978, $a['iso']);
	}

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();
		$this->repository = new ArrayRepository([
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
			]
		]);
	}


	public function testHasCurrency() {
		$this->assertTrue($this->repository->has('EUR'), 'Uppercase');
		$this->assertTrue($this->repository->has('eur'), 'Lowercase');
		$this->assertFalse($this->repository->has('USD'), 'NotFound');
	}

	/**
	 * @expectedException \Bnet\Money\Repositories\Exception\CurrencyNotFoundException
	 */
	public function testAssertCurrency() {
		$this->repository->get('USD');
	}


}
