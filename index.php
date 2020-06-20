<?php
/*
Author: Jimmy Adaro
Description: Get posts from an API-enabled Instagram account
Link: https://github.com/jimmyadaro/ig-api-scrapper-php
*/

// Get last Instagram posts
function get_instagram_posts() {
  // Initialize empty array
  $ig_posts = array();

  // Define the base URL for posts
  $base_url_ig = "https://www.instagram.com/p";

  // Set the "dist" path from the theme's main folder
  $dist_folder = "assets/ig/dist/";

  // Get the "dist" path
  $dist_path = dirname(__FILE__)."/{$dist_folder}";

  // Scan the "dist" directory for our files
  // This deletes both the "." and the  ".." values returned from "scandir()",
  // and also resets the array keys so it starts from zero
  $ig_photos = array_values(
    array_diff(
      scandir($dist_path),
      array('.', '..')
    )
  );

  // Loop through the array that "scandir()" returned
  foreach($ig_photos as $k => $images){
    // Start loop from zero
    //$k = $k-1;

    // Get image filename
    // E.g. "1-B9wLmirAyhi.jpg"
    $image_name = pathinfo($images, PATHINFO_BASENAME);

    // Explode the filename
    $image_name_exploded = explode("-", $image_name);

    // If the first item is a number (e.g. "1", "2", "3"...)
    if ( is_numeric($image_name_exploded[0]) ) {
      // Get the post code
      $post_code = explode(".", $image_name_exploded[1])[0];

      // Save this image data in the posts array
      $ig_posts[$k]["image"] = "./{$dist_folder}{$image_name}";
      $ig_posts[$k]["url"] = "{$base_url_ig}/{$post_code}/";
    }
  }

  return $ig_posts;
}

// Call the previous function
$ig_posts = get_instagram_posts();

// Show the posts with its link
foreach ($ig_posts as $ig_post) {
  $post_url = $ig_post["url"];
  $post_img = $ig_post["image"];

  echo '<a href="'.$post_url.'" target="_blank"><img src="'.$post_img.'" alt="" width="300" /></a> <br>';
}
?>
