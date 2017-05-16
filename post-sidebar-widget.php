<?php
/*
Plugin Name: Post Sidebar Widget
Plugin URI: 
Description: Wordpress-Plugin: A simple Wordpress Widget for displaying Post on Sidebar 
Version: 1.0.0
Author: RRZE-Webteam
Author URI: 
Contact Name: RRZE Webmaster
Contact Email: webmaster@rrze.fau.de
License: GNU GPLv2
License URI: https://gnu.org/licenses/gpl.html

RRZE-Video is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with RRZE-Video. If not, see http://www.gnu.de/documents/gpl-2.0.de.html.
*/

add_action( 'plugins_loaded', array('Post_Widget', 'instance' ) );
add_action( 'widgets_init', array( 'Post_Widget' , 'register_post_sidebar_widget' ) );

register_activation_hook(__FILE__, array('RRZE_Video', 'activation'));
register_deactivation_hook(__FILE__, array('RRZE_Video', 'deactivation'));

/*
 * RRZE_Video-Klasse
 */
class Post_Widget extends WP_Widget {
    
    /*
     * Name der Textdomain in Custom Post Types und Custom Taxonomies
     */
    const TEXT_DOMAIN = 'post-sidebar-widget';

    /*
     * Name der Variable zum Zwecke der Aktualisierung des Plugins.
     * string
     */    
    const VERSION_OPTION_NAME = 'post-sidebar-widget';

    /*
     * Version des Plugins.
     * string
     */    
    const VERSION = '1.0.0';
        
    /*
     * Minimal erforderliche PHP-Version.
     * string
     */
    const PHP_VERSION = '7.1';

    /*
     * Minimal erforderliche WordPress-Version.
     * string 
     */
    const WP_VERSION = '4.7';
    
    /*
     * Bezieht sich auf eine einzige Instanz dieser Klasse.
     * mixed
     */
    protected static $instance = null;

    /*
     * Erstellt und gibt eine Instanz der Klasse zurück.
     * Es stellt sicher, dass von der Klasse genau ein Objekt existiert (Singleton Pattern).
     * @return object
     */
    public static function instance() {

        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /*
     * Initialisiert das Plugin, indem die Lokalisierung, Hooks und Verwaltungsfunktionen festgesetzt werden.
     * @return void
     */
    public function __construct() {
        // Sprachdateien werden eingebunden.
        self::load_textdomain();
        
        // Aktualisierung des Plugins (ggf).
        self::update_version();
        
        $widget_options = array( 
            'classname' => 'post_widget',
            'description' => 'A simple Wordpress Widget for displaying Post on Sidebar ',
        );
        
        parent::__construct( 'post-widget', 'Post Widget', $widget_options );
        
        // Ab hier können weitere Hooks angelegt werden.
    }

    // Einbindung der Sprachdateien.
    private static function load_textdomain() {    
        load_plugin_textdomain('post-sidebar-widget', false, sprintf('%s/languages/', dirname(plugin_basename(__FILE__))));
    }
    
    /*
     * Wird durchgeführt wenn das Plugin aktiviert wird.
     * @return void
     */
    public static function activation() {
        // Sprachdateien werden eingebunden.
        self::load_textdomain();
        
        // Überprüft die minimal erforderliche PHP- u. WP-Version.
        self::system_requirements();
        
        // Aktualisierung des Plugins (ggf).
        self::update_version();
        
        // Ab hier können die Funktionen/Methoden hinzugefügt werden, 
        // die bei der Aktivierung des Plugins aufgerufen werden müssen.
        // Bspw. wp_schedule_event, flush_rewrite_rules, etc.
    }

    /*
     * Wird durchgeführt wenn das Plugin deaktiviert wird.
     * @return void
     */
    public static function deactivation() {
        // Hier können die Funktionen/Methoden hinzugefügt werden, die
        // bei der Deaktivierung des Plugins aufgerufen werden müssen.
        // Bspw. wp_clear_scheduled_hook
    }

    /*
     * Überprüft die minimal erforderliche PHP- u. WP-Version.
     * @return void
     */
    private static function system_requirements() {
        $error = '';

        if (version_compare(PHP_VERSION, static::PHP_VERSION, '<')) {
            $error = sprintf(__('Your server is running PHP version %s. Please upgrade at least to PHP version %s.', 'cms-basis'), PHP_VERSION, static::PHP_VERSION);
        }

        if (version_compare($GLOBALS['wp_version'], static::WP_VERSION, '<')) {
            $error = sprintf(__('Your Wordpress version is %s. Please upgrade at least to Wordpress version %s.', 'cms-basis'), $GLOBALS['wp_version'], static::WP_VERSION);
        }

        // Wenn die Überprüfung fehlschlägt, dann wird das Plugin automatisch deaktiviert.
        if (!empty($error)) {
            deactivate_plugins(plugin_basename(__FILE__), false, true);
            wp_die($error);
        }
    }

    /*
     * Aktualisierung des Plugins
     * @return void
     */
    private static function update_version() {
        $version = get_option(static::VERSION_OPTION_NAME, '0');
        
        if (version_compare($version, static::VERSION, '<')) {
            // Wird durchgeführt wenn das Plugin aktualisiert muss.
        }
        
        update_option(static::VERSION_OPTION_NAME, static::VERSION);
    }
    
    public static function register_post_sidebar_widget() {
        
        register_widget( 'Post_Widget' );
    }
    
    /*
     * Gibt die Daten im Frontend aus.
     */ 
    public function widget( $args, $instance ) {
        
       
        if ( ! empty( $instance['title'] ) ) {
            $html = $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        
        $posts  =   get_posts( 'numberposts=' . $instance['number'] );
        $html  .=   '<ul class="list-group">';
            
        foreach ($posts as $post) {
            $html .= '<li class="list-group-item">';
            $html .= '<span class="badge">' . date('j F, Y', strtotime($post->post_date)) .'</span>';
            $html .= '<a href="' . $post->post_name .' ">' . $post->post_title . '</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
        echo $html;
    }
         
    
    /*
     * Definiert die Backend-Formularfelder für den Admin.
     * Die Daten im Widget-Screen können kundenindividuell angepasst werden.   
     */
    
    public function form( $instance ) {
        
        
        $title  = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Titel', 'post-sidebar-widget' );
        $number = ! empty( $instance['number'] ) ? $instance['number'] : 5;
        
        /*
         * The Title Form Field
         */
        ?>

        <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'post-sidebar-widget' ); ?></label> 
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        
        <?php
        /*
         * The Number Form Field
         */
        ?>
        
        <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_attr_e( 'Anzahl der Posts:', 'post-sidebar-widget' ); ?></label> 
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>">
        </p>
        <?php 
         
    }
    
    /*
     * Im Widget-Screen werden die alten Eingaben mit
     * den neuen Eingaben ersetzt und gespeichert.  
     */
    public function update( $new_instance, $old_instance ) { 
        
        $instance = $old_instance;
        $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
        $instance[ 'number' ] = strip_tags( $new_instance[ 'number' ] );
        
        return $instance;
    }
}
