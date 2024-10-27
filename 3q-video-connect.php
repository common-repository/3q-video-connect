<?php
/**
 * Plugin Name: 3Q Video Connect
 * Plugin URI: https://github.com/3QSDN
 * Description: Plugin to provide your <strong>3Q SDN</strong> videos to your Wordpress Website.
 * Version: 1.0.0
 * Author: 3Q GmbH
 * Author URI: https://www.3qsdn.com
 * License: GPL2
 */
namespace P3QVideoConnect;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//  Absolute path to plugin's root directory in file system.
define('P3QVC_VIDEOS_ROOT_PATH', plugin_dir_path( __FILE__ ) );
//  URL to the plugin's root directory.
define('P3QVC_VIDEOS_ROOT_URL', plugin_dir_url( __FILE__ ) );
//  Absolute path to the main plugin file (this one).
define('P3QVC_VIDEOS_PLUGIN_FILE', P3QVC_VIDEOS_ROOT_PATH . '3q-video-connect.php' );
define('P3QVC_PLUGIN_SUFFIX', 'P3QVC_' );

require_once( P3QVC_VIDEOS_ROOT_PATH. 'classes/P3QVC_Setup.class.php' );
require_once( P3QVC_VIDEOS_ROOT_PATH. 'classes/P3QVC_API.class.php' );

class P3QVC_ThreeQ {
    private $threeQSetup;
    private $threeQApi;
    public function __construct() {
        $this->threeQSetup = new P3QVC_Setup();
        $this->threeQApi = new P3QVC_API();
    }

    static function p3qvc_activate(){
        add_option(P3QVC_PLUGIN_SUFFIX."root_url", "https://sdn.3qsdn.com/api/v2/");
    }
    static function p3qvc_uninstall(){
        delete_option(P3QVC_PLUGIN_SUFFIX."root_url");

    }

}

register_uninstall_hook( __FILE__, array('P3QVideoConnect\P3QVC_ThreeQ','p3qvc_uninstall'));
register_activation_hook( __FILE__, array('P3QVideoConnect\P3QVC_ThreeQ','p3qvc_activate'));

new P3QVC_ThreeQ();