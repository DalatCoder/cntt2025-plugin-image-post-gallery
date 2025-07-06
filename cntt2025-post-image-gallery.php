<?php
/**
 * Plugin Name: CNTT2025 Post Image Gallery
 * Plugin URI: https://dlu.edu.vn
 * Description: Tạo và quản lý thư viện ảnh với popup viewer cho bài viết. Hỗ trợ shortcode để chèn gallery vào nội dung.
 * Version: 1.0.0
 * Author: NGUYỄN TRỌNG HIẾU
 * Author URI: https://nguyentronghieu.io.vn
 * Text Domain: cntt2025-gallery
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.0
 * Requires PHP: 7.4
 * Network: false
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) exit;

// Prevent direct access
defined('ABSPATH') or die('Direct access not allowed.');

// Plugin main class
class CNTT2025_PostImageGallery {
    
    public function __construct() {
        // Register post type with higher priority to avoid conflicts
        add_action('init', array($this, 'register_post_type'), 0);
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_data'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_shortcode('cntt2025_image_gallery', array($this, 'gallery_shortcode'));
        add_filter('manage_cntt2025_img_gallery_posts_columns', array($this, 'set_custom_columns'));
        add_action('manage_cntt2025_img_gallery_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
        
        // Add debug menu
        add_action('admin_menu', array($this, 'add_debug_menu'));
        add_action('admin_notices', array($this, 'debug_notice'));
        
        // Add test menu to verify admin_menu hook works
        add_action('admin_menu', array($this, 'add_test_menu'), 999);
        
        // Check post type registration after init
        add_action('init', array($this, 'check_post_type_registration'), 999);
        
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function debug_notice() {
        $screen = get_current_screen();
        if ($screen && $screen->base === 'plugins') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>CNTT2025 Post Image Gallery:</strong> Plugin đã được load! Kiểm tra menu <strong>"Image Galleries"</strong> trong admin.</p>';
            echo '</div>';
        }
    }

    public function add_debug_menu() {
        add_submenu_page(
            'tools.php',
            'Gallery Debug',
            'Gallery Debug',
            'manage_options',
            'gallery-debug',
            array($this, 'debug_page')
        );
        
        // If post type doesn't create menu automatically, create it manually
        if (post_type_exists('cntt2025_img_gallery')) {
            $post_type_object = get_post_type_object('cntt2025_img_gallery');
            if (!$post_type_object->show_in_menu) {
                add_menu_page(
                    'Image Galleries',
                    'Image Galleries',
                    'edit_posts',
                    'edit.php?post_type=cntt2025_img_gallery',
                    '',
                    'dashicons-images-alt2',
                    27
                );
            }
        }
    }
    
    public function debug_page() {
        $post_types = get_post_types(array('public' => true), 'objects');
        $our_post_type = get_post_type_object('cntt2025_img_gallery');
        
        echo '<div class="wrap">';
        echo '<h1>Gallery Debug Information</h1>';
        
        echo '<h2>Post Type Registration Status</h2>';
        echo '<p><strong>Post Type:</strong> cntt2025_img_gallery</p>';
        echo '<p><strong>Registered:</strong> ' . (post_type_exists('cntt2025_img_gallery') ? 'Yes' : 'No') . '</p>';
        
        if ($our_post_type) {
            echo '<h3>Post Type Details</h3>';
            echo '<p><strong>Menu Name:</strong> ' . $our_post_type->labels->menu_name . '</p>';
            echo '<p><strong>Menu Position:</strong> ' . $our_post_type->menu_position . '</p>';
            echo '<p><strong>Show in Menu:</strong> ' . ($our_post_type->show_in_menu ? 'Yes' : 'No') . '</p>';
            echo '<p><strong>Show UI:</strong> ' . ($our_post_type->show_ui ? 'Yes' : 'No') . '</p>';
            echo '<p><strong>Menu Icon:</strong> ' . $our_post_type->menu_icon . '</p>';
            echo '<details><summary>Full Details</summary><pre>' . print_r($our_post_type, true) . '</pre></details>';
        }
        
        echo '<h2>All Registered Post Types</h2>';
        echo '<ul>';
        foreach ($post_types as $post_type) {
            echo '<li><strong>' . $post_type->name . '</strong> - ' . $post_type->labels->name . ' (Menu: ' . ($post_type->show_in_menu ? 'Yes' : 'No') . ', Position: ' . $post_type->menu_position . ')</li>';
        }
        echo '</ul>';
        
        echo '<h2>Current User Capabilities</h2>';
        $current_user = wp_get_current_user();
        echo '<p><strong>User:</strong> ' . $current_user->display_name . '</p>';
        echo '<p><strong>Can edit posts:</strong> ' . (current_user_can('edit_posts') ? 'Yes' : 'No') . '</p>';
        echo '<p><strong>Can publish posts:</strong> ' . (current_user_can('publish_posts') ? 'Yes' : 'No') . '</p>';
        echo '<p><strong>Can manage options:</strong> ' . (current_user_can('manage_options') ? 'Yes' : 'No') . '</p>';
        
        echo '<h2>Active Plugins</h2>';
        $active_plugins = get_option('active_plugins');
        echo '<ul>';
        foreach ($active_plugins as $plugin) {
            if (strpos($plugin, 'cntt2025') !== false) {
                echo '<li><strong>' . $plugin . '</strong></li>';
            } else {
                echo '<li>' . $plugin . '</li>';
            }
        }
        echo '</ul>';
        
        echo '<h2>Manual Registration Test</h2>';
        echo '<p><a href="' . admin_url('tools.php?page=gallery-debug&action=test_register') . '" class="button">Test Register Post Type</a></p>';
        
        if (isset($_GET['action']) && $_GET['action'] === 'test_register') {
            echo '<div class="notice notice-info"><p>Testing post type registration...</p></div>';
            $this->register_post_type();
            echo '<div class="notice ' . (post_type_exists('cntt2025_img_gallery') ? 'notice-success' : 'notice-error') . '"><p>Post type registration result: ' . (post_type_exists('cntt2025_img_gallery') ? 'SUCCESS' : 'FAILED') . '</p></div>';
        }
        
        echo '</div>';
    }

    public function activate() {
        // Register post type
        $this->register_post_type();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public function register_post_type() {
        // Ensure we don't conflict with existing post types
        if (post_type_exists('cntt2025_img_gallery')) {
            error_log('CNTT2025 Gallery: Post type cntt2025_img_gallery already exists!');
            return;
        }
        
        $labels = array(
            'name'               => 'Post Image Galleries',
            'singular_name'      => 'Post Image Gallery',
            'menu_name'          => 'Image Galleries',
            'add_new'            => 'Thêm Gallery',
            'add_new_item'       => 'Thêm Gallery Mới',
            'edit_item'          => 'Chỉnh sửa Gallery',
            'new_item'           => 'Gallery Mới',
            'view_item'          => 'Xem Gallery',
            'search_items'       => 'Tìm Gallery',
            'not_found'          => 'Không tìm thấy gallery nào',
            'not_found_in_trash' => 'Không có gallery nào trong thùng rác',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'show_in_nav_menus'  => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'image-gallery'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 27, // Changed to avoid conflicts
            'menu_icon'          => 'dashicons-images-alt2',
            'supports'           => array('title', 'editor', 'thumbnail'),
            'can_export'         => true,
        );

        $result = register_post_type('cntt2025_img_gallery', $args);
        
        // Debug: Check if post type registration succeeded
        if (is_wp_error($result)) {
            error_log('CNTT2025 Gallery: Post type registration failed - ' . $result->get_error_message());
        } else {
            error_log('CNTT2025 Gallery: Post type registered successfully at position 27');
        }
        
        return $result;
    }

    public function check_post_type_registration() {
        if (!post_type_exists('cntt2025_img_gallery')) {
            error_log('CNTT2025 Post Image Gallery: Post type NOT registered after init!');
            // Try to register again
            $this->register_post_type();
        } else {
            error_log('CNTT2025 Post Image Gallery: Post type successfully registered!');
        }
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'gallery_images',
            'Quản lý Hình ảnh Gallery',
            array($this, 'gallery_images_callback'),
            'cntt2025_img_gallery',
            'normal',
            'high'
        );

        add_meta_box(
            'gallery_settings',
            'Cài đặt Gallery',
            array($this, 'gallery_settings_callback'),
            'cntt2025_img_gallery',
            'side',
            'default'
        );
    }

    public function gallery_images_callback($post) {
        wp_nonce_field('gallery_meta_nonce', 'gallery_meta_nonce_field');
        $gallery_images = get_post_meta($post->ID, '_cntt2025_img_gallery_images', true);
        $gallery_images = $gallery_images ? $gallery_images : array();
        ?>
        <div id="gallery-manager">
            <div class="gallery-controls" style="margin-bottom: 20px;">
                <button type="button" class="button button-primary" id="add-gallery-image">
                    <span class="dashicons dashicons-plus-alt"></span> Thêm hình ảnh
                </button>
                <button type="button" class="button" id="clear-all-images">
                    <span class="dashicons dashicons-trash"></span> Xóa tất cả
                </button>
            </div>
            
            <div id="gallery-images-container">
                <?php if (!empty($gallery_images)): ?>
                    <?php foreach ($gallery_images as $index => $image): ?>
                        <div class="gallery-item" data-index="<?php echo $index; ?>">
                            <div class="gallery-item-content">
                                <div class="image-preview">
                                    <img src="<?php echo esc_url($image['thumbnail']); ?>" alt="Gallery Image" style="max-width: 150px; height: auto;">
                                </div>
                                <div class="image-details">
                                    <p><strong>Caption:</strong></p>
                                    <input type="text" name="gallery_images[<?php echo $index; ?>][caption]" value="<?php echo esc_attr($image['caption']); ?>" placeholder="Nhập caption cho ảnh..." style="width: 100%; margin-bottom: 10px;">
                                    
                                    <input type="hidden" name="gallery_images[<?php echo $index; ?>][id]" value="<?php echo esc_attr($image['id']); ?>">
                                    <input type="hidden" name="gallery_images[<?php echo $index; ?>][url]" value="<?php echo esc_url($image['url']); ?>">
                                    <input type="hidden" name="gallery_images[<?php echo $index; ?>][thumbnail]" value="<?php echo esc_url($image['thumbnail']); ?>">
                                </div>
                                <div class="image-actions">
                                    <button type="button" class="button button-small remove-image">
                                        <span class="dashicons dashicons-no-alt"></span> Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div id="no-images-message" style="<?php echo !empty($gallery_images) ? 'display: none;' : ''; ?>">
                <p style="text-align: center; color: #666; font-style: italic;">Chưa có hình ảnh nào. Nhấn "Thêm hình ảnh" để bắt đầu.</p>
            </div>
        </div>

        <script type="text/template" id="gallery-item-template">
            <div class="gallery-item" data-index="{{index}}">
                <div class="gallery-item-content">
                    <div class="image-preview">
                        <img src="{{thumbnail}}" alt="Gallery Image" style="max-width: 150px; height: auto;">
                    </div>
                    <div class="image-details">
                        <p><strong>Caption:</strong></p>
                        <input type="text" name="gallery_images[{{index}}][caption]" value="" placeholder="Nhập caption cho ảnh..." style="width: 100%; margin-bottom: 10px;">
                        
                        <input type="hidden" name="gallery_images[{{index}}][id]" value="{{id}}">
                        <input type="hidden" name="gallery_images[{{index}}][url]" value="{{url}}">
                        <input type="hidden" name="gallery_images[{{index}}][thumbnail]" value="{{thumbnail}}">
                    </div>
                    <div class="image-actions">
                        <button type="button" class="button button-small remove-image">
                            <span class="dashicons dashicons-no-alt"></span> Xóa
                        </button>
                    </div>
                </div>
            </div>
        </script>
        <?php
    }

    public function gallery_settings_callback($post) {
        $columns = get_post_meta($post->ID, '_cntt2025_img_gallery_columns', true) ?: '3';
        $gap = get_post_meta($post->ID, '_cntt2025_img_gallery_gap', true) ?: '4';
        $border_radius = get_post_meta($post->ID, '_cntt2025_img_gallery_border_radius', true) ?: 'rounded-lg';
        ?>
        <table class="form-table">
            <tr>
                <td>
                    <label for="gallery_columns"><strong>Số cột hiển thị:</strong></label>
                    <select name="gallery_columns" id="gallery_columns" style="width: 100%;">
                        <option value="1" <?php selected($columns, '1'); ?>>1 cột</option>
                        <option value="2" <?php selected($columns, '2'); ?>>2 cột</option>
                        <option value="3" <?php selected($columns, '3'); ?>>3 cột</option>
                        <option value="4" <?php selected($columns, '4'); ?>>4 cột</option>
                        <option value="5" <?php selected($columns, '5'); ?>>5 cột</option>
                        <option value="6" <?php selected($columns, '6'); ?>>6 cột</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="gallery_gap"><strong>Khoảng cách ảnh:</strong></label>
                    <select name="gallery_gap" id="gallery_gap" style="width: 100%;">
                        <option value="0" <?php selected($gap, '0'); ?>>Không có</option>
                        <option value="1" <?php selected($gap, '1'); ?>>Nhỏ (4px)</option>
                        <option value="2" <?php selected($gap, '2'); ?>>Vừa (8px)</option>
                        <option value="4" <?php selected($gap, '4'); ?>>Lớn (16px)</option>
                        <option value="6" <?php selected($gap, '6'); ?>>Rất lớn (24px)</option>
                        <option value="8" <?php selected($gap, '8'); ?>>Cực lớn (32px)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="gallery_border_radius"><strong>Bo góc ảnh:</strong></label>
                    <select name="gallery_border_radius" id="gallery_border_radius" style="width: 100%;">
                        <option value="" <?php selected($border_radius, ''); ?>>Không bo góc</option>
                        <option value="rounded-sm" <?php selected($border_radius, 'rounded-sm'); ?>>Nhỏ</option>
                        <option value="rounded" <?php selected($border_radius, 'rounded'); ?>>Vừa</option>
                        <option value="rounded-lg" <?php selected($border_radius, 'rounded-lg'); ?>>Lớn</option>
                        <option value="rounded-xl" <?php selected($border_radius, 'rounded-xl'); ?>>Rất lớn</option>
                        <option value="rounded-full" <?php selected($border_radius, 'rounded-full'); ?>>Tròn</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border: 1px solid #0073aa; border-radius: 4px;">
            <h4 style="margin: 0 0 10px 0; color: #0073aa;">📋 Shortcode:</h4>
            <input type="text" value='[cntt2025_image_gallery id="<?php echo $post->ID; ?>"]' readonly style="width: 100%; background: #fff; font-family: monospace;" onclick="this.select();">
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">
                💡 Copy shortcode này và dán vào nội dung bài viết để hiển thị gallery.
            </p>
        </div>
        <?php
    }

    public function save_meta_data($post_id) {
        if (!isset($_POST['gallery_meta_nonce_field']) || !wp_verify_nonce($_POST['gallery_meta_nonce_field'], 'gallery_meta_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save gallery images
        if (isset($_POST['gallery_images'])) {
            $gallery_images = array();
            foreach ($_POST['gallery_images'] as $image_data) {
                if (!empty($image_data['id'])) {
                    $gallery_images[] = array(
                        'id' => sanitize_text_field($image_data['id']),
                        'url' => esc_url_raw($image_data['url']),
                        'thumbnail' => esc_url_raw($image_data['thumbnail']),
                        'caption' => sanitize_text_field($image_data['caption'])
                    );
                }
            }
            update_post_meta($post_id, '_cntt2025_img_gallery_images', $gallery_images);
        } else {
            delete_post_meta($post_id, '_cntt2025_img_gallery_images');
        }

        // Save gallery settings
        if (isset($_POST['gallery_columns'])) {
            update_post_meta($post_id, '_cntt2025_img_gallery_columns', sanitize_text_field($_POST['gallery_columns']));
        }

        if (isset($_POST['gallery_gap'])) {
            update_post_meta($post_id, '_cntt2025_img_gallery_gap', sanitize_text_field($_POST['gallery_gap']));
        }

        if (isset($_POST['gallery_border_radius'])) {
            update_post_meta($post_id, '_cntt2025_img_gallery_border_radius', sanitize_text_field($_POST['gallery_border_radius']));
        }
    }

    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if (($hook == 'post.php' || $hook == 'post-new.php') && $post_type == 'cntt2025_img_gallery') {
            wp_enqueue_media();
            wp_enqueue_script('cntt2025-gallery-admin', plugin_dir_url(__FILE__) . 'admin.js', array('jquery'), '1.0', true);
            wp_enqueue_style('cntt2025-gallery-admin', plugin_dir_url(__FILE__) . 'admin.css', array(), '1.0');
        }
    }

    public function enqueue_frontend_scripts() {
        wp_enqueue_style('cntt2025-gallery-frontend', plugin_dir_url(__FILE__) . 'frontend.css', array(), '1.0');
        wp_enqueue_script('cntt2025-gallery-frontend', plugin_dir_url(__FILE__) . 'frontend.js', array('jquery'), '1.0', true);
    }

    public function gallery_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'columns' => '',
            'gap' => '',
            'border_radius' => ''
        ), $atts);

        if (empty($atts['id'])) {
            return '<p style="color: #e74c3c; font-style: italic;">⚠️ Vui lòng cung cấp ID của gallery.</p>';
        }

        $gallery_post = get_post($atts['id']);
        if (!$gallery_post || $gallery_post->post_type !== 'cntt2025_img_gallery') {
            return '<p style="color: #e74c3c; font-style: italic;">⚠️ Gallery không tồn tại hoặc đã bị xóa.</p>';
        }

        $gallery_images = get_post_meta($atts['id'], '_cntt2025_img_gallery_images', true);
        if (empty($gallery_images)) {
            return '<p style="color: #f39c12; font-style: italic;">📷 Gallery này chưa có hình ảnh nào.</p>';
        }

        // Get gallery settings
        $columns = !empty($atts['columns']) ? $atts['columns'] : (get_post_meta($atts['id'], '_cntt2025_img_gallery_columns', true) ?: '3');
        $gap = !empty($atts['gap']) ? $atts['gap'] : (get_post_meta($atts['id'], '_cntt2025_img_gallery_gap', true) ?: '4');
        $border_radius = !empty($atts['border_radius']) ? $atts['border_radius'] : (get_post_meta($atts['id'], '_cntt2025_img_gallery_border_radius', true) ?: 'rounded-lg');

        $column_classes = array(
            '1' => 'grid-cols-1',
            '2' => 'grid-cols-1 sm:grid-cols-2',
            '3' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
            '4' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
            '5' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
            '6' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6'
        );

        $gap_class = 'gap-' . $gap;
        $column_class = $column_classes[$columns] ?? $column_classes['3'];

        ob_start();
        ?>
        <div class="cntt2025-gallery-container" data-gallery-id="<?php echo esc_attr($atts['id']); ?>">
            <div class="grid <?php echo esc_attr($column_class); ?> <?php echo esc_attr($gap_class); ?>">
                <?php foreach ($gallery_images as $index => $image): ?>
                    <div class="cntt2025-gallery-item cursor-pointer transform transition-transform duration-300 hover:scale-105" 
                         data-image-url="<?php echo esc_url($image['url']); ?>"
                         data-image-caption="<?php echo esc_attr($image['caption']); ?>"
                         data-image-index="<?php echo $index; ?>">
                        <img src="<?php echo esc_url($image['thumbnail']); ?>" 
                             alt="<?php echo esc_attr($image['caption']); ?>"
                             class="w-full h-auto object-cover <?php echo esc_attr($border_radius); ?> shadow-md hover:shadow-lg transition-shadow duration-300"
                             loading="lazy">
                        <?php if (!empty($image['caption'])): ?>
                            <div class="mt-2 text-sm text-gray-600 text-center">
                                <?php echo esc_html($image['caption']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Gallery Modal -->
        <div id="cntt2025-gallery-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="relative max-w-4xl max-h-screen mx-4">
                <button id="gallery-modal-close" class="absolute -top-10 right-0 text-white text-2xl hover:text-gray-300 transition-colors duration-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                
                <div class="bg-white rounded-lg overflow-hidden shadow-2xl">
                    <div class="relative">
                        <img id="gallery-modal-image" src="" alt="" class="max-w-full max-h-[80vh] object-contain">
                        
                        <!-- Navigation arrows -->
                        <button id="gallery-prev" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        
                        <button id="gallery-next" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div id="gallery-modal-caption" class="p-4 text-center text-gray-700 bg-gray-50"></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function set_custom_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['gallery_preview'] = 'Preview Gallery';
        $new_columns['image_count'] = 'Số ảnh';
        $new_columns['shortcode'] = 'Shortcode';
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    public function add_test_menu() {
        add_menu_page(
            'TEST Image Gallery',
            'TEST Image Gallery',
            'edit_posts',
            'test-image-gallery',
            array($this, 'test_page'),
            'dashicons-images-alt2',
            28
        );
    }
    
    public function test_page() {
        echo '<div class="wrap">';
        echo '<h1>TEST Image Gallery Plugin</h1>';
        echo '<p>✅ Nếu bạn thấy menu này, nghĩa là admin_menu hook hoạt động bình thường.</p>';
        echo '<p><strong>Post Type Status:</strong> ' . (post_type_exists('cntt2025_img_gallery') ? 'REGISTERED' : 'NOT REGISTERED') . '</p>';
        echo '<p>Hãy kiểm tra xem menu "Image Galleries" có xuất hiện không.</p>';
        echo '</div>';
    }
    
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'gallery_preview':
                $images = get_post_meta($post_id, '_cntt2025_img_gallery_images', true);
                if (!empty($images)) {
                    $first_image = reset($images);
                    $image_url = wp_get_attachment_image_url($first_image, array(50, 50));
                    if ($image_url) {
                        echo '<img src="' . esc_url($image_url) . '" style="width: 50px; height: 50px; object-fit: cover;" alt="Gallery Preview">';
                    }
                }
                break;
            case 'image_count':
                $images = get_post_meta($post_id, '_cntt2025_img_gallery_images', true);
                echo is_array($images) ? count($images) : 0;
                break;
            case 'shortcode':
                echo '<code>[cntt2025_image_gallery id="' . esc_attr($post_id) . '"]</code>';
                break;
        }
    }
}

new CNTT2025_PostImageGallery();
?>
