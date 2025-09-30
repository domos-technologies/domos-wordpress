<?php
    global $post;
	$instance = \Domos\Core\DOMOS::instance();
	$language = $instance->getFrontendLanguage();
	$shouldFallbackToDefaultLanguageExpose = $instance->shouldFallbackToDefaultLanguageExpose();

    $estatePost = \Domos\Core\EstatePost::fromPost($post, language: $language);
    $estate = $estatePost->data;

	if ($estate->expose === null) {
		if ($shouldFallbackToDefaultLanguageExpose) {
			$estatePost = \Domos\Core\EstatePost::fromPost($post);
			$estate = $estatePost->data;
		} else {
			// Show 404
			status_header(404);
			include get_404_template();
			exit;
		}
	}

	// Render WordPress header
	get_header();

	// Render page content: estate page
	try {
		echo view('frontend.adler.estate', [
			'estate' => $estate,
		])->render();
	} catch (\Throwable $th) {
		// Log error (wordpress)
		error_log($th->getMessage());

		echo "Fehler beim Anzeigen der Immobilie. Bitte kontaktieren Sie einen Ansprechpartner.";
	}

	// Render WordPress footer
	get_footer();
?>

<!--<style>-->
<!--	body, :root, :host, .domos-estate {-->
<!--		background: black;-->
<!--		color: white;-->
<!--	}-->
<!--</style>-->
