# PHP MoneyDataType - use Money as that what it is - an amount with currency

A simple Money Datatype Implementation

## Main Features

* Contains amount AND currency as package
* calculate with the amount
* format with the currency
* parse string to money datatype


##INSTALLATION

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

	"require": {
		"bussnet/money-datatype": "*"
	}

	then run:  $ composer update

or execute

    composer require "bussnet/money-datatype"

## Usage

### Manual Currency Attributes 
```php

	$a = [
		'code' => 'EUR',
		'iso' => 978,
		'name' => 'Euro',
		'symbol_left' => '',
		'symbol_right' => '€',
		'decimal_place' => 2,
		'decimal_mark' => ',',
		'thousands_separator' => '.',
		'unit_factor' => 100
	];
	$c = new \Bnet\Money\Currency('EUR', $a);

```

### Currency with ArrayRepository 
```php

	$repository = new ArrayRepository([
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
	\Bnet\Money\Currency::registerCurrencyRepository($repository);

	$c = new \Bnet\Money\Currency('EUR');

```

### Currency with CallbackRepository 
```php

	$repository = new \Bnet\Money\Repositories\CallbackRepository(function($code) {
		// request the data from Database and return it 
	});
	\Bnet\Money\Currency::registerCurrencyRepository($repository);

	$c = new \Bnet\Money\Currency('EUR');

```

### Use the Money Object 
```php

	// assign own currency
	$m = new Money(123456, 'EUR');
	$m = new Money(123456, new Currency('EUR'));
	
	// use systemwide default currency
	$m = new Money(123456);

	// int 123456
	$m->amount();
	
	// float 1234.56
	$m->normalize();
	
	// 1234,56€
	$m->format();

	// 1.234,56€ (with thousand mark)
	$m->format(true);
	
	// 1.234,56 EUR (with thousand mark ans code instead of sign)
	$m->format(true, true);
	
	// EUR 1.234,56 (with thousand mark ans code instead of sign swap before/after part)
	$m->format(true, true, true);
	
	// 1234,56€ with html spans arround the parts
	$m->html(/* use the same params as format() above */);

	// return [
    // 	  'amount' => $this->amount(),
    // 	  'number' => $this->normalize(),
    // 	  'format' => $this->format(),
    // 	  'currency' => $this->currency->code,
    // ];
    $m->toArray();
	
	// toArray() as JsonString
	$m->toJson();
		
	// alias for format()
	$m->render();
	
	// parse Moneystrings -> 123456 cents
	$m = Money::parse('1.234,56');
	// +/- sign before is allowed, currency sign is not allowed
	// the first ./, is interpreted as thousand mark, the second as decimal makr - more than two are not allowed

```

### Calucations/Checks with the Money Object 
```php

	// bool - same currency
	$m->isSameCurrency(Money $otherMoney);
	
	// 0=equal, 1=$m>$o, -1=$m<$o
	$m->compare(Money $o);

	// no explanation needed
	$m->equals(Money $other);
	$m->greaterThan(Money $o);
	$m->greaterThanOrEqual(Money $o);
	$m->lessThan(Money $o);
	$m->lessThanOrEqual(Money $o);
	$m->isZero();
	$m->isPositive();
	$m->isNegative();
	
	// ALL MATH OPERATIONS ARE ASSERTING THE SAME CURRENCY !!!
	
	// add amount and return new obj
	$m->add(Money $o);
	
	// substruct amount and return new obj
	$m->subtract(Money $o);
	
	// multiply amount with multiplier and return new obj
	$m->multiply($multiplier);
	
	// divide amount by divisor and return new obj
	$m->divide($divisor, $roundingMode = PHP_ROUND_HALF_UP);
	
	// allocate the amount in count($ratios) parts with 
	// the weight of the valiue of $ratios and return Money[]
	$m->allocate(array $ratios);
		
	// has this MoneyObj TaxCalculation (TaxedMoney)
	$m->hasTax();
	
```


### Use the TaxedMoney Object
 
The **TaxedMoney** Class contains a amount (gross OR net) and a tax percentage.
On creation you define if the given amount is net or gross and if the default return value
(for amount() and calucations) is net or gross.

There are MoneyGross and MoneyNet as simpler representations for a TaxedMoney Object
with the static methods ::fromGross() and ::fromNet().

Examples:
```php

	use Bnet\Money\MoneyNet;
	use Bnet\Money\MoneyGross;
	
	// 10EUR is the Net and 19% Tax
	$m = MoneyGross::fromNet(1000, 19, 'EUR');
	// return the net: 1000 -> 10EUR
	$m->amountWithoutTax();

	// both return the gross: 1190 -> 11,90EUR
	$m->amount();
	$m->amountWithTax();

	// all calucaltions with this object are with the **gross**,
	// cause this is the default amount
	// so you can replace any Money Obj ex.: in a Cart, with a TaxedMoneyObj
	// and check with the hasTax() method if you can show the Net/Gross

```

### Use the CurrencyUpdater
 
Download and parse a CurrencyFile and save it with a Closure

Examples:
```php

		$updater = new \Bnet\Money\Updater\CurrencyUpdater();

		$updater->update_currency_table(function($item) use($db) {
			$db->save($item);
		});

```

## Changelog

**0.1.4
- add CurrencyUpdater to download and parse a file and save the data with a Closure 
**0.1.3
- add TaxedMoney for use MoneyObject with Tax 
**0.1.2
- add some math and compare methods to Money and Currency
- add __toString, toArray, toJson methods
- add static Calling of Currency
**0.1.1
- add default currency repository for easier usage in small environments
**0.1.0
- create the basic functionality, prepared with Array and Callback CurrencyRepository

## License

The MoneyDatatype Package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Disclaimer

THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR, OR ANY OF THE CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
