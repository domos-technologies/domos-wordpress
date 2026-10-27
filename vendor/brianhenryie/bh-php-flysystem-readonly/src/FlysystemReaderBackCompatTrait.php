<?php

/**
 * `FilesystemReader` interface v3 introduced `::directoryExists()` and `::has()`.
 *
 * When this trait is used with your implementation of {@see \League\Flysystem\FilesystemReader} and `league/flysystem`
 * v3 is installed, the genuine parent methods are used, when v2 is installed the trait provides implementations.
 *
 * directoryExists, has
 *
 * v2: https://github.com/thephpleague/flysystem/blob/2.x/src/FilesystemReader.php
 * v3: https://github.com/thephpleague/flysystem/blob/3.x/src/FilesystemReader.php
 * Implementation: https://github.com/thephpleague/flysystem/blob/3.x/src/Filesystem.php#L46-L51
 *
 * `FilesystemAdapter` 3.x also introduced `::directoryExists()` so we re-use its compatibility trait,
 * {@see FlysystemAdapterBackCompatTrait}, here.
 *
 * @package BrianHenryIE\FlysystemReadOnly
 */

namespace BrianHenryIE\FlysystemReadOnly;

/**
 * Back-fills `FilesystemReader::has()` and `::directoryExists()` when `league/flysystem` v2 is installed.
 *
 * @mixin FlysystemReaderBackCompatTraitInterface
 *
 * @method string normalizePath($location)
 * @method bool fileExists(string $location)
 */
trait FlysystemReaderBackCompatTrait
{
    use FlysystemAdapterBackCompatTrait;

    /**
     * Determine whether a file or a directory exists, using the parent's implementation where there is one.
     *
     * @param string $location The path to check.
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function has(string $location): bool
    {
        /**
         * The parent of the current class is checked, not necessarily the parent of the class which was called.
         *
         * @phpstan-ignore booleanAnd.leftAlwaysTrue
         */
        if (get_parent_class(self::class) && method_exists(get_parent_class(self::class), 'has')) {
            /**
             * The parent's `::has()` is not declared by v2's `FilesystemReader`, so it is not visible here.
             *
             * @phpstan-ignore staticMethod.notFound
             */
            return parent::has($location);
        }

        return $this->fileExists($this->normalizePath($location))
               || $this->directoryExists($this->normalizePath($location));
    }
}
