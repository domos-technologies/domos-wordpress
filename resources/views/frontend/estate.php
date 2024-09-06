<?php
    global $post;
    $estatePost = \Domos\Core\EstatePost::fromPost($post);
    $estate = $estatePost->data;

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
