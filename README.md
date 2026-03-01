# FFI IDE Helper Generator

<p align="center">
    <a href="https://packagist.org/packages/ffi/ide-helper-generator"><img src="https://poser.pugx.org/ffi/ide-helper-generator/require/php?style=for-the-badge" alt="PHP 8.1+"></a>
    <a href="https://packagist.org/packages/ffi/ide-helper-generator"><img src="https://poser.pugx.org/ffi/ide-helper-generator/version?style=for-the-badge" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/ffi/ide-helper-generator"><img src="https://poser.pugx.org/ffi/ide-helper-generator/v/unstable?style=for-the-badge" alt="Latest Unstable Version"></a>
    <a href="https://packagist.org/packages/ffi/ide-helper-generator"><img src="https://poser.pugx.org/ffi/ide-helper-generator/downloads?style=for-the-badge" alt="Total Downloads"></a>
    <a href="https://raw.githubusercontent.com/php-ffi/ide-helper-generator/master/LICENSE.md"><img src="https://poser.pugx.org/ffi/ide-helper-generator/license?style=for-the-badge" alt="License MIT"></a>
</p>
<p align="center">
    <a href="https://github.com/php-ffi/ide-helper-generator/actions"><img src="https://github.com/php-ffi/ide-helper-generator/workflows/build/badge.svg"></a>
</p>

## Requirements

- PHP ^8.4
- [castxml](https://github.com/CastXML/CastXML) ([binaries](https://github.com/CastXML/CastXMLSuperbuild/releases))

## Installation

Library is available as Сomposer repository and can be installed using the 
following command in the root of your project as a dev-dependency.

```sh
$ composer require ffi/ide-helper-generator --dev
```

## Usage

### Generate Metadata

Before generating the helper, the headers must be parsed to build the metadata
data. To do this, `castxml` will be used, which in turn uses the original
compiler (like `clang`) to build the AST.

```php
echo new \FFI\Generator\CodeGenerator()
    ->parse(__DIR__ . '/example-header.h')
    ->generate(new PhpStormMetadataPrinter())
    ->save(__DIR__ . '/resources/.phpstorm.meta.php');
```

### Analyze Metadata

After the metadata is generated, it should be parsed and an abstract syntax tree
built in memory.

```php
use FFI\Generator\Backup\Metadata\CastXMLParser;

$result = new \FFI\Generator\CodeGenerator()
    ->parse(__DIR__ . '/example-header.h');

foreach ($result->nodes as $namespace) {
    var_dump($namespace);
}
```
