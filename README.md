# CNTT2025 Post Image Gallery

## Mô tả

Plugin **CNTT2025 Post Image Gallery** cho phép tạo và quản lý các thư viện ảnh (gallery) một cách chuyên nghiệp với giao diện hiện đại sử dụng Tailwind CSS. Plugin cung cấp popup viewer chất lượng cao khi click vào hình ảnh và shortcode để dễ dàng chèn gallery vào bài viết.

**Tác giả:** NGUYỄN TRỌNG HIẾU - hieunt@dlu.edu.vn  
**Phiên bản:** 1.0

## Tính năng chính

### 🖼️ Quản lý Gallery

- Tạo và quản lý nhiều gallery riêng biệt
- Upload và sắp xếp hình ảnh dễ dàng
- Thêm caption/mô tả cho từng hình ảnh
- Drag & drop để sắp xếp thứ tự ảnh

### 🎨 Tùy chỉnh Hiển thị

- Chọn số cột hiển thị (1-6 cột)
- Điều chỉnh khoảng cách giữa các ảnh
- Tùy chỉnh bo góc ảnh (từ không bo góc đến tròn)
- Responsive design trên mọi thiết bị

### 🔍 Popup Viewer

- Hiển thị ảnh chất lượng cao trong popup
- Điều hướng bằng mũi tên hoặc keyboard
- Swipe hỗ trợ trên mobile
- Hiển thị caption và thông tin ảnh

### 📝 Shortcode

- Shortcode đơn giản: `[cntt2025_gallery id="123"]`
- Ghi đè cài đặt thông qua shortcode parameters
- Tự động tạo shortcode trong admin

### 🛠️ Giao diện Admin

- Giao diện quản lý trực quan
- Preview gallery ngay trong admin
- Tích hợp WordPress Media Library
- Hiển thị số lượng ảnh trong danh sách

## Cài đặt

1. Tải plugin vào thư mục `/wp-content/plugins/cntt2025-post-image-gallery/`
2. Kích hoạt plugin trong WordPress Admin → Plugins
3. Truy cập **Image Galleries** trong menu admin để tạo gallery

## Cấu trúc Files

```
cntt2025-post-image-gallery/
├── cntt2025-post-image-gallery.php  # File chính
├── admin.js                         # JavaScript cho admin
├── admin.css                        # CSS cho admin
├── frontend.js                      # JavaScript cho frontend
├── frontend.css                     # CSS cho frontend
└── README.md                        # Tài liệu này
```

## Hướng dẫn sử dụng

### Tạo Gallery mới

1. Vào **Admin Dashboard → Image Galleries**
2. Nhấn **"Thêm Gallery"**
3. Nhập tiêu đề và mô tả gallery
4. Trong phần **"Quản lý Hình ảnh Gallery"**:
   - Nhấn **"Thêm hình ảnh"**
   - Chọn ảnh từ Media Library
   - Thêm caption cho mỗi ảnh
5. Tùy chỉnh **"Cài đặt Gallery"**:
   - Chọn số cột hiển thị
   - Điều chỉnh khoảng cách ảnh
   - Chọn kiểu bo góc
6. **Publish** gallery

### Sử dụng Shortcode

Copy shortcode từ admin hoặc tạo thủ công:

```php
// Shortcode cơ bản
[cntt2025_image_gallery id="123"]

// Shortcode với tùy chỉnh
[cntt2025_image_gallery id="123" columns="4" gap="6" border_radius="rounded-xl"]
```

### Tùy chỉnh trong Template

```php
// Hiển thị gallery trong template
echo do_shortcode('[cntt2025_image_gallery id="123"]');

// Hoặc sử dụng function
if (function_exists('cntt2025_gallery_shortcode')) {
    echo cntt2025_gallery_shortcode(['id' => '123']);
}
```

## Shortcode Parameters

| Parameter       | Mô tả           | Giá trị                                                   | Mặc định     |
| --------------- | --------------- | --------------------------------------------------------- | ------------ |
| `id`            | ID của gallery  | Số                                                        | **Bắt buộc** |
| `columns`       | Số cột hiển thị | 1-6                                                       | 3            |
| `gap`           | Khoảng cách ảnh | 0, 1, 2, 4, 6, 8                                          | 4            |
| `border_radius` | Bo góc ảnh      | rounded-sm, rounded, rounded-lg, rounded-xl, rounded-full | rounded-lg   |

### Ví dụ Shortcode

```php
// Gallery 4 cột, khoảng cách lớn, bo góc tròn
[cntt2025_image_gallery id="123" columns="4" gap="6" border_radius="rounded-xl"]

// Gallery 2 cột, không khoảng cách, góc vuông
[cntt2025_image_gallery id="123" columns="2" gap="0" border_radius=""]

// Gallery 6 cột cho desktop, responsive
[cntt2025_image_gallery id="123" columns="6"]
```

## Custom Post Type

Plugin đăng ký custom post type `cntt2025_image_gallery`:

```php
// Query galleries
$galleries = new WP_Query([
    'post_type' => 'cntt2025_image_gallery',
    'posts_per_page' => 10,
    'post_status' => 'publish'
]);

// Get gallery images
$gallery_images = get_post_meta($gallery_id, '_cntt2025_image_gallery_images', true);

// Get gallery settings
$columns = get_post_meta($gallery_id, '_cntt2025_image_gallery_columns', true);
$gap = get_post_meta($gallery_id, '_cntt2025_image_gallery_gap', true);
$border_radius = get_post_meta($gallery_id, '_cntt2025_image_gallery_border_radius', true);
```

## Meta Fields

### `_cntt2025_image_gallery_images`

Array chứa thông tin các hình ảnh:

```php
[
    [
        'id' => '123',           // Attachment ID
        'url' => 'full-url',     // URL ảnh gốc
        'thumbnail' => 'thumb-url', // URL ảnh thumbnail
        'caption' => 'Caption text'  // Mô tả ảnh
    ],
    // ... more images
]
```

### `_cntt2025_gallery_columns`

Số cột hiển thị: `1`, `2`, `3`, `4`, `5`, `6`

### `_cntt2025_gallery_gap`

Khoảng cách giữa ảnh: `0`, `1`, `2`, `4`, `6`, `8`

### `_cntt2025_gallery_border_radius`

Bo góc ảnh: `rounded-sm`, `rounded`, `rounded-lg`, `rounded-xl`, `rounded-full`

## Hooks và Filters

### Actions

```php
// Before gallery render
do_action('cntt2025_before_gallery_render', $gallery_id, $atts);

// After gallery render
do_action('cntt2025_after_gallery_render', $gallery_id, $atts, $output);

// Before image render
do_action('cntt2025_before_image_render', $image_data, $gallery_id);

// After image render
do_action('cntt2025_after_image_render', $image_data, $gallery_id);
```

### Filters

```php
// Filter gallery output
add_filter('cntt2025_gallery_output', function($output, $gallery_id, $atts) {
    // Modify gallery HTML
    return $output;
}, 10, 3);

// Filter gallery images
add_filter('cntt2025_gallery_images', function($images, $gallery_id) {
    // Modify images array
    return $images;
}, 10, 2);

// Filter gallery settings
add_filter('cntt2025_gallery_settings', function($settings, $gallery_id) {
    // Modify settings
    return $settings;
}, 10, 2);
```

## CSS Classes

### Gallery Container

```css
.cntt2025-gallery-container {
  /* Gallery wrapper */
}

.cntt2025-gallery-item {
  /* Individual image item */
}

.cntt2025-gallery-item img {
  /* Image styling */
}
```

### Modal Classes

```css
#cntt2025-gallery-modal {
  /* Modal overlay */
}

#gallery-modal-image {
  /* Modal image */
}

#gallery-modal-caption {
  /* Image caption */
}

#gallery-prev,
#gallery-next {
  /* Navigation buttons */
}

#gallery-modal-close {
  /* Close button */
}
```

## JavaScript Events

### Frontend Events

```javascript
// Gallery item clicked
$(document).on(
  "cntt2025_gallery_item_clicked",
  function (e, imageData, galleryId) {
    console.log("Image clicked:", imageData);
  }
);

// Modal opened
$(document).on(
  "cntt2025_modal_opened",
  function (e, imageIndex, galleryImages) {
    console.log("Modal opened with image:", imageIndex);
  }
);

// Modal closed
$(document).on("cntt2025_modal_closed", function (e) {
  console.log("Modal closed");
});

// Image navigation
$(document).on("cntt2025_image_navigation", function (e, direction, newIndex) {
  console.log("Navigated", direction, "to image:", newIndex);
});
```

### Admin Events

```javascript
// Image added to gallery
$(document).on("cntt2025_image_added", function (e, attachment, galleryId) {
  console.log("Image added:", attachment);
});

// Image removed from gallery
$(document).on("cntt2025_image_removed", function (e, imageIndex, galleryId) {
  console.log("Image removed at index:", imageIndex);
});

// Gallery settings changed
$(document).on(
  "cntt2025_settings_changed",
  function (e, setting, value, galleryId) {
    console.log("Setting changed:", setting, value);
  }
);
```

## Responsive Design

Plugin sử dụng Tailwind CSS classes cho responsive:

```css
/* Mobile first approach */
.grid-cols-1                    /* 1 column on mobile */
.sm:grid-cols-2                 /* 2 columns on small screens (640px+) */
.lg:grid-cols-3                 /* 3 columns on large screens (1024px+) */
.xl:grid-cols-4                 /* 4 columns on extra large screens (1280px+) */

/* Custom breakpoints */
@media (max-width: 768px) {
  /* Mobile-specific styles */
}

@media (min-width: 769px) and (max-width: 1024px) {
  /* Tablet-specific styles */
}

@media (min-width: 1025px) {
  /* Desktop-specific styles */
}
```

## Tùy chỉnh CSS

### Override Default Styles

```css
/* Custom gallery styling */
.cntt2025-gallery-container {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 12px;
}

.cntt2025-gallery-item {
  border: 2px solid #e9ecef;
  transition: all 0.3s ease;
}

.cntt2025-gallery-item:hover {
  border-color: #007bff;
  transform: translateY(-5px);
}

/* Custom modal styling */
#cntt2025-gallery-modal {
  background: rgba(0, 0, 0, 0.9);
}

#gallery-modal-image {
  border: 5px solid white;
  border-radius: 10px;
}
```

### Custom Animation

```css
/* Custom hover effects */
.cntt2025-gallery-item {
  position: relative;
  overflow: hidden;
}

.cntt2025-gallery-item::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    45deg,
    transparent,
    rgba(255, 255, 255, 0.2),
    transparent
  );
  transform: translateX(-100%);
  transition: transform 0.6s ease;
}

.cntt2025-gallery-item:hover::before {
  transform: translateX(100%);
}
```

## Tối ưu hóa Performance

### Lazy Loading

```php
// Enable lazy loading (tự động)
add_filter('cntt2025_gallery_lazy_load', '__return_true');

// Custom lazy loading threshold
add_filter('cntt2025_lazy_load_threshold', function() {
    return '100px'; // Load image when 100px away
});
```

### Image Optimization

```php
// Custom image sizes
add_filter('cntt2025_gallery_image_sizes', function($sizes) {
    $sizes['gallery_thumb'] = [300, 300, true];
    $sizes['gallery_medium'] = [600, 600, true];
    return $sizes;
});

// WebP support
add_filter('cntt2025_gallery_webp_support', '__return_true');
```

## Bảo mật

### Nonce Protection

```php
// Tự động xác thực nonce
wp_verify_nonce($_POST['gallery_meta_nonce_field'], 'gallery_meta_nonce');

// Capability check
if (!current_user_can('edit_post', $post_id)) {
    return;
}
```

### Data Sanitization

```php
// Sanitize image data
$image_id = sanitize_text_field($image_data['id']);
$image_url = esc_url_raw($image_data['url']);
$image_caption = sanitize_text_field($image_data['caption']);
```

### XSS Prevention

```php
// Escape output
echo esc_attr($image_caption);
echo esc_url($image_url);
echo esc_html($gallery_title);
```

## Khắc phục sự cố

### Gallery không hiển thị

1. Kiểm tra ID gallery có chính xác không
2. Đảm bảo gallery đã được publish
3. Kiểm tra gallery có chứa ảnh không

### Popup không hoạt động

1. Kiểm tra JavaScript có load không
2. Xem Console browser để debug
3. Đảm bảo jQuery đã được load

### Responsive không hoạt động

1. Kiểm tra Tailwind CSS classes
2. Verify CSS file được load đúng
3. Test trên các breakpoint khác nhau

### Debug Mode

```php
// Enable debug trong wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Plugin debug
add_filter('cntt2025_gallery_debug', '__return_true');
```

## Tương thích

- **WordPress:** 5.0 trở lên
- **PHP:** 7.4 trở lên
- **Browsers:** Chrome, Firefox, Safari, Edge (current versions)
- **Mobile:** iOS Safari, Android Chrome
- **Themes:** Tương thích với mọi theme

## Changelog

### Version 1.0

- Tạo và quản lý gallery với giao diện admin
- Popup viewer với navigation
- Shortcode system
- Responsive design với Tailwind CSS
- Mobile swipe support
- Drag & drop image ordering
- Custom settings (columns, gap, border-radius)

## Hỗ trợ

Để được hỗ trợ, vui lòng liên hệ:

- **Email:** hieunt@dlu.edu.vn
- **Tác giả:** NGUYỄN TRỌNG HIẾU

## Giấy phép

Plugin này được phát triển cho mục đích giáo dục và nghiên cứu tại trường Đại học Đà Lạt.

---

**Tip:** Sử dụng plugin này cùng với các plugin khác trong suite CNTT2025 để có trải nghiệm quản lý nội dung tối ưu nhất.
