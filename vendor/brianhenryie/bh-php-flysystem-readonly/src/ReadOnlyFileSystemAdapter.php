<?php
/**
 * A read-only filesystem adapter, e.g. for use when running with `--dry-run`.
 *
 * Read operations work as normal but write operations are cached in memory so they appear to have been
 * successful without ever being written to the delegate filesystem.
 *
 * Three layers are consulted, in order:
 *
 * 1. deleted files – tombstones recorded by {@see self::delete()} and {@see self::deleteDirectory()}
 * 2. modified files – everything written during this session
 * 3. the delegate adapter – the real filesystem, which is never mutated
 *
 * @package BrianHenryIE\FlysystemReadOnly
 */

namespace BrianHenryIE\FlysystemReadOnly;

use League\Flysystem\Config;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\InvalidVisibilityProvided;
use League\Flysystem\PathNormalizer;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\Visibility;
use League\Flysystem\WhitespacePathNormalizer;
use Traversable;

/**
 * An adapter which wraps another: reads pass through, writes and deletes are captured in memory.
 */
class ReadOnlyFileSystemAdapter implements FilesystemAdapter, FlysystemAdapterBackCompatTraitInterface
{
    protected FilesystemAdapter $delegateFilesystemAdapter;
    protected ModifiedFilesInMemoryFilesystemAdapter $inMemoryFiles;
    protected DeletedFilesInMemoryFilesystemAdapter $deletedFiles;

    protected PathNormalizer $pathNormalizer;

    /**
     * Directories deleted during this session, keyed on the normalized path.
     *
     * Directory tombstones are tracked here rather than in {@see self::$deletedFiles} because that layer
     * is a filesystem: a tombstone for `dir/file.php` would make `dir` itself look deleted.
     *
     * @var array<string, bool>
     */
    protected array $deletedDirectories = [];

    /**
     * Constructor.
     *
     * @param FilesystemAdapter   $delegateFilesystem The filesystem to read from and to protect from writes.
     * @param PathNormalizer|null $pathNormalizer     Must be idempotent – it is applied by this adapter and
     *                                                again by the in-memory layers it delegates to.
     */
    public function __construct(
        FilesystemAdapter $delegateFilesystem,
        ?PathNormalizer $pathNormalizer = null
    ) {
        $this->delegateFilesystemAdapter = $delegateFilesystem;

        $this->pathNormalizer = $pathNormalizer ?? new WhitespacePathNormalizer();

        $this->inMemoryFiles = new ModifiedFilesInMemoryFilesystemAdapter(
            Visibility::PUBLIC,
            null,
            $this->pathNormalizer
        );
        $this->deletedFiles = new DeletedFilesInMemoryFilesystemAdapter(
            Visibility::PUBLIC,
            null,
            $this->pathNormalizer
        );
    }

    /**
     * Return the wrapped adapter, i.e. the real filesystem this one protects from writes.
     */
    public function getAdapter(): FilesystemAdapter
    {
        return $this->delegateFilesystemAdapter;
    }

    /**
     * Determine whether a file exists in memory or on the delegate filesystem, but not in the deleted files filesystem.
     *
     * @param string $location The path to check.
     */
    public function fileExists(string $location): bool
    {
        $location = $this->pathNormalizer->normalizePath($location);

        if ($this->deletedFiles->fileExists($location)) {
            return false;
        }
        return $this->inMemoryFiles->fileExists($location)
               || $this->delegateFilesystemAdapter->fileExists($location);
    }

    /**
     * Write a file to the in-memory layer, leaving the delegate filesystem untouched.
     *
     * @param string                           $location The path to write to.
     * @param string                           $contents The file's contents.
     * @param Config|array{visibility?:string} $config   Flysystem's per-operation config, e.g. visibility.
     *
     * @throws FilesystemException
     */
    public function write(string $location, string $contents, $config = []): void
    {
        $location = $this->pathNormalizer->normalizePath($location);

        $this->inMemoryFiles->write($location, $contents, $this->toConfig($config));

        $this->undeleteAncestorDirectories($location);

        if ($this->deletedFiles->fileExists($location)) {
            $this->deletedFiles->delete($location);
        }
    }

    /**
     * Write a stream to the in-memory layer, leaving the delegate filesystem untouched.
     *
     * @param string                           $path     The path to write to.
     * @param resource                         $contents The stream to read the contents from.
     * @param Config|array{visibility?:string} $config   Flysystem's per-operation config, e.g. visibility.
     *
     * @throws FilesystemException
     *
     * @see FilesystemAdapter::writeStream()
     */
    public function writeStream(string $path, $contents, $config = []): void
    {
        $path = $this->pathNormalizer->normalizePath($path);

        $this->rewindStream($contents);
        $this->inMemoryFiles->writeStream($path, $contents, $this->toConfig($config));

        $this->undeleteAncestorDirectories($path);

        if ($this->deletedFiles->fileExists($path)) {
            $this->deletedFiles->delete($path);
        }
    }

    /**
     * Rewind a seekable stream which has already been read from, so its full contents are written.
     *
     * @param resource $resource The stream to rewind.
     */
    private function rewindStream($resource): void
    {
        if (ftell($resource) !== 0 && stream_get_meta_data($resource)['seekable']) {
            rewind($resource);
        }
    }

    /**
     * Accept the array form Flysystem 2 callers pass, as well as a `Config` object.
     *
     * @param Config|array<string,mixed>|null $config The config to normalize.
     */
    private function toConfig($config): Config
    {
        return $config instanceof Config ? $config : new Config($config ?? []);
    }

    /**
     * Read a file, preferring the in-memory version where this session has written one.
     *
     * @param string $path The path to read.
     */
    public function read(string $path): string
    {
        $path = $this->pathNormalizer->normalizePath($path);

        if ($this->deletedFiles->fileExists($path)) {
            throw UnableToReadFile::fromLocation($path);
        }
        if ($this->inMemoryFiles->fileExists($path)) {
            return $this->inMemoryFiles->read($path);
        }
        return $this->delegateFilesystemAdapter->read($path);
    }

    /**
     * Read a file as a stream, preferring the in-memory version where this session has written one.
     *
     * @param string $path The path to read.
     *
     * @return resource
     */
    public function readStream(string $path)
    {
        $path = $this->pathNormalizer->normalizePath($path);

        if ($this->deletedFiles->fileExists($path)) {
            throw UnableToReadFile::fromLocation($path);
        }
        if ($this->inMemoryFiles->fileExists($path)) {
            return $this->inMemoryFiles->readStream($path);
        }
        return $this->delegateFilesystemAdapter->readStream($path);
    }

    /**
     * Record the file as deleted, keeping a copy so it can still be restored by a later write.
     *
     * @param string $path The path to delete.
     */
    public function delete(string $path): void
    {
        $path = $this->pathNormalizer->normalizePath($path);

        if ($this->fileExists($path)) {
            $file = $this->read($path);
            $this->deletedFiles->write($path, $file, new Config([]));
        }

        if ($this->inMemoryFiles->fileExists($path)) {
            $this->inMemoryFiles->delete($path);
        }
    }

    /**
     * Record the directory as deleted.
     *
     * Note: files inside the directory which exist only on the delegate filesystem are not individually
     * tombstoned; they remain readable by path. See the "Known limitations" section of the README.
     *
     * @param string $path The directory to delete.
     */
    public function deleteDirectory(string $path): void
    {
        $path = $this->directoryKey($path);

        $this->deletedDirectories[$path] = true;
        $this->inMemoryFiles->deleteDirectory($path);
    }

    /**
     * Has this directory, or any directory containing it, been deleted?
     *
     * @param string $path The directory to check.
     */
    protected function isDirectoryDeleted(string $path): bool
    {
        $path = $this->directoryKey($path);

        foreach (array_keys($this->deletedDirectories) as $deletedDirectory) {
            if ($path === $deletedDirectory || 0 === strpos($path, $deletedDirectory . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creating a directory, or writing a file into one, brings back any deleted directory containing it.
     *
     * @param string $path The path whose ancestor directories to restore.
     */
    protected function undeleteAncestorDirectories(string $path): void
    {
        $path = $this->directoryKey($path);

        while ($path !== '' && $path !== '.' && $path !== '/') {
            $path = $this->directoryKey(dirname($path));

            unset($this->deletedDirectories[$path]);

            if ($path === '.' || $path === '/') {
                break;
            }
        }
    }

    /**
     * Return the key a directory is tracked under: normalized, without a trailing slash.
     *
     * @param string $path The directory path to key.
     */
    protected function directoryKey(string $path): string
    {
        return rtrim($this->pathNormalizer->normalizePath($path), '/');
    }

    /**
     * Create a directory in the in-memory layer, restoring it if it had been deleted.
     *
     * @param string                           $path   The directory to create.
     * @param Config|array{visibility?:string} $config Flysystem's per-operation config, e.g. visibility.
     *
     * @throws FilesystemException
     */
    public function createDirectory(string $path, $config = []): void
    {
        $path = $this->pathNormalizer->normalizePath($path);

        $this->inMemoryFiles->createDirectory($path, $this->toConfig($config));

        unset($this->deletedDirectories[$this->directoryKey($path)]);
        $this->undeleteAncestorDirectories($path);
    }

    /**
     * List a directory's contents, merging the three layers: deleted files are omitted and
     * modified files replace their delegate filesystem counterparts.
     *
     * @param string $path The directory to list.
     * @param bool   $deep Whether to list the contents of subdirectories too.
     *
     * @return iterable<StorageAttributes>
     */
    public function listContents(string $path, bool $deep): iterable
    {
        $path = $this->pathNormalizer->normalizePath($path);

        $deletedFilesArray = $this->toArray($this->deletedFiles->listContents($path, $deep));
        $deletedFilePaths  = array_map(fn($file) => $file->path(), $deletedFilesArray);

        $inMemoryFilesArray = $this->toArray($this->inMemoryFiles->listContents($path, $deep));

        // Remove deleted files from the modified files filesystem array.
        $inMemoryFilesArray = array_filter(
            $inMemoryFilesArray,
            fn($file) => !in_array($file->path(), $deletedFilePaths)
        );

        $inMemoryFilePaths = array_map(fn($file) => $file->path(), $inMemoryFilesArray);

        $delegateFilesystemArray = $this->toArray($this->delegateFilesystemAdapter->listContents($path, $deep));

        // Remove modified files from the delegate filesystem array.
        $delegateFilesystemArray = array_filter(
            $delegateFilesystemArray,
            fn($file) => !in_array($file->path(), $inMemoryFilePaths)
        );
        // Remove deleted files from the delegate filesystem array.
        $delegateFilesystemArray = array_filter(
            $delegateFilesystemArray,
            fn($file) => !in_array($file->path(), $deletedFilePaths)
        );

        return new DirectoryListing(array_merge($delegateFilesystemArray, $inMemoryFilesArray));
    }

    /**
     * Materialize a directory listing so it can be filtered and merged.
     *
     * @param iterable<StorageAttributes> $listing The listing to materialize.
     *
     * @return array<StorageAttributes>
     */
    private function toArray(iterable $listing): array
    {
        return $listing instanceof Traversable
            ? iterator_to_array($listing, false)
            : (array) $listing;
    }

    /**
     * Copy the file to its destination in memory, then record the source as deleted.
     *
     * @param string                                $source      The path to move from.
     * @param string                                $destination The path to move to.
     * @param Config|array{visibility?:string}|null $config      Flysystem's per-operation config.
     *
     * @throws FilesystemException
     */
    public function move(string $source, string $destination, $config = null): void
    {
        $source      = $this->pathNormalizer->normalizePath($source);
        $destination = $this->pathNormalizer->normalizePath($destination);

        if ($source === $destination) {
            return;
        }

        if (!$this->fileExists($source)) {
            throw UnableToMoveFile::fromLocationTo($source, $destination);
        }

        try {
            $this->copy($source, $destination, $config);
            $this->delete($source);
        } catch (FilesystemException $exception) {
            throw UnableToMoveFile::fromLocationTo($source, $destination, $exception);
        }
    }

    /**
     * Copy the file to its destination in the in-memory layer.
     *
     * @param string                                $source      The path to copy from.
     * @param string                                $destination The path to copy to.
     * @param Config|array{visibility?:string}|null $config      Flysystem's per-operation config.
     *
     * @throws FilesystemException
     *
     * @see FilesystemAdapter::copy()
     */
    public function copy(string $source, string $destination, $config = null): void
    {
        $source      = $this->pathNormalizer->normalizePath($source);
        $destination = $this->pathNormalizer->normalizePath($destination);

        if (!$this->fileExists($source)) {
            throw UnableToCopyFile::fromLocationTo($source, $destination);
        }

        try {
            $this->inMemoryFiles->write($destination, $this->read($source), $this->toConfig($config));
        } catch (FilesystemException $exception) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $exception);
        }

        if ($this->deletedFiles->fileExists($destination)) {
            $this->deletedFiles->delete($destination);
        }
    }

    /**
     * Find a path's attributes by listing its parent directory, so all three layers are consulted.
     *
     * @param string $path The path to describe.
     *
     * @throws FilesystemException
     */
    private function getAttributes(string $path): StorageAttributes
    {
        $path = $this->pathNormalizer->normalizePath($path);

        $parentDirectoryContents = $this->listContents(dirname($path), false);
        /**
         * Listings yield `StorageAttributes`; both its implementations expose `::path()`.
         *
         * @var FileAttributes $entry
         */
        foreach ($parentDirectoryContents as $entry) {
            if ($entry->path() == $path) {
                return $entry;
            }
        }
        throw UnableToRetrieveMetadata::lastModified($path, 'file does not exist');
    }

    /**
     * Return the modified time of a file, from whichever layer provides it.
     *
     * @param string $path The path to read the modified time of.
     */
    public function lastModified(string $path): FileAttributes
    {
        $path = $this->pathNormalizer->normalizePath($path);

        $storageAttributes = $this->getAttributes($path);

        return new FileAttributes(
            $path,
            null,
            $storageAttributes->visibility(),
            $storageAttributes->lastModified() ?? 0
        );
    }

    /**
     * Return the size of a file, from whichever layer provides it.
     *
     * @param string $path The path to read the size of.
     *
     * @see FilesystemAdapter::fileSize()
     */
    public function fileSize(string $path): FileAttributes
    {
        $path = $this->pathNormalizer->normalizePath($path);

        switch (true) {
            case $this->deletedFiles->fileExists($path):
                throw UnableToRetrieveMetadata::fileSize($path, 'file does not exist');
            case $this->inMemoryFiles->fileExists($path):
                $fileSize = $this->inMemoryFiles->fileSize($path)->fileSize();
                break;
            case $this->delegateFilesystemAdapter->fileExists($path):
                $fileSize = $this->delegateFilesystemAdapter->fileSize($path)->fileSize();
                break;
            default:
                throw UnableToRetrieveMetadata::fileSize($path, 'file does not exist');
        }

        return new FileAttributes($path, $fileSize);
    }

    /**
     * Return the MIME type of a file, from whichever layer provides it.
     *
     * @param string $path The path to read the MIME type of.
     *
     * @see FilesystemAdapter::mimeType()
     */
    public function mimeType(string $path): FileAttributes
    {
        $path = $this->pathNormalizer->normalizePath($path);

        switch (true) {
            case $this->deletedFiles->fileExists($path):
                throw UnableToRetrieveMetadata::mimeType($path, 'file does not exist');
            case $this->inMemoryFiles->fileExists($path):
                $mimeType = $this->inMemoryFiles->mimeType($path)->mimeType();
                break;
            case $this->delegateFilesystemAdapter->fileExists($path):
                $mimeType = $this->delegateFilesystemAdapter->mimeType($path)->mimeType();
                break;
            default:
                throw UnableToRetrieveMetadata::mimeType($path, 'file does not exist');
        }

        return new FileAttributes($path, null, null, null, $mimeType);
    }

    /**
     * Set the visibility of a file or directory in the in-memory layer.
     *
     * The delegate filesystem is never modified: a file which exists only there is first copied into the
     * in-memory layer, then its visibility is set there.
     *
     * @param string $path       The path whose visibility to set.
     * @param string $visibility One of {@see Visibility}'s constants.
     *
     * @throws FilesystemException
     */
    public function setVisibility(string $path, string $visibility): void
    {
        if (!in_array($visibility, [Visibility::PUBLIC, Visibility::PRIVATE], true)) {
            throw InvalidVisibilityProvided::withVisibility($visibility, "either 'public' or 'private'");
        }

        $path = $this->pathNormalizer->normalizePath($path);

        if ($this->deletedFiles->fileExists($path)) {
            throw UnableToSetVisibility::atLocation($path, 'file does not exist');
        }

        if ($this->inMemoryFiles->fileExists($path)) {
            $this->inMemoryFiles->setVisibility($path, $visibility);
            return;
        }

        if ($this->delegateFilesystemAdapter->fileExists($path)) {
            // Copy on write.
            $this->inMemoryFiles->write(
                $path,
                $this->delegateFilesystemAdapter->read($path),
                new Config([Config::OPTION_VISIBILITY => $visibility])
            );
            return;
        }

        if ($this->directoryExists($path)) {
            $directoryConfig = new Config(
                [
                    Config::OPTION_VISIBILITY           => $visibility,
                    Config::OPTION_DIRECTORY_VISIBILITY => $visibility,
                ]
            );
            $this->inMemoryFiles->createDirectory($path, $directoryConfig);
            $this->inMemoryFiles->setVisibility($path, $visibility);
            return;
        }

        throw UnableToSetVisibility::atLocation($path, 'file does not exist');
    }

    /**
     * Return the visibility of a file or directory, from whichever layer provides it.
     *
     * @param string $path The path to read the visibility of.
     *
     * @see FilesystemAdapter::visibility()
     * @see \League\Flysystem\Local\LocalFilesystemAdapter::visibility()
     */
    public function visibility(string $path): FileAttributes
    {
        $path = $this->pathNormalizer->normalizePath($path);

        if (!$this->fileExists($path) && !$this->directoryExists($path)) {
            throw UnableToRetrieveMetadata::visibility($path, 'file does not exist');
        }

        $visibility = $this->inMemoryFiles->fileExists($path) || $this->inMemoryFiles->directoryExists($path)
            ? $this->inMemoryFiles->visibility($path)->visibility()
            : $this->delegateFilesystemAdapter->visibility($path)->visibility();

        return new FileAttributes($path, null, $visibility);
    }

    /**
     * Determine whether a directory exists, unless this session has deleted it.
     *
     * @param string $path The path to check.
     */
    public function directoryExists(string $path): bool
    {
        $path = $this->pathNormalizer->normalizePath($path);

        if ($this->isDirectoryDeleted($path)) {
            return false;
        }

        return $this->directoryExistsIn($path, $this->inMemoryFiles)
            || $this->directoryExistsIn($path, $this->delegateFilesystemAdapter);
    }

    /**
     * Determine whether a directory exists in one particular adapter.
     *
     * `FilesystemAdapter::directoryExists()` was introduced in `league/flysystem` v3; when it is absent
     * fall back to listing the parent directory.
     *
     * @param string            $path              The path to check.
     * @param FilesystemAdapter $filesystemAdapter The adapter to check in.
     *
     * @throws FilesystemException
     */
    protected function directoryExistsIn(string $path, FilesystemAdapter $filesystemAdapter): bool
    {
        $path = $this->pathNormalizer->normalizePath($path);

        if (method_exists($filesystemAdapter, 'directoryExists')) {
            // v2's `FilesystemAdapter` does not declare `::directoryExists()`, so its return value is
            // untyped there; anything other than `true` is treated as the directory not existing.
            return true === $filesystemAdapter->directoryExists($path);
        }

        return $this->directoryExistsByListing($path, $filesystemAdapter);
    }

    /**
     * Determine whether a directory exists by listing its parent directory and looking for it.
     *
     * @param string            $path              The path to check.
     * @param FilesystemAdapter $filesystemAdapter The adapter to list from.
     *
     * @throws FilesystemException
     */
    protected function directoryExistsByListing(string $path, FilesystemAdapter $filesystemAdapter): bool
    {
        $path = $this->pathNormalizer->normalizePath($path);

        $parentDirectoryPath = $this->pathNormalizer->normalizePath(dirname($path));

        if ($parentDirectoryPath === $path) {
            // The root directory must exist.
            return true;
        }

        /**
         * Listings yield `StorageAttributes`; both its implementations expose `::isDir()`.
         *
         * @var FileAttributes $entry
         */
        foreach ($filesystemAdapter->listContents($parentDirectoryPath, false) as $entry) {
            if ($entry->path() == $path) {
                return $entry->isDir();
            }
        }

        return false;
    }

    /**
     * Return the normalizer this adapter and its in-memory layers share.
     */
    public function getNormalizer(): PathNormalizer
    {
        return $this->pathNormalizer;
    }

    /**
     * Return the path in its canonical form, as this adapter stores it.
     *
     * @param string $path The path to normalize.
     */
    public function normalizePath(string $path): string
    {
        return $this->pathNormalizer->normalizePath($path);
    }
}
