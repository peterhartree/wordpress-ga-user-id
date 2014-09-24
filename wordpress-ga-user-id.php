<?php
/*
Plugin Name: Google Analytics User ID Helper
Plugin URI: http://web.peterhartree.co.uk/
Description: Enables user ID tracking using Google Analytics. User ID value must be alphanumeric.
Version: 0.1
Author: Peter Hartree
Author URI: http://web.peterhartree.co.uk/
Dependencies: Constant GOOGLE_ANALYTICS_ID must be set to a valid Universal Analytics tracking ID in your theme's functions.php.
*/

function google_analytics_user_id() {
  if(GOOGLE_ANALYTICS_ID):
    set_google_analytics_user_id();

    if(GOOGLE_ANALYTICS_USER_ID):
      // Add Google Analytics tracking script with User ID set.
      add_action('wp_footer', 'script_google_analytics_user_id', 20);

      // Remove the Google Analytics code set by Roots.io theme
      if(!current_user_can('manage_options')):
        remove_action('wp_footer', 'roots_google_analytics', 20);
      endif;
    else:
      // User ID wasn't passed.
      // => DO NOTHING (assume the regular Google Analytics tracking code
      // will be injected elsewhere)
    endif;
  endif;
}

add_action('init', 'google_analytics_user_id');

function set_google_analytics_user_id() {
  $ga_user_id_safe = false;

  // Has the user ID already been set in a session cookie?
  if(isset($_COOKIE['google_analytics_user_id'])):
    $ga_user_id = $_COOKIE['google_analytics_user_id'];
    $ga_user_id_safe = sanitize_google_analytics_user_id($ga_user_id);
  endif;

  // Is the user ID being passed via a querystring?
  if(isset($_GET['ga_user_id'])):
    $ga_user_id = $_GET['ga_user_id'];
    $ga_user_id_safe = sanitize_google_analytics_user_id($ga_user_id);
    setcookie('google_analytics_user_id', $ga_user_id, time() + (86400 * 7));
  endif;

  if(!defined('GOOGLE_ANALYTICS_USER_ID')):
    define('GOOGLE_ANALYTICS_USER_ID', $ga_user_id_safe);
  endif;
}

function sanitize_google_analytics_user_id($ga_user_id) {

  if(ctype_alnum($ga_user_id)):
    $ga_user_id_safe = $ga_user_id;
  endif;

  return $ga_user_id_safe;
}

function script_google_analytics_user_id() {
  echo "
    <script>
      (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
      function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
      e=o.createElement(i);r=o.getElementsByTagName(i)[0];
      e.src='//www.google-analytics.com/analytics.js';
      r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
      ga('create','". GOOGLE_ANALYTICS_ID . "', { 'userId': '" . GOOGLE_ANALYTICS_USER_ID . "'});ga('send','pageview');
    </script>
  ";
}

?>