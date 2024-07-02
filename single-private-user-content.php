<?php get_header(); ?>

<div class="single-user-content-main">
<?php if(is_user_logged_in()){
    $user = wp_get_current_user();

    $userlogin = $user->user_login;
    $username_post = get_post_meta($post->ID, 'username', true);

    // Verifico che l'utente corrente sia lo stesso che è inserito in backend
    if($userlogin === $username_post){?>

        <h1><?php the_title(); ?></h1>
        <?php the_content();

    } else { ?>
        <h1><?php esc_html_e('Questo non è il contenuto destinato a te, torna in Homepage o conttataci', 'single-user-content')?></h1>
        <div class="button-container">
            <a class="single-user-content-btn" href="<?php echo esc_url(home_url()); ?>" class="button"><?php esc_html_e('Home', 'single-user-content')?></a>
        </div>
  <?php  }
} else { ?>
    <h1><?php esc_html_e('Per vedere il contenuto devi essere loggato, fai login per visualizzarlo', 'single-user-content')?></h1>
    <div class="button-container">
        <a class="single-user-content-btn" href="<?php echo esc_url(wp_login_url()); ?>" class="button"><?php esc_html_e('Login', 'single-user-content') ?></a>
    </div>
<?php } ?>

</div>

<?php get_footer(); ?>