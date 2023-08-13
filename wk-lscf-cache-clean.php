<?php 
/*
 * Plugin Name:       LS & CF Cache Cleaner
 * Plugin URI:        https://9amdev.com
 * Description:       This plugin automatically clean Litespeed and Cloudflare cache each week. No manual settings required. 
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Md Imran Khan
 * Author URI:        https://9amdev.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wk-lscf-cleaner
 * Domain Path:       /languages
 */


CONST WK_LSCFC_HOOK = 'wk_clean_lscfc';

//Set the weekly schedule 
function wk_add_weekly_time( $schedules ) {
  $schedules['weekly'] = array(
    'interval' => 604800,
    'display' => __('On Weekend')
  );
  return $schedules;
}
add_filter('cron_schedules', 'wk_add_weekly_time');


register_activation_hook( __FILE__,  'wk_enable_cleaner' );
register_deactivation_hook( __FILE__,'wk_disable_cleaner');

function wk_enable_cleaner(){
	wk_setup_cleaning_cron();
}

function wk_disable_cleaner(){
	wk_destroy_cleaning_cron();
}



function wk_setup_cleaning_cron(){
	$next_stamp = strtotime('next saturday');
	$timestamp = wp_next_scheduled(WK_LSCFC_HOOK);
	if ( !$timestamp ) {
	    wp_schedule_event( $next_stamp, 'weekly', WK_LSCFC_HOOK );
    } else {
	    $day = date("D", $timestamp);
	    if ( $day != 'Sat' ){
	        wp_clear_scheduled_hook(WK_LSCFC_HOOK);
	        wp_schedule_event( $next_stamp, 'weekly', WK_LSCFC_HOOK );
	    }
	}
}


function wk_destroy_cleaning_cron(){
	$timestamp = wp_next_scheduled( WK_LSCFC_HOOK );
    wp_unschedule_event( $timestamp, WK_LSCFC_HOOK );
}

add_action(WK_LSCFC_HOOK,'wk_trigger_the_cache_cleaner');

function wk_trigger_the_cache_cleaner(){
	if(has_action('litespeed_purge_all')){
		do_action('litespeed_purge_all');
	}

	if(has_action('swcfpc_purge_cache')){
		do_action('swcfpc_purge_cache');
	}
}