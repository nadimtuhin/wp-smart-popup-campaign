<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPSP_Admin {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action( 'add_meta_boxes_popup_campaign', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_popup_campaign', array( $this, 'save_meta_boxes' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

        // Custom columns for CPT
        add_filter( 'manage_popup_campaign_posts_columns', array( $this, 'set_custom_edit_popup_campaign_columns' ) );
        add_action( 'manage_popup_campaign_posts_custom_column', array( $this, 'custom_popup_campaign_column' ), 10, 2 );
    }

    public function enqueue_styles_scripts( $hook_suffix ) {
        global $post_type;
        if ( ( $hook_suffix == 'post.php' || $hook_suffix == 'post-new.php' ) && $post_type == 'popup_campaign' ) {
            wp_enqueue_style( $this->plugin_name . '-admin', WPSP_PLUGIN_URL . 'admin/assets/css/wpsp-admin.css', array(), $this->version, 'all' );
            wp_enqueue_script( $this->plugin_name . '-admin', WPSP_PLUGIN_URL . 'admin/assets/js/wpsp-admin.js', array( 'jquery', 'jquery-ui-datepicker' ), $this->version, true );
            wp_enqueue_media();
            wp_enqueue_style('jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
        }
    }

    public function add_meta_boxes() {
        add_meta_box(
            'wpsp_campaign_settings',
            __( 'Campaign Settings', 'wp-smart-popup' ),
            array( $this, 'render_campaign_settings_meta_box' ),
            'popup_campaign',
            'normal',
            'high'
        );
        add_meta_box(
            'wpsp_campaign_analytics',
            __( 'Campaign Analytics', 'wp-smart-popup' ),
            array( $this, 'render_campaign_analytics_meta_box' ),
            'popup_campaign',
            'side',
            'low'
        );
    }

    public function render_campaign_settings_meta_box( $post ) {
        wp_nonce_field( 'wpsp_save_campaign_settings', 'wpsp_campaign_settings_nonce' );

        $content_type = get_post_meta( $post->ID, '_wpsp_content_type', true ) ?: 'image';
        $image_id = get_post_meta( $post->ID, '_wpsp_image_id', true );
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
        $redirect_url = get_post_meta( $post->ID, '_wpsp_redirect_url', true );
        $open_in_new_tab = get_post_meta( $post->ID, '_wpsp_open_in_new_tab', true );
        $custom_html = get_post_meta( $post->ID, '_wpsp_custom_html', true );

        $target_pages_type = get_post_meta( $post->ID, '_wpsp_target_pages_type', true ) ?: 'all';
        $specific_pages = get_post_meta( $post->ID, '_wpsp_specific_pages', true ) ?: array();
        
        $close_behavior = get_post_meta( $post->ID, '_wpsp_close_behavior', true ) ?: 'hide_forever';
        $reappear_days = get_post_meta( $post->ID, '_wpsp_reappear_days', true ) ?: 7;

        $start_date = get_post_meta( $post->ID, '_wpsp_start_date', true );
        $end_date = get_post_meta( $post->ID, '_wpsp_end_date', true );
        $status = get_post_meta( $post->ID, '_wpsp_status', true ) ?: 'inactive';

        ?>
        <table class="form-table wpsp-form-table">
            <tbody>
                <!-- Campaign Name: Handled by Post Title -->

                <!-- Status -->
                <tr>
                    <th scope="row"><label for="wpsp_status"><?php _e( 'Status', 'wp-smart-popup' ); ?></label></th>
                    <td>
                        <select name="wpsp_status" id="wpsp_status">
                            <option value="active" <?php selected( $status, 'active' ); ?>><?php _e( 'Active', 'wp-smart-popup' ); ?></option>
                            <option value="inactive" <?php selected( $status, 'inactive' ); ?>><?php _e( 'Inactive', 'wp-smart-popup' ); ?></option>
                        </select>
                    </td>
                </tr>

                <!-- Content Type -->
                <tr>
                    <th scope="row"><label for="wpsp_content_type"><?php _e( 'Content Type', 'wp-smart-popup' ); ?></label></th>
                    <td>
                        <select name="wpsp_content_type" id="wpsp_content_type">
                            <option value="image" <?php selected( $content_type, 'image' ); ?>><?php _e( 'Image', 'wp-smart-popup' ); ?></option>
                            <option value="html" <?php selected( $content_type, 'html' ); ?>><?php _e( 'HTML', 'wp-smart-popup' ); ?></option>
                        </select>
                    </td>
                </tr>

                <!-- Image Upload (Conditional) -->
                <tr class="wpsp-setting-image">
                    <th scope="row"><label for="wpsp_image_upload_button"><?php _e( 'Image Upload', 'wp-smart-popup' ); ?></label></th>
                    <td>
                        <div class="wpsp-image-preview">
                            <?php if ( $image_url ) : ?>
                                <img src="<?php echo esc_url( $image_url ); ?>" style="max-width:200px; height:auto;" />
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="wpsp_image_id" id="wpsp_image_id" value="<?php echo esc_attr( $image_id ); ?>" />
                        <button type="button" class="button" id="wpsp_image_upload_button"><?php _e( 'Upload/Select Image', 'wp-smart-popup' ); ?></button>
                        <button type="button" class="button wpsp-image-remove-button" style="<?php echo $image_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove Image', 'wp-smart-popup' ); ?></button>
                    </td>
                </tr>

                <!-- Redirect URL (Conditional) -->
                <tr class="wpsp-setting-image">
                    <th scope="row"><label for="wpsp_redirect_url"><?php _e( 'Redirect URL', 'wp-smart-popup' ); ?></label></th>
                    <td>
                        <input type="url" name="wpsp_redirect_url" id="wpsp_redirect_url" value="<?php echo esc_url( $redirect_url ); ?>" class="regular-text" />
                        <p class="description"><?php _e( 'Required if image is selected.', 'wp-smart-popup' ); ?></p>
                    </td>
                </tr>

                <!-- Open In (Conditional) -->
                <tr class="wpsp-setting-image">
                    <th scope="row"><label for="wpsp_open_in_new_tab"><?php _e( 'Open In', 'wp-smart-popup' ); ?></label></th>
                    <td>
                        <select name="wpsp_open_in_new_tab" id="wpsp_open_in_new_tab">
                            <option value="0" <?php selected( $open_in_new_tab, '0' ); ?>><?php _e( 'Same Tab', 'wp-smart-popup' ); ?></option>
                            <option value="1" <?php selected( $open_in_new_tab, '1' ); ?>><?php _e( 'New Tab', 'wp-smart-popup' ); ?></option>
                        </select>
                    </td>
                </tr>
                
                <!-- Custom HTML (Conditional) -->
                <tr class="wpsp-setting-html">
                    <th scope="row"><label for="wpsp_custom_html"><?php _e( 'Custom HTML', 'wp-smart-popup' ); ?></label></th>
                    <td>
                        <?php
                        wp_editor( $custom_html, 'wpsp_custom_html', array(
                            'textarea_name' => 'wpsp_custom_html',
                            'media_buttons' => true,
                            'textarea_rows' => 10,
                            'teeny'         => false,
                        ) );
                        ?>
                        <p class="description"><?php _e( 'Add <code>data-popup-click="true"</code> to any element (e.g., a button) to track its clicks.', 'wp-smart-popup' ); ?></p>
                    </td>
                </tr>

                <!-- Target Pages -->
                <tr>
                    <th scope="row"><label for="wpsp_target_pages_type"><?php _e( 'Target Pages', 'wp-smart-popup' ); ?></label></th>
                    <td>
                        <select name="wpsp_target_pages_type" id="wpsp_target_pages_type">
                            <option value="all" <?php selected( $target_pages_type, 'all' ); ?>><?php _e( 'All Pages', 'wp-smart-popup' ); ?></option>
                            <option value="specific" <?php selected( $target_pages_type, 'specific' ); ?>><?php _e( 'Specific Pages', 'wp-smart-popup' ); ?></option>
                        </select>
                    </td>
                </tr>

                <!-- Page Selector (Conditional) -->
                <tr class="wpsp-setting-specific-pages">
                    <th scope="row"><label for="wpsp_specific_pages"><?php _e( 'Select Pages', 'wp-smart-popup' ); ?></label></th>
                    <td>
                        <select name="wpsp_specific_pages[]" id="wpsp_specific_pages" multiple="multiple" style="width:100%; min-height:150px;">
                            <?php
                            $pages = get_pages();
                            foreach ( $pages as $page ) {
                                $selected = in_array( $page->ID, (array)$specific_pages ) ? 'selected' : '';
                                echo '<option value="' . esc_attr( $page->ID ) . '" ' . $selected . '>' . esc_html( $page->post_title ) . '</option>';
                            }
                            $posts = get_posts(array('post_type' => 'post', 'posts_per_page' => -1));
                             foreach ( $posts as $p ) {
                                $selected = in_array( $p->ID, (array)$specific_pages ) ? 'selected' : '';
                                echo '<option value="' . esc_attr( $p->ID ) . '" ' . $selected . '>' . esc_html( $p->post_title ) . ' (Post)</option>';
                            }
                            ?>
                        </select>
                        <p class="description"><?php _e( 'Hold Ctrl/Cmd to select multiple. Consider using a JS library like Select2 for better UX with many pages.', 'wp-smart-popup' ); ?></p>
                    </td>
                </tr>

                <!-- Close Behavior -->
                <tr>
                    <th scope="row"><label for="wpsp_close_behavior"><?php _e( 'Close Behavior', 'wp-smart-popup' ); ?></label></th>
                    <td>
                        <select name="wpsp_close_behavior" id="wpsp_close_behavior">
                            <option value="hide_forever" <?php selected( $close_behavior, 'hide_forever' ); ?>><?php _e( 'Hide forever (per browser via cookie)', 'wp-smart-popup' ); ?></option>
                            <option value="reappear_after" <?php selected( $close_behavior, 'reappear_after' ); ?>><?php _e( 'Reappear after X days', 'wp-smart-popup' ); ?></option>
                        </select>
                    </td>
                </tr>

                <!-- Show Again After Days (Conditional) -->
                <tr class="wpsp-setting-reappear-days">
                    <th scope="row"><label for="wpsp_reappear_days"><?php _e( 'Show Again After (Days)', 'wp-smart-popup' ); ?></label></th>
                    <td>
                        <input type="number" name="wpsp_reappear_days" id="wpsp_reappear_days" value="<?php echo esc_attr( $reappear_days ); ?>" class="small-text" min="1" />
                    </td>
                </tr>

                <!-- Schedule -->
                <tr>
                    <th scope="row"><?php _e( 'Schedule', 'wp-smart-popup' ); ?></th>
                    <td>
                        <label for="wpsp_start_date"><?php _e( 'Start Date:', 'wp-smart-popup' ); ?></label>
                        <input type="text" name="wpsp_start_date" id="wpsp_start_date" value="<?php echo esc_attr( $start_date ); ?>" class="wpsp-datepicker" placeholder="YYYY-MM-DD" />
                          
                        <label for="wpsp_end_date"><?php _e( 'End Date (Optional):', 'wp-smart-popup' ); ?></label>
                        <input type="text" name="wpsp_end_date" id="wpsp_end_date" value="<?php echo esc_attr( $end_date ); ?>" class="wpsp-datepicker" placeholder="YYYY-MM-DD" />
                    </td>
                </tr>

            </tbody>
        </table>
        <?php
    }
    
    public function render_campaign_analytics_meta_box( $post ) {
        $views = (int) get_post_meta( $post->ID, '_wpsp_views', true );
        $clicks = (int) get_post_meta( $post->ID, '_wpsp_clicks', true );
        $ctr = ( $views > 0 ) ? round( ( $clicks / $views ) * 100, 2 ) : 0;
        ?>
        <p><strong><?php _e( 'Views:', 'wp-smart-popup' ); ?></strong> <?php echo $views; ?></p>
        <p><strong><?php _e( 'Clicks:', 'wp-smart-popup' ); ?></strong> <?php echo $clicks; ?></p>
        <p><strong><?php _e( 'CTR:', 'wp-smart-popup' ); ?></strong> <?php echo $ctr; ?>%</p>
        <p class="description"><?php _e( 'Analytics are updated automatically.', 'wp-smart-popup' ); ?></p>
        <?php
    }

    public function save_meta_boxes( $post_id, $post ) {
        // Check nonce
        if ( ! isset( $_POST['wpsp_campaign_settings_nonce'] ) || ! wp_verify_nonce( $_POST['wpsp_campaign_settings_nonce'], 'wpsp_save_campaign_settings' ) ) {
            return;
        }

        // Check if current user can edit post
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Don't save on autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Ensure it's our CPT
        if ( 'popup_campaign' !== $post->post_type ) {
            return;
        }

        // --- Save Data ---

        // Status
        if ( isset( $_POST['wpsp_status'] ) ) {
            update_post_meta( $post_id, '_wpsp_status', sanitize_text_field( $_POST['wpsp_status'] ) );
        }

        // Content Type
        if ( isset( $_POST['wpsp_content_type'] ) ) {
            update_post_meta( $post_id, '_wpsp_content_type', sanitize_text_field( $_POST['wpsp_content_type'] ) );
        }

        // Image ID
        if ( isset( $_POST['wpsp_image_id'] ) ) {
            update_post_meta( $post_id, '_wpsp_image_id', intval( $_POST['wpsp_image_id'] ) );
        }

        // Redirect URL
        if ( isset( $_POST['wpsp_redirect_url'] ) ) {
            update_post_meta( $post_id, '_wpsp_redirect_url', esc_url_raw( $_POST['wpsp_redirect_url'] ) );
        }

        // Open In New Tab
        update_post_meta( $post_id, '_wpsp_open_in_new_tab', isset( $_POST['wpsp_open_in_new_tab'] ) ? sanitize_text_field($_POST['wpsp_open_in_new_tab']) : '0' );

        // Custom HTML
        if ( isset( $_POST['wpsp_custom_html'] ) ) {
            update_post_meta( $post_id, '_wpsp_custom_html', wp_kses_post( $_POST['wpsp_custom_html'] ) );
        }

        // Target Pages Type
        if ( isset( $_POST['wpsp_target_pages_type'] ) ) {
            update_post_meta( $post_id, '_wpsp_target_pages_type', sanitize_text_field( $_POST['wpsp_target_pages_type'] ) );
        }

        // Specific Pages
        if ( isset( $_POST['wpsp_specific_pages'] ) ) {
            $specific_pages = array_map( 'intval', (array) $_POST['wpsp_specific_pages'] );
            update_post_meta( $post_id, '_wpsp_specific_pages', $specific_pages );
        } else {
             update_post_meta( $post_id, '_wpsp_specific_pages', array() );
        }

        // Close Behavior
        if ( isset( $_POST['wpsp_close_behavior'] ) ) {
            update_post_meta( $post_id, '_wpsp_close_behavior', sanitize_text_field( $_POST['wpsp_close_behavior'] ) );
        }

        // Reappear Days
        if ( isset( $_POST['wpsp_reappear_days'] ) ) {
            update_post_meta( $post_id, '_wpsp_reappear_days', intval( $_POST['wpsp_reappear_days'] ) );
        }

        // Start Date
        if ( isset( $_POST['wpsp_start_date'] ) ) {
            update_post_meta( $post_id, '_wpsp_start_date', sanitize_text_field( $_POST['wpsp_start_date'] ) );
        }
        
        // End Date
        if ( isset( $_POST['wpsp_end_date'] ) ) {
            update_post_meta( $post_id, '_wpsp_end_date', sanitize_text_field( $_POST['wpsp_end_date'] ) );
        }
    }

    public function set_custom_edit_popup_campaign_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key == 'title') {
                $new_columns['wpsp_status'] = __( 'Status', 'wp-smart-popup' );
                $new_columns['wpsp_views'] = __( 'Views', 'wp-smart-popup' );
                $new_columns['wpsp_clicks'] = __( 'Clicks', 'wp-smart-popup' );
                $new_columns['wpsp_ctr'] = __( 'CTR', 'wp-smart-popup' );
            }
        }
        return $new_columns;
    }

    public function custom_popup_campaign_column( $column, $post_id ) {
        switch ( $column ) {
            case 'wpsp_status':
                $status = get_post_meta( $post_id, '_wpsp_status', true );
                echo ( $status === 'active' ) ? '<span style="color:green;">'.__('Active', 'wp-smart-popup').'</span>' : '<span style="color:red;">'.__('Inactive', 'wp-smart-popup').'</span>';
                break;
            case 'wpsp_views':
                echo (int) get_post_meta( $post_id, '_wpsp_views', true );
                break;
            case 'wpsp_clicks':
                echo (int) get_post_meta( $post_id, '_wpsp_clicks', true );
                break;
            case 'wpsp_ctr':
                $views = (int) get_post_meta( $post_id, '_wpsp_views', true );
                $clicks = (int) get_post_meta( $post_id, '_wpsp_clicks', true );
                $ctr = ( $views > 0 ) ? round( ( $clicks / $views ) * 100, 2 ) . '%' : '0%';
                echo $ctr;
                break;
        }
    }
} 