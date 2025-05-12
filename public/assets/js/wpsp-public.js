jQuery(document).ready(function($) {

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            if (days === 'forever') {
                 date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000));
            } else {
                 date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            }
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    $('.wpsp-popup-overlay').each(function() {
        var $popup = $(this);
        var campaignId = $popup.data('campaign-id');
        var closeBehavior = $popup.data('close-behavior');
        var reappearDays = parseInt($popup.data('reappear-days'), 10);
        var cookieName = 'wpsp_dismissed_' + campaignId;
        var dismissedCookie = getCookie(cookieName);

        if (dismissedCookie) {
            if (closeBehavior === 'hide_forever') {
                return;
            } else if (closeBehavior === 'reappear_after') {
                var dismissedTimestamp = parseInt(dismissedCookie, 10);
                var now = new Date().getTime();
                if (now < dismissedTimestamp) {
                    return;
                }
            }
        }

        $popup.fadeIn();

        $.post(wpsp_ajax.ajax_url, {
            action: 'wpsp_increment_view',
            campaign_id: campaignId,
            nonce: wpsp_ajax.nonce
        });

        $popup.find('.wpsp-popup-close').on('click', function() {
            $popup.fadeOut();
            if (closeBehavior === 'hide_forever') {
                setCookie(cookieName, '1', 'forever');
            } else if (closeBehavior === 'reappear_after') {
                var reappearTimestamp = new Date().getTime() + (reappearDays * 24 * 60 * 60 * 1000);
                setCookie(cookieName, reappearTimestamp.toString(), reappearDays);
            }
        });

        if ($popup.data('is-image-popup') === true) {
            $popup.find('a[data-popup-track-click="true"]').on('click', function(e) {
                $.post(wpsp_ajax.ajax_url, {
                    action: 'wpsp_increment_click',
                    campaign_id: campaignId,
                    nonce: wpsp_ajax.nonce
                });
            });
        } else {
            $popup.find('[data-popup-click="true"]').on('click', function() {
                $.post(wpsp_ajax.ajax_url, {
                    action: 'wpsp_increment_click',
                    campaign_id: campaignId,
                    nonce: wpsp_ajax.nonce
                });
            });
        }
    });
}); 