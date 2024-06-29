<?php
/*
Plugin Name: Single User Content
Author: Mauro De Falco
Version: 1.0
text-domain: sucplugin
*/

add_action('init', 'sucplugin_add_cpt');
function sucplugin_add_cpt() {
    register_post_type('private-user-content',
        array(
            'labels' => array(
                'name' => 'Single User Content',
                'singular_name' => 'Single User Content'
            ),
            'public' => true,
            'has_archive' => true
        )
    );
}

add_filter('single_template', 'sucplugin_single_template');
function sucplugin_single_template($single) {
    global $post;

    // Verifica se il post è del tipo private-user-content
    if ($post->post_type == 'private-user-content') {
        // Percorso al file single-private-user-content.php nel plugin
        $plugin_template = plugin_dir_path(__FILE__) . 'single-private-user-content.php';

        // Se il file esiste, sovrascrivi il template del tema
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    return $single;
}

/* Add Metaboxes for Private User Content */
function sucplugin_add_metaboxes() {
    add_meta_box(
        'sucplugin-metabox', //id
        'Email for Private User Content', //titolo
        'sucplugin_metabox_content', //callback
        'private-user-content', //post type
        'normal', //posizione
        'high' //priorità
    );
}
add_action('add_meta_boxes', 'sucplugin_add_metaboxes');

//add the HTML for the metabox
function sucplugin_metabox_content() {?>
    <input type="email" name="email" id="email">
<?php }
?>
