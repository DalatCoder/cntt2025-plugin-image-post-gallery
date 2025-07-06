<?php
/**
 * Plugin Name: CNTT2025 Post Image Gallery
 * Plugin URI: https://dlu.edu.vn
 * Description: Tạo và quản lý thư viện ảnh với popup viewer cho bài viết. Hỗ trợ shortcode để chèn gallery và                </td>
            </tr>
            <tr>
                <td>
                    <label for="gallery_layout_style"><strong>Kiểu hiển thị:</strong></label>
                    <select name="gallery_layout_style" id="gallery_layout_style" style="width: 100%;">
                        <option value="masonry" <?php selected($layout_style, 'masonry'); ?>>Pinterest-style (Masonry)</option>
                        <option value="grid" <?php selected($layout_style, 'grid'); ?>>Grid đều nhau</option>
                    </select>
                    <p style="font-size: 11px; color: #666; margin-top: 5px;">💡 Masonry: Chiều cao linh hoạt theo tỉ lệ ảnh. Grid: Chiều cao đồng đều.</p>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="event_date"><strong>Ngày diễn ra sự kiện:</strong></label> dung với Tailwind CSS styling.
 * Version: 2.0.0
 * Author: NGUYỄN TRỌNG HIẾU
 * Author URI: https://nguyentronghieu.io.vn
 * Text Domain: cntt2025-post-image-gallery
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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
        
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
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
            'menu_position'      => 27,
            'menu_icon'          => 'dashicons-images-alt2',
            'supports'           => array('title', 'editor', 'thumbnail'),
            'can_export'         => true,
        );

        register_post_type('cntt2025_img_gallery', $args);
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
        $preview_quality = get_post_meta($post->ID, '_cntt2025_img_gallery_preview_quality', true) ?: 'medium';
        $layout_style = get_post_meta($post->ID, '_cntt2025_img_gallery_layout_style', true) ?: 'masonry';
        $event_date = get_post_meta($post->ID, '_cntt2025_img_gallery_event_date', true);
        $event_location = get_post_meta($post->ID, '_cntt2025_img_gallery_event_location', true);
        ?>
        <table class="form-table">
            <tr>
                <td>
                    <label for="event_date"><strong>Ngày diễn ra sự kiện:</strong></label>
                    <input type="date" name="event_date" id="event_date" value="<?php echo esc_attr($event_date); ?>" style="width: 100%;">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="event_location"><strong>Địa điểm sự kiện:</strong></label>
                    <input type="text" name="event_location" id="event_location" value="<?php echo esc_attr($event_location); ?>" placeholder="Nhập địa điểm diễn ra sự kiện..." style="width: 100%;">
                </td>
            </tr>
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
            <tr>
                <td>
                    <label for="gallery_preview_quality"><strong>Chất lượng ảnh preview:</strong></label>
                    <select name="gallery_preview_quality" id="gallery_preview_quality" style="width: 100%;">
                        <option value="thumbnail" <?php selected($preview_quality, 'thumbnail'); ?>>Thấp (150x150px)</option>
                        <option value="medium" <?php selected($preview_quality, 'medium'); ?>>Trung bình (300x300px)</option>
                        <option value="medium_large" <?php selected($preview_quality, 'medium_large'); ?>>Cao (768x768px)</option>
                        <option value="large" <?php selected($preview_quality, 'large'); ?>>Rất cao (1024x1024px)</option>
                        <option value="full" <?php selected($preview_quality, 'full'); ?>>Tối đa (kích thước gốc)</option>
                    </select>
                    <p style="font-size: 11px; color: #666; margin-top: 5px;">💡 Chất lượng cao hơn sẽ tải chậm hơn nhưng hiển thị đẹp hơn.</p>
                </td>
            </tr>
        </table>
        
        <div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border: 1px solid #0073aa; border-radius: 4px;">
            <h4 style="margin: 0 0 10px 0; color: #0073aa;">📋 Shortcode:</h4>
            <input type="text" value='[cntt2025_image_gallery id="<?php echo $post->ID; ?>"]' readonly style="width: 100%; background: #fff; font-family: monospace; margin-bottom: 10px;" onclick="this.select();">
            <p style="margin: 10px 0 5px 0; font-size: 12px; color: #666;">
                💡 Copy shortcode này và dán vào nội dung bài viết để hiển thị gallery.
            </p>
            <p style="margin: 5px 0 0 0; font-size: 11px; color: #888;">
                <strong>Tham số bổ sung:</strong><br>
                • <code>show_event_info="false"</code> - Ẩn thông tin sự kiện<br>
                • <code>columns="4"</code> - Số cột hiển thị<br>
                • <code>gap="2"</code> - Khoảng cách ảnh<br>
                • <code>border_radius="rounded"</code> - Bo góc ảnh<br>
                • <code>preview_quality="medium"</code> - Chất lượng ảnh preview<br>
                • <code>layout_style="masonry"</code> - Kiểu hiển thị (masonry/grid)
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

        if (isset($_POST['gallery_preview_quality'])) {
            update_post_meta($post_id, '_cntt2025_img_gallery_preview_quality', sanitize_text_field($_POST['gallery_preview_quality']));
        }

        if (isset($_POST['gallery_layout_style'])) {
            update_post_meta($post_id, '_cntt2025_img_gallery_layout_style', sanitize_text_field($_POST['gallery_layout_style']));
        }

        // Save event information
        if (isset($_POST['event_date'])) {
            update_post_meta($post_id, '_cntt2025_img_gallery_event_date', sanitize_text_field($_POST['event_date']));
        }

        if (isset($_POST['event_location'])) {
            update_post_meta($post_id, '_cntt2025_img_gallery_event_location', sanitize_text_field($_POST['event_location']));
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
            'border_radius' => '',
            'preview_quality' => '',
            'layout_style' => '',
            'show_event_info' => 'false'
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
        $preview_quality = !empty($atts['preview_quality']) ? $atts['preview_quality'] : (get_post_meta($atts['id'], '_cntt2025_img_gallery_preview_quality', true) ?: 'medium');
        $layout_style = !empty($atts['layout_style']) ? $atts['layout_style'] : (get_post_meta($atts['id'], '_cntt2025_img_gallery_layout_style', true) ?: 'masonry');
        
        // Get event information
        $event_date = get_post_meta($atts['id'], '_cntt2025_img_gallery_event_date', true);
        $event_location = get_post_meta($atts['id'], '_cntt2025_img_gallery_event_location', true);
        $show_event_info = $atts['show_event_info'] === 'true';

        // Determine layout classes based on style
        if ($layout_style === 'grid') {
            // Traditional grid layout
            $column_classes = array(
                '1' => 'grid grid-cols-1',
                '2' => 'grid grid-cols-1 sm:grid-cols-2',
                '3' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                '4' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
                '5' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
                '6' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6'
            );
            $gap_class = 'gap-' . $gap;
            $column_class = $column_classes[$columns] ?? $column_classes['3'];
            $container_class = 'cntt2025-grid-container';
            $item_class = 'cntt2025-grid-item';
            $image_class = 'cntt2025-grid-image w-full h-64 object-cover';
            $caption_class = 'mt-2 text-sm text-gray-600 text-center';
        } else {
            // Masonry layout (default)
            $column_classes = array(
                '1' => 'cntt2025-masonry-columns-1',
                '2' => 'cntt2025-masonry-columns-2',
                '3' => 'cntt2025-masonry-columns-3',
                '4' => 'cntt2025-masonry-columns-4',
                '5' => 'cntt2025-masonry-columns-5',
                '6' => 'cntt2025-masonry-columns-6'
            );
            $gap_class = 'cntt2025-masonry-gap-' . $gap;
            $column_class = $column_classes[$columns] ?? $column_classes['3'];
            $container_class = 'cntt2025-masonry-grid';
            $item_class = 'cntt2025-masonry-item';
            $image_class = 'cntt2025-masonry-image';
            $caption_class = 'cntt2025-image-caption';
        }

        ob_start();
        ?>
        <div class="cntt2025-gallery-container" data-gallery-id="<?php echo esc_attr($atts['id']); ?>">
            <?php if ($show_event_info && (!empty($event_date) || !empty($event_location))): ?>
                <div class="cntt2025-event-info mb-6 p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">📸 Thông tin sự kiện</h3>
                    <?php if (!empty($event_date)): ?>
                        <div class="flex items-center mb-2">
                            <span class="text-blue-600 mr-2">📅</span>
                            <span class="font-medium">Ngày:</span>
                            <span class="ml-2"><?php echo esc_html(date_i18n('d/m/Y', strtotime($event_date))); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($event_location)): ?>
                        <div class="flex items-center">
                            <span class="text-blue-600 mr-2">📍</span>
                            <span class="font-medium">Địa điểm:</span>
                            <span class="ml-2"><?php echo esc_html($event_location); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="<?php echo esc_attr($container_class); ?> <?php echo esc_attr($column_class); ?> <?php echo esc_attr($gap_class); ?>" data-columns="<?php echo esc_attr($columns); ?>" data-gap="<?php echo esc_attr($gap); ?>" data-layout="<?php echo esc_attr($layout_style); ?>">
                <?php foreach ($gallery_images as $index => $image): ?>
                    <?php 
                    // Get preview image URL based on quality setting
                    $preview_url = $image['thumbnail']; // default fallback
                    $image_width = 0;
                    $image_height = 0;
                    
                    if (!empty($image['id'])) {
                        $preview_image = wp_get_attachment_image_src($image['id'], $preview_quality);
                        if ($preview_image) {
                            $preview_url = $preview_image[0];
                            $image_width = $preview_image[1];
                            $image_height = $preview_image[2];
                        }
                    }
                    ?>
                    <div class="<?php echo esc_attr($item_class); ?> <?php echo esc_attr($border_radius); ?> cursor-pointer" 
                         data-image-url="<?php echo esc_url($image['url']); ?>"
                         data-image-caption="<?php echo esc_attr($image['caption']); ?>"
                         data-image-index="<?php echo $index; ?>"
                         data-width="<?php echo $image_width; ?>"
                         data-height="<?php echo $image_height; ?>">
                        <img src="<?php echo esc_url($preview_url); ?>" 
                             alt="<?php echo esc_attr($image['caption']); ?>"
                             class="<?php echo esc_attr($image_class); ?> <?php echo esc_attr($border_radius); ?>"
                             loading="lazy"
                             <?php if ($layout_style === 'masonry'): ?>
                             onload="CNTTMasonry.imageLoaded(this)"
                             <?php endif; ?>>
                        <?php if (!empty($image['caption'])): ?>
                            <div class="<?php echo esc_attr($caption_class); ?>">
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
        $new_columns['event_date'] = 'Ngày sự kiện';
        $new_columns['event_location'] = 'Địa điểm';
        $new_columns['image_count'] = 'Số ảnh';
        $new_columns['shortcode'] = 'Shortcode';
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'gallery_preview':
                $gallery_images = get_post_meta($post_id, '_cntt2025_img_gallery_images', true);
                if (!empty($gallery_images)) {
                    $first_images = array_slice($gallery_images, 0, 3);
                    echo '<div style="display: flex; gap: 4px;">';
                    foreach ($first_images as $image) {
                        echo '<img src="' . esc_url($image['thumbnail']) . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" alt="Preview">';
                    }
                    if (count($gallery_images) > 3) {
                        echo '<div style="width: 40px; height: 40px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #666;">+' . (count($gallery_images) - 3) . '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<span style="color: #999; font-style: italic;">Không có ảnh</span>';
                }
                break;
                
            case 'event_date':
                $event_date = get_post_meta($post_id, '_cntt2025_img_gallery_event_date', true);
                if (!empty($event_date)) {
                    $formatted_date = date_i18n('d/m/Y', strtotime($event_date));
                    echo '<span style="font-weight: 500;">📅 ' . $formatted_date . '</span>';
                } else {
                    echo '<span style="color: #999; font-style: italic;">Chưa đặt</span>';
                }
                break;
                
            case 'event_location':
                $event_location = get_post_meta($post_id, '_cntt2025_img_gallery_event_location', true);
                if (!empty($event_location)) {
                    echo '<span style="font-weight: 500;">📍 ' . esc_html($event_location) . '</span>';
                } else {
                    echo '<span style="color: #999; font-style: italic;">Chưa đặt</span>';
                }
                break;
                
            case 'image_count':
                $gallery_images = get_post_meta($post_id, '_cntt2025_img_gallery_images', true);
                $count = is_array($gallery_images) ? count($gallery_images) : 0;
                echo '<span class="count-badge">' . $count . ' ảnh</span>';
                break;
                
            case 'shortcode':
                echo '<code style="background: #f1f1f1; padding: 2px 4px; border-radius: 3px; font-size: 11px;">[cntt2025_image_gallery id="' . $post_id . '"]</code>';
                break;
        }
    }
}

new CNTT2025_PostImageGallery();
?>
