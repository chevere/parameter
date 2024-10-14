# Parameter

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

Parameter is a library around parameter-argument which provides additional functionality with validation rules and schema introspection.

## Installing

Parameter is available through [Packagist](https://packagist.org/packages/chevere/parameter) and the repository source is at [chevere/parameter](https://github.com/chevere/parameter).

```sh
composer require chevere/parameter
```

## What it does?

Parameter enables to spawn dynamic parameters of any type with extra rules.

For example, an integer of minimum value 10.

```php
use function Chevere\Parameter\int;

$int = int(min: 10);
$int($var); // exception if $var < 10
```

In function or method parameters you can use attributes to define validation rules for parameters and return value.

```php
use Chevere\Parameter\Attributes\FloatAttr;
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use function Chevere\Parameter\returnAttr;
use function Chevere\Parameter\validated;

#[ReturnAttr(
    new FloatAttr(min: 0, max: 2400)
)]
function wageWeekWA(
    #[IntAttr(min: 1628)]
    int $cents,
    #[FloatAttr(min: 0, max: 40)]
    float $hours
) {
    return $cents*$hours/100;
}
validated('wageWeekWA', $cents, $hours);
```

Validation can be triggered using `validated` (example above), [inline](#inline-usage) and/or [delegated](#attribute-delegated-validation) to a caller wrapper. Parameter provides helpers to access rules for both parameters and return value to ease wiring process.

Rules defined by each parameter provide a human-readable schema which allows to expose the validation criteria.

## How to use

Parameter provides an API which can be used to create parameters using functions and/or attributes. Parameter objects can be used directly in the logic while attributes requires a read step.

### Inline usage

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

### Attribute-based usage

Use attributes to define rules for parameters and return value.

Use [attribute delegated validation](#attribute-delegated-validation) with the `validated()` function to go from this:

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

Use `reflectionToParameters` and `reflectionToReturn` functions for manual validation for arguments and return value:

```php
use ReflectionFunction;
use function Chevere\Parameter\reflectionToParameters;
use function Chevere\Parameter\reflectionToReturn;

$reflection = new ReflectionFunction('myFunction');
$parameters = reflectionToParameters($reflection);
$return = reflectionToReturn($reflection);
$parameters(...$args); // valid $args
$result = myFunction(...$args); // myFunction call
$return($result); // valid $result
```

Use [attribute inline validation](#attribute-inline-validation) for manual validation within the function body:

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
    valid(); // valid $var
    $return = 'ok';

    return returnAttr()($return); // valid $return
}
```

### CallableAttr

Attributes in PHP only support expressions you can use on class constants. Is not possible to directly define dynamic parameters using attributes.

To avoid this limitation you can use `CallableAttr` attribute which enables to forward parameter resolution to a callable returning a `ParameterInterface` instance.

```php
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Attributes\CallableAttr;

function myCallable(): ParameterInterface
{
    return arrayp(
        email: string(),
    )->withOptional(
        name: string(),
    );
}

#[CallableAttr('myCallable')]
```

## Types

A Parameter is an object implementing `ParameterInterface`. Every Parameter can define a `description` and a `default` value, plus additional validation rules depending on the type.

A Parameter can be defined using functions and/or attributes, it takes same arguments for both.

When invoking a Parameter `$param('value')` it will trigger validation against the passed argument.

## String

Use function `string` to create a `StringParameter`. Pass a `regex` for string matching.

```php
use function Chevere\Parameter\string;

// Any string
$string = string();
// String matching bin-<digits>
$string = string('/^bin-[\d]+$/');
$string('bin-123');
```

Use `StringAttr` attribute to define a string parameter.

```php
use Chevere\Parameter\Attributes\StringAttr;

#[StringAttr('/^bin-[\d]+$/')]
```

## String pseudo-parameters

The following parameters are based on String.

### Enum string

Use function `enum` to create a `StringParameter` matching a list of strings.

```php
use function Chevere\Parameter\enum;

$enum = enum('on', 'off');
$enum('on');
$enum('off');
```

Use `EnumAttr` attribute to define an enum string parameter.

```php
use Chevere\Parameter\Attributes\EnumAttr;

#[EnumAttr('on', 'off')]
```

### Int string

Use function `intString` to create a `StringParameter` matching a string integers.

```php
use function Chevere\Parameter\intString;

$int = intString();
$int('100');
```

### Bool string

Use function `boolString` to create a `StringParameter` matching `0` and `1` strings.

```php
use function Chevere\Parameter\boolString;

$bool = boolString();
$bool('0');
$bool('1');
```

### Date string

Use function `date` to create a `StringParameter` matching `YYYY-MM-DD` strings.

```php
use function Chevere\Parameter\date;

$date = date();
$date('2021-01-01');
```

### Time string

Use function `time` to create a `StringParameter` matching `hh:mm:ss` strings.

```php
use function Chevere\Parameter\time;

$time = time();
$time('12:00:00');
```

### Datetime string

Use function `datetime` to create a `StringParameter` matching `YYYY-MM-DD hh:mm:ss` strings.

```php
use function Chevere\Parameter\datetime;

$datetime = datetime();
$datetime('2024-01-09 10:53:00');
```

## Int

Use function `int` to create a `IntParameter`. Pass `min` and `max` values for integer range, `accept` for a list of accepted integers and `reject` for a list of rejected integers.

```php
use function Chevere\Parameter\int;

// Any int
$int = int();
$int(1);
// Integer between 0 and 100
$int = int(min: 0, max: 100);
$int(50);
// Integer matching 1, 2 or 3
$int = int(accept: [1, 2, 3]);
$int(2);
// Integer not-matching 1, 2 or 3
$int = int(reject: [1, 2, 3]);
$int(4);
```

Use `IntAttr` attribute to define an integer parameter.

```php
use Chevere\Parameter\Attributes\IntAttr;

#[IntAttr(min: 0, max: 100)]
```

## Int pseudo-parameters

The following parameters are based on Int.

### Bool int

Use function `boolInt` to create a `IntParameter` matching `0` and `1` integers.

```php
use function Chevere\Parameter\boolInt;

$bool = boolInt();
$bool(0);
$bool(1);
```

## Float

Use function `float` to create a `FloatParameter`. Pass `min` and `max` values for float range, `accept` for a list of accepted floats and `reject` for a list of rejected floats.

```php
use function Chevere\Parameter\float;

// Any float
$float = float();
$float(1.5);
// Float between 0 and 100
$float = float(min: 0, max: 100);
$float(50.5);
// Float matching 1.5, 2.5 or 3.5
$float = float(accept: [1.5, 2.5, 3.5]);
$float(2.5);
// Float not-matching 1.5, 2.5 or 3.5
$float = float(reject: [1.5, 2.5, 3.5]);
$float(4.5);
```

Use `FloatAttr` attribute to define a float parameter.

```php
use Chevere\Parameter\Attributes\FloatAttr;

#[FloatAttr(min: 0, max: 100)]
```

## Bool

Use function `bool` to create a `BoolParameter`.

```php
use function Chevere\Parameter\bool;

$bool = bool();
$bool(true);
$bool(false);
```

Use `BoolAttr` attribute to define a bool parameter.

```php
use Chevere\Parameter\Attributes\BoolAttr;

#[BoolAttr]
```

## Null

Use function `null` to create a `NullParameter`.

```php
use function Chevere\Parameter\null;

$null = null();
$null(null);
```

Use `NullAttr` attribute to define a null parameter.

```php
use Chevere\Parameter\Attributes\NullAttr;

#[NullAttr]
```

## Object

Use function `object` to create a `ObjectParameter`. Pass a className for the object class name.

```php
use function Chevere\Parameter\object;

$object = object(stdClass::class);
$object(new stdClass());
```

Use `ObjectAttr` attribute to define an object parameter.

```php
use Chevere\Parameter\Attributes\ObjectAttr;

#[ObjectAttr(stdClass::class)]
```

## Mixed

Use function `mixed` to create a `MixedParameter`.

```php
use function Chevere\Parameter\mixed;

$mixed = mixed();
$mixed(1);
$mixed('1');
$mixed(true);
$mixed(null);
```

## Union

Use function `union` to create a `UnionParameter`. Pass a list of parameters to match, target value must match at least one.

```php
use function Chevere\Parameter\union;

// Any string or null
$union = union(string(), null());
$union('abc');
$union(null);
// Any digit string or any integer
$union = union(
    intString(),
    integer()
);
$union('100');
$union(100);
```

## Array

Parameter for type `array` is handled as a composite Parameter holding parameter definition for **each one** of its members.

Use function `arrayp` to create an `ArrayParameter` for named arguments as required array keys.

```php
use function Chevere\Parameter\arrayp;

// Empty array
$array = arrayp();
$array([]);
// Required 'a' => <string>
$array = arrayp(a: string());
$array(['a' => 'Hello world']);
```

Parameter supports nested arrays of any depth:

```php
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\float;
use function Chevere\Parameter\int;

$array = arrayp(
    id: int(min: 0),
    items: arrayp(
        id: int(min: 0),
        price: float(min: 0),
    ),
);
$array([
    'id' => 1,
    'items' => [
        'id' => 25,
        'price' => 16.5,
    ]
]);
```

Use `ArrayAttr` attribute to define an array parameter.

```php
use Chevere\Parameter\Attributes\ArrayAttr;
use Chevere\Parameter\Attributes\FloatAttr;
use Chevere\Parameter\Attributes\IntAttr;

#[ArrayAttr(
    id: new IntAttr(),
    items: new ArrayAttr(
        id: new IntAttr(),
        price: new FloatAttr(),
    ),
)]
```

### With required

use method `withRequired` to define required parameters.

```php
$array = $array
    ->withRequired(
        username: string(),
        email: string()
    );
```

### With optional

use method `withOptional` to define optional parameters.

```php
$array = $array
    ->withOptional(address: string());
```

👉 **Note:** Optional parameters will be validated only if a matching key is provided.

### With modify

use method `withModify` to define modify parameters.

```php
$array = $array
    ->withModify(
        username: string('/\w+/'),
    );
```

### With make optional

use method `withMakeOptional` to make required parameters optional.

```php
$array = $array
    ->withMakeOptional('username');
```

### With make required

use method `withMakeRequired` to make optional parameters required.

```php
$array = $array
    ->withMakeRequired('email');
```

### Without

use method `without` to remove parameters.

```php
$array = $array
    ->without('a');
```

### With optional minimum

use method `withOptionalMinimum` to define a minimum number of optional parameters. Useful if all parameters are optional but 1.

```php
$array = $array
    ->withOptionalMinimum(1);
```

## Array pseudo-parameters

The following parameters are based on Array.

### Array String

Use function `arrayString` to create an `ArrayStringParameterInterface` for string values. It only supports string parameters.

```php
use function Chevere\Parameter\arrayString;
use function Chevere\Parameter\string;

$array = arrayString(
    test: string(),
);
$array(['test' => 'foo']);
```

### File

Use function `file` to create an `ArrayParameter` for file uploads.

```php
use function Chevere\Parameter\file;

$array = file();
$file = [
    'name' => 'foo.txt',
    'type' => 'text/plain',
    'tmp_name' => '/tmp/phpYzdqkD',
    'error' => 0,
    'size' => 123,
];
$array($file);
```

By default it provides validation for `$_FILES` shape, but you can define your own validation rules. For example, to validate name and contents:

```php
use function Chevere\Parameter\file;

$array = file(
    name: string('/^\.txt$/'),
    contents: string('/wage-/'),
);
$array(
    'name' => 'wage-2024.txt',
    'type' => 'text/plain',
    'tmp_name' => '/tmp/phpYzdqkD',
    'error' => 0,
    'size' => 27,
    'contents' => 'yada yada wage-2024 bla bla',
);
```

## Iterable

Iterable type `Traversable|array` is considered as a composite Parameter holding a generic definition for key and value. Parameter enables to describe this collection of items sharing the same shape.

Use function `iterable` to create an `IterableParameter`. Pass a `V` and `K` parameters for generic key and value.

```php
use function Chevere\Parameter\int;
use function Chevere\Parameter\iterable;

$iterable = iterable(int(min: 0));
$iterable([0, 1, 2, 3]);
```

It also works with named keys:

```php
use function Chevere\Parameter\int;
use function Chevere\Parameter\iterable;
use function Chevere\Parameter\string;

$iterable = iterable(
    V: arrayp(
        id: int(min: 0),
        name: string('^[\w]{1,255}'),
    )
    K: string(),
);
$iterable([
    'based' => [
        'id' => 1,
        'name' => 'OscarGangas'
    ],
    'fome' => [
        'id' => 2,
        'name' => 'BomboFica'
    ],
]);
```

## Helpers

### parameters

Use function `parameters` to create a `Parameters` instance.

```php
use function Chevere\Parameters\parameters;
use function Chevere\Parameters\string;

$parameters = parameters(foo: string());
```

### arguments

Use function `arguments` to create a `Arguments` instance.

```php
use function Chevere\Parameters\arguments;
use function Chevere\Parameters\string;

$arguments = arguments($parameters, ['foo' => 'bar']);
```

### assertNamedArgument

Use function `assertNamedArgument` to assert a named argument.

```php
use function Chevere\Parameters\assertNamedArgument;
use function Chevere\Parameters\int;
use function Chevere\Parameters\parameters;

$parameter = int(min: 10);
assertNamedArgument(
    name: 'foo',
    parameter: $parameter,
    argument: 20
);
```

### toParameter

Use function `toParameter` to create a `ParameterInterface` instance from a type string. In the example below the resulting `$parameter` will be an `IntParameter`.

```php
use function Chevere\Parameters\toParameter;

$parameter = toParameter('int');
```

### arrayFrom

Use function `arrayFrom` to create an [Array parameter](#array) from another array parameter. In the example below the resulting `$array` will contain only `name` and `id` keys as defined in `$source`.

```php
use function Chevere\Parameters\arrayFrom;
use function Chevere\Parameters\arrayp;
use function Chevere\Parameters\int;
use function Chevere\Parameters\string;

$source = arrayp(
    id: int(),
    name: string(),
    email: string(),
    age: int(),
);
$array = arrayFrom($source, 'name', 'id');
```

### takeKeys

Use function `takeKeys` to retrieve an array with the keys from a parameter. In the example below `$keys` will contain `id` and `size`.

```php
use function Chevere\Parameters\arrayp;
use function Chevere\Parameters\int;
use function Chevere\Parameters\takeKeys;

$array = arrayp(
    id: int(),
    size: int(),
);
$keys = takeKeys($array);
```

### takeFrom

Use function `takeFrom` to retrieve an iterator with the desired keys from a parameter. In the example below `$iterator` will yield `size` and `name` keys.

```php
use function Chevere\Parameters\arrayp;
use function Chevere\Parameters\int;
use function Chevere\Parameters\string;
use function Chevere\Parameters\takeFrom;

$array = arrayp(
    id: int(min: 0),
    size: int(min: 100),
    name: string(),
);
$iterator = takeFrom($array, 'size', 'name');
```

### parametersFrom

Use function `parametersFrom` to create a `Parameters` with desired keys from a parameter. In the example below `$parameters` will contain `size` and `name` keys.

```php
use function Chevere\Parameters\arrayp;
use function Chevere\Parameters\int;
use function Chevere\Parameters\string;
use function Chevere\Parameters\parametersFrom;

$array = arrayp(
    id: int(min: 0),
    size: int(min: 100),
    name: string(),
);
$parameters = parametersFrom($array, 'size', 'name');
```

### getParameters

Use function `getParameters` to retrieve a `Parameters` instance from an object implementing either `ParameterAccessInterface` or `ParametersInterface`.

```php
use function Chevere\Parameters\getParameters;

$parameters = getParameters($object);
```

### getType

Use function `getType` to retrieve the type as is known by this library.

```php
use function Chevere\Parameters\getType;

$type = getType(1); // int
```

### parameterAttr

Use function `parameterAttr` to retrieve an object implementing `ParameterAttributeInterface` from a function or class method parameter.

```php
use function Chevere\Parameters\parameterAttr;
use Chevere\Parameter\Attributes\StringAttr;


function myFunction(
    #[StringAttr('/^bin-[\d]+$/')]
    string $foo
): void {
    // ...
}

$stringAttr = parameterAttr('foo', 'myFunction');
$stringAttr('bin-123');
```

### reflectionToParameters

Use function `reflectionToParameters` to retrieve a `Parameters` instance from a `ReflectionFunction` or `ReflectionMethod` instance.

```php
use function Chevere\Parameter\reflectionToParameters;

$parameters = reflectionToParameters($reflection);
```

### reflectionToReturn

Use function `reflectionToReturn` to retrieve a `ParameterInterface` instance from a `ReflectionFunction` or `ReflectionMethod` instance.

```php
use function Chevere\Parameter\reflectionToReturn;

$parameter = reflectionToReturn($reflection);
```

### reflectedParameterAttribute

Use function `reflectedParameterAttribute` to retrieve an object implementing `ParameterAttributeInterface` from a `ReflectionParameter` instance.

```php
use function Chevere\Parameter\reflectedParameterAttribute;

$parameterAttribute = reflectedParameterAttribute($reflectionParameter);
```

### validated

Use function `validated` to validate a function or method arguments.

```php
use function Chevere\Parameter\validated;

$result = validated('myFunction', $arg1, $arg2,);
```

## Examples

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
$parameters(...$args); // valid $args
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

Use function `returnAttr()` on the function/method body.

* Validate int `min: 0, max: 5` return:

```php
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use function Chevere\Parameter\returnAttr;

#[ReturnAttr(
    new IntAttr(min: 0, max: 5)
)]
public function myReturnInt(): int
{
    $result = 1;

    return returnAttr()($result);
}
```

* Validate array return:

```php
use Chevere\Parameter\Attributes\ArrayAttr;
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\StringAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use function Chevere\Parameter\returnAttr;

#[ReturnAttr(
    new ArrayAttr(
        id: new IntAttr(min: 0),
        name: new StringAttr()
    )
)]
public function myReturnArray(): array
{
    $result = [
        'id' => 1,
        'name' => 'Peoples Hernandez'
    ];

    return returnAttr()($result);
}
```

💡 By convention when omitting `ReturnAttr` the method `public static function return(): ParameterInterface` (if any) will be used to determine return validation rules.

## Documentation

Documentation is available at [chevere.org](https://chevere.org/packages/parameter).

## License

Copyright [Rodolfo Berrios A.](https://rodolfoberrios.com/)

Chevere is licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for the full license text.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
