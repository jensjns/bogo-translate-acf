<?php
/*
    Plugin Name: Bogo <3 ACF
    Description: This plugins works like a bridge between Bogo and Advanced Custom Fields to allow for translation of your Advanced Custom Fields with Bogo.
    Plugin URI: http://jensnilsson.nu
    Author: Jens Nilsson
    Author URI: http://jensnilsson.nu
    Text Domain: bogo-acf
    Domain Path: /languages/
    Version: 0.1
*/


class Bogo_Acf {

    private $bogo_acf_getting_field = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action( 'plugins_loaded', array( $this, 'bogo_acf_plugins_loaded' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'bogo_acf_select_default_template' ) );
        add_filter( 'acf/load_field', array( $this, 'bogo_acf_load_original_post_values') );

    }

    /**
     * Load pluin text-domain.
     */
    function bogo_acf_plugins_loaded() {
        load_plugin_textdomain( 'bogo-acf', 'wp-content/plugins/bogo-acf/languages', 'bogo-acf/languages' );
    }

    /**
     * Fetch data and enqueue a script that will select the correct template for the page based on the original post
     */
    function bogo_acf_select_default_template() {
        // only use select default template and load original post values if this is a page thats adds a translation
        if( array_key_exists('locale', $_REQUEST) && array_key_exists('original_post', $_REQUEST) ) {
            $original_post_id = $_REQUEST['original_post'];

            $original_post_template = get_post_meta( $original_post_id, '_wp_page_template', true );

            wp_enqueue_script( 'bogo_acf_js', plugin_dir_url( __FILE__ ) . '/bogo-translate-acf.js', 'jquery', '2013-09-11' );

            wp_localize_script( 'bogo_acf_js', 'bogo_acf_js', array( 'parents_default_template' => $original_post_template ) );
        }
    }

    function bogo_acf_load_original_post_values( $field ) {

        $args = wp_parse_args( $_SERVER['HTTP_REFERER'] );
        $original_post_field = array();

        if( array_key_exists('locale', $args) && array_key_exists('original_post', $args) && !$this->bogo_acf_getting_field ) {
            $this->bogo_acf_getting_field = true;

            $original_post_field = get_field_object( $field['key'], $args['original_post'], array( 'load_value' => true, 'format_value' => false ));

            switch( $original_post_field['type'] ) {
                case 'repeater':
                    $original_post_field['value'] = acf_field_repeater::format_value( $original_post_field['value'], $args['original_post'], $original_post_field );
                    break;
                case 'flexible_content':
                    $original_post_field['value'] = acf_flexible_content::format_value( $original_post_field['value'], $args['original_post'], $original_post_field );                                    
                    break;
                default:
                    break;
            }

            $this->bogo_acf_getting_field = false;
        }
        $field = array_merge($field, $original_post_field);

        return $field;
    }

    /**
     * Returns as singleton
     *
     * @return bogo_acf
     */
    public static function Instance() {
        static $inst = null;
        if ($inst === null) {
            $inst = new bogo_acf();
        }
        return $inst;
    }
}

$bogo_acf = Bogo_Acf::Instance();

?>