/**
 * Created by sametatabasch on 12.06.2017.
 */
(function ($) {
    /**
     * jQuery extention for show window type notice like a modal
     * @param parameters
     * @constructor
     */
    $.fn.GBWindow = function (parameters) {
        var _param = $.extend({
            'noticesClass': 'window'
        }, parameters);

        /**
         * index number of windowObjects which current showing
         * @type {number} -1 => backgroundClickMessage, -2 => closeDialog
         */
        var _activeIndex = 0;

        /**
         * Store previous notice index number. Because closeDialog and readConfirm message show with _activeIndex
         * @type {number}
         * @private
         */
        var _tempIndex = -1;

        /**
         *
         * @type {*}
         * @private
         */
        var _htmlBody = $('body');

        /**
         * Overlay for window mode notices
         * @type {*}
         */
        var _background = $('<div class="windowBackground"></div>');

        /**
         * Html element which will contain window mode Notice
         *
         * @type {*}
         * @private
         */
        var _windowBox = $('<div id="windowBox" class="notice-class"></div>');

        /**
         * Loading Animation
         * @type {*}
         */
        var _loadingAnimation = $(
            '<div id="noticeLoading" class="spinner">' +
            '		<div class="bounce1"></div>' +
            '		<div class="bounce2"></div>' +
            '		<div class="bounce3"></div>' +
            '</div>'
        );

        /**
         *
         * @type {*}
         */
        var closeDialog = $(
            '<div id="closeDialog" class="window notice-white" data-size="medium">' +
            '<div class="window-content">' +
            '<div>' +
            '<p>' + noticeLocalizeMessage.closeMessage + '</p>' +
            '</div>' +
            '</div>' +
            '<div class="window-footer">' +
            '<div class="center">' +
            '<button id="dontShowAgainNotice">' + noticeLocalizeMessage.dontShow + '</button>' +
            '<button id="closeNotice">' + noticeLocalizeMessage.close + '</button>' +
            '<div style="clear: both"></div>' +
            '</div>' +
            '</div>' +
            '</div>'
        );

        /**
         * for prevent multi click
         * @type {boolean}
         */
        var isBackgroundClicked = false;

        /**
         *
         * @type {*}
         */
        var backgroundClickMessage = $(
            '<div id="backgroundClickMessage" class="window notice-white" data-size="small" data-displayTime="5">' +
            '<div class="window-content">' +
            '<div>' +
            '<p>' + noticeLocalizeMessage.backgroundClickMessage + '</p>' +
            '</div>' +
            '</div>' +
            '<div class="window-footer">' +
            '<progress value="100" max="100"></progress>' +
            '</div>' +
            '</div>'
        );

        /**
         *
         * @type {*}
         */
        var _windowBoxPreviousWindowButton = $('<a title="Previous" class="window-nav window-nav-previous" href="javascript:;"><span></span></a>');

        /**
         *
         * @type {*}
         */
        var _windowBoxNextWindowButton = $('<a title="Next" class="window-nav window-nav-next" href="javascript:;"><span></span></a>');

        /**
         * Store all window mode Notice id numbers
         */
        var _noticeIds;

        /**
         * currently showing notice Object
         * Oluşturduğum window sınıfının özelliklerine ulaşabilmek için kullanılıyor. _progress, _size,_displayTime gibi
         * @type {*}
         * @private
         */
        var _activeWindowNoticeObject;

        /**
         * Duyurunun eklenmesi için gerekli html elementlerini sayfaya ekliyor. Arkaplan ve duyuru kutusu gibi
         */
        function prepareForShow() {
            _htmlBody.append(_background);
            _htmlBody.append(_windowBox);
            _noticeIds = _param.windowModeNoticeIds;
            showNextAndPrevButtonIfNecessary();

            _background.click(handleClick_backgroundClick);
        }

        /**
         * Gösterilecek duyurunun notiveWindow sınıfını hazırlar
         * @return {boolean}
         */
        function setActiveNotice() {
            if (_noticeIds.length > 0) {//gösterilecek duyuru var ise
                if (_activeIndex >= 0) {// eğer 0 ve üstü bir sayı ise duyuru
                    /*
                     * get single notice
                     */
                    $.ajax({
                        type: "POST",
                        url: ajaxData_GBWindow.ajaxurl,
                        data: {
                            noticeId: _noticeIds[_activeIndex],
                            security: ajaxData_GBWindow.securityFor_getSingleWindowModeNotice,
                            action: 'getSingleWindowModeNotice'
                        },
                        success: function (response) {
                            _activeWindowNoticeObject = new GB_D_noticeWindow($(response.html));
                            _activeWindowNoticeObject._jObject = _activeWindowNoticeObject._jObject;

                        },
                        async:false
                    });
                } else { // değilse ön tanımlı mesajlardan biri gösterilecek
                    switch (_activeIndex) {
                        case -1:
                            _activeWindowNoticeObject = new GB_D_noticeWindow(backgroundClickMessage);
                            _activeWindowNoticeObject._jObject = _activeWindowNoticeObject._jObject;
                            break;
                        case -2:
                            _activeWindowNoticeObject = new GB_D_noticeWindow(closeDialog);
                            _activeWindowNoticeObject._jObject = _activeWindowNoticeObject._jObject;
                            break;
                    }
                }
                return true;
            } else {// gösterğilecek duyuru yok ise
                closeAllWindows();
                return false;
            }
        }

        /**
         *
         */
        function showActiveNotice() {
            if(setActiveNotice()){
                _background.removeClass('notice-white notice-red notice-green notice-blue'); // remove old color class
                _background.addClass(_activeWindowNoticeObject._color);
                _windowBox.removeClass('xLarge large medium small');
                _windowBox.addClass(_activeWindowNoticeObject._size);
                $('#wpadminbar').hide();
                _windowBox.append(_activeWindowNoticeObject._jObject);
                _windowBox.append(_loadingAnimation);
                _windowBox.imagesLoaded().done(function () {
                    _loadingAnimation.remove();
                    _activeWindowNoticeObject._jObject.fadeIn();
                    locateNotice();
                    /**
                     * add click event listenner to window close button
                     */
                    _activeWindowNoticeObject._jObject.find('.close').click(showReadConfirmMessage);
                    handleDisplayTime();
                });
            }
        }

        function handleDisplayTime() {
            if (false !== _activeWindowNoticeObject._displayTime) {
                _activeWindowNoticeObject._progress.animate(
                    {value: 0},
                    {
                        duration: _activeWindowNoticeObject._displayTime * 1000,
                        done: function () {
                            if (_activeWindowNoticeObject._jObject.attr('id') === 'backgroundClickMessage') {
                                closeAllWindows();
                            } else {
                                closeActiveWindow();
                            }
                        },
                        fail: function () {
                            _activeWindowNoticeObject._progress.val(100);
                        }

                    }
                );
            }
        }

        /**
         *
         */
        function showNextNotice() {
            _activeWindowNoticeObject._progress.stop(true, false);// fail display time Animation
            _activeWindowNoticeObject._jObject.fadeOut(function () {
                _activeWindowNoticeObject._jObject.remove();
                _activeIndex++;
                if (_activeIndex > _noticeIds.length - 1) _activeIndex = 0;// set firs notice
                showActiveNotice();
                showNextAndPrevButtonIfNecessary();
            });
        }

        /**
         *
         */
        function showPreviousNotice() {
            _activeWindowNoticeObject._progress.stop(true, false);// fail display time Animation
            _activeWindowNoticeObject._jObject.fadeOut(function () {
                _activeWindowNoticeObject._jObject.remove();
                _activeIndex--;
                if (_activeIndex < 0) _activeIndex = _noticeIds.length - 1;
                showActiveNotice();
                showNextAndPrevButtonIfNecessary();
            });
        }

        /**
         * Add next and previous button if exist more than one window mode notice
         */
        function showNextAndPrevButtonIfNecessary() {
            if (_noticeIds.length > 1) {
                _windowBox.append(_windowBoxNextWindowButton);
                _windowBox.append(_windowBoxPreviousWindowButton);
                _windowBoxNextWindowButton.click(showNextNotice);
                _windowBoxPreviousWindowButton.click(showPreviousNotice)
            }
        }

        /**
         *
         */
        function hideNextAndPrevButton() {
            if (_noticeIds.length > 1) {
                _windowBoxNextWindowButton.remove();
                _windowBoxPreviousWindowButton.remove();
            }
        }

        /**
         *  kapat butonuna basıldığında bir daha gösterilsin mi uyarısı gösterir ve sonrasında gelen yanıta göre
         *  duyuruyu kapatır ve varsa sonraki duyuruyu gösterir
         */
        function showReadConfirmMessage() {
            _activeWindowNoticeObject._progress.stop(true, false);// fail display time Animation
            _activeWindowNoticeObject._jObject.fadeOut(function () {
                _activeWindowNoticeObject._jObject.remove();
                _tempIndex = _activeIndex; // set actve index to temp index
                _activeIndex = -2;//set Close Dialog
                showActiveNotice();
                hideNextAndPrevButton();
                $('#dontShowAgainNotice', closeDialog).click(handleClick_dontShowAgainNotice);
                $('#closeNotice', closeDialog).click(closeActiveWindow);
            });

        }

        /**
         *
         */
        function handleClick_dontShowAgainNotice() {
            toggleActiveIndexWithTempIndex();
            /*
             * Send mark as read request
             */
            $.post(ajaxData_GBWindow.ajaxurl, {
                noticeId: _activeWindowNoticeObject._postId,
                action: 'markAsReadNotice',
                security: ajaxData_GBWindow.securityFor_markAsReadNotice
            }, function (response) {
            });
            toggleActiveIndexWithTempIndex();
            closeActiveWindow();
        }

        /**
         *
         */
        function handleClick_backgroundClick() {
            if (!isBackgroundClicked) {
                _activeWindowNoticeObject._progress.stop(true, false);// fail display time Animation
                isBackgroundClicked = true;
                _activeWindowNoticeObject._jObject.fadeOut(function () {
                    _activeWindowNoticeObject._jObject.remove();
                    _activeIndex = -1;//set background Click message
                    showActiveNotice();
                    hideNextAndPrevButton();
                });
            }
        }

        /**
         * Close and delete notice in current session. And after show next Notice
         */
        function closeActiveWindow() {
            _activeWindowNoticeObject._jObject.fadeOut(function () {
                _activeWindowNoticeObject._jObject.remove();
                toggleActiveIndexWithTempIndex();
                _noticeIds.splice(_activeIndex, 1);
                if (_activeIndex > _noticeIds.length - 1) {
                    _activeIndex = 0;//set first Notice
                }
                showActiveNotice();
                showNextAndPrevButtonIfNecessary();
            });

        }

        /**
         *
         */
        function toggleActiveIndexWithTempIndex() {
            if (_tempIndex !== -1) {
                var temp = _activeIndex;
                _activeIndex = _tempIndex;
                _tempIndex = temp;
                setActiveNotice();
            }

        }

        /**
         * Remove all window mode Notices
         */
        function closeAllWindows() {
            _background.remove();
            _windowBox.remove();
            $('#wpadminbar').show();
        }

        /**
         *
         */
        function locateNotice() {
            var maxHeight = window.innerHeight - 20;
            var top = (window.innerHeight - _activeWindowNoticeObject._jObject.height()) / 2;
            top = top < 15 ? 15 : top;
            var left = (window.innerWidth - _activeWindowNoticeObject._jObject.width()) / 2; //todo width() ve height() fonksiyonları burada çalışıyorda window.class da neden çalışmıyor tekrar denenecek.
            var maxWidth = window.innerWidth - 115;
            _windowBox.css({'top': top, 'left': left, 'max-width': maxWidth, 'max-height': maxHeight});
            _activeWindowNoticeObject._jObject.find('.window-content').css({'max-height': maxHeight - _activeWindowNoticeObject._jObject.find('.window-footer').height()});
            if (_activeWindowNoticeObject._jObject.hasClass('noborder')) {
                _activeWindowNoticeObject._jObject.find('img').css({'max-height': maxHeight - _activeWindowNoticeObject._jObject.find('.window-footer').height()});
            }

        }

        /**
         *
         */
        function _run() {
            prepareForShow();
            showActiveNotice();
            /**
             * ekran yeniden boyutlandırıldığında duyuruyu sayfada yeniden konumlandırır
             */
            $(window).resize(locateNotice);
        }

        _run();
    };

})(jQuery);
