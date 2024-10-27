<?php
namespace P3QVideoConnect;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class P3QVC_Setup {
	
    public function __construct() {		
        add_action( 'admin_init', array( $this , 'p3qvc_init' ), 1 );
        add_action( 'admin_menu', array( $this ,'p3qvc_settings') );
        // load the frontend scripts
        if ( ! is_admin() ) {
            add_action( 'wp_enqueue_scripts', array( $this , 'p3qvc_register_sdn') );
            add_shortcode('3q', array( $this , 'p3qvc_sdn_shortcode'));
        }
    }

    /* --- FRONTEND FUNCTIONS --- */

    /* Register the scripts and enqueue css files */
    public function p3qvc_register_sdn(){
        $options = get_option('sdn_options');
        wp_register_style( 'sdn-source', 'https://playout.3qsdn.com/player/css/player.css', null, null, false);
        wp_register_script( 'sdn-source1', 'https://playout.3qsdn.com/player/js/sdnplayer.js', null, null, false);
        wp_register_script( 'sdn3', plugins_url( '/js/sdn-plugin.js' , dirname(__FILE__)));
        wp_enqueue_style( 'sdn-source' );
        wp_enqueue_script( 'sdn-source1' );
    }

    public function p3qvc_add_sdn_header(){
        wp_enqueue_script( 'sdn3' );
    }

    /**
     * @desc function to create content from shortcode
     * @param array $atts
     * @param object $content
     * @return string
     * @throws \Exception
     */
    public function p3qvc_sdn_shortcode($atts, $content=null){
        $_userToken = 0;
        $this->p3qvc_add_sdn_header();
        $options = get_option('sdn_options'); //load the defaults
        extract(shortcode_atts(array(
            'sdnPlayoutId' => $options['data-id'],
            'width' => $options['width'],
            'height' => $options['height'],
            'thumb' => $options['thumb'],
            'usertoken' => $options['usertoken'],
            'autoplay' => $options['autoplay'],
            'vast' => $options['vast'],
            'layout' => $options['responsive'],
        ), $atts));
        if(!empty($atts["usertoken"])) {
            $_userToken = $atts["usertoken"];
        }
        if(empty($atts["autoplay"])) {
            $atts["autoplay"] = 'false';
        }
        if(empty($atts["vast"])) {
            $atts["vast"] = '';
        }
        if(!empty($atts["layout"])) {
            $atts["width"] = '100%';
            $atts["height"] = '360px';
        } else {
            $atts["width"] = '100%25';
            $atts["height"] = '360px';
        }
        $projectId = $atts["data-projectid"];
        $projectSecret = $atts["data-projectsecret"];

        $timestamp = new \DateTime('now');
        $timestamp = $timestamp->getTimestamp();
        // You'll find the Project Key at Project Settings.
        $key = md5($projectId.$projectSecret.$timestamp);
        $id = 'player_'.$this->p3qvc_generateHash();

        $sdnplayer = "";
        $sdnplayer .= '<style>.js3q-player{width: 100%;max-width: 100%!important;padding-top: 56.25%;} .js3q-player > .sdn-display{position: absolute;top: 0;}</style>';

        if($atts['type'] == "livestream") {
            $sdnplayer .= '<div id="'.esc_attr($id).'"></div>';
            $sdnplayer .= '<script type="text/javascript" src="'.esc_attr($atts['data-playerurl']).'?js=true&container='.esc_attr($id).'&width=640&height=360"></script>';
        } elseif ($atts['type'] == "video") {
            if(!empty($atts["sdn_thumb"]) && $atts["sdn_thumb"] == true) {
                $sdnplayer = '
                    <div id="'.esc_attr($id).'"></div>
                    <script type="text/javascript" src="//playout.3qsdn.com/'.esc_attr($atts["data-id"]).'?key='.esc_attr($key).'&amp;timestamp='.esc_attr($timestamp).'&amp;js=true&amp;container='.esc_attr($id).'&amp;autoplay='.esc_attr($atts["autoplay"]).'&amp;width='.esc_attr($atts["width"]).'&amp;height='.esc_attr($atts["height"]).'&amp;preload=false"></script>
                            ';
            } else {
                $sdnplayer = '
                    <div id="'.esc_attr($id).'"></div>
                    <script type="text/javascript" src="//playout.3qsdn.com/'.esc_attr($atts["data-id"]).'?key='.esc_attr($key).'&amp;timestamp='.esc_attr($timestamp).'&amp;js=true&amp;container='.esc_attr($id).'&amp;autoplay='.esc_attr($atts["autoplay"]).'&amp;width='.esc_attr($atts["width"]).'&amp;height='.esc_attr($atts["height"]).'"></script>
                    ';
            }
        }
        return $sdnplayer;
    }

    /**
     * @desc function to generate hash for shortcode function
     * @param int $length
     * @return string
     */
    private function p3qvc_generateHash($length = 16) {
        $password = "";
        $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
        $maxlength = strlen($possible);
        if ($length > $maxlength) {
            $length = $maxlength;
        }
        $i = 0;
        while ($i < $length) {
            $char = substr($possible, mt_rand(0, $maxlength - 1), 1);
            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }
        return $password;
    }	

    /* --- FRONTEND FUNCTIONS END --- */

    /**
     * setup all required functions for this plugin
     */
    public function p3qvc_init () {
        add_action('admin_head', array( $this, 'p3qvc_threeQ_js') );
        add_action('admin_init', array( $this, 'p3qvc_register_admin_script' )); // register all styles and scripts
        add_action('media_buttons', array( $this, 'p3qvc_add_3q_media_button' ) ); // add the media Button
        add_action('admin_footer', array( $this, 'p3qvc_modal_box') );
        add_filter('mce_external_plugins', array( $this, 'p3qvc_add_custom_tinymce_plugin' ));
    }

    //include the tinymce javascript plugin
    function p3qvc_add_custom_tinymce_plugin($plugin_array) {
        $plugin_array['threeQ'] = plugins_url('/js/editor_plugin.js', dirname(__FILE__));
        return $plugin_array;
    }

    /**
     * set the baseUrl var for further use in custom scripts (required to call the wp-json api correctly)
     */
    public function p3qvc_threeQ_js() {
        echo '<script type="text/javascript">';
        echo 'var threeQ_baseURL = "'.get_site_url().'/index.php";';
        echo 'var threeQ_rootURL = "'.get_option(P3QVC_PLUGIN_SUFFIX.'root_url', '').'";';
        echo 'var threeQ_token = "'.get_option(P3QVC_PLUGIN_SUFFIX.'api_token', '').'";';
        echo '</script>';
    }

    /**
     * add the required modal window to the content
     */
    public function p3qvc_modal_box() {
        include P3QVC_VIDEOS_ROOT_PATH.'/templates/modal_template.php';
    }

    /**
     * add the media button to posts and pages
     */
    public function p3qvc_add_3q_media_button() {
        echo '<a href="#" id="threeQ-add-media" class="button"><img class="threeQ_icon" src="' . esc_url( P3QVC_VIDEOS_ROOT_URL. 'images/3q-icon.jpg' ) . '"></a>';
    }

    /**
     * load all requires scripts and styles
     */
    public function p3qvc_register_admin_script() {
        // styles 
        wp_enqueue_script('3q_bootstrap', plugins_url('/js/bootstrap/bootstrap.js', dirname(__FILE__)), array('jquery'), '1', true );
        wp_enqueue_script('3q-video-connect', plugins_url('/js/3q_videos.js', dirname(__FILE__)), array('jquery'), '1.0', true);
        wp_enqueue_script('3q_bootstrap');
        wp_enqueue_script('3q-video-connect');
        // scripts
        wp_register_style( 'bootstrap', plugins_url('/css/bootstrap/bootstrap.css', dirname(__FILE__)), array() );
        wp_register_style( 'bootstrap-theme', plugins_url('/css/bootstrap/bootstrap-theme.css', dirname(__FILE__)), array() );
        wp_register_style( '3q-video-connect', plugins_url('/css/3q_videos.css', dirname(__FILE__)), array() );
        wp_enqueue_style( 'bootstrap' );
        wp_enqueue_style( 'bootstrap-theme' );
        wp_enqueue_style( '3q-video-connect' );

        add_editor_style(plugins_url( '3q-video-connect/css/3q_editor_styles.css'));
    }

    /**
     * prepare admin menu entry
     */
    public function p3qvc_settings() {
        $page_title = '3Q Admin Page';
        $menu_title = 'Settings';
        $capability = 'edit_posts';
        $menu_slug = '3Q Media settings';
        $function = array($this,'p3qvc_display_settings');
        $icon_url =  esc_url( P3QVC_VIDEOS_ROOT_URL. 'images/3qlogo.png' );
        $position = 78;
        add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        $parent_slug = 'edit.php';
    }

    /**
     * function to display the settings page and save the wp-options
     */
    public function p3qvc_display_settings() {
        $update = false;
        $error = false;
        $messages = array();
        if (isset($_POST['root_url']) && $_POST['root_url'] != "") {
            $newRoot = sanitize_text_field($_POST['root_url']);
            if(wp_http_validate_url($newRoot)) {
                if(substr($newRoot, -1) != "/"){
                    $newRoot = $newRoot."/";
                }
                update_option(P3QVC_PLUGIN_SUFFIX.'root_url', $newRoot);
            } else {
                $error = true;
                $messages['root_url'] = "The value for field '3Q API URI' is not a valid URL. The '3Q API URI' could not be saved.";
            }
            $update = true;
        }
        $rootUrl = get_option(P3QVC_PLUGIN_SUFFIX.'root_url', '');

        if (isset($_POST['api_token'])) {
            $newApiToken = sanitize_text_field($_POST['api_token']);
            if($newApiToken != "" && strlen($newApiToken) == 64 ) {
                update_option(P3QVC_PLUGIN_SUFFIX.'api_token', $newApiToken);
            } else {
                $error = true;
                $messages['api_token'] = "The value for field 'API access key' is not valid. The 'API access key' could not be saved.";
            }
            $update = true;
        }
        $apiToken = get_option(P3QVC_PLUGIN_SUFFIX.'api_token', '');

        if (isset($_POST['pager']) && is_numeric(sanitize_text_field($_POST['pager']))) {
            update_option(P3QVC_PLUGIN_SUFFIX.'pager', sanitize_text_field($_POST['pager']));
            $update = true;
        }  elseif (isset($_POST['pager']) && $_POST['pager'] !== "") {
            $error = true;
            $messages['pager'] = "The value for field 'Number of videos per page' is not a number. The 'Number of videos per page' could not be saved.";
        }
        $pager = get_option(P3QVC_PLUGIN_SUFFIX.'pager', '');
        include P3QVC_VIDEOS_ROOT_PATH.'/templates/settings_template.php';
    }
	
}