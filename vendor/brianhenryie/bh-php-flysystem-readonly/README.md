[![Flysystem 2 | 3](https://img.shields.io/badge/Flysystem-2%20%7C%203-blue.svg)](https://flysystem.thephpleague.com/)
[![PHP 7.4 | 8.x](https://img.shields.io/badge/PHP-7.4%20%7C%208.x-8892BF.svg)]()
[![Code Coverage](.github/coverage.svg)](https://brianhenryie.github.io/bh-php-flysystem-readonly/)
[![PHPCS PEAR](https://img.shields.io/badge/PHPCS-PEAR%7CPSR%2012%20-4e9a06.svg)](https://pear.php.net/manual/en/standards.php)
[![PHPStan ](.github/phpstan.svg)](https://phpstan.org/)

# Flysystem Read-Only Adapter

A [League Flysystem](https://flysystem.thephpleague.com/) adapter which wraps another adapter: reads pass
through to it, writes and deletes are captured in memory. The underlying filesystem is never modified, but
code running on top of it sees its own changes as though they had been written.
Uses [league/flysystem-memory](https://flysystem.thephpleague.com/docs/adapter/in-memory/) to store changes.

Created to use for `--dry-run` mode needs: the whole program runs normally – writing files, deleting them, listing directories, reading back what it just wrote – and nothing reaches the disk. This was extracted from [Strauss](https://github.com/BrianHenryIE/strauss), where it implements
`strauss --dry-run`.

## Install

```bash
composer require brianhenryie/bh-php-flysystem-readonly
```

## Use

```php
use BrianHenryIE\FlysystemReadOnly\ReadOnlyFileSystemAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

$adapter = new LocalFilesystemAdapter('/path/to/project');

if ($isDryRun) {
    $adapter = new ReadOnlyFileSystemAdapter($adapter);
}

$filesystem = new Filesystem($adapter);

$filesystem->write('src/Example.php', '<?php // rewritten');

// The new content is visible...
echo $filesystem->read('src/Example.php');       
// <?php // rewritten

// ...but the file on disk is untouched.
echo file_get_contents('/path/to/project/src/Example.php');
// Warning: file_get_contents(/path/to/project/src/Example.php): failed to open stream: No such file or directory in Command line code on line 1
```

## How it works

Three layers are consulted, in order:

| Layer | Contents |
|---|---|
| deleted files | tombstones written by `delete()`; directory tombstones recorded by `deleteDirectory()` |
| modified files | everything written during this session, in an in-memory adapter |
| delegate | the real filesystem, read-only |

So `read()` throws if the path was deleted, returns the in-memory copy if it was written, and otherwise
reads from the delegate. `listContents()` merges the delegate's listing with the in-memory one, removing
deleted paths and preferring the in-memory version of any path present in both.

Mutating operations only ever touch the in-memory layers. `setVisibility()` on a file which exists only on
the delegate filesystem first copies that file into memory – so the whole file is read into memory, as it
also is for `copy()` and `move()`.

## Path normalization

A `PathNormalizer` may be injected; it defaults to League's `WhitespacePathNormalizer` (which, among other
things, strips the leading `/` so paths match those returned by the adapters' directory listings).

```php
$adapter = new ReadOnlyFileSystemAdapter($localAdapter, $myPathNormalizer);
```

The normalizer is applied by this adapter and again by the in-memory layers it delegates to, so **an
injected normalizer must be idempotent**: `normalize(normalize($path)) === normalize($path)`.

## Flysystem 2 and 3

`league/flysystem` 3 added `directoryExists()` to `FilesystemAdapter` and `FilesystemReader`, and `has()` to
`FilesystemReader`. Two traits back-fill them, calling the genuine parent implementation when it exists:

* `FlysystemAdapterBackCompatTrait` – for your `FilesystemAdapter` implementations
* `FlysystemReaderBackCompatTrait` – for your `FilesystemReader` / `Filesystem` implementations

These require the class using the trait to implement `::normalizePath()`.

```php
class MyAdapter extends SomeAdapter implements FlysystemAdapterBackCompatTraitInterface
{
    use FlysystemAdapterBackCompatTrait;

    public function normalizePath(string $path): string
    {
        return (new WhitespacePathNormalizer())->normalizePath($path);
    }
}
```

| PHP | Flysystem 2 | Flysystem 3 |
|---|---|---|
| 7.4 | ✅ | – (requires PHP 8.0.2) |
| 8.0 – 8.4 | ✅ | ✅ |

## Known limitations

* `deleteDirectory()` records the directory as deleted, but files inside it which exist only on the delegate
  filesystem are not individually tombstoned, so they remain readable by their full path. Writing a file into
  a deleted directory, or creating it again, restores it.
* Files are held in memory in full. This is a dry-run tool, not a caching layer.
* `visibility()` of a directory defaults to `public` unless a visibility was set on it during the session.
* `mimeType()` of an in-memory file is detected from the path and contents by
  `league/mime-type-detection`, which may disagree with what the delegate filesystem would report.

## Tests

```bash
composer test               # against whichever flysystem is installed
composer test-flysystem2    # re-resolve against league/flysystem 2.x, then test
composer test-flysystem3    # re-resolve against league/flysystem 3.x, then test
composer cs                 # PHP_CodeSniffer and PHPStan
XDEBUG_MODE=coverage composer test-coverage
```

## Credits

Handwritten by [Brian Henry](https://BrianHenry.ie) for [Strauss](https://github.com/BrianHenryIE/strauss) then extracted by Claude. 
