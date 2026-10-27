<?php
/**
 * The layer holding files which have been written during the read-only session.
 *
 * A marker subclass so the two in-memory layers of {@see ReadOnlyFileSystemAdapter} are distinguishable
 * in stack traces, in a debugger, and to static analysis.
 *
 * @package BrianHenryIE\FlysystemReadOnly
 */

namespace BrianHenryIE\FlysystemReadOnly;

/**
 * An {@see InMemoryFilesystemAdapter} holding the files written during the read-only session.
 */
class ModifiedFilesInMemoryFilesystemAdapter extends InMemoryFilesystemAdapter
{
}
