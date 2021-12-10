<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Taxonomy Index
 * Description: Displays Index of Categories/Tags or other taxonomy types using taxonomy-index Shortcode.
 * Version: 1.2.2
 * Author: azurecurve
 * Author URI: https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/taxonomy-index/
 * Text Domain: taxonomy-index
 * Domain Path: /languages
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Prevent direct access.
if (!defined('ABSPATH')){
	die();
}

// include plugin menu
require_once(dirname( __FILE__).'/pluginmenu/menu.php');
add_action('admin_init', 'azrcrv_create_plugin_menu_ti');

// include update client
require_once(dirname(__FILE__).'/libraries/updateclient/UpdateClient.class.php');

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 *
 */

// add actions
add_action('admin_menu', 'azrcrv_ti_create_admin_menu');
add_action('plugins_loaded', 'azrcrv_ti_load_languages');

// add filters
add_filter('plugin_action_links', 'azrcrv_ti_add_plugin_action_link', 10, 2);
add_filter('the_posts', 'azrcrv_ti_check_for_shortcode');
add_filter('codepotent_update_manager_image_path', 'azrcrv_ti_custom_image_path');
add_filter('codepotent_update_manager_image_url', 'azrcrv_ti_custom_image_url');

// add shortcodes
add_shortcode('taxonomy-index', 'azrcrv_ti_display_index');
add_shortcode('TAXONOMY-INDEX', 'azrcrv_ti_display_index');

/**
 * Load language files.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ti_load_languages() {
    $plugin_rel_path = basename(dirname(__FILE__)).'/languages';
    load_plugin_textdomain('taxonomy-index', false, $plugin_rel_path);
}

/**
 * Check if shortcode on current page and then load css and jqeury.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ti_check_for_shortcode($posts){
    if (empty($posts)){
        return $posts;
	}
	
	// array of shortcodes to search for
	$shortcodes = array(
						'taxonomy-index','TAXONOMY-INDEX'
						);
	
    // loop through posts
    $found = false;
    foreach ($posts as $post){
		// loop through shortcodes
		foreach ($shortcodes as $shortcode){
			// check the post content for the shortcode
			if (has_shortcode($post->post_content, $shortcode)){
				$found = true;
				// break loop as shortcode found in page content
				break 2;
			}
		}
	}
 
    if ($found){
		// as shortcode found call functions to load css and jquery
        azrcrv_ti_load_css();
    }
    return $posts;
}

/**
 * Load CSS.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ti_load_css(){
	wp_enqueue_style('azrcrv-ti', plugins_url('assets/css/style.css', __FILE__), '', '1.0.0');
}

/**
 * Custom plugin image path.
 *
 * @since 1.2.0
 *
 */
function azrcrv_ti_custom_image_path($path){
    if (strpos($path, 'azrcrv-taxonomy-index') !== false){
        $path = plugin_dir_path(__FILE__).'assets/pluginimages';
    }
    return $path;
}

/**
 * Custom plugin image url.
 *
 * @since 1.2.0
 *
 */
function azrcrv_ti_custom_image_url($url){
    if (strpos($url, 'azrcrv-taxonomy-index') !== false){
        $url = plugin_dir_url(__FILE__).'assets/pluginimages';
    }
    return $url;
}

/**
 * Add Taxonomy Index action link on plugins page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ti_add_plugin_action_link($links, $file){
	static $this_plugin;

	if (!$this_plugin){
		$this_plugin = plugin_basename(__FILE__);
	}

	if ($file == $this_plugin){
		$settings_link = '<a href="'.admin_url('admin.php?page=azrcrv-ti').'"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />'.esc_html__('Settings' ,'taxonomy-index').'</a>';
		array_unshift($links, $settings_link);
	}

	return $links;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ti_create_admin_menu(){
	//global $admin_page_hooks;
	
	add_submenu_page("azrcrv-plugin-menu"
						,esc_html__("Taxonomy Index Settings", "taxonomy-index")
						,esc_html__("Taxonomy Index", "taxonomy-index")
						,'manage_options'
						,'azrcrv-ti'
						,'azrcrv_ti_display_options');
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 *
 */
function azrcrv_ti_display_options(){
	if (!current_user_can('manage_options')){
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'taxonomy-index'));
    }
	
	// Retrieve plugin configuration options from database
	$options = get_option('azrcrv-ti');
	?>
	<div id="azrcrv-ti-general" class="wrap">
		<h1>
			<?php
				echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="'.plugins_url('/pluginmenu/images/logo.svg', __FILE__).'" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve" /></a>';
				esc_html_e(get_admin_page_title());
			?>
		</h1>
		<p>
			<?php esc_html_e('Displays Index of Categories/Tags or other taxonomy types using taxonomy-index Shortcode. This plugin is multi-site compatible.', 'taxonomy-index'); ?>
		</p>
		<p><?php esc_html_e('Example use: [taxonomy-index taxonomy="category" slug="ice-cream"]', 'taxonomy-index'); ?></p>
		<p><?php esc_html_e('Alternative <strong>ti</strong> shortcode can also be used', 'taxonomy-index'); ?></p>
		<p><?php esc_html_e('Taxonomy can be set to <strong>category</strong> or <strong>tag</strong> or other taxonomy post type.', 'taxonomy-index'); ?></p>
		<p>
			<label for="additional-plugins">
				azurecurve <?php esc_html_e('has the following plugins which allow shortcodes to be used in comments and widgets:', 'azc_gpi'); ?>
			</label>
			<ul class='azc_plugin_index'>
				<li>
					<?php
					if (azrcrv_ti_is_plugin_active('azurecurve-shortcodes-in-comments/azurecurve-shortcodes-in-comments.php')){
						echo "<a href='admin.php?page=azrcrv-sic' class='azc_plugin_index'>Shortcodes in Comments</a>";
					}else{
						echo "<a href='https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-comments/' class='azrcrv-plugin-index'>Shortcodes in Comments</a>";
					}
					?>
				</li>
				<li>
					<?php
					if (azrcrv_ti_is_plugin_active('azurecurve-shortcodes-in-widgets/azurecurve-shortcodes-in-widgets.php')){
						echo "<a href='admin.php?page=azrcrv-siw' class='azc_plugin_index'>Shortcodes in Widgets</a>";
					}else{
						echo "<a href='https://development.azurecurve.co.uk/classicpress-plugins/shortcodes-in-widgets/' class='azrcrv-plugin-index'>Shortcodes in Widgets</a>";
					}
					?>
				</li>
			</ul>
		</p>
	</div>
	<?php
}

/**
 * Check if function active (included due to standard function failing due to order of load).
 *
 * @since 1.0.0
 *
 */
function azrcrv_ti_is_plugin_active($plugin){
    return in_array($plugin, (array) get_option('active_plugins', array()));
}

function azrcrv_ti_display_index($atts, $content = null){
	$args = shortcode_atts(array(
		'taxonomy' => '',
		'slug' => ''
	), $atts);
	$taxonomy = $args['taxonomy'];
	$slug = $args['slug'];
	
	$taxonomy_meta = get_term_by('slug', $slug, $taxonomy);
	if ($taxonomy == 'tag'){
		$taxonomy = 'post_tag';
	}
	
	$args = array('parent' => $taxonomy_meta->term_id, 'taxonomy' => $taxonomy);
	$categories = get_categories($args); 
	
	$output = '';
	foreach ($categories as $category){
		$category_link = get_category_link($category->term_id);
		$output .= "<a href='$category_link' class='azrcrv-ti'>$category->name</a>";
	}
	
	if (strlen($output) > 0){
		$output = "<span class='azrcrv-ti'>".$output."</span>";
	}
	
	$args = array('category' => $taxonomy_meta->term_id);
	
	$posts = get_posts($args);
	
	foreach ($posts as $post){
		$output .= "<a href='".get_permalink($post->ID) ."' class='azrcrv-ti'>".$post->post_title."</a>";
	}
  
	return "<span class='azrcrv-ti'>".$output."</span>";
	
}

?>