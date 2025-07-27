<?php
/**
 * Plugin Name: Signalfire Reading Estimator
 * Plugin URI: https://signalfire.co.uk
 * Description: Calculates and displays estimated reading time for posts with configurable speed and flexible display options.
 * Version: 1.0.0
 * Author: Signalfire
 * Text Domain: signalfire-reading-estimator
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.7
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

class SignalfireReadingEstimator {
    
    private $option_name = 'sre_settings';
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        
        add_filter('the_content', array($this, 'add_reading_time_to_content'));
        add_shortcode('reading_time', array($this, 'reading_time_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }
    
    public function activate() {
        $default_options = array(
            'words_per_minute' => 200,
            'display_on_posts' => 1,
            'display_position' => 'top',
            'post_types' => array('post'),
            // translators: %s is the reading time in minutes
            'display_text' => __('Reading time: %s min', 'signalfire-reading-estimator')
        );
        
        if (!get_option($this->option_name)) {
            add_option($this->option_name, $default_options);
        }
    }
    
    public function deactivate() {
        // Clean up any temporary data or scheduled events
        // Keep user settings for potential reactivation
    }
    
    public function calculate_reading_time($content, $post_id = null) {
        $options = get_option($this->option_name);
        $words_per_minute = isset($options['words_per_minute']) ? (int)$options['words_per_minute'] : 200;
        
        $content = wp_strip_all_tags($content);
        $word_count = str_word_count($content);
        $reading_time = ceil($word_count / $words_per_minute);
        
        return max(1, $reading_time);
    }
    
    public function enqueue_styles() {
        wp_enqueue_style(
            'signalfire-reading-estimator-style',
            plugin_dir_url(__FILE__) . 'assets/style.css',
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'assets/style.css')
        );
    }
    
    public function get_reading_time_display($content, $post_id = null) {
        $reading_time = $this->calculate_reading_time($content, $post_id);
        $options = get_option($this->option_name);
        // translators: %s is the reading time in minutes
        $display_text = isset($options['display_text']) ? $options['display_text'] : __('Reading time: %s min', 'signalfire-reading-estimator');
        
        // translators: %s in $display_text will be replaced with the reading time in minutes
        return sprintf(esc_html($display_text), $reading_time);
    }
    
    public function add_reading_time_to_content($content) {
        if (!is_singular()) {
            return $content;
        }
        
        $options = get_option($this->option_name);
        
        if (!isset($options['display_on_posts']) || !$options['display_on_posts']) {
            return $content;
        }
        
        $post_types = isset($options['post_types']) ? $options['post_types'] : array('post');
        $current_post_type = get_post_type();
        
        if (!in_array($current_post_type, $post_types)) {
            return $content;
        }
        
        $reading_time_display = $this->get_reading_time_display($content);
        $reading_time_html = '<div class="reading-time-estimate">' . $reading_time_display . '</div>';
        
        $position = isset($options['display_position']) ? $options['display_position'] : 'top';
        
        if ($position === 'bottom') {
            return $content . $reading_time_html;
        } else {
            return $reading_time_html . $content;
        }
    }
    
    public function reading_time_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_id' => get_the_ID()
        ), $atts);
        
        $post_id = (int)$atts['post_id'];
        $post = get_post($post_id);
        
        if (!$post) {
            return '';
        }
        
        $content = $post->post_content;
        return esc_html($this->get_reading_time_display($content, $post_id));
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Reading Estimator Settings', 'signalfire-reading-estimator'),
            __('Reading Estimator', 'signalfire-reading-estimator'),
            'manage_options',
            'signalfire-reading-estimator',
            array($this, 'admin_page')
        );
    }
    
    public function admin_init() {
        register_setting('sre_settings_group', $this->option_name, array($this, 'sanitize_settings'));
        
        add_settings_section(
            'sre_general_section',
            __('General Settings', 'signalfire-reading-estimator'),
            array($this, 'general_section_callback'),
            'signalfire-reading-estimator'
        );
        
        add_settings_field(
            'words_per_minute',
            __('Words Per Minute', 'signalfire-reading-estimator'),
            array($this, 'words_per_minute_callback'),
            'signalfire-reading-estimator',
            'sre_general_section'
        );
        
        add_settings_field(
            'display_on_posts',
            __('Auto Display', 'signalfire-reading-estimator'),
            array($this, 'display_on_posts_callback'),
            'signalfire-reading-estimator',
            'sre_general_section'
        );
        
        add_settings_field(
            'display_position',
            __('Display Position', 'signalfire-reading-estimator'),
            array($this, 'display_position_callback'),
            'signalfire-reading-estimator',
            'sre_general_section'
        );
        
        add_settings_field(
            'post_types',
            __('Post Types', 'signalfire-reading-estimator'),
            array($this, 'post_types_callback'),
            'signalfire-reading-estimator',
            'sre_general_section'
        );
        
        add_settings_field(
            'display_text',
            __('Display Text', 'signalfire-reading-estimator'),
            array($this, 'display_text_callback'),
            'signalfire-reading-estimator',
            'sre_general_section'
        );
    }
    
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['words_per_minute'] = isset($input['words_per_minute']) ? absint($input['words_per_minute']) : 200;
        if ($sanitized['words_per_minute'] < 1) {
            $sanitized['words_per_minute'] = 200;
        }
        
        $sanitized['display_on_posts'] = isset($input['display_on_posts']) ? 1 : 0;
        $sanitized['display_position'] = isset($input['display_position']) && in_array($input['display_position'], array('top', 'bottom')) ? $input['display_position'] : 'top';
        $sanitized['post_types'] = isset($input['post_types']) && is_array($input['post_types']) ? array_map('sanitize_text_field', $input['post_types']) : array('post');
        // translators: %s is the reading time in minutes
        $sanitized['display_text'] = isset($input['display_text']) ? sanitize_text_field($input['display_text']) : __('Reading time: %s min', 'signalfire-reading-estimator');
        
        return $sanitized;
    }
    
    public function general_section_callback() {
        echo '<p>' . esc_html__('Configure how reading time is calculated and displayed.', 'signalfire-reading-estimator') . '</p>';
    }
    
    public function words_per_minute_callback() {
        $options = get_option($this->option_name);
        $value = isset($options['words_per_minute']) ? $options['words_per_minute'] : 200;
        echo '<input type="number" name="' . esc_attr($this->option_name) . '[words_per_minute]" value="' . esc_attr($value) . '" min="1" max="1000" />';
        echo '<p class="description">' . esc_html__('Average reading speed in words per minute (default: 200).', 'signalfire-reading-estimator') . '</p>';
    }
    
    public function display_on_posts_callback() {
        $options = get_option($this->option_name);
        $checked = isset($options['display_on_posts']) && $options['display_on_posts'] ? 'checked' : '';
        echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[display_on_posts]" value="1" ' . esc_attr($checked) . ' />';
        echo '<p class="description">' . esc_html__('Automatically display reading time on posts.', 'signalfire-reading-estimator') . '</p>';
    }
    
    public function display_position_callback() {
        $options = get_option($this->option_name);
        $position = isset($options['display_position']) ? $options['display_position'] : 'top';
        
        echo '<select name="' . esc_attr($this->option_name) . '[display_position]">';
        echo '<option value="top"' . selected($position, 'top', false) . '>' . esc_html__('Top of content', 'signalfire-reading-estimator') . '</option>';
        echo '<option value="bottom"' . selected($position, 'bottom', false) . '>' . esc_html__('Bottom of content', 'signalfire-reading-estimator') . '</option>';
        echo '</select>';
    }
    
    public function post_types_callback() {
        $options = get_option($this->option_name);
        $selected_types = isset($options['post_types']) ? $options['post_types'] : array('post');
        
        $post_types = get_post_types(array('public' => true), 'objects');
        
        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected_types) ? 'checked' : '';
            echo '<label><input type="checkbox" name="' . esc_attr($this->option_name) . '[post_types][]" value="' . esc_attr($post_type->name) . '" ' . esc_attr($checked) . ' /> ' . esc_html($post_type->label) . '</label><br>';
        }
        
        echo '<p class="description">' . esc_html__('Select which post types should display reading time.', 'signalfire-reading-estimator') . '</p>';
    }
    
    public function display_text_callback() {
        $options = get_option($this->option_name);
        // translators: %s is the reading time in minutes
        $value = isset($options['display_text']) ? $options['display_text'] : __('Reading time: %s min', 'signalfire-reading-estimator');
        echo '<input type="text" name="' . esc_attr($this->option_name) . '[display_text]" value="' . esc_attr($value) . '" class="regular-text" />';
        // translators: %s is a placeholder for the time value
        echo '<p class="description">' . esc_html__('Text to display. Use %s as placeholder for the time value.', 'signalfire-reading-estimator') . '</p>';
    }
    
    public function admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'signalfire-reading-estimator'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Reading Estimator Settings', 'signalfire-reading-estimator'); ?></h1>
            
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <form method="post" action="options.php">
                            <?php
                            settings_fields('sre_settings_group');
                            do_settings_sections('signalfire-reading-estimator');
                            submit_button();
                            ?>
                        </form>
                    </div>
                    
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="postbox">
                            <h3 class="hndle"><span><?php echo esc_html__('Shortcode Usage', 'signalfire-reading-estimator'); ?></span></h3>
                            <div class="inside">
                                <p><?php echo esc_html__('Use the shortcode to display reading time anywhere:', 'signalfire-reading-estimator'); ?></p>
                                <code>[reading_time]</code>
                                <p><?php echo esc_html__('For a specific post:', 'signalfire-reading-estimator'); ?></p>
                                <code>[reading_time post_id="123"]</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

new SignalfireReadingEstimator();