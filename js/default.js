jQuery(document).ready(function ($) {
    /**
     * if page contain admin bar, set noticeContainer top property with admin bar height
     */
    function adjustNoticeContainerCSSTop() {
        $('.noticeContainer').css({'top': $('#wpadminbar').height()});
    }

    function handleCloseButtonOfBarNotice() {
        $('.bar .close').click(function () {
            console.log('bar Close tıklandı');
            //Aktif duyurunun id bilgisi  alınıyor
            var currentId = $(this).parents('.bar')[0].id;

            var reg = /\d/g;
            currentId = currentId.match(reg).join(''); //id  değerinin sadece sayı olduğu doğrulanıyor.

            // çoklu  dil desteği için noticeLocalizeMessage nesnesi kullanılıyor ilgili fonksiyon: GB_D_addScriptAndStyle todo tasarım güncellenecek
            var icerik =
                '<div id="closeDialog" class="bar notice-blue">' +
                '<div class="bar-content">' +
                '<div>' +
                '<p>' + noticeLocalizeMessage.closeMessage + '</p>' +
                '</div>' +
                '</div>' +
                '<div class="bar-footer">' +
                '<div class="center">' +
                '<button id="dontShowAgainNotice">' + noticeLocalizeMessage.dontShow + '</button>' +
                '<button id="closeNotice">' + noticeLocalizeMessage.close + '</button>' +
                '<div style="clear: both"></div>' +
                '</div>' +
                '</div>' +
                '</div>';

            $('.noticeContainer').find('#bar-' + currentId).replaceWith(icerik);

            $('#dontShowAgainNotice').click(function () {//todo post işlemi yapılacak
                /*
                 * Send mark as read request
                 */
                $.post(ajaxData_default.ajaxurl, {
                    noticeId: currentId,
                    action: 'markAsReadNotice',
                    security: ajaxData_default.securityFor_markAsReadNotice
                }, function (response) {
                });
                $(this).parents('.bar').remove();
            });

            $('#closeNotice').click(function () {
                $(this).parents('.bar').remove();
            });
        });
    }

    /**
     * Get available Notice
     */
    $.ajax({
        type: "post",
        url: ajaxData_default.ajaxurl,
        data: {action: "getNoticesContainer", security: ajaxData_default.securityFor_getNoticesContainer},
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (response) {
            $("body").append(response.noticesContainer);
            if (response.isThereWindowModeNotice) {
                $(".noticeContainer").GBWindow();
            }
            adjustNoticeContainerCSSTop();
            handleCloseButtonOfBarNotice();
        }
    });
});
