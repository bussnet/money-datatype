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

### Money 
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
	
	// parse Moneystrings -> 123456 cents
	$m = Money::parse('1.234,56');
	// +/- sign before is allowed, currency sign is not allowed
	// the first ./, is interpreted as thousand mark, the second as decimal makr - more than two are not allowed

```


## Changelog

**0.1
- create the basic functionality, prepared with Array and Callback CurrencyRepository

## License

The MoneyDatatype Package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Disclaimer

THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR, OR ANY OF THE CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
