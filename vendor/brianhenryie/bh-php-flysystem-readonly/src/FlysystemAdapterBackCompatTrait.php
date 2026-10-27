<?php
/**
 * `FilesystemAdapter` interface v3 introduced `::directoryExists()`.
 *
 * When this trait is used with your implementation of {@see \League\Flysystem\FilesystemAdapter} and
 * `league/flysystem` v3 is installed, the genuine parent method is used; when v2 is installed the trait
 * provides an implementation.
 *
 * v2: https://github.com/thephpleague/flysystem/blob/2.x/src/FilesystemAdapter.php
 * Interface: https://github.com/thephpleague/flysystem/blob/3.x/src/FilesystemAdapter.php
 * Implementations:
 * https://github.com/thephpleague/flysystem/blob/3.x/src/Filesystem.php#L41-L44
 * https://github.com/thephpleague/flysystem/blob/3.x/src/Local/LocalFilesystemAdapter.php#L346-L351
 *
 * @package BrianHenryIE\FlysystemReadOnly
 */

namespace BrianHenryIE\FlysystemReadOnly;

use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;

/**
 * Back-fills `FilesystemAdapter::directoryExists()` when `league/flysystem` v2 is installed.
 *
 * @mixin FlysystemAdapterBackCompatTraitInterface
 *
 * @method string normalizePath($location)
 */
trait FlysystemAdapterBackCompatTrait
{
    /**
     * Determine whether a directory exists, using the parent's implementation where there is one.
     *
     * @param string $path The path to check.
     *
     * @throws FilesystemException
     *
     * @see FilesystemAdapter::directoryExists()
     */
    public function directoryExists(string $path): bool
    {
        /**
         * Use `self::class` here to check the parent of the current class, not necessarily the parent of the class
         * which was called.
         *
         * @phpstan-ignore booleanAnd.leftAlwaysTrue
         */
        if (get_parent_class(self::class) && method_exists(get_parent_class(self::class), 'directoryExists')) {
            /**
             * The parent's `::directoryExists()` is not declared by v2's `FilesystemAdapter`, so its
             * return value is untyped there; anything other than `true` is treated as not existing.
             *
             * @phpstan-ignore staticMethod.notFound
             */
            return true === parent::directoryExists($path);
        }

        return $this->directoryExistsImplementation($path);
    }

    /**
     * Determine whether a directory exists by listing its parent directory and looking for it.
     *
     * @param string $path The path to check.
     *
     * @throws FilesystemException
     */
    protected function directoryExistsImplementation(string $path): bool
    {
        $path = $this->normalizePath($path);

        $parentDir = dirname($path);
        $parentDir = $this->normalizePath($parentDir);
        // TODO: This might not be Windows compatible.
        $parentDir = $parentDir === '.' ? '/' : $parentDir;

        // Root dir must exist!
        if ($parentDir === $path) {
            return true;
        }

        $parentDirectoryContents = $this->listContents($parentDir, false);
        /**
         * Listings yield `StorageAttributes`; both its implementations expose `::isDir()`.
         *
         * @var FileAttributes $entry
         */
        foreach ($parentDirectoryContents as $entry) {
            if ($entry->path() == $path) {
                return $entry->isDir();
            }
        }

        return false;
    }
}
