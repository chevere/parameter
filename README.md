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

The beauty™ of this package is that all PHP types have been abstracted into its own "type system" and you can mix them to form any data-structure.

The following examples should give you a glimpse.

`namespace Chevere\Parameter;`

### Inline validation

It refers to classic on-site validation, it enables to go from this:

```php
if($var > 10 || $var < 1) {
    throw new InvalidArgumentException();
}
```

To this:

```php
int(min: 1, max:10)($var);
```

To use inline-validation simply invoke any parameter with the value you need to validate.

* Assert an string starting with "a":

```php
$value = 'ahhh';
$string = string('/^a.+/')($value);
```

* Assert an int of min value zero:

```php
$value = 100;
$int = int(min: 0)($value);
```

* Assert a float list:

```php
$value = 1.1;
$float = float(accept: [1.1, 2.1])($value);
```

* Assert an array:

```php
$value = [
    'id' => 1,
    'name' => 'José'
];
$array = arrayp(id: int(), name: string())($value);
```

* Assert a generic:

```php
$value = [0, 1, 1, 2, 3, 5];
$generic = generic(int())($value);
```

* Assert a union:

```php
$value = 1;
$union = union((int(), null()))($value);
```

### Attribute-based inline parameter validation

Use attributes on the function/method parameters to define validation rules. Use `validate()` on the function body to trigger validation.

* Assert an string starting with "a":

```php
function myString(
    #[new StringAttr('/^a.+/')]
    string $name
): void
{
    validate('name');
}
```

* Assert an int of min value zero:

```php
function myInt(
    #[new IntAttr(min: 0)]
    int $id
): void
{
    validate('id');
}
```

* Assert a float list:

```php
function myFloat(
    #[new FloatAttr(accept: [1.1, 2.1])]
    float $id
): void
{
    validate('id');
}
```

* Assert an array:

```php
function myArray(
    #[new ArrayAttr(
        id: IntAttr(),
        name: StringAttr()
    )]
    array $map
): void
{
    validate('map');
}
```

* Assert a generic:

```php
function myGeneric(
    #[new GenericAttr(
        IntAttr(),
    )]
    array $list
): void
{
    validate('list');
}
```

### Attribute-based inline return validation

Use attribute `ReturnAttr` on the function/method in combination with `returnAttr()` on the function body.

```php
#[ReturnAttr(
    new IntAttr(min: 0, max: 5)
)]
public function myReturn(int $int): int
{
    return returnAttr($int);
}
```

### Attribute-based injected validation

When working with reiterative interfaces or code structures you may want to delegate validation on the *caller* and not directly in the function body.

By doing this validation will happen before passing the function arguments, and after getting the return value.

`🚧 Work in progress`

## Function reference

Following functions enables to quickly spawn any parameter type.

`namespace Chevere\Parameter;`

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
| *      | `union()`    |

### Attributes

Following attributes enables to define validation rules using attributes.

`namespace Chevere\Parameter\Attributes;`

| Type   | Attribute     |
| ------ | ------------- |
| string | `StringAttr`  |
| int    | `IntAttr`     |
| float  | `FloatAttr`   |
| array  | `ArrayAttr`   |
| array  | `GenericAttr` |
| *      | `ReturnAttr`  |

## Documentation

Documentation is available at [chevere.org](https://chevere.org/).

## License

Copyright 2023 [Rodolfo Berrios A.](https://rodolfoberrios.com/)

Chevere is licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for the full license text.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
