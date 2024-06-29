<?php
/*
Plugin Name: Single User Content
Author: Mauro De Falco
Version: 1.0
text-domain: sucplugin
*/

// Registrazione del Custom Post Type
add_action('init', 'sucplugin_add_cpt');
function sucplugin_add_cpt() {
    register_post_type('private-user-content',
        array(
            'labels' => array(
                'name' => 'Single User Content',
                'singular_name' => 'Single User Content'
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true, // Supporto per il blocco editor
            'supports' => array('title', 'editor', 'custom-fields') // Aggiungi il supporto per i campi personalizzati
        )
    );
}

// Aggiunta dello stile
function sucplugin_add_style() {
    wp_enqueue_style('sucplugin-style', plugins_url('style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'sucplugin_add_style');

// Sovrascrittura del template singolo per il CPT
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

// Aggiunta della metabox per Private User Content
function sucplugin_add_metaboxes() {
    add_meta_box(
        'sucplugin-metabox', // id
        'Email for Private User Content', // titolo
        'sucplugin_metabox_content', // callback
        'private-user-content', // post type
        'normal', // posizione
        'high' // priorità
    );
}
add_action('add_meta_boxes', 'sucplugin_add_metaboxes');

// HTML per la metabox
function sucplugin_metabox_content() {
    global $post;
    // Use nonce for verification to secure data sending
    wp_nonce_field( basename( __FILE__ ), 'email_nonce' );
    ?>
    <label for="email">Email</label>
    <input type="email" name="email" id="email" value="<?php echo esc_attr(get_post_meta($post->ID, 'email', true)); ?>">
    <?php
}

// Salvataggio dei dati della metabox
function sucplugin_save_metabox_field($post_id) {
    // Verify nonce
    if (!isset($_POST['email_nonce']) || !wp_verify_nonce($_POST['email_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    // Check autosave
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return $post_id;
    }

    // Check permissions
    if (isset($_POST['post_type']) && $_POST['post_type'] == 'private-user-content') {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Salvataggio dell'email
    if (isset($_POST['email'])) {
        $email_inserted = sanitize_email($_POST['email']);
        update_post_meta($post_id, 'email', $email_inserted);

        // Salvataggio nel database personalizzato
        global $wpdb;
        $table = $wpdb->base_prefix . 'project_bids_mitglied';

        // Inserimento o aggiornamento del record
        $wpdb->replace(
            $table,
            array(
                'col_post_id' => $post_id, // as we are having it by default with this function
                'col_value'   => $email_inserted  // pass the email's string
            ),
            array(
                '%d', // %d - integer
                '%s', // %s - string
            )
        );
    }
}
add_action('save_post', 'sucplugin_save_metabox_field');
add_action('new_to_publish', 'sucplugin_save_metabox_field');
?>
