# Parameter

> 🔔 Subscribe to the [newsletter](https://chv.to/chevere-newsletter) to don't miss any update regarding Chevere.

![Chevere](chevere.svg)

[![Build](https://img.shields.io/github/actions/workflow/status/chevere/parameter/test.yml?branch=1.0&style=flat-square)](https://github.com/chevere/parameter/actions)
![Code size](https://img.shields.io/github/languages/code-size/chevere/parameter?style=flat-square)
[![Apache-2.0](https://img.shields.io/github/license/chevere/parameter?style=flat-square)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-blueviolet?style=flat-square)](https://phpstan.org/)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchevere%2Fparameter%2F1.0)](https://dashboard.stryker-mutator.io/reports/github.com/chevere/parameter/1.0)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=alert_status)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=security_rating)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=coverage)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![CodeFactor](https://www.codefactor.io/repository/github/chevere/parameter/badge)](https://www.codefactor.io/repository/github/chevere/parameter)

## Summary

Parameter is a user-land typed layer abstraction around parameter-argument. Its goal is to provide *more* validation rules for PHP and is intended to be used in I/O validation systems.

Parameter enables to validate **any** type of arguments for **any** kind of data-structure.

## Quick start

Install with [Composer](https://packagist.org/packages/chevere/parameter).

```sh
composer require chevere/parameter
```

## Cookbook

`namespace Chevere\Parameter`

The beauty™ of this package is that all PHP types have been abstracted into its own "type system" and you can mix them to form any data-structure.

The following examples should give you a glimpse.

### Inline validation

It refers to classic on-site validation, it enables to go from this:

```php
if($var > 10 || $var < 1) {
    throw new InvalidArgumentException();
}
```

To this:

```php
int(min: 1, max: 10)($var);
```

To use inline-validation invoke a parameter with the value you need to validate.

* Validate string starting with "a":

```php
use function Chevere\Parameter\string;

$value = 'ahhh';
string('/^a.+/')($value);
```

* Validate an int of min value `100`:

```php
use function Chevere\Parameter\int;

$value = 100;
int(min: 100)($value);
```

* Validate an int accept list:

```php
use function Chevere\Parameter\int;

$value = 1;
int(accept: [1, 2, 3])($value);
```

* Validate a float reject list:

```php
use function Chevere\Parameter\float;

$value = 3.1;
float(reject: [1.1, 2.1])($value);
```

* Validate an array:

```php
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;
use function Chevere\Parameter\string;

$value = [
    'id' => 1,
    'name' => 'Pepe'
];
arrayp(
    id: int(min: 1),
    name: string('/^[A-Z]{1}\w+$/')
)($value);
```

* Validate a generic `int` list:

```php
use function Chevere\Parameter\int;
use function Chevere\Parameter\generic;

$value = [1, 2, 3];
generic(int())($value);
```

* Validate a generic int list with string key type rules:

```php
use function Chevere\Parameter\int;
use function Chevere\Parameter\generic;

$value = [
    'unila' => 1,
    'dorila' => 2,
    'tirifila' => 3,
];
generic(
    K: string('/ila$/'),
    V: int(min: 1)
)($value);
```

* Validate an union of type ?int:

```php
use function Chevere\Parameter\int;
use function Chevere\Parameter\null;

$value = 1;
union(int(), null())($value);
```

### Attribute-based inline parameter validation

Use attributes on the function/method parameters to define validation rules. Use `valid()` on the function body to trigger validation on all parameters. Optionally pass the parameter name for single argument validation.

* Validate an string enum for `Hugo`, `Paco`, `Luis`:
* Validate a min float value of `1000`:

```php
use Chevere\Parameter\Attributes\EnumAttr;
use function Chevere\Parameter\validate;

function myEnum(
    #[EnumAttr('Hugo', 'Paco', 'Luis')]
    string $name,
    #[FloatAttr(min: 1000)]
    float $money
): void
{
    valid();
    // Or single...
    valid('name');
    valid('money');
}
$arg1 = 'Paco';
$arg2 = 1000.50;
myEnum($arg1, $arg2);
```

* Validate an int of any value but `0` and `100`:

```php
use Chevere\Parameter\Attributes\IntAttr;
use function Chevere\Parameter\validate;

function myInt(
    #[IntAttr(reject: [0, 100])]
    int $id
): void
{
    valid();
}
$value = 50;
myInt($value);
```

* Validate a ~~nasty~~ nested array:

```php
use Chevere\Parameter\Attributes\ArrayAttr;
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\StringAttr;
use Chevere\Parameter\Attributes\GenericAttr;
use function Chevere\Parameter\validate;

function myArray(
    #[ArrayAttr(
        id: new IntAttr(min: 1),
        role: new ArrayAttr(
            mask: new IntAttr(accept: [64, 128, 256]),
            name: new StringAttr('/[a-z]+/'),
            tenants: new GenericAttr(
                new IntAttr(min: 1)
            )
        ),
    )]
    array $spooky
): void
{
    valid();
}
$value = [
    'id' => 10,
    'role' => [
        'mask' => 128,
        'name' => 'admin',
        'tenants' => [1, 2, 3, 4, 5]
    ],
];
myArray($value);
```

* Validate a generic int list:

```php
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\GenericAttr;
use function Chevere\Parameter\validate;

function myGeneric(
    #[GenericAttr(
        new IntAttr(),
    )]
    array $list
): void
{
    valid();
}
```

### Attribute-based inline return validation

Use attribute `ReturnAttr` on the function/method in combination with `validReturn($value)` on the function body. When omitting `ReturnAttr` the method `public static function return(): ParameterInterface` will be used to determine return validation rules (if present).

* Validate int [min: 0, max: 5] return:

```php
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use function Chevere\Parameter\validReturn;

#[ReturnAttr(
    new IntAttr(min: 0, max: 5)
)]
public function myReturnInt(): int
{
    $value = 1;

    return validReturn($value);
}
```

* Validate array members return:

```php
use Chevere\Parameter\Attributes\ArrayAttr;
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\StringAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use function Chevere\Parameter\validReturn;

#[ReturnAttr(
    new ArrayAttr(
        id: new IntAttr(min: 0),
        name: new StringAttr()
    )
)]
public function myReturnArray(): array
{
    $value = [
        'id' => 1,
        'name' => 'Peoples Hernandez'
    ];

    return validReturn($value);
}
```

### Attribute-based delegated validation

`namespace Chevere\Parameter`

When working with reiterative interfaces you may want to delegate validation on the *caller* and not directly in the function body. This will save you time as validation wraps your function I/O, you only need to worry about validation rules and wire the thing.

This works with `ReflectionFunction` and `ReflectionMethod`.

Use function `reflectionToParameters()` to get the parameters with rules for validating arguments. Use function `reflectionToReturnParameter()` to get the parameter rules for validating return value.

* Validate anon class method arguments:

```php
use ReflectionMethod;
use Chevere\Parameter\Attributes\IntAttr;
use function Chevere\Parameter\arguments;

$class = new class() {
    public function wea(
        #[IntAttr(accept: [1, 10, 100])]
        int $base
    ): void {
    }
};
$reflection = new ReflectionMethod($class, 'wea');
$parameters = reflectionToParameters($reflection);
$object = new $class();
$object->wea(0); // nothing happens...
$parameters(base: 0); // Validates!
```

```php
use ReflectionFunction;
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;

$function =
    #[ReturnAttr(
        new IntAttr(min: 1000)
    )]
    function (int $base): int {
        return 10 * $base;
    };
$reflection = new ReflectionFunction($function);
$return = reflectionToReturnParameter($reflection);
$function(10); // nothing happens...
$return($function(10)); // Validates!
```

## Function reference

Following functions enables to quickly spawn any parameter type.

| Type   | Function     |
| ------ | ------------ |
| string | `string()`   |
| string | `enum()`     |
| string | `date()`     |
| string | `time()`     |
| string | `datetime()` |
| int    | `int()`      |
| float  | `float()`    |
| bool   | `bool()`     |
| array  | `arrayp()`   |
| array  | `file()`     |
| array  | `generic()`  |
| null   | `null()`     |
| mixed  | `mixed()`    |
| *many* | `union()`    |

### Attributes

`namespace Chevere\Parameter\Attributes`

Following attributes enables to define validation rules for parameters.

| Type   | Attribute      |
| ------ | -------------- |
| string | `StringAttr`   |
| string | `EnumAttr`     |
| int    | `IntAttr`      |
| float  | `FloatAttr`    |
| bool   | `BoolAttr`     |
| array  | `ArrayAttr`    |
| array  | `GenericAttr`  |
| null   | `NullAttr`     |
| *      | `CallableAttr` |

The `CallableAttr` enables to forward parameter assignment to a callable returning `ParameterInterface`.

For `return` there's the `ReturnAttr` attribute:

```php
#[ReturnAttr(<TypeAttr>)]
```

## Documentation

Documentation is available at [chevere.org](https://chevere.org/).

## License

Copyright 2023 [Rodolfo Berrios A.](https://rodolfoberrios.com/)

Chevere is licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for the full license text.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
