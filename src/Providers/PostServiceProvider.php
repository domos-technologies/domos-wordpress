<?php

namespace Domos\Core\Providers;

use Domos\Core\EstatePost;
use Domos\Core\Support\View;

class PostServiceProvider implements Provider
{
    /**
     * Stores the plugin version that last regenerated the rewrite rules.
     */
    protected const REWRITE_VERSION_OPTION = 'immocore_rewrite_version';

    public function register()
    {
    }

    public function boot()
    {
        EstatePost::register();

        // Flushing before wp_loaded is deferred by WordPress itself, but hooking
        // wp_loaded explicitly means the flush and the version marker below
        // happen in the same callback, so the marker cannot get ahead of it.
        add_action('wp_loaded', [$this, 'flushRewriteRulesOnce']);
    }

    /**
     * WordPress regenerates the stored rewrite rules when the permalink settings
     * change, or when a plugin asks for it on activation - but never on update.
     * The `objekte` permastruct is therefore missing on a fresh install (every
     * estate URL serves a 404 until someone happens to re-save Settings ->
     * Permalinks) and would stay missing after a future rewrite change.
     *
     * Flushing once per plugin version covers both cases.
     *
     * Soft flush on purpose: that regenerates the `rewrite_rules` option, which
     * is where the estate permastruct lives. A hard flush would additionally
     * rewrite the `# BEGIN WordPress` block in .htaccess (Apache/LiteSpeed) or
     * web.config (IIS) during admin requests, and server config files are not
     * the plugin's business to change behind a site owner's back.
     */
    public function flushRewriteRulesOnce(): void
    {
        if (get_option(self::REWRITE_VERSION_OPTION) === DOMOS_CORE_VERSION) {
            return;
        }

        flush_rewrite_rules(false);

        update_option(self::REWRITE_VERSION_OPTION, DOMOS_CORE_VERSION, true);
    }

//    public function addMetaBox()
//    {
//        add_meta_box(
//            "author_meta_box", // Meta box ID
//            "DOMOS Objekt", // Meta box title
//            [$this, 'renderMetaBox'], // Meta box callback function
//            "domos_estate", // The custom post type parameter 1
//            "side", // Meta box location in the edit screen
//            "high" // Meta box priority
//        );
//    }
//    function renderMetaBox()
//    {
//        wp_nonce_field('author-nonce', 'meta-box-nonce');
//        global $post;
//
//        echo View::render('estates/meta-box', ['test' => 'hello']);
//    }
}
