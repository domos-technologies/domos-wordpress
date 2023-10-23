<?php

namespace Domos\Core\Providers;

class BlockServiceProvider implements Provider
{
    public function register()
    {
        $entrypoints_manifest = realpath(__DIR__.'/../../dist/entrypoints.json');

        if (! $entrypoints_manifest) {
            return;
        }

        $entrypoints = json_decode(file_get_contents($entrypoints_manifest));

        add_action('wp_enqueue_editor', function () use ($entrypoints) {
            wp_enqueue_script(
                'domos/editor',
                plugins_url('../dist/js/editor.js', dirname(__FILE__)),
                $entrypoints->editor->dependencies,
                null,
                true
            );
        }, 100);
    }

    public function boot()
    {
    }
}
