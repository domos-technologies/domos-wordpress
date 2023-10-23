<?php

namespace Domos\Core\Providers;

use Domos\Core\EstatePost;
use Domos\Core\Support\View;

class PostServiceProvider implements Provider
{
    public function register()
    {
    }

    public function boot()
    {
        EstatePost::register();
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
