<?php

namespace Domos\Core;

use Domos\Core\Exceptions\CouldNotCreatePost;
use Domos\Core\Exceptions\EstateAlreadyExists;
use Domos\Core\Exceptions\EstateNotFound;
use SchemaImmo\Estate;
use SchemaImmo\Estate\UsageByType;
use WP_Post;
use WP_Query;

class EstatePost
{
	public const POST_TYPE = "domos_estate";

	public ?WP_Post $post = null;
	public ?string $id = null;
	public string $domosID;
	public string $title;
	public Estate $data;

	public function __construct(
		string $domosID,
		string $title,
		Estate $data,
		?WP_Post $post = null,
	) {
		$this->domosID = $domosID;
		$this->title = $title;
		$this->data = $data;

		if ($post) {
			$this->post = $post;
			$this->id = $post->ID;
		}
	}

	public static function getEstateDataMetaKey(?string $language = null): string
	{
		$default_language = DOMOS::instance()->options->default_language->get();

		if ($language && $language !== $default_language) {
			return "estate_data_{$language}";
		}

		return "estate_data";
	}

	public static function fromPost(WP_Post $post, ?string $language = null): self
	{
		$data = get_post_meta($post->ID, self::getEstateDataMetaKey($language), true);

		if (!$data) {
			$data = get_post_meta($post->ID, "estate_data", true);
		}

		// If for some reason the data transfer into WordPress broke and we get invalid data here,
		// then we try to fail somewhat gracefully and just return an empty Estate.
		// This will ensure estates can still be synchronized!
		if (is_string($data) || empty($data)) {
			$estate = new Estate(
				id: get_post_meta($post->ID, "domos_id", true),
				slug: get_post_meta($post->ID, "domos_id", true),
				name: $post->post_title,
			);

			error_log(
				"[Immocore EstatePost::fromPost] get_post_meta returned invalid data: \n\n{$data}",
			);
		} else {
			$estate = Estate::from($data);
		}

		$instance = new self(
			get_post_meta($post->ID, "domos_id", true),
			$post->post_title,
			$estate,
			$post,
		);

		return $instance;
	}

	public static function create(string $external_id, Estate $data, ?string $language = null)
	{
		$estate_data = $data->toArray();
		$post = self::find($external_id);

		if ($post !== null) {
			throw new EstateAlreadyExists($external_id);
		}

		$post_data = [
			"post_title" => $data->name,
			"post_name" => $data->slug,
			"post_content" => $data->texts->description ?? "",
			"post_excerpt" => $data->texts->slogan ?? "",

			"post_status" => "publish",
			"post_author" => 1,
			"post_type" => self::POST_TYPE,
		];

		$result = wp_insert_post($post_data);

		// If its an error
		if (is_wp_error($result)) {
			throw new CouldNotCreatePost($result->get_error_message());
		}

		update_post_meta($result, "domos_id", $external_id);

		add_post_meta($result, self::getEstateDataMetaKey($language), $estate_data, true);

		self::setSearchableMeta($result, $data);
	}

	public static function update(string $external_id, Estate $data, ?string $language = null)
	{
		$default_language = DOMOS::instance()->options->default_language->get();
		$estate_data = $data->toArray();

		$post = self::find($external_id);

		if ($post === null) {
			throw new EstateNotFound($external_id);
		}

		if ($language === $default_language) {
			$default_language_data = [
				"post_title" => $data->name,
				"post_name" => $data->slug,
				"post_content" => $data->texts->description ?? "",
				"post_excerpt" => $data->texts->slogan ?? "",
			];
		} else {
			$default_language_data = [];
		}

		// Update title
		wp_update_post(
			[
				"ID" => $post->id,
				"comment_status" => "closed",
				"post_name" => $data->slug,
				...$default_language_data,
			],
			true,
		);

		// Update data
		update_post_meta($post->id, self::getEstateDataMetaKey($language), $estate_data);

		// Only set searchable meta for the default language
		if ($language === $default_language) {
			self::setSearchableMeta($post->id, $data);
		}
	}

	protected static function setSearchableMeta(string $post_id, Estate $data)
	{
		if ($data->address?->city) {
			update_post_meta($post_id, "estate_city", $data->address?->city);
		} else {
			delete_post_meta($post_id, "estate_city");
		}

		if ($data->address?->country) {
			update_post_meta($post_id, "estate_country", $data->address?->country);
		} else {
			delete_post_meta($post_id, "estate_country");
		}

		if ($data->usage->main?->value) {
			update_post_meta($post_id, "estate_main_usage", $data->usage->main?->value);
		} else {
			delete_post_meta($post_id, "estate_main_usage");
		}

		if (count($data->usage->all) > 0) {
			$usages = array_keys($data->usage->all);

			update_post_meta($post_id, "estate_usages", json_encode($usages));
		} else {
			delete_post_meta($post_id, "estate_usages");
		}
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
	public static function findUnneeded(array $excluded_ids, ?string $language = null): array
	{
		$args = [
			"post_type" => self::POST_TYPE,
			"posts_per_page" => -1, // Retrieve all posts of the specified post type.
			"meta_query" => [
				[
					"key" => "domos_id",
					"value" => $excluded_ids,
					"compare" => "NOT IN",
				],
			],
		];

		$query = new WP_Query($args);
		$estates = [];

		foreach ($query->posts as $post) {
			$estates[] = self::fromPost($post, language: $language);
		}

		return $estates;
	}

	/**
	 * Look up a post type by external ID.
	 *
	 * @param string $external_id The external ID to search for.
	 * @return WP_Post|null The found post or null if not found.
	 */
	public static function find(string $external_id, ?string $language = null): ?self
	{
		// Perform a custom query to search for the post by external ID.
		$args = [
			"post_type" => self::POST_TYPE,
			"posts_per_page" => 1,
			"meta_key" => "domos_id", // Replace with the actual meta key where you store external IDs.
			"meta_value" => $external_id,
		];

		$query = new WP_Query($args);

		// Check if a post with the external ID exists.
		if ($query->have_posts()) {
			$post = $query->posts[0];

			return self::fromPost($post, language: $language);
		} else {
			return null; // No post with the external ID found.
		}
	}

	public static function register()
	{
		//        flush_rewrite_rules();
		register_post_type("domos_estate", [
			"labels" => [
				"name" => "Objekte",
				"singular_name" => "Objekt",
			],
			"public" => true,
			"has_archive" => true,
			"rewrite" => [
				"slug" => "objekte",
				"with_front" => false,
				"pages" => true,
			],
			"revisions" => false,
			"supports" => [
				"editor",
				"custom-fields",
				"title",
				"excerpt",
				"attachments",
				"thumbnail",
			],
			"publicly_queryable" => true,
			// icon: building
			"menu_icon" => "dashicons-building",
		]);

		//        add_action('add_meta_boxes', [$this, 'addMetaBox']);
	}

	public static function template()
	{
		global $post;

		/* Checks for single template by post type */
		if ($post->post_type == self::POST_TYPE) {
			// We don't enqueue any styles here, as we're using shadow DOM for block encapsulation.
			// Thus, style tags need to be placed inside the template manually.

			wp_enqueue_script("domos-frontend--estate");
			//			wp_enqueue_script('domos-frontend--estate-external');

			return DOMOS_CORE_ROOT . "/resources/views/frontend/estate.php";
		}
	}

	public static function setPostThumbnailByUrl(
		int $post_id,
		string $url,
		?string $alt = null,
	) {
		self::deletePostThumbnailIfSet($post_id);

		// media_sideload_image may not be loaded
		if (!function_exists("media_sideload_image")) {
			require_once ABSPATH . "wp-admin/includes/media.php";
			require_once ABSPATH . "wp-admin/includes/file.php";
			require_once ABSPATH . "wp-admin/includes/image.php";
		}

		$image = \media_sideload_image($url, $post_id, $alt, "id");

		\set_post_thumbnail($post_id, $image);
	}

	protected static function deletePostThumbnailIfSet(int $post_id)
	{
		$image_id = \get_post_thumbnail_id($post_id);

		if ($image_id) {
			\wp_delete_attachment($image_id);
		}
	}
}
