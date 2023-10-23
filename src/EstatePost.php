<?php

namespace Domos\Core;

use Domos\Core\Exceptions\CouldNotCreatePost;
use Domos\Core\Exceptions\EstateAlreadyExists;
use Domos\Core\Exceptions\EstateNotFound;
use SchemaImmo\Estate;
use WP_Post;
use WP_Query;

class EstatePost
{
    public const POST_TYPE = 'domos_estate';

    public ?WP_Post $post = null;
    public ?string $id = null;
    public string $domosID;
    public string $title;
    public Estate $data;

    public function __construct(
        string $domosID,
        string $title,
        Estate $data,
        ?WP_Post $post = null
    )
    {
        $this->domosID = $domosID;
        $this->title = $title;
        $this->data = $data;

        if ($post) {
            $this->post = $post;
            $this->id = $post->ID;
        }
    }

    public static function fromPost(WP_Post $post): self
    {
        $data = get_post_meta($post->ID, 'estate_data', true);

        $estate = Estate::from($data);

        $instance = new self(
            get_post_meta($post->ID, 'domos_id', true),
            $post->post_title,
            $estate,
            $post
        );

        return $instance;
    }

    public static function create(string $external_id, Estate $data)
    {
        $estate_data = $data->toArray();
        $post = self::find($external_id);

        if ($post !== null) {
            throw new EstateAlreadyExists($external_id);
        }

        $post_data = [
            'post_title' => $data->name,
	        'post_name' => $data->slug,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => self::POST_TYPE,
        ];

        $result = wp_insert_post($post_data);

        // If its an error
        if (is_wp_error($result)) {
            throw new CouldNotCreatePost($result->get_error_message());
        }

        update_post_meta(
            $result,
            'domos_id',
            $external_id
        );

        add_post_meta(
            $result,
            'estate_data',
            $estate_data,
            true
        );
    }

    public static function update(string $external_id, Estate $data)
    {
        $estate_data = $data->toArray();

        $post = self::find($external_id);

        if ($post === null) {
            throw new EstateNotFound($external_id);
        }

        // Update title
        wp_update_post([
            'ID' => $post->id,
            'post_title' => $data->name,
	        'post_name' => $data->slug,
        ], true);

        // Update data
        update_post_meta(
            $post->id,
            'estate_data',
            $estate_data
        );
    }

    public static function delete(string $external_id)
    {
        $post = self::find($external_id);

        if ($post === null) {
            throw new EstateNotFound($external_id);
        }

        wp_delete_post($post->ID);
    }

    /**
     * @return EstatePost[]
     */
    public static function findUnneeded(array $excluded_ids): array
    {
        $args = [
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => -1, // Retrieve all posts of the specified post type.
            'meta_query'     => [
                [
                    'key'     => 'domos_id',
                    'value'   => $excluded_ids,
                    'compare' => 'NOT IN',
                ],
            ],
        ];

        $query = new WP_Query($args);
        $estates = [];

        foreach ($query->posts as $post) {
            $estates[] = self::fromPost($post);
        }

        return $estates;
    }

    /**
     * Look up a post type by external ID.
     *
     * @param string $external_id The external ID to search for.
     * @return WP_Post|null The found post or null if not found.
     */
    public static function find(string $external_id): ?self
    {
        // Perform a custom query to search for the post by external ID.
        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => 1,
            'meta_key'       => 'domos_id', // Replace with the actual meta key where you store external IDs.
            'meta_value'     => $external_id,
        );

        $query = new WP_Query($args);

        // Check if a post with the external ID exists.
        if ($query->have_posts()) {
            $post = $query->posts[0];

            return self::fromPost($post);
        } else {
            return null; // No post with the external ID found.
        }
    }

    public static function register()
    {
//        flush_rewrite_rules();
        register_post_type('domos_estate', [
            'labels' => [
                'name' => 'Objekte',
                'singular_name' => 'Objekt'
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => [
                'slug' => 'objekte',
                'with_front' => false,
                'pages' => true,
            ],
            'revisions' => false,
            'supports' => array(
                'custom-fields',
                'title',
                'attachments',
                'thumbnail'
            ),
            'publicly_queryable' => true,
            // icon: building
            'menu_icon' => 'dashicons-building',
        ]);

//        add_action('add_meta_boxes', [$this, 'addMetaBox']);
    }

    public static function template()
    {
        global $post;

        /* Checks for single template by post type */
        if ($post->post_type == self::POST_TYPE) {
			wp_enqueue_style('domos-frontend');
			wp_enqueue_script('domos-frontend--estate');

            return DOMOS_CORE_ROOT . '/resources/views/frontend/estate.php';
        }
    }
}
