<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPSP_Public {

    private $plugin_name;
    private $version;
    private static $popup_displayed = false; // Ensure only one popup per page load

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
        add_action( 'wp_footer', array( $this, 'display_popup' ) );

        // AJAX Handlers for analytics
        add_action( 'wp_ajax_wpsp_increment_view', array( $this, 'ajax_increment_view' ) );
        add_action( 'wp_ajax_nopriv_wpsp_increment_view', array( $this, 'ajax_increment_view' ) );
        add_action( 'wp_ajax_wpsp_increment_click', array( $this, 'ajax_increment_click' ) );
        add_action( 'wp_ajax_nopriv_wpsp_increment_click', array( $this, 'ajax_increment_click' ) );
    }

    public function enqueue_styles_scripts() {
        wp_enqueue_style( $this->plugin_name . '-public', WPSP_PLUGIN_URL . 'public/assets/css/wpsp-public.css', array(), $this->version, 'all' );
        wp_enqueue_script( $this->plugin_name . '-public', WPSP_PLUGIN_URL . 'public/assets/js/wpsp-public.js', array( 'jquery' ), $this->version, true );
        
        wp_localize_script( $this->plugin_name . '-public', 'wpsp_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wpsp_ajax_nonce' )
        ) );
    }

    public function display_popup() {
        if ( self::$popup_displayed || is_admin() ) {
            return;
        }

        $current_page_id = get_queried_object_id();
        $today_date_str = date('Y-m-d');

        $args = array(
            'post_type'      => 'popup_campaign',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_wpsp_status',
                    'value'   => 'active',
                    'compare' => '=',
                ),
            ),
            'orderby' => 'modified',
            'order' => 'DESC'
        );
        $campaigns = get_posts( $args );

        foreach ( $campaigns as $campaign ) {
            $campaign_id = $campaign->ID;

            // 1. Check Schedule
            $start_date_str = get_post_meta( $campaign_id, '_wpsp_start_date', true );
            $end_date_str = get_post_meta( $campaign_id, '_wpsp_end_date', true );

            if ( $start_date_str && strtotime( $start_date_str ) > strtotime( $today_date_str ) ) {
                continue;
            }
            if ( $end_date_str && strtotime( $end_date_str ) < strtotime( $today_date_str ) ) {
                continue;
            }

            // 2. Check Page Targeting
            $target_pages_type = get_post_meta( $campaign_id, '_wpsp_target_pages_type', true );
            if ( $target_pages_type === 'specific' ) {
                $specific_pages = get_post_meta( $campaign_id, '_wpsp_specific_pages', true );
                if ( ! is_array( $specific_pages ) || ! in_array( $current_page_id, $specific_pages ) ) {
                    continue;
                }
            }

            $this->render_popup_html( $campaign_id );
            self::$popup_displayed = true;
            return;
        }
    }

    private function render_popup_html( $campaign_id ) {
        $content_type = get_post_meta( $campaign_id, '_wpsp_content_type', true );
        $close_behavior = get_post_meta( $campaign_id, '_wpsp_close_behavior', true );
        $reappear_days = (int) get_post_meta( $campaign_id, '_wpsp_reappear_days', true ) ?: 7;

        $popup_content = '';
        $is_image_popup = false;
        $redirect_url = '';
        $open_in_new_tab = false;

        if ( $content_type === 'image' ) {
            $is_image_popup = true;
            $image_id = get_post_meta( $campaign_id, '_wpsp_image_id', true );
            $redirect_url = get_post_meta( $campaign_id, '_wpsp_redirect_url', true );
            $open_in_new_tab = get_post_meta( $campaign_id, '_wpsp_open_in_new_tab', true ) === '1';
            
            if ( $image_id && $redirect_url ) {
                $image_full_url = wp_get_attachment_image_url( $image_id, 'full' );
                if ($image_full_url) {
                     $popup_content = '<a href="' . esc_url( $redirect_url ) . '" ' . ( $open_in_new_tab ? 'target="_blank"' : '' ) . ' data-popup-track-click="true"><img src="' . esc_url( $image_full_url ) . '" alt="Popup Image"></a>';
                }
            }
        } else {
            $custom_html = get_post_meta( $campaign_id, '_wpsp_custom_html', true );
            $popup_content = do_shortcode( wp_kses_post( $custom_html ) );
        }

        if ( empty( $popup_content ) ) {
            return;
        }

        ?>
        <div id="wpsp-popup-<?php echo esc_attr( $campaign_id ); ?>" class="wpsp-popup-overlay" style="display:none;"
             data-campaign-id="<?php echo esc_attr( $campaign_id ); ?>"
             data-close-behavior="<?php echo esc_attr( $close_behavior ); ?>"
             data-reappear-days="<?php echo esc_attr( $reappear_days ); ?>"
             data-is-image-popup="<?php echo $is_image_popup ? 'true' : 'false'; ?>">
            <div class="wpsp-popup-content">
                <button class="wpsp-popup-close" aria-label="<?php _e('Close popup', 'wp-smart-popup'); ?>">×</button>
                <?php echo $popup_content; ?>
            </div>
        </div>
        <?php
    }

    public function ajax_increment_view() {
        check_ajax_referer( 'wpsp_ajax_nonce', 'nonce' );

        if ( isset( $_POST['campaign_id'] ) ) {
            $campaign_id = intval( $_POST['campaign_id'] );
            $views = (int) get_post_meta( $campaign_id, '_wpsp_views', true );
            update_post_meta( $campaign_id, '_wpsp_views', $views + 1 );
            wp_send_json_success( array( 'message' => 'View tracked.' ) );
        }
        wp_send_json_error( array( 'message' => 'Invalid campaign ID.' ) );
    }

    public function ajax_increment_click() {
        check_ajax_referer( 'wpsp_ajax_nonce', 'nonce' );

        if ( isset( $_POST['campaign_id'] ) ) {
            $campaign_id = intval( $_POST['campaign_id'] );
            $clicks = (int) get_post_meta( $campaign_id, '_wpsp_clicks', true );
            update_post_meta( $campaign_id, '_wpsp_clicks', $clicks + 1 );
            wp_send_json_success( array( 'message' => 'Click tracked.' ) );
        }
        wp_send_json_error( array( 'message' => 'Invalid campaign ID.' ) );
    }
} 