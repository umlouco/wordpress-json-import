<?php
/**
 * @package Import JSON
 * @version 0.1
 */
/*
Plugin Name: Wordpress Import from JSON
Plugin URI: http://mario-flores.com/
Description: This will import POSTS IN JSON 
Author: Mario Flores
Version: 0.1
Author URI: http://mario-flores.com/
*/
if(! defined( 'WPINC')){
    die; 
}
require plugin_dir_path(__file__).'includes/classes.php'; 

function mf_import(){
    $plugin = new MF_Import(); 
    $plugin->plugin_url = plugins_url('/', __FILE__); 
    $plugin->run(); 
}

mf_import(); 