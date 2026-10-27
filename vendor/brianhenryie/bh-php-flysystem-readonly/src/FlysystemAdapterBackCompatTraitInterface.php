<?php
/**
 * `FlysystemAdapterBackCompatTrait` needs to normalize paths.
 *
 * @package BrianHenryIE\FlysystemReadOnly
 *
 * @see \League\Flysystem\FilesystemAdapter
 */

namespace BrianHenryIE\FlysystemReadOnly;

use League\Flysystem\FilesystemReader;
use League\Flysystem\PathNormalizer;

/**
 * The methods {@see FlysystemAdapterBackCompatTrait} back-fills on an adapter under Flysystem 3.
 *
 * @see FlysystemAdapterBackCompatTrait
 */
interface FlysystemAdapterBackCompatTraitInterface
{
    /**
     * Determine whether a directory exists at the given location.
     *
     * Implementation is provided by {@see FlysystemAdapterBackCompatTrait::directoryExists()}.
     *
     * @param string $location The path to check.
     *
     * @see FilesystemReader::directoryExists()
     */
    public function directoryExists(string $location): bool;

    /**
     * Return the path in its canonical form, as the adapter stores it.
     *
     * @param string $path The path to normalize.
     *
     * @see PathNormalizer
     */
    public function normalizePath(string $path): string;
}
