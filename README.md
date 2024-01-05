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

![Parameter](.github/banner/parameter-logo.svg)

## Summary

Parameter is a library around parameter-argument which provides additional functionality with validation rules and schema introspection.

Parameter supports built-in types including scalar (bool, int, string, float), null, array, object, union composite type and both mixed and iterable type alias.

👉 Read [Parameter 1.0](#eee) author's introduction to this package.
💡 Check [chevere/action](https://github.com/chevere/action) for a higher-level abstraction around this package.

## Quick start

Install with [Composer](https://packagist.org/packages/chevere/parameter).

```sh
composer require chevere/parameter
```

Check the [function](#function-reference) and [attribute](#attribute-reference) reference.

## Inline use

Use [inline validation](#inline-validation) to go from this:

```php
if($var > 10 || $var < 1) {
    throw new InvalidArgumentException();
}
```

To this:

```php
use function \Chevere\Parameter\int;

int(min: 1, max: 10)($var);
```

### Attribute-based use

Use [attribute delegated validation](#attribute-delegated-validation) to go from this:

```php
function myFunction(int $var): string
{
    if($var > 10 || $var < 1) {
        throw new InvalidArgumentException();
    }
    $return = 'done ok';
    return preg_match('/ok$/', $return)
        ? $return
        : throw new InvalidArgumentException();
}
$result = myFunction($var);
```

To this:

```php
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use Chevere\Parameter\Attributes\StringAttr;
use function Chevere\Parameter\validated;

#[ReturnAttr(
    new StringAttr('/ok$/')
)]
function myFunction(
    #[IntAttr(min: 1, max: 10)]
    int $var
): string
{
    return 'done ok';
}
$result = validated('myFunction', $var);
```

Use `reflectionToParameters` and `reflectionToReturn` functions for manual validation:

```php
use ReflectionFunction;
use function Chevere\Parameter\reflectionToParameters;
use function Chevere\Parameter\reflectionToReturn;

$reflection = new ReflectionFunction('myFunction');
$parameters = reflectionToParameters($reflection);
$return = reflectionToReturn($reflection);
$parameters(...$args); // validate $args
$result = myFunction(...$args);
$return($result); // validate $result
```

Use [attribute inline validation](#attribute-inline-validation) for manual validation:

```php
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use Chevere\Parameter\Attributes\StringAttr;
use function Chevere\Parameter\valid;
use function Chevere\Parameter\returnAttr;

#[ReturnAttr(
    new StringAttr('/ok$/')
)]
function myFunction(
    #[IntAttr(min: 1, max: 10)]
    int $var
): string
{
    valid(); // validate args
    $return = 'ok';

    return returnAttr()($return); // validate return
}
```

## Reference

### Function reference

`namespace Chevere\Parameter`

Following functions are available to create types and pseudo-types.

| Type     | Function        | Arguments                                                                  |
| -------- | --------------- | -------------------------------------------------------------------------- |
| string   | `string()`      | description, regex, default                                                |
| string   | `intString()`   | description, default                                                       |
| string   | `boolString()`  | description, default                                                       |
| string   | `enum()`        | `string,`                                                                  |
| string   | `date()`        | description, default                                                       |
| string   | `time()`        | description, default                                                       |
| string   | `datetime()`    | description, default                                                       |
| int      | `int()`         | description, default, min, max, accept, reject                             |
| int      | `boolInt()`     | description, default                                                       |
| float    | `float()`       | description, default, min, max, accept, reject                             |
| bool     | `bool()`        | description, default                                                       |
| array    | `arrayp()`      | Named `ParameterInterface,`                                                |
| array    | `arrayString()` | Named `StringParameterInterface,`                                          |
| array    | `file()`        | error, name, type, tmp_name, size, contents                                |
| iterable | `iterable()`    | `V: ParameterInterface` (value), `K: ParameterInterface`(key), description |
| null     | `null()`        | description                                                                |
| mixed    | `mixed()`       | description                                                                |
| *many*   | `union()`       | `ParameterInterface,`                                                      |

### Attribute reference

Following attributes enables to define validation rules for **parameters**.

`namespace Chevere\Parameter\Attributes`

| Type     | Attribute      | Arguments                                                                  |
| -------- | -------------- | -------------------------------------------------------------------------- |
| string   | `StringAttr`   | description, regex                                                         |
| string   | `EnumAttr`     | `string,`                                                                  |
| int      | `IntAttr`      | description, min, max, accept, reject                                      |
| float    | `FloatAttr`    | description, min, max, accept, reject                                      |
| bool     | `BoolAttr`     | description                                                                |
| array    | `ArrayAttr`    | `ParameterAttributeInterface,`                                             |
| iterable | `IterableAttr` | `V: ParameterInterface` (value), `K: ParameterInterface`(key), description |
| null     | `NullAttr`     | description                                                                |
| *        | `CallableAttr` | callable                                                                   |

💡 `CallableAttr` enables to forward parameter assignment to a callable returning `ParameterInterface` (bypass attribute limitation).

For hinting **return** there's the `ReturnAttr` attribute, which takes any `ParameterAttributeInterface`:

```php
#[ReturnAttr(<ParameterAttributeInterface>)]
```

## Cookbook

### Inline validation

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

* Validate an iterable `int` list:

```php
use function Chevere\Parameter\int;
use function Chevere\Parameter\iterable;

$value = [1, 2, 3];
iterable(int())($value);
```

* Validate an iterable int list with string key type rules:

```php
use function Chevere\Parameter\int;
use function Chevere\Parameter\iterable;

$value = [
    'unila' => 1,
    'dorila' => 2,
    'tirifila' => 3,
];
iterable(
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

### Attribute delegated validation

This enables to delegate validation on the caller, not in the function body.

* Use function `validated()` to get a return validated against all rules.

```php
use function Chevere\Parameter\validated;

$result = validated('myFunction', $var);
```

* Use function `reflectionToParameters()` to get rules for validating arguments.

```php
use ReflectionMethod;
use Chevere\Parameter\Attributes\IntAttr;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\reflectionToParameters;

$class = new class() {
    public function wea(
        #[IntAttr(accept: [1, 10, 100])]
        int $base
    ): void {
    }
};
$object = new $class();
$reflection = new ReflectionMethod($object, 'wea');
$parameters = reflectionToParameters($reflection);
$args = ['base' => 10];
$parameters(...$args); // Validates args
$result = $object->wea(...$args);
```

* Use function `reflectionToReturn()` to get rules for validating function/method return value:

```php
use ReflectionFunction;
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use function Chevere\Parameter\reflectionToReturn;

$function =
    #[ReturnAttr(
        new IntAttr(min: 1000)
    )]
    function (int $base): int {
        return 10 * $base;
    };
$reflection = new ReflectionFunction($function);
$return = reflectionToReturn($reflection);
$base = 10;
$result = $function($base);
$result = $return($result); // Validates result
```

### Attribute inline validation

Use `valid()` on the function/method body to trigger validation for arguments.

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
use Chevere\Parameter\Attributes\IterableAttr;
use function Chevere\Parameter\validate;

function myArray(
    #[ArrayAttr(
        id: new IntAttr(min: 1),
        role: new ArrayAttr(
            mask: new IntAttr(accept: [64, 128, 256]),
            name: new StringAttr('/[a-z]+/'),
            tenants: new IterableAttr(
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

* Validate iterable int list:

```php
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\IterableAttr;
use function Chevere\Parameter\validate;

function myIterable(
    #[IterableAttr(
        new IntAttr(),
    )]
    array $list = [0,1,2]
): void
{
    valid();
}
```

Use function `validReturn($value)` on the function/method body. When omitting `ReturnAttr` the method `public static function return(): ParameterInterface` (if any) will be used to determine return validation rules.

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

## Documentation

Documentation is available at [chevere.org](https://chevere.org/).

## License

Copyright 2024 [Rodolfo Berrios A.](https://rodolfoberrios.com/)

Chevere is licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for the full license text.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
