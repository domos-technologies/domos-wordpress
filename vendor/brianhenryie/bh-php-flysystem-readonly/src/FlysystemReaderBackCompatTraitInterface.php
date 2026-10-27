<?php
/**
 * Flysystem 3.x requires PHP 8.
 *
 * @package BrianHenryIE\FlysystemReadOnly
 */

namespace BrianHenryIE\FlysystemReadOnly;

/**
 * The methods {@see FlysystemReaderBackCompatTrait} back-fills on a `Filesystem` under Flysystem 2.
 *
 * @see \League\Flysystem\FilesystemReader
 * @see \League\Flysystem\FilesystemOperator
 * @see \League\Flysystem\Filesystem
 */
interface FlysystemReaderBackCompatTraitInterface extends FlysystemAdapterBackCompatTraitInterface
{
    /**
     * Determine whether a directory exists at the given location.
     *
     * @param string $location The path to check.
     */
    public function directoryExists(string $location): bool;

    /**
     * Determine whether a file or a directory exists at the given location.
     *
     * @param string $location The path to check.
     */
    public function has(string $location): bool;
}
