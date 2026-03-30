# Parameter

![Chevere](chevere.svg)

[![Build](https://img.shields.io/github/actions/workflow/status/chevere/parameter/test.yml?branch=2.0&style=flat-square)](https://github.com/chevere/parameter/actions)
![Code size](https://img.shields.io/github/languages/code-size/chevere/parameter?style=flat-square)
[![Apache-2.0](https://img.shields.io/github/license/chevere/parameter?style=flat-square)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-blueviolet?style=flat-square)](https://phpstan.org/)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fchevere%2Fparameter%2F2.0)](https://dashboard.stryker-mutator.io/reports/github.com/chevere/parameter/2.0)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=alert_status)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=security_rating)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=coverage)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=chevere_parameter&metric=sqale_index)](https://sonarcloud.io/dashboard?id=chevere_parameter)
[![CodeFactor](https://www.codefactor.io/repository/github/chevere/parameter/badge)](https://www.codefactor.io/repository/github/chevere/parameter)

## Summary

**Chevere Parameter** is a library for building dynamic, validated parameters with type-safe rules and schema introspection. It enables you to define rich validation constraints for any PHP type, from simple scalars to deeply nested arrays, using either helper functions or attributes, eliminating boilerplate validation logic across your codebase.

* **[chevere/action](https://chevere.org/packages/action)**: Implements the action design pattern for encapsulating business logic, utilizing this package for comprehensive parameter validation.
* **[chevere/router](https://chevere.org/packages/router)**: Offers powerful routing with built-in parameter validation for handling HTTP requests and responses.
* **[chevere/sql2p](https://chevere.org/packages/sql2p)**: Transforms SQL queries into parameter definitions, enabling automated validation of database inputs and outputs.
* **[chevere/workflow](https://chevere.org/packages/workflow)**: Provides a workflow engine for defining and executing multi-step processes, using this package for parameter validation across workflow jobs.

## Installing

Parameter is available through [Packagist](https://packagist.org/packages/chevere/parameter) and the repository source is at [chevere/parameter](https://github.com/chevere/parameter).

```sh
composer require chevere/parameter
```

## Quick start

### Validate a single value

Create a parameter with rules and invoke it to validate. If validation fails, an exception is thrown.

```php
use function Chevere\Parameter\int;

$id = int(min: 1);
$id(5);    // returns 5
$id(-1);   // throws InvalidArgumentException
```

### Validate multiple values

Compose parameters to validate structured data in a single call.

```php
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;
use function Chevere\Parameter\string;

$userSchema = arrayp(
    id: int(min: 1),
    name: string('/^[A-Z][a-z]+$/'),
);
$userSchema([
    'id' => 1,
    'name' => 'Rodolfo',
]);
```

### Validate function arguments with attributes

Decorate functions with attributes and call `validated()` to enforce rules on both arguments and return values automatically.

```php
use Chevere\Parameter\Attributes\_float;
use Chevere\Parameter\Attributes\_int;
use Chevere\Parameter\Attributes\_return;
use function Chevere\Parameter\validated;

#[_return(
    new _float(min: 0, max: 2400)
)]
function wageWeekWA(
    #[_int(min: 1628)]
    int $cents,
    #[_float(min: 0, max: 40)]
    float $hours,
): float {
    return $cents * $hours / 100;
}

$wage = validated('wageWeekWA', 1628, 40.0);
```

## Why Parameter?

**Replace scattered validation with declarative rules.** Instead of writing `if`/`throw` blocks for every function, define constraints once using expressive helpers or attributes. Parameter turns validation into a first-class concern.

**Type safety beyond PHP's type system.** PHP enforces types, but not ranges, patterns, or allowed values. Parameter bridges that gap, an `int(min: 1, max: 100)` is more than `int`.

**Schema introspection.** Every parameter exposes its rules via `schema()`, enabling you to generate documentation, API contracts, or UI form definitions from the same source of truth.

```php
use function Chevere\Parameter\int;

$param = int(min: 0, max: 255);
$param->schema();
// ['type' => 'int', 'min' => 0, 'max' => 255]
```

**Immutable design.** All `with*` methods return new instances. Build parameter variations safely without side effects.

**Three validation strategies.** Choose the approach that fits your architecture:

| Strategy                                               | How                             | Best for               |
| ------------------------------------------------------ | ------------------------------- | ---------------------- |
| [Inline](#inline-validation)                           | `int(min: 1)($value)`           | Quick checks, scripts  |
| [Attribute delegated](#attribute-delegated-validation) | `validated('fn', ...$args)`     | Functions, controllers |
| [Attribute inline](#attribute-inline-validation)       | `assertArguments()` inside body | Granular control       |

## Reference

### Core types

| Type                     | Helper      | Attribute    | Description                                   |
| ------------------------ | ----------- | ------------ | --------------------------------------------- |
| [String](#string)        | `string`    | `_string`    | String, optionally matching a regex           |
| [Int](#int)              | `int`       | `_int`       | Integer with optional range/accept/reject     |
| [Float](#float)          | `float`     | `_float`     | Float with optional range/accept/reject       |
| [Bool](#bool)            | `bool`      | `_bool`      | Boolean                                       |
| [Null](#null)            | `null`      | `_null`      | Null                                          |
| [Object](#object)        | `object`    | —            | Object of a given class                       |
| [Mixed](#mixed)          | `mixed`     | `_mixed`     | Any type                                      |
| [Array](#array)          | `arrayp`    | `_arrayp`    | Array with named parameters                   |
| [Iterable](#iterable)    | `iterable`  | `_iterable`  | Iterable with generic key/value               |
| [Union](#union)          | `union`     | `_union`     | Value matching at least one parameter         |
| [UnionNull](#union-null) | `unionNull` | `_unionNull` | Value matching at least one parameter or null |

### Derived types

String-based parameters:

| Type                         | Helper       | Description                  |
| ---------------------------- | ------------ | ---------------------------- |
| [Enum](#enum-string)         | `enum`       | String matching a fixed list |
| [IntString](#int-string)     | `intString`  | String of digits             |
| [BoolString](#bool-string)   | `boolString` | `"0"` or `"1"`               |
| [Date](#date-string)         | `date`       | `YYYY-MM-DD` string          |
| [Time](#time-string)         | `time`       | `hh:mm:ss` string            |
| [Datetime](#datetime-string) | `datetime`   | `YYYY-MM-DD hh:mm:ss` string |

Int-based parameters:

| Type                 | Helper    | Description        |
| -------------------- | --------- | ------------------ |
| [BoolInt](#bool-int) | `boolInt` | `0` or `1` integer |

Array-based parameters:

| Type                         | Helper        | Description                   |
| ---------------------------- | ------------- | ----------------------------- |
| [ArrayString](#array-string) | `arrayString` | Array with string values only |
| [File](#file)                | `file`        | `$_FILES` upload shape        |

### Nullable helpers

Shorthand functions that create a union of a type with `null`:

| Helper                 | Equivalent to                     |
| ---------------------- | --------------------------------- |
| `nullInt(...)`         | `union(null(), int(...))`         |
| `nullFloat(...)`       | `union(null(), float(...))`       |
| `nullBool(...)`        | `union(null(), bool(...))`        |
| `nullString(...)`      | `union(null(), string(...))`      |
| `nullArray(...)`       | `union(null(), arrayp(...))`      |
| `nullArrayString(...)` | `union(null(), arrayString(...))` |
| `nullObject(...)`      | `union(null(), object(...))`      |

All accept the same arguments as their non-null counterparts.

```php
use function Chevere\Parameter\nullInt;

$maybeId = nullInt(min: 1);
$maybeId(42);   // 42
$maybeId(null); // null
```

### Special attributes

| Attribute               | Description                                |
| ----------------------- | ------------------------------------------ |
| [_callable](#_callable) | Forward parameter resolution to a callable |
| [_return](#_return)     | Return value validation                    |

## Types

A Parameter is an object implementing `ParameterInterface`. Every parameter can define a `description`, `default` value, and `sensitive` flag, plus additional validation rules depending on the type.

Parameters can be created using either helper functions or PHP attributes, both accept the same arguments.

### Immutability

Every `with*` method returns a new instance preserving the original state. This enables building parameter variations from a base definition without side effects.

```php
use function Chevere\Parameter\int;

// All three are independent instances
$base = int(min: 0);
$small = $base->withMax(100);
$large = $base->withMax(10000);
```

Common methods available on all parameters:

```php
$parameter->withDescription('A human-readable label');
$parameter->withIsSensitive(true); // marks value as sensitive (omitted from error messages)
$parameter->withDefault($defaultValue); // sets a default value used when the parameter is optional and not provided
```

Methods specific to each parameter type:

| Parameter         | Immutable methods                                                                                                      |
| ----------------- | ---------------------------------------------------------------------------------------------------------------------- |
| `IntParameter`    | `withMin`, `withMax`, `withAccept`, `withReject`                                                                       |
| `FloatParameter`  | `withMin`, `withMax`, `withAccept`, `withReject`                                                                       |
| `StringParameter` | `withRegex`                                                                                                            |
| `ArrayParameter`  | `withRequired`, `withOptional`, `withModify`, `withMakeOptional`, `withMakeRequired`, `without`, `withOptionalMinimum` |
| `ObjectParameter` | `withClassName`,                                                                                                       |

### Schema introspection

Every parameter exposes its validation rules via the `schema()` method. This enables programmatic access to constraints for documentation generation, API contracts, or UI form building.

```php
use function Chevere\Parameter\string;
use function Chevere\Parameter\int;

string('/^[a-z]+$/')->schema();
// ['type' => 'string', 'regex' => '/^[a-z]+$/']

int(min: 0, max: 100)->schema();
// ['type' => 'int', 'min' => 0, 'max' => 100]
```

## String

Use function `string` to create a `StringParameter`. Pass a `regex` for pattern matching.

```php
use function Chevere\Parameter\string;

// Any string
$string = string();

// String matching "bin-" followed by digits
$string = string('/^bin-[\d]+$/');
$string('bin-123'); // 'bin-123'

// With a default value
$string = string(default: 'hello');
```

Attribute notation:

```php
use Chevere\Parameter\Attributes\_string;

#[_string('/^bin-[\d]+$/')]
```

### String-based derived types

#### Enum string

Matches one of a fixed list of strings.

```php
use function Chevere\Parameter\enum;

$status = enum('active', 'inactive', 'pending');
$status('active');  // 'active'
$status('deleted'); // throws
```

Attribute notation:

```php
use Chevere\Parameter\Attributes\_enum;

#[_enum('active', 'inactive', 'pending')]
```

#### Int string

Matches strings representing integer digits.

```php
use function Chevere\Parameter\intString;

$int = intString();
$int('100'); // '100'
$int('abc'); // throws
```

#### Bool string

Matches `"0"` and `"1"` strings.

```php
use function Chevere\Parameter\boolString;

$bool = boolString();
$bool('0'); // '0'
$bool('1'); // '1'
```

#### Date string

Matches `YYYY-MM-DD` date strings.

```php
use function Chevere\Parameter\date;

$date = date();
$date('2025-01-15'); // '2025-01-15'
```

#### Time string

Matches `hh:mm:ss` time strings.

```php
use function Chevere\Parameter\time;

$time = time();
$time('14:30:00'); // '14:30:00'
```

#### Datetime string

Matches `YYYY-MM-DD hh:mm:ss` datetime strings. Supports an optional `precision` argument for fractional seconds.

```php
use function Chevere\Parameter\datetime;

$datetime = datetime();
$datetime('2025-01-15 14:30:00');

// With fractional seconds (up to 6 digits)
$precise = datetime(precision: 6);
$precise('2025-01-15 14:30:00.123456');
```

## Int

Use function `int` to create an `IntParameter`. Supports `min`, `max`, `accept` (whitelist), and `reject` (blacklist).

```php
use function Chevere\Parameter\int;

// Any integer
$int = int();
$int(42);

// Ranged
$int = int(min: 0, max: 100);
$int(50);  // 50
$int(200); // throws

// Whitelist
$int = int(accept: [1, 2, 3]);
$int(2);  // 2
$int(4);  // throws

// Blacklist
$int = int(reject: [0, -1]);
$int(5);  // 5
$int(0);  // throws
```

Attribute notation:

```php
use Chevere\Parameter\Attributes\_int;

#[_int(min: 0, max: 100)]
```

### Int-based derived types

#### Bool int

Matches `0` or `1` integers.

```php
use function Chevere\Parameter\boolInt;

$flag = boolInt();
$flag(0); // 0
$flag(1); // 1
$flag(2); // throws
```

## Float

Use function `float` to create a `FloatParameter`. Supports `min`, `max`, `accept`, and `reject`.

```php
use function Chevere\Parameter\float;

// Ranged
$price = float(min: 0.0, max: 9999.99);
$price(19.95); // 19.95

// Whitelist
$rate = float(accept: [0.5, 1.0, 1.5]);
$rate(1.0); // 1.0

// Blacklist
$score = float(reject: [0.0]);
$score(3.14); // 3.14
```

Attribute notation:

```php
use Chevere\Parameter\Attributes\_float;

#[_float(min: 0, max: 100)]
```

## Bool

Use function `bool` to create a `BoolParameter`.

```php
use function Chevere\Parameter\bool;

$flag = bool();
$flag(true);  // true
$flag(false); // false
```

Attribute notation:

```php
use Chevere\Parameter\Attributes\_bool;

#[_bool]
```

## Null

Use function `null` to create a `NullParameter`.

```php
use function Chevere\Parameter\null;

$null = null();
$null(null); // null
```

Attribute notation:

```php
use Chevere\Parameter\Attributes\_null;

#[_null]
```

## Object

Use function `object` to create an `ObjectParameter`. Pass a `className` to restrict the accepted class.

```php
use function Chevere\Parameter\object;

$object = object(stdClass::class);
$object(new stdClass()); // stdClass instance
```

## Mixed

Use function `mixed` to create a `MixedParameter`. Accepts any value.

```php
use function Chevere\Parameter\mixed;

$any = mixed();
$any(1);
$any('hello');
$any(null);
```

Attribute notation:

```php
use Chevere\Parameter\Attributes\_mixed;

#[_mixed]
```

## Union

Use function `union` to create a `UnionParameter`. The value must match at least one of the provided parameters.

```php
use function Chevere\Parameter\union;
use function Chevere\Parameter\int;
use function Chevere\Parameter\null;
use function Chevere\Parameter\intString;
use function Chevere\Parameter\string;

// Nullable string
$union = union(string(), null());
$union('hello'); // 'hello'
$union(null);    // null

// Accept both digit strings and integers
$union = union(intString(), int());
$union('100'); // '100'
$union(100);   // 100
```

## UnionNull

You can also use `unionNull()` as a shorthand for nullable unions:

```php
use function Chevere\Parameter\unionNull;
use function Chevere\Parameter\int;

$maybeInt = unionNull(int(min: 1));
$maybeInt(5);    // 5
$maybeInt(null); // null
```

Attribute notation:

```php
use Chevere\Parameter\Attributes\_null;
use Chevere\Parameter\Attributes\_int;

#[_unionNull(
    new _int()
)]
```

## Array

The array parameter validates each member of an associative array against its own parameter definition.

Use function `arrayp` to create an `ArrayParameter`.

```php
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;
use function Chevere\Parameter\string;

// Empty array
$array = arrayp();
$array([]); // []

// Required keys
$user = arrayp(
    id: int(min: 1),
    name: string('/^[A-Z][a-z]+$/'),
);
$user(['id' => 1, 'name' => 'Rodolfo']);
```

Nested arrays of any depth are supported:

```php
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\float;
use function Chevere\Parameter\int;

$order = arrayp(
    id: int(min: 0),
    item: arrayp(
        sku: int(min: 0),
        price: float(min: 0),
    ),
);
$order([
    'id' => 1,
    'item' => [
        'sku' => 25,
        'price' => 16.50,
    ],
]);
```

Attribute notation:

```php
use Chevere\Parameter\Attributes\_arrayp;
use Chevere\Parameter\Attributes\_float;
use Chevere\Parameter\Attributes\_int;

#[_arrayp(
    id: new _int(),
    item: new _arrayp(
        sku: new _int(),
        price: new _float(),
    ),
)]
```

### Modifying array parameters

Array parameters provide methods to add, modify, and remove keys.

#### withRequired

Add required keys.

```php
$array = $array->withRequired(
    username: string(),
    email: string(),
);
```

#### withOptional

Add optional keys. Optional parameters are validated only when a matching key is present.

```php
$array = $array->withOptional(
    address: string(),
);
```

#### withModify

Replace the definition of an existing key.

```php
$array = $array->withModify(
    username: string('/^\w+$/'),
);
```

#### withMakeOptional

Convert a required key to optional.

```php
$array = $array->withMakeOptional('username');
```

#### withMakeRequired

Convert an optional key to required.

```php
$array = $array->withMakeRequired('email');
```

#### without

Remove keys.

```php
$array = $array->without('address');
```

#### withOptionalMinimum

Require at least `n` optional parameters to be present. Useful when all keys are optional but at least one is expected.

```php
$array = $array->withOptionalMinimum(1);
```

### Array-based derived types

#### Array String

An array parameter accepting only string values.

```php
use function Chevere\Parameter\arrayString;
use function Chevere\Parameter\string;

$headers = arrayString(
    accept: string('/^application\/json$/'),
);
$headers(['accept' => 'application/json']);
```

#### File

An array parameter matching the `$_FILES` upload shape. Customize individual fields as needed.

```php
use function Chevere\Parameter\file;
use function Chevere\Parameter\string;

// Default $_FILES shape validation
$upload = file();
$upload([
    'name' => 'report.pdf',
    'type' => 'application/pdf',
    'tmp_name' => '/tmp/phpABC123',
    'error' => 0,
    'size' => 4096,
]);

// With custom rules
$upload = file(
    name: string('/\.csv$/'),
    contents: string('/^id,name/'),
);
```

## Iterable

Validates each item in a `Traversable|array` against a generic key/value definition. Use this to define collections of items sharing the same shape.

Use function `iterable` to create an `IterableParameter`. Pass `V` for the value parameter and optionally `K` for the key parameter (defaults to `int`).

```php
use function Chevere\Parameter\int;
use function Chevere\Parameter\iterable;

// List of non-negative integers
$ids = iterable(int(min: 0));
$ids([0, 1, 2, 3]);
```

With named string keys and structured values:

```php
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;
use function Chevere\Parameter\iterable;
use function Chevere\Parameter\string;

$roster = iterable(
    V: arrayp(
        id: int(min: 0),
        name: string('/^[\w]{1,255}$/'),
    ),
    K: string(),
);
$roster([
    'player1' => [
        'id' => 1,
        'name' => 'OscarGangas',
    ],
    'player2' => [
        'id' => 2,
        'name' => 'BomboFica',
    ],
]);
```

Attribute notation:

```php
use Chevere\Parameter\Attributes\_int;
use Chevere\Parameter\Attributes\_iterable;

#[_iterable(
    new _int(min: 0),
)]
```

## Inline validation

Inline validation is the direct use of parameter functions to validate values. Create the parameter and then invoke it with the value.

```php
use function Chevere\Parameter\string;
use function Chevere\Parameter\int;
use function Chevere\Parameter\float;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\iterable;
use function Chevere\Parameter\union;
use function Chevere\Parameter\null;

// String starting with "a"
string('/^a.+/')('ahhh');

// Integer in range
int(min: 1, max: 100)(50);

// Integer whitelist
int(accept: [1, 2, 3])(2);

// Float blacklist
float(reject: [1.1, 2.1])(3.14);

// Structured array
arrayp(
    id: int(min: 1),
    name: string('/^[A-Z]{1}\w+$/'),
)(['id' => 1, 'name' => 'Pepe']);

// Iterable with key rules
iterable(
    K: string('/ila$/'),
    V: int(min: 1),
)(['unila' => 1, 'dorila' => 2, 'tirifila' => 3]);

// Nullable integer
union(int(), null())(null);
```

## Attribute-based validation

Use PHP 8 attributes to declare validation rules directly on function/method signatures. This keeps constraints co-located with the code they protect.

### Attribute delegated validation

Call `validated()` to validate both arguments and return value against attribute rules.

```php
use Chevere\Parameter\Attributes\_int;
use Chevere\Parameter\Attributes\_return;
use Chevere\Parameter\Attributes\_string;
use function Chevere\Parameter\validated;

#[_return(
    new String('/ok$/')
)]
function process(
    #[Int(min: 1, max: 10)]
    int $var,
): string {
    return 'done ok';
}

$result = validated('process', 5); // 'done ok'
```

For manual control, use `reflectionToParameters` and `reflectionToReturn` to extract and apply rules separately:

```php
use ReflectionFunction;
use function Chevere\Parameter\reflectionToParameters;
use function Chevere\Parameter\reflectionToReturn;

$reflection = new ReflectionFunction('process');
$parameters = reflectionToParameters($reflection);
$return = reflectionToReturn($reflection);

$parameters(...$args);               // validate arguments
$result = process(...$args);          // call
$return($result);                     // validate return
```

### Attribute inline validation

Use `assertArguments()` inside the function body for granular control over when validation runs.

```php
use Chevere\Parameter\Attributes\_enum;
use Chevere\Parameter\Attributes\_float;
use function Chevere\Parameter\Attributes\assertArguments;

function myEnum(
    #[_enum('Hugo', 'Paco', 'Luis')]
    string $name,
    #[_float(min: 1000)]
    float $money,
): void {
    // Validate all arguments
    assertArguments();
    // Or validate specific ones
    assertArguments('name');
    assertArguments('money');
}

myEnum('Paco', 1000.50);
```

Use `assertReturn()` to validate the return value inline:

```php
use Chevere\Parameter\Attributes\_int;
use Chevere\Parameter\Attributes\_return;
use function Chevere\Parameter\Attributes\assertReturn;

#[_return(
    new _int(min: 0, max: 5)
)]
function clamp(): int
{
    $result = 3;

    return assertReturn($result);
}
```

### _return

Defines a validation rule for the return value of a function or method.

```php
use Chevere\Parameter\Attributes\_return;
use Chevere\Parameter\Attributes\_string;

#[_return(
    new _string('/ok$/')
)]
function myFunction(): string
{
    return 'done ok';
}
```

> By convention, when `_return` is omitted the method `public static function return(): ParameterInterface` (if any) will be used to determine return validation rules.

### _callable

PHP attributes only support constant expressions. To define dynamic parameters (e.g., nested arrays with optional keys), use `_callable` to delegate parameter resolution to a callable.

```php
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Attributes\_callable;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\string;

function contactSchema(): ParameterInterface
{
    return arrayp(
        email: string(),
    )->withOptional(
        name: string(),
    );
}

function saveContact(
    #[_callable('contactSchema')]
    array $contact,
): void {
    // ...
}
```

### Native attributes support

Parameter recognizes native PHP attribute annotations and works alongside them.

```php
use SensitiveParameter;
use Chevere\Parameter\Attributes\_int;

function authenticate(
    #[SensitiveParameter]
    #[_int(min: 1)]
    int $token,
): void {
    // $token value will be omitted from error messages
}
```

## Arguments

The `Arguments` object is the validated counterpart to `Parameters`. It holds argument values that have been validated against a `Parameters` instance, providing type-safe access by name.

### Creating arguments

```php
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\int;
use function Chevere\Parameter\string;

$parameters = parameters(
    id: int(min: 1),
    name: string('/^[A-Z]{1}\w+$/'),
)->withOptional(
    email: string(),
);

$arguments = arguments($parameters, [
    'id' => 1,
    'name' => 'Pepe',
]);
```

### Checking and retrieving values

```php
// Check existence
$arguments->has('id');    // true
$arguments->has('nope');  // false

// Get as mixed
$id = $arguments->get('id'); // 1

// Get with type safety
$id = $arguments->required('id')->int();       // 1
$email = $arguments->optional('email')?->string(); // null
```

### Modifying arguments

Use `withPut()` to create a new instance with an added or replaced argument.

```php
$arguments = $arguments->withPut('email', 'mail@chevere.org');
```

### Converting to array

```php
// Only provided arguments
$array = $arguments->toArray();

// Including optional parameters filled with a default
$array = $arguments->toArrayFill(null);
```

### Nested arguments

Access nested validated data structures using `nested()`.

```php
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\string;

$parameters = parameters(
    meta: arrayp(
        custom_data: arrayp(
            product: string(),
            product_id_external: string(),
        ),
    ),
);
$data = [
    'meta' => [
        'custom_data' => [
            'product' => 'Book',
            'product_id_external' => 'book_987654321',
        ],
    ],
];
$arguments = arguments($parameters, $data);
$product = $arguments
    ->nested('meta', 'custom_data')
    ->required('product')->string(); // 'Book'
```

## Type-safe access with `typed()`

Use function `typed` to get a `TypedInterface` accessor for any variable, enabling safe type casting with optional deep array access.

```php
use function Chevere\Parameter\typed;

$data = ['user' => ['age' => 30]];
$age = typed($data, 'user', 'age')->int(); // 30
```

## Cast helpers

### castArguments

Casts argument values to match the types defined by parameters, then validates. Useful for loosely-typed input (e.g., query strings).

```php
use function Chevere\Parameter\castArguments;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\int;
use function Chevere\Parameter\bool;

$parameters = parameters(
    page: int(min: 1),
    active: bool(),
);
$arguments = castArguments($parameters, [
    'page' => '3',     // cast to int
    'active' => '1',   // cast to bool
]);
```

### castValues

Returns the casted values as an array without creating an `Arguments` instance.

```php
use function Chevere\Parameter\castValues;

$values = castValues($parameters, ['page' => '3', 'active' => '1']);
// ['page' => 3, 'active' => true]
```

## Parameters

The `Parameters` object collects parameter definitions and validates arguments against them.

### Creating parameters

```php
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\int;
use function Chevere\Parameter\string;

// Required parameters via named arguments
$params = parameters(
    id: int(min: 1),
    name: string(),
);
```

### Adding and modifying

```php
// Add optional parameters
$params = $params->withOptional('email', string());

// Make a required parameter optional
$params = $params->withMakeOptional('name');

// Make an optional parameter required
$params = $params->withMakeRequired('email');

// Remove a parameter
$params = $params->without('email');

// Merge with another Parameters instance
$params = $params->withMerge($otherParameters);

// Require at least n optional parameters
$params = $params->withOptionalMinimum(1);
```

### Querying

```php
$params->has('id');          // true
$params->requiredKeys();     // VectorInterface<string>
$params->optionalKeys();     // VectorInterface<string>
$params->optionalMinimum();  // int
$params->get('id');          // ParameterInterface
```

### Direct invocation

Invoke a `Parameters` instance to validate named arguments and get an `Arguments` instance back.

```php
$arguments = $params(id: 1, name: 'Rodolfo');
```

## Helpers

### toParameter

Create a `ParameterInterface` from a type string.

```php
use function Chevere\Parameter\toParameter;

$param = toParameter('int'); // IntParameter
```

### toUnionParameter

Create a `UnionParameter` from multiple type strings.

```php
use function Chevere\Parameter\toUnionParameter;

$param = toUnionParameter('int', 'string'); // UnionParameter
```

### assertNamedArgument

Assert a single named argument against a parameter.

```php
use function Chevere\Parameter\assertNamedArgument;
use function Chevere\Parameter\int;

assertNamedArgument(
    name: 'age',
    parameter: int(min: 0),
    argument: 25,
);
```

### arrayFrom

Create an `ArrayParameter` from selected keys of another array parameter.

```php
use function Chevere\Parameter\arrayFrom;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;
use function Chevere\Parameter\string;

$source = arrayp(
    id: int(),
    name: string(),
    email: string(),
    age: int(),
);
$subset = arrayFrom($source, 'name', 'id');
```

### takeKeys

Retrieve an array of key names from a parameter.

```php
use function Chevere\Parameter\takeKeys;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;

$keys = takeKeys(arrayp(id: int(), size: int()));
// ['id', 'size']
```

### takeOne

Retrieve a single key-parameter pair as an array.

```php
use function Chevere\Parameter\takeOne;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;
use function Chevere\Parameter\string;

$array = arrayp(
    id: int(min: 0),
    size: int(min: 100),
    name: string(),
);
$parameter = takeOne($array, 'size');
```

### takeFrom

Retrieve an iterator yielding selected key-parameter pairs.

```php
use function Chevere\Parameter\takeFrom;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;
use function Chevere\Parameter\string;

$array = arrayp(
    id: int(min: 0),
    size: int(min: 100),
    name: string(),
);
$iterator = takeFrom($array, 'size', 'name');
```

### parametersFrom

Create a `Parameters` instance from selected keys of a parameter.

```php
use function Chevere\Parameter\parametersFrom;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;
use function Chevere\Parameter\string;

$array = arrayp(
    id: int(min: 0),
    size: int(min: 100),
    name: string(),
);
$parameters = parametersFrom($array, 'size', 'name');
```

### getParameters

Retrieve a `Parameters` instance from an object implementing `ParametersAccessInterface` or `ParametersInterface`.

```php
use function Chevere\Parameter\getParameters;

$parameters = getParameters($object);
```

### getType

Get the type name of a variable as defined by this library.

```php
use function Chevere\Parameter\getType;

getType(1);     // 'int'
getType(true);  // 'bool'
getType(1.5);   // 'float'
getType(null);  // 'null'
```

### parameterAttribute

Retrieve a `ParameterAttributeInterface` from a function or method parameter by name.

```php
use function Chevere\Parameter\parameterAttribute;
use Chevere\Parameter\Attributes\_string;

function myFunction(
    #[_string('/^bin-[\d]+$/')]
    string $foo,
): void {
    // ...
}

$attr = parameterAttribute('foo', 'myFunction');
$attr('bin-123'); // validates
```

### reflectionToParameter

Retrieve a `ParameterInterface` instance from a `ReflectionProperty` or `ReflectionParameter`.

```php
use function Chevere\Parameter\reflectionToParameter;
;
$parameter = reflectionToParameter($reflection);
```

### reflectionToParameters

Retrieve a `ParametersInterface` instance from a `ReflectionFunction` or `ReflectionMethod`.

```php
use function Chevere\Parameter\reflectionToParameters;

$parameters = reflectionToParameters($reflection);
```

Pass an array by reference as `$violations` to collect all logic violations instead of failing fast on the first one.

```php
$violations = [];
$parameters = reflectionToParameters($reflection, $violations);
// [
//     [
//         'parameter' => 'the_parameter',
//         'message' => 'Error message',
//     ],
// ]
```

When `$violations` is `null` (default), the function throws on the first reflection error. When an array is passed, errors are collected and the function continues processing remaining parameters.

### reflectionToReturn

Retrieve a `ParameterInterface` instance for the return type from a `ReflectionFunction` or `ReflectionMethod`.

```php
use function Chevere\Parameter\reflectionToReturn;

$return = reflectionToReturn($reflection);
```

### validated

Validate function/method arguments and return value in one call.

```php
use function Chevere\Parameter\validated;

$result = validated('myFunction', $arg1, $arg2);
```

## Advanced examples

### Nested array with attribute validation

```php
use Chevere\Parameter\Attributes\_arrayp;
use Chevere\Parameter\Attributes\_int;
use Chevere\Parameter\Attributes\_string;
use Chevere\Parameter\Attributes\_iterable;
use function Chevere\Parameter\Attributes\assertArguments;

function createUser(
    #[_arrayp(
        id: new _int(min: 1),
        role: new _arrayp(
            mask: new _int(accept: [64, 128, 256]),
            name: new _string('/[a-z]+/'),
            tenants: new _iterable(
                new _int(min: 1)
            ),
        ),
    )]
    array $user,
): void {
    assertArguments();
}

createUser([
    'id' => 10,
    'role' => [
        'mask' => 128,
        'name' => 'admin',
        'tenants' => [1, 2, 3, 4, 5],
    ],
]);
```

### Validating a return array

```php
use Chevere\Parameter\Attributes\_arrayp;
use Chevere\Parameter\Attributes\_int;
use Chevere\Parameter\Attributes\_string;
use Chevere\Parameter\Attributes\_return;
use function Chevere\Parameter\Attributes\assertReturn;

#[_return(
    new _arrayp(
        id: new _int(min: 0),
        name: new _string(),
    )
)]
function fetchUser(): array
{
    $result = [
        'id' => 1,
        'name' => 'Peoples Hernandez',
    ];

    return assertReturn($result);
}
```

### Using reflection for method validation

```php
use ReflectionMethod;
use Chevere\Parameter\Attributes\_int;
use function Chevere\Parameter\reflectionToParameters;

$class = new class() {
    public function score(
        #[_int(accept: [1, 10, 100])]
        int $base,
    ): void {
    }
};

$reflection = new ReflectionMethod($class, 'score');
$parameters = reflectionToParameters($reflection);
$parameters(base: 10); // validates
```

## Documentation

Documentation is available at [chevere.org/packages/parameter](https://chevere.org/packages/parameter).

## License

Copyright [Rodolfo Berrios A.](https://rodolfoberrios.com/)

Chevere is licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for the full license text.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
