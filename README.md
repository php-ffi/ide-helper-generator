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

- PHP ^8.1
- [castxml](https://github.com/CastXML/CastXML) ([binaries](https://github.com/CastXML/CastXMLSuperbuild/releases))

## Installation

Library is available as composer repository and can be installed using the 
following command in a root of your project as dev-dependency.

```sh
$ composer require ffi/ide-helper-generator --dev
```

## Usage

### Generate Metadata

Before generating the helper, the headers must be parsed to build the metadata
data. To do this, `castxml` will be used, which in turn uses the original
compiler (like `clang`) to build the AST.

```php
use FFI\Generator\Metadata\CastXMLGenerator;

(new CastXMLGenerator(
    binary: 'castxml', // path to binary (optional)
    temp: 'storage', // path to temp directory (optional)
))
    ->generate('/path/to/headers.h')
    ->save('/path/to/metadata.xml')
;
```

You can also to optimize this step by adding a file existence check:

```php
if (!is_file('/path/to/metadata.xml')) {
    // Generate metadata: (new CastXMLGenerator())->...
}
```

### Analyze Metadata

After the metadata is generated, it should be parsed and an abstract syntax tree
built in memory.

```php
use FFI\Generator\Metadata\CastXMLParser;

$ast = (new CastXMLParser())
    ->parse('/path/to/metadata.xml')
;
```

### Building IDE Helper

```php
use FFI\Generator\PhpStormMetadataGenerator;
use FFI\Generator\SimpleNamingStrategy;

$generator = new PhpStormMetadataGenerator(
    argumentSetPrefix: 'ffi_', // Optional prefix for all argument sets to be registered
                               // in metadata files.
                               
    ignoreDirectories: ['/usr'], // Optional list of directories with headers whose types
                                 // should be excluded from the generated code.
                                 
    naming: new SimpleNamingStrategy(
        entrypoint: 'FFI\\Generated\\EntrypointInterface',  // The name of the main FFI class
                                                            // for which methods for autocomplete
                                                            // will be generated.
                                                            
        externalNamespace: 'FFI\\Generated', // Namespace for all public types (e.g. enums) that
                                             // can be used in PHP code.
                                             
        internalNamespace: 'PHPSTORM_META', // Namespace for all generated types which should not
                                            // be included in the PHP code and will only be used
                                            // for autocomplete.
    ),
);

// Pass AST into generator
$result = $generator->generate($ast);

// Write result code into stdout
echo $result;

file_put_contents(__DIR__ . '/.phpstorm.meta.php', (string)$result);
```

You can also override some naming methods:

```php
use FFI\Generator\PhpStormMetadataGenerator;
use FFI\Generator\SimpleNamingStrategy;

$generator = new PhpStormMetadataGenerator(
    naming: new class extends SimpleNamingStrategy 
    {
        // Each enum value will be converted to CamelCase
        // instead of UPPER_SNAKE_CASE (by default)
        protected function getEnumValueName(string $name): string
        {
            return $this->toCamelCase($name);
        }
    }
);
```

## Example

Below is the simplest complex code example:

```php
use FFI\Generator\Metadata\CastXMLGenerator;
use FFI\Generator\Metadata\CastXMLParser;
use FFI\Generator\PhpStormMetadataGenerator;
use FFI\Generator\SimpleNamingStrategy;

const INPUT_HEADERS = __DIR__ . '/path/to/headers.h';
const OUTPUT_FILE = __DIR__ . '/path/to/.phpstorm.meta.php';

fwrite(STDOUT, " - [1/3] Generating metadata files\n");
if (!is_file(INPUT_HEADERS . '.xml')) {
    (new CastXMLGenerator())
        ->generate(INPUT_HEADERS)
        ->save(INPUT_HEADERS . '.xml')
    ;
}

fwrite(STDOUT, " - [2/3] Building AST\n");
$ast = (new CastXMLParser())
    ->parse(INPUT_HEADERS . '.xml')
;

fwrite(STDOUT, " - [3/3] Generating IDE helper\n");
$result = (new PhpStormMetadataGenerator())
    ->generate($ast)
;

fwrite(STDOUT, " - DONE!\n");
file_put_contents(OUTPUT_FILE, (string)$result);
```
