<?php 
/**
 * Remote Posts Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Remote Posts
 * Description:       Shortcode to fetch and display posts with Wordpress API.
 * Version:           1.0.0
 * Author:            Mehdy Elm
 * Author URI:        https://mehdy-elm.com
 * Text Domain:       remote-posts
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) {
    exit;
}

class FetchApi {

    public function __construct() {

        add_shortcode('remote-posts', [$this, 'render_posts_shortcode']);
    }

    public function fetch_posts($site_url, $args = array()) {

        $api_url = trailingslashit($site_url) . 'wp-json/wp/v2/posts';
        
        $defaults = array(
            'per_page' => 5,
            'orderby' => 'date',
            'order' => 'desc'
        );
        
        $args = wp_parse_args($args, $defaults);
        $api_url = add_query_arg($args, $api_url);
        
        $response = wp_remote_get($api_url, array(
            'sslverify' => false, // for local use only, remove in production
            'blocking' => true,
            'timeout' => 45
        ));
    
        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code !== 200) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);

        return json_decode($body, true);
    }

    public function render_posts_shortcode($atts) {

        $atts = shortcode_atts(array(
            'count' => ''
        ), $atts);
        
        $site_url = home_url();
        
        $posts = $this->fetch_posts($site_url, array(
            'per_page' => intval($atts['count'])
        ));
        
        if (!$posts) {
            return 'Unable to fetch posts';
        }
        
        ob_start();
        foreach ($posts as $post) { ?>
            <div class="external-post">';
                <h3><?php esc_html($post['title']['rendered']) ?></h3>';
                <div><?php wp_kses_post($post['excerpt']['rendered']) ?></div>';
            </div>
        <?php }
        return ob_get_clean();
    }
}

new FetchApi();