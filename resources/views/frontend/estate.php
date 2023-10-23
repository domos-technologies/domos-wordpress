<?php
    global $post;
    $estatePost = \Domos\Core\EstatePost::fromPost($post);
    $estate = $estatePost->data;
?>

<?php get_header(); ?>

<div class="domos-estate">

<?php
try {
    echo view('frontend.adler.estate', [
        'estate' => $estate,
    ])->render();
} catch (\Throwable $th) {
    dd($th);
}
?>

</div>

<?php get_footer(); ?>
