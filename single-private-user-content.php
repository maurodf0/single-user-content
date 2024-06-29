<?php get_header(); ?>

<div class="main" style="max-width: 800px; margin: 0 auto;">
<?php if(is_user_logged_in()){
    $user = wp_get_current_user();
    $user_email = $user->user_email;
    echo $user_email;

    $email_post = get_post_meta($post->ID, 'email', true);
    echo $email_post;

    if($user_email === $email_post){?>

        <h1><?php the_title(); ?></h1>
        <?php the_content();

    }
} else { ?>
    <h1>Per vedere il contenuto devi essere loggato, fai login per visualizzarlo</h1>
    <div class="button-container">
        <a href="<?php esc_url(wp_login_url()); ?>" class="button">Login</a>
    </div>
<?php } ?>

</div>