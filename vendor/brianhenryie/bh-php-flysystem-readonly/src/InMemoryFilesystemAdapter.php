<?php
/**
 * An extension of League's in-memory adapter which:
 *
 * * back-fills `::directoryExists()` when `league/flysystem` v2 is installed
 * * normalizes paths using an injectable {@see PathNormalizer}
 * * creates parent directories when a file is written, so directory listings are complete
 * * records the visibility of directories, which League's adapter does not track
 *
 * @package BrianHenryIE\FlysystemReadOnly
 */

namespace BrianHenryIE\FlysystemReadOnly;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter as LeagueInMemoryFilesystemAdapter;
use League\Flysystem\PathNormalizer;
use League\Flysystem\Visibility;
use League\Flysystem\WhitespacePathNormalizer;
use League\MimeTypeDetection\MimeTypeDetector;

/**
 * An in-memory adapter which records directory visibility and creates parent directories on write.
 */
class InMemoryFilesystemAdapter extends LeagueInMemoryFilesystemAdapter implements
    FlysystemAdapterBackCompatTraitInterface
{
    use FlysystemAdapterBackCompatTrait;

    protected PathNormalizer $pathNormalizer;

    /**
     * League's `$defaultVisibility` is private, so keep our own copy.
     */
    protected string $defaultFileVisibility;

    /**
     * League's adapter has no concept of a directory's visibility – directories only exist by
     * implication – so record it here, keyed on the normalized path without a trailing slash.
     *
     * @var array<string, string>
     */
    protected array $directoryVisibility = [];

    /**
     * Constructor.
     *
     * @param string            $defaultVisibility Visibility applied to files and directories written without one.
     * @param ?MimeTypeDetector $mimeTypeDetector  Detector used for `::mimeType()`; League's default when null.
     * @param ?PathNormalizer   $pathNormalizer    Normalizer used for path comparisons; whitespace when null.
     */
    public function __construct(
        string $defaultVisibility = Visibility::PUBLIC,
        ?MimeTypeDetector $mimeTypeDetector = null,
        ?PathNormalizer $pathNormalizer = null
    ) {
        parent::__construct($defaultVisibility, $mimeTypeDetector);

        $this->defaultFileVisibility = $defaultVisibility;
        $this->pathNormalizer        = $pathNormalizer ?? new WhitespacePathNormalizer();
    }

    /**
     * Return the path in its canonical form, as this adapter stores it.
     *
     * @param string $path The path to normalize.
     *
     * @see FlysystemAdapterBackCompatTrait::directoryExistsImplementation()
     */
    public function normalizePath(string $path): string
    {
        return $this->pathNormalizer->normalizePath($path);
    }

    /**
     * Write a {@see InMemoryFile}, first creating each of its ancestor directories.
     *
     * @param string $path     The path to write to.
     * @param string $contents The file's contents.
     * @param Config $config   Flysystem's per-operation config, e.g. visibility.
     *
     * @see \League\Flysystem\FilesystemAdapter::write()
     */
    public function write(string $path, string $contents, Config $config): void
    {
        /**
         * `parent::createDirectory()` writes a dummy file to record the directory; without this guard
         * `write()` → `createDirectories()` → `createDirectory()` → `write()` would recurse forever.
         */
        if (false === strpos($path, self::DUMMY_FILE_FOR_FORCED_LISTING_IN_FLYSYSTEM_TEST)) {
            $this->createDirectories($path, $config);
        }

        parent::write($path, $contents, $config);
    }

    /**
     * Copy a file, first creating each of the destination's ancestor directories.
     *
     * @param string $source      The path to copy from.
     * @param string $destination The path to copy to.
     * @param Config $config      Flysystem's per-operation config, e.g. visibility.
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $this->createDirectories($destination, $config);

        parent::copy($source, $destination, $config);
    }

    /**
     * Create a directory, recording its visibility, which League's adapter does not track.
     *
     * @param string $path   The directory to create.
     * @param Config $config Flysystem's per-operation config, e.g. directory visibility.
     */
    public function createDirectory(string $path, Config $config): void
    {
        $visibility = $config->get(
            Config::OPTION_DIRECTORY_VISIBILITY,
            $config->get(Config::OPTION_VISIBILITY, $this->defaultFileVisibility)
        );

        // `Config::get()` returns `mixed`; anything that is not a visibility string is treated as absent.
        $this->directoryVisibility[$this->directoryKey($path)] = is_string($visibility)
            ? $visibility
            : $this->defaultFileVisibility;

        parent::createDirectory($path, $config);
    }

    /**
     * Delete a directory, discarding the recorded visibility of it and of everything below it.
     *
     * @param string $path The directory to delete.
     */
    public function deleteDirectory(string $path): void
    {
        $prefix = $this->directoryKey($path);
        foreach (array_keys($this->directoryVisibility) as $key) {
            if ($key === $prefix || 0 === strpos($key, $prefix . '/')) {
                unset($this->directoryVisibility[$key]);
            }
        }

        parent::deleteDirectory($path);
    }

    /**
     * Set the visibility of a file, or record it when the path is a directory.
     *
     * @param string $path       The path whose visibility to set.
     * @param string $visibility One of {@see Visibility}'s constants.
     */
    public function setVisibility(string $path, string $visibility): void
    {
        if (!$this->fileExists($path) && $this->directoryExists($path)) {
            $this->directoryVisibility[$this->directoryKey($path)] = $visibility;
            return;
        }

        parent::setVisibility($path, $visibility);
    }

    /**
     * Return the visibility of a file, or the recorded visibility when the path is a directory.
     *
     * @param string $path The path to read the visibility of.
     */
    public function visibility(string $path): FileAttributes
    {
        if (!$this->fileExists($path)) {
            // Assume it is a directory.
            return new FileAttributes(
                $path,
                null,
                $this->directoryVisibility[$this->directoryKey($path)] ?? Visibility::PUBLIC
            );
        }

        return parent::visibility($path);
    }

    /**
     * Return the modified time of a file; directories, which have none, report `0`.
     *
     * @param string $path The path to read the modified time of.
     */
    public function lastModified(string $path): FileAttributes
    {
        if (!$this->fileExists($path)) {
            // Assume it is a directory.
            return new FileAttributes($path, null, null, 0);
        }

        return parent::lastModified($path);
    }

    /**
     * Create each ancestor directory of the given file path.
     *
     * @param string $path   The file path whose ancestors to create.
     * @param Config $config Flysystem's per-operation config, e.g. directory visibility.
     */
    protected function createDirectories(string $path, Config $config): void
    {
        $pathDirs = explode('/', dirname($path));
        for ($level = 0; $level < count($pathDirs); $level++) {
            $dir = implode('/', array_slice($pathDirs, 0, $level + 1));
            $this->createDirectory($dir, $config);
        }
    }

    /**
     * Return the key a directory's visibility is recorded under: normalized, without a trailing slash.
     *
     * @param string $path The directory path to key.
     */
    protected function directoryKey(string $path): string
    {
        return rtrim($this->normalizePath($path), '/');
    }
}
