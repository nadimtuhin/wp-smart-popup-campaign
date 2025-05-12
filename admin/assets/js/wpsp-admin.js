jQuery(document).ready(function($) {
    // Datepicker
    $('.wpsp-datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    // Conditional logic for content type
    function toggleContentTypeSettings() {
        var contentType = $('#wpsp_content_type').val();
        if (contentType === 'image') {
            $('.wpsp-setting-image').show();
            $('.wpsp-setting-html').hide();
        } else if (contentType === 'html') {
            $('.wpsp-setting-image').hide();
            $('.wpsp-setting-html').show();
        }
    }
    $('#wpsp_content_type').on('change', toggleContentTypeSettings);
    toggleContentTypeSettings();

    // Conditional logic for target pages
    function toggleSpecificPagesSettings() {
        var targetType = $('#wpsp_target_pages_type').val();
        if (targetType === 'specific') {
            $('.wpsp-setting-specific-pages').show();
        } else {
            $('.wpsp-setting-specific-pages').hide();
        }
    }
    $('#wpsp_target_pages_type').on('change', toggleSpecificPagesSettings);
    toggleSpecificPagesSettings();

    // Conditional logic for reappear days
    function toggleReappearDaysSettings() {
        var closeBehavior = $('#wpsp_close_behavior').val();
        if (closeBehavior === 'reappear_after') {
            $('.wpsp-setting-reappear-days').show();
        } else {
            $('.wpsp-setting-reappear-days').hide();
        }
    }
    $('#wpsp_close_behavior').on('change', toggleReappearDaysSettings);
    toggleReappearDaysSettings();

    // Media Uploader
    var mediaUploader;
    $('#wpsp_image_upload_button').on('click', function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: { text: 'Choose Image' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#wpsp_image_id').val(attachment.id);
            $('.wpsp-image-preview').html('<img src="' + attachment.sizes.medium.url + '" style="max-width:200px; height:auto;" />');
            $('.wpsp-image-remove-button').show();
        });
        mediaUploader.open();
    });

    $('.wpsp-image-remove-button').on('click', function(e) {
        e.preventDefault();
        $('#wpsp_image_id').val('');
        $('.wpsp-image-preview').html('');
        $(this).hide();
    });
}); 