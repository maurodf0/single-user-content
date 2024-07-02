<?php
/*
Plugin Name: Single User Content
Author: Mauro De Falco
Description: Create a private page for single user registered on your site. Only the designed user can see the post, fast, secure, free.
Version: 1.0
Text Domain: single-user-content
Domain Path: /languages
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SingleUserContent {
    function __construct() {
        add_action('init', array($this, 'add_cpt'));
        add_action('wp_enqueue_scripts', array($this, 'add_style'));
        add_filter('single_template', array($this, 'load_single_template'));
        add_action('add_meta_boxes', array($this, 'add_metaboxes'));
        add_action('save_post', array($this, 'save_metabox_field'));
        add_action('new_to_publish', array($this, 'save_metabox_field'));
        // Flush permalinks quando il plugin viene attivato
        register_activation_hook(__FILE__, array($this, 'flush_rewrite_rules_on_activation'));
    }

    // Registrazione del Custom Post Type
    function add_cpt() {
        register_post_type('private-user-content',
            array(
                'labels' => array(
                    'name' => __('Single User Content', 'single-user-content'),
                    'singular_name' => __('Single User Content', 'single-user-content'),
                    'add_new' => __('Add New', 'single-user-content'),
                    'add_new_item' => __('Add New Single User Content', 'single-user-content'),
                    'edit_item' => __('Edit Single User Content', 'single-user-content'),
                    'new_item' => __('New Single User Content', 'single-user-content'),
                    'view_item' => __('View Single User Content', 'single-user-content'),
                    'search_items' => __('Search Single User Content', 'single-user-content'),
                    'not_found' => __('No Single User Content found', 'single-user-content'),
                    'not_found_in_trash' => __('No Single User Content found in Trash', 'single-user-content'),
                    'all_items' => __('All Single User Content', 'single-user-content'),
                    'archives' => __('Single User Content Archives', 'single-user-content'),
                    'insert_into_item' => __('Insert into Single User Content', 'single-user-content'),
                    'uploaded_to_this_item' => __('Uploaded to this Single User Content', 'single-user-content'),
                    'filter_items_list' => __('Filter Single User Content list', 'single-user-content'),
                    'items_list_navigation' => __('Single User Content list navigation', 'single-user-content'),
                    'items_list' => __('Single User Content list', 'single-user-content'),
                ),
                'public' => true,
                'has_archive' => true,
                'show_in_rest' => true, // Supporto per il blocco editor
                'supports' => array('title', 'editor', 'custom-fields', 'thumbnail', 'excerpt', 'comments', 'revisions'), // Aggiungi il supporto per i campi personalizzati e altre funzionalità
                'rewrite' => array('slug' => 'private-user-content'), // Personalizza lo slug del CPT
                'menu_position' => 5, // Posizione nel menu di amministrazione
                'menu_icon' => 'dashicons-admin-post', // Icona nel menu di amministrazione
            )
        );
    }

    // Aggiunta dello stile
    function add_style() {
        //Load CSS only if is a private-user-content CPT
        if (is_singular('private-user-content')) {
        $version = '1.0.1'; // Aggiorna questa versione manualmente per miglior compatibiktò con WP
    wp_enqueue_style('single-user-content-style', plugins_url('style.css', __FILE__), array(), $version);
        }
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
            'single-user-content-metabox', // id
            __('User for Private User Content', 'single-user-content'), // titolo
            array($this, 'metabox_content'), // callback
            'private-user-content', // post type
            'normal', // posizione
            'high' // priorità
        );
    }

    // HTML per la metabox
    function metabox_content() {
        global $post;
        // Use nonce for verification to secure data sending
        wp_nonce_field(basename(__FILE__), 'loginname_nonce');
        ?>
        <div style="margin-top:25px">
            <label for="user"><?php esc_html_e('Select User', 'single-user-content'); ?></label>
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
    function save_metabox_field($post_id) {
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

        // Salvataggio dell'username
        if (isset($_POST['user'])) {
            $user_login = sanitize_text_field($_POST['user']);
            update_post_meta($post_id, 'username', $user_login);

        }
    }

    // Funzione per flushare i permalinks all'attivazione del plugin
    function flush_rewrite_rules_on_activation() {
        // Registrazione del Custom Post Type
        $this->add_cpt();
        // Flush permalinks
        flush_rewrite_rules();
    }
}

// Inizializzazione della classe
$singleUserContent = new SingleUserContent();
?>
