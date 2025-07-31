# WP Smart Popup Campaigns

A WordPress plugin for creating and managing popup campaigns using images or custom HTML.

## Description

WP Smart Popup Campaigns allows you to create engaging popup campaigns for your WordPress site. You can use images or custom HTML to create popups that capture user attention and drive conversions.

## Features

### 🎯 **Campaign Management**
- Create unlimited popup campaigns with intuitive admin interface
- Support for both image-based and custom HTML popups
- Real-time campaign status control (Active/Inactive)
- Campaign scheduling with start and end dates
- Custom post type integration with WordPress admin

### 🖼️ **Content Types**
- **Image Popups**: Upload images with customizable redirect URLs
- **HTML Popups**: Rich text editor with full HTML support and shortcode compatibility
- Media library integration for easy image management
- Target link control (same tab or new tab)

### 🎯 **Smart Targeting**
- **Page Targeting**: Display on all pages or specific pages/posts
- Multi-select interface for choosing specific content
- Support for both pages and posts targeting
- Conditional display logic

### 📊 **Analytics & Tracking**
- Real-time view and click tracking
- Click-through rate (CTR) calculation
- AJAX-powered analytics without page reload
- Custom analytics dashboard in admin
- Campaign performance metrics in post listing

### 🔄 **User Experience Controls**
- **Close Behavior Options**:
  - Hide forever (browser cookie-based)
  - Reappear after X days (customizable interval)
- Single popup per page load prevention
- Clean, responsive popup design
- Accessibility-compliant close buttons

### 🛡️ **Security & Performance**
- WordPress nonce verification for all AJAX requests
- Proper data sanitization and validation
- Secure HTML content handling with `wp_kses_post()`
- User capability checks for admin functions
- WPINC security checks

### 🎨 **Customization**
- Custom CSS and JS asset loading
- jQuery UI datepicker integration
- Responsive design compatibility
- Extensible hook system
- Translation-ready with text domain support

## Installation

1. Download the plugin files
2. Upload to your WordPress `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to **Popups** in your WordPress admin menu
5. Click **Add New** to create your first popup campaign

## Usage

### Creating an Image Popup
1. Go to **Popups > Add New**
2. Enter a campaign name
3. Set **Status** to **Active**
4. Choose **Content Type**: **Image**
5. Upload an image and set redirect URL
6. Configure targeting and schedule options
7. **Publish** your campaign

### Creating an HTML Popup
1. Go to **Popups > Add New**
2. Enter a campaign name
3. Set **Status** to **Active**
4. Choose **Content Type**: **HTML**
5. Use the rich text editor to create your popup content
6. Add `data-popup-click="true"` to elements you want to track clicks on
7. Configure targeting and schedule options
8. **Publish** your campaign

### Analytics Dashboard
- View campaign performance in the **Popups** post listing
- See **Views**, **Clicks**, and **CTR** for each campaign
- Access detailed analytics in the campaign edit screen

## Technical Details

### Custom Post Type
- **Post Type**: `popup_campaign`
- **Menu Position**: 20 (below Pages)
- **Menu Icon**: `dashicons-slides`
- **Supports**: Title only (content managed via meta boxes)

### AJAX Endpoints
- `wp_ajax_wpsp_increment_view` - Tracks popup views
- `wp_ajax_wpsp_increment_click` - Tracks popup clicks
- Both endpoints support logged-in and non-logged-in users

### Hooks & Filters
The plugin provides several action hooks for developers:
- `plugins_loaded` - Initializes plugin classes
- `add_meta_boxes_popup_campaign` - Adds admin meta boxes
- `save_post_popup_campaign` - Saves campaign settings
- `wp_footer` - Displays popup HTML

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- jQuery (included with WordPress)
- Modern browser with cookie support

## Author

**Nadim Tuhin**
- Website: [https://nadimtuhin.com/](https://nadimtuhin.com/)

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support and feature requests, please open an issue on GitHub.
