<?php // custom admin styling

function login_styles() {
    echo '<link rel="stylesheet" id="custom-admin-styles" href="'. get_bloginfo("template_directory") .'/static/css/admin-login.css" type="text/css" media="all">';     
 }
add_action('login_head', 'login_styles');

// removing adminbuttons
function remove_menus () {
global $menu;
    $restricted = array(__('Links'), __('Comments')); // for extra removal use: $restricted = array(__('Links'), __('Media'), __('etc etc'));
    end ($menu);
    while (prev($menu)){
      $value = explode(' ',$menu[key($menu)][0]);
      if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
    }
}
add_action('admin_menu', 'remove_menus');

// wrapping div around oembed for responsiveness
add_filter('embed_oembed_html', 'my_embed_oembed_html', 99, 4);
function my_embed_oembed_html($html, $url, $attr, $post_id) {
  return '<div class="video-wrapper"><div class="video-wrap">' . $html . '</div></div>';
}

// Attach a class to linked images' parent anchors e.g. a img => a.fancy img
add_filter('the_content', 'addlightboxrel_replace');
function addlightboxrel_replace ($content)
{	global $post;
	$pattern = "/<a(.*?)href=('|\")(.*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
  	$replacement = '<a$1class="fancy" href=$2$3.$4$5$6</a>';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}

// remove wrapping <p> from images
function filter_ptags_on_images($content){
    return preg_replace('/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '\1', $content);
}
add_filter('the_content', 'filter_ptags_on_images');

// remove dashboardwidgets
function remove_dashboard_widgets() {
  remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
  remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
  remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
  remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
  remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
} 
add_action('wp_dashboard_setup', 'remove_dashboard_widgets' );

function remove_admin_bar_links() {
  global $wp_admin_bar;
  $wp_admin_bar->remove_menu('wp-logo');          // Remove the WordPress logo
  $wp_admin_bar->remove_menu('about');            // Remove the about WordPress link
  $wp_admin_bar->remove_menu('wporg');            // Remove the WordPress.org link
  $wp_admin_bar->remove_menu('documentation');    // Remove the WordPress documentation link
  $wp_admin_bar->remove_menu('support-forums');   // Remove the support forums link
  $wp_admin_bar->remove_menu('feedback');         // Remove the feedback link
  $wp_admin_bar->remove_menu('customize');        // Remove the customiser link
  //$wp_admin_bar->remove_menu('site-name');      // Remove the site name menu
  //$wp_admin_bar->remove_menu('view-site');      // Remove the view site link
  $wp_admin_bar->remove_menu('updates');          // Remove the updates link
  $wp_admin_bar->remove_menu('comments');         // Remove the comments link
  $wp_admin_bar->remove_menu('new-content');      // Remove the content link
  $wp_admin_bar->remove_menu('w3tc');             // If you use w3 total cache remove the performance link
  //$wp_admin_bar->remove_menu('my-account');     // Remove the user details tab
  $wp_admin_bar->remove_menu('wpseo-menu');       // Remove the Yoast SEO link
}
add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_links' );

/*
// get the the role object
$role_object = get_role( 'editor' );

// add $cap capability to this role object
$role_object->add_cap( 'edit_theme_options' );
$role_object->remove_cap( 'edit_themes' );
*/

