<?php
/*
Author: Jimmy Adaro
Description: Get posts from an API-enabled Instagram account
Link: https://github.com/jimmyadaro/ig-api-scrapper-php
*/

// Composer Autoload
require __DIR__.'/vendor/autoload.php';

// Use "InstagramBasicDisplay"
use EspressoDev\InstagramBasicDisplay\InstagramBasicDisplay;

// Set configuration file
$config_file = __DIR__."/ig-cronjob.ini";

// Get configuration file
if (!file_exists($config_file)) {
    die("[".log_date()."] [ERROR] Config file was not found, execution failed \r\n");
}

// Get configuration data
$config = parse_ini_file($config_file,true,INI_SCANNER_RAW);
$numer_of_posts_to_get = $config["config"]["post_to_get"];
$ig_test_user_token = $config["config"]["fb_app_token"];

// Define "tmp" directory
$tmp_foldername_path = __DIR__."/assets/ig/tmp";

// Define "dist" directory
$dist_foldername_path = __DIR__."/assets/ig/dist";

// We create the "tmp" directory if doesn't exist
if ( !is_dir( $tmp_foldername_path ) ) {
  mkdir($tmp_foldername_path);
}

// We create the "dist" directory if doesn't exist
if ( !is_dir( $dist_foldername_path ) ) {
  mkdir($dist_foldername_path);
}

// Log file date
function log_date() {
  return date("c");
}

// ----------------------

// Instance "InstagramBasicDisplay" using the user's token
$instagram = new InstagramBasicDisplay($ig_test_user_token);

// Access to Instagram using access token
// "The total number of calls your app can make per hour is 240 times the number of users"
$instagram->setAccessToken($ig_test_user_token);

// Get user's media, second parameter is how many media data we want (max 99)
// See: @link https://github.com/espresso-dev/instagram-basic-display-php#pagination
$media = $instagram->getUserMedia("me", $numer_of_posts_to_get);

// ----------------------

// Initialize empty array
$posts_data = array();

// Save API response to the empty array
foreach ($media->data as $k => $ig_image) {
  $key_from_1 = $k+1;
  $permalink = $media->data[$k]->permalink;

  $item_code = preg_match('/\/p\/([A-Za-z0-9].+)\/$/i', $permalink, $matches);
  $item_code = $matches[1];

  $posts_data[$k]["filename"] = "{$key_from_1}-{$item_code}.jpg";
  if (isset ($media->data[$k]->thumbnail_url)) {
    $posts_data[$k]["image_url"] = $media->data[$k]->thumbnail_url;
  } else {
    $posts_data[$k]["image_url"] = $media->data[$k]->media_url;
  }
}

// ----------------------

// If the first item from the list doesn't exist (it means it's newer), we save the next content
if ( !file_exists("{$dist_foldername_path}/".$posts_data[0]["filename"]) ) {

    echo "[".log_date()."] [INFO] Starting download... \r\n";

  // We save the file as "n-code.format" inside the "tmp" folder
  foreach($posts_data as $key => $post_to_save) {
    $post_number = $key+1;
    $post_to_save_url = $post_to_save["image_url"];
    $post_to_save_filename = $post_to_save["filename"];

    file_put_contents("{$tmp_foldername_path}/{$post_to_save_filename}", fopen("$post_to_save_url", 'r'));

    echo "[".log_date()."] [INFO] Downloaded post #{$post_number}: $post_to_save_filename \r\n";
  }

  // Delete the old posts from "dist" (will delete ALL files in that folder!)
  array_map('unlink', glob("{$dist_foldername_path}/*"));

  // Move the new posts from "tmp" to "dist"
  foreach($posts_data as $post_to_save) {
    $post_to_save_filename = $post_to_save["filename"];
    if ( file_exists("{$tmp_foldername_path}/{$post_to_save_filename}") ) {
      rename("{$tmp_foldername_path}/{$post_to_save_filename}", "{$dist_foldername_path}/{$post_to_save_filename}");
    }
  }

  // Flush "tmp" folder (will delete ALL files in that folder!)
  array_map('unlink', glob("{$tmp_foldername_path}/*"));

  die("[".log_date()."] [OK] Successfully downloaded last {$numer_of_posts_to_get} posts \r\n");
}

die();
?>
