<?php
/*
Plugin Name: Single User Content
Author: Mauro De Falco
Author URI: https://maurodefalco.it
Description: A Simple WordPress Plugin, for create a post assigned to a specific user into your site.
Version: 1.0
Text Domain: sucplugin
Domain Path: /languages
*/

// creo una classe per evitare problemi di incompatibilità con altri plugin
class SingleUserContent {
    function __construct() {
        add_action('init', array($this, 'add_cpt'));
        add_action('wp_enqueue_scripts', array($this, 'add_style'));
        add_filter('single_template', array($this, 'load_single_template'));
        add_filter('single_template', array($this, 'load_single_template'));
        add_action('add_meta_boxes', array($this, 'add_metaboxes'));
        add_action('save_post', array($this, 'save_metabox_field'));
        add_action('new_to_publish', array($this, 'save_metabox_field'));
    }


// Registrazione del Custom Post Type
function add_cpt() {
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
function add_style() {
    wp_enqueue_style('sucplugin-style', plugins_url('style.css', __FILE__));
}

// Sovrascrittura del template singolo per il CPT
function load_single_template($single) {
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
function add_metaboxes() {
    add_meta_box(
        'sucplugin-metabox', // id
        'Email for Private User Content', // titolo
        'sucplugin_metabox_content', // callback
        'private-user-content', // post type
        'normal', // posizione
        'high' // priorità
    );
}


// HTML per la metabox
function sucplugin_metabox_content() {
    global $post;
    // Use nonce for verification to secure data sending
    wp_nonce_field(basename(__FILE__), 'loginname_nonce');
    ?>
    <div style="margin-top:25px">
    <label for="user">Select User</label>
    <select name="user" id="user">
        <?php 
        $all_users = get_users(); 
        foreach($all_users as $user) { ?>
            <option value="<?php echo esc_attr($user->user_login); ?>" <?php selected($user->user_login, get_post_meta($post->ID, 'username', true)); ?>>
                <?php echo esc_html($user->user_login); ?>
            </option>
        <?php } ?>
    </select>
    </div>
    <?php
}

// Salvataggio dei dati della metabox
function sucplugin_save_metabox_field($post_id) {
    // Verify nonce
    if (!isset($_POST['loginname_nonce']) || !wp_verify_nonce($_POST['loginname_nonce'], basename(__FILE__))) {
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
    if (isset($_POST['user'])) {
        $email_inserted = sanitize_text_field($_POST['user']);
        update_post_meta($post_id, 'username', $email_inserted);

        // Salvataggio nel database personalizzato
        global $wpdb;
        $table = $wpdb->base_prefix . 'project_bids_mitglied';

        // Inserimento o aggiornamento del record
        $wpdb->replace(
            $table,
            array(
                'col_post_id' => $post_id, // ID del post
                'col_value'   => $email_inserted  // Valore dell'email
            ),
            array(
                '%d', // %d - intero
                '%s', // %s - stringa
            )
        );
    }
}
}
$singleUserContent = new SingleUserContent();

?>
