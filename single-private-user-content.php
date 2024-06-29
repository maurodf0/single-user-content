<?php get_header(); ?>

<div class="sucplugin-main">
<?php if(is_user_logged_in()){
    $user = wp_get_current_user();

    $userlogin = $user->user_login;
    $username_post = get_post_meta($post->ID, 'username', true);

    // Verifico che l'utente corrente sia lo stesso che è inserito in backend
    if($userlogin === $username_post){?>

        <h1><?php the_title(); ?></h1>
        <?php the_content();

    } else { ?>
        <h1>Questo non è il contenuto destinato a te, torna in Homepage o conttataci</h1>
        <div class="button-container">
            <a class="sucplugin-btn" href="<?php echo esc_url(home_url()); ?>" class="button"><?php _e('Home', 'sucplugin')?></a>
        </div>
  <?php  }
} else { ?>
    <h1><?php _e('Per vedere il contenuto devi essere loggato, fai login per visualizzarlo', 'sucplugin')?></h1>
    <div class="button-container">
        <a class="sucplugin-btn" href="<?php echo esc_url(wp_login_url()); ?>" class="button">Login</a>
    </div>
<?php } ?>

</div>

<?php get_footer(); ?>