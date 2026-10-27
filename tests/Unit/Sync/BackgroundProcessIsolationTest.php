<?php

declare(strict_types=1);

namespace Domos\Core\Tests\Unit\Sync;

use Domos\Core\Sync\SyncQueue;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * wp-background-processing declares WP_Background_Process in the global
 * namespace, and PHP resolves a parent class to whichever copy was declared
 * first. On a site where another plugin loaded its own (older) copy before us,
 * SyncQueue silently inherited that one and the sync died with "Call to
 * undefined method Domos\Core\Sync\SyncQueue::is_active()" before it queued
 * anything.
 *
 * These assertions fail if the scoping is dropped (extends the library's global
 * class again), if the generated tree is not loaded, or if the prefix changes
 * without updating SyncQueue - they are the regression test for that customer
 * report.
 */
final class BackgroundProcessIsolationTest extends TestCase
{
    public function test_the_queue_extends_our_scoped_copy_of_the_library(): void
    {
        $parent = (new ReflectionClass(SyncQueue::class))->getParentClass();

        self::assertNotFalse($parent, 'SyncQueue must have a parent class.');
        self::assertSame(
            'DOMOS_WP_Background_Process',
            $parent->getName(),
            'SyncQueue must extend the prefixed class from src/Dependencies, not the library\'s global one.'
        );
    }

    public function test_the_scoped_parent_lives_in_our_own_source_tree(): void
    {
        $parent = (new ReflectionClass(SyncQueue::class))->getParentClass();
        self::assertNotFalse($parent);

        $file = $parent->getFileName();

        self::assertIsString($file);
        self::assertStringStartsWith(DOMOS_CORE_ROOT.'/src/Dependencies/', $file);
        self::assertStringNotContainsString('/vendor/', $file);
    }

    public function test_the_librarys_global_class_is_never_defined_by_the_plugin(): void
    {
        self::assertTrue(class_exists(SyncQueue::class));

        // No autoloading: these may only be set when our code actually uses them.
        self::assertFalse(class_exists('WP_Background_Process', false));
        self::assertFalse(class_exists('WP_Async_Request', false));

        // The method the customer's inherited copy was missing.
        self::assertTrue(method_exists(SyncQueue::class, 'is_active'));
        self::assertTrue(method_exists(SyncQueue::class, 'dispatch'));
    }
}
