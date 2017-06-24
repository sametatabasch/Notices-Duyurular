jQuery(document).ready(function ($) {
    /**
     * if page contain admin bar, set noticeContainer top property with admin bar height
     */
    function adjustNoticeContainerCSSTop() {
        console.log('default adjustNoticeContainerCSSTop');
        $('.noticeContainer').css({'top': $('#wpadminbar').height()});
    }

    function handleCloseButtonOfBarNotice() {
        console.log('default handleCloseButtonOfBarNotice');
        $('.bar .close').click(function () {
            console.log('bar Close tıklandı');
            //Aktif duyurunun id bilgisi  alınıyor
            var currentId = $(this).parent()[0].id;

            var reg = /\d/g;
            currentId = currentId.match(reg).join(''); //id  değerinin sadece sayı olduğu doğrulanıyor.

            // çoklu  dil desteği için noticeLocalizeMessage nesnesi kullanılıyor ilgili fonksiyon: GB_D_addScriptAndStyle
            var icerik =
                '<div class="bar alert alert-info">' +
                '<h4></h4>' +
                '<p>' + noticeLocalizeMessage.closeMessage + '</p>' +
                '<button id="yes" class="btn">' + noticeLocalizeMessage.dontShow + '</button> - <button id="no" class="btn">' + noticeLocalizeMessage.close + '</button>' +
                '</div>';

            $('.noticeContainer').find('#bar-' + currentId).replaceWith(icerik);

            $('#yes').click(function () {
                $.post('', {GB_D_noticeId: +currentId}, 'json'); //okundu olarak işaretleme yapılıyor
                $(this).parent().remove();
            });

            $('#no').click(function () {
                $(this).parent().remove();
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
