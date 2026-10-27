<?php
/**
 * The layer holding tombstones for files and directories deleted during the read-only session.
 *
 * A marker subclass so the two in-memory layers of {@see ReadOnlyFileSystemAdapter} are distinguishable
 * in stack traces, in a debugger, and to static analysis.
 *
 * @package BrianHenryIE\FlysystemReadOnly
 */

namespace BrianHenryIE\FlysystemReadOnly;

/**
 * An {@see InMemoryFilesystemAdapter} holding tombstones for paths deleted during the read-only session.
 */
class DeletedFilesInMemoryFilesystemAdapter extends InMemoryFilesystemAdapter
{
}
