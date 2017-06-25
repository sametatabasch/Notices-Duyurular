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
         * @type {number}
         */
        var _activeIndex = 2;

        /**
         * Store previous notice index number. Because closeDialog and readConfirm message show with _activeIndex
         * @type {number}
         * @private
         */
        var _tempIndex = 2;

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
        var _windowBox = $('<div id="windowBox"></div>');

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
            '<div id="closeDialog" class="window" data-size="medium">' +
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
            '<div class="window" data-size="small">' +
            '<div class="window-content">' +
            '<div>' +
            '<p>' + noticeLocalizeMessage.backgroundClickMessage + '</p>' +
            '</div>' +
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
         * Store all window mode Notice
         */
        var _notices = $('.' + _param.noticesClass, this);

        /**
         * store all window object
         *
         * @type {Array}
         */
        var _windowObjects = [];

        /**
         * currently showing notice jQuery Object
         * @type {*}
         * @private
         */
        var _activeWindowNoticeJqueryObject;

        /**
         * currently showing notice Object
         * @type {*}
         * @private
         */
        var _activeWindowNoticeObject;

        /**
         *
         */
        function prepareForShow() {
            _htmlBody.append(_background);
            _htmlBody.append(_windowBox);
            setWindowObjects();
            showNextAndPrevButtonIfNecessary();

            _background.click(handleClick_backgroundClick);
        }

        /**
         * push window mode notices to windowObjects
         */
        function setWindowObjects() {
            console.log('GBWindow setWindowObjects');
            /**
             * @type {Window.GB_D_noticeWindow}
             */
            var noticeWindow;
            /*
             * Add Background click message to _windowObjects[0]
             */
            noticeWindow = new GB_D_noticeWindow(backgroundClickMessage);
            _windowObjects.push(noticeWindow);
            /*
             * Add close dialog to _windowObjects[1]
             */
            noticeWindow = new GB_D_noticeWindow(closeDialog);
            _windowObjects.push(noticeWindow);
            /*
             * Add Notices to _windowObjects
             */
            _notices.each(function (index, value) {
                noticeWindow = new GB_D_noticeWindow($(this));
                _windowObjects.push(noticeWindow);
            });
        }

        /**
         *
         * @returns {boolean}
         */
        function setActiveNotice() {
            console.log('SetActiveNotice. Active index=' + _activeIndex + '. Temp İndex=' + _tempIndex);
            if (_windowObjects.length > 2) {
                _activeWindowNoticeObject = _windowObjects[_activeIndex];
                _activeWindowNoticeJqueryObject = _windowObjects[_activeIndex]._jObject;
                return true;
            } else {
                closeAllWindows();
                return false;
            }
        }

        /**
         *
         */
        function showActiveNotice() {
            if (setActiveNotice()) {
                console.log('ShowActiveNotice. Active index=' + _activeIndex + '. Temp İndex=' + _tempIndex);
                console.log(_activeWindowNoticeJqueryObject);
                _windowBox.removeClass();
                _windowBox.addClass(_activeWindowNoticeObject._sizeClass);
                _windowBox.append(_activeWindowNoticeJqueryObject).imagesLoaded()
                    .progress(function () {
                        $('#windowBox').append(_loadingAnimation);
                    })
                    .done(function () {
                        _loadingAnimation.remove();
                        _activeWindowNoticeJqueryObject.fadeIn();
                        locateNotice();
                        /**
                         * add click event listenner to window close button
                         */
                        _activeWindowNoticeJqueryObject.find('.close').click(showReadConfirmMessage);
                    });
            } else console.log('there is no notice');
        }

        /**
         *
         */
        function showNextNotice() {
            _activeWindowNoticeJqueryObject.fadeOut(function () {
                _activeWindowNoticeJqueryObject.remove();
                _activeIndex++;
                if (_activeIndex > _windowObjects.length - 1) _activeIndex = 2;// set firs notice
                showActiveNotice();
                showNextAndPrevButtonIfNecessary();
            });
        }

        /**
         *
         */
        function showPreviousNotice() {
            _activeWindowNoticeJqueryObject.fadeOut(function () {
                _activeWindowNoticeJqueryObject.remove();
                _activeIndex--;
                if (_activeIndex < 2) _activeIndex = _windowObjects.length - 1;
                showActiveNotice();
                showNextAndPrevButtonIfNecessary();
            });
        }

        /**
         * Add next and previous button if exist more than one window mode notice
         */
        function showNextAndPrevButtonIfNecessary() {
            if (_windowObjects.length > 3) {
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
            if (_windowObjects.length > 3) {
                _windowBoxNextWindowButton.remove();
                _windowBoxPreviousWindowButton.remove();
            }
        }

        /**
         *  kapat butonuna basıldığında bir daha gösterilsin mi uyarısı gösterir ve sonrasında gelen yanıta göre
         *  duyuruyu kapatır ve varsa sonraki duyuruyu gösterir
         */
        function showReadConfirmMessage() {
            console.log('GBWindow showReadConfirmMessage ');
            _activeWindowNoticeJqueryObject.fadeOut(function () {
                _activeWindowNoticeJqueryObject.remove();
                _tempIndex = _activeIndex; // set actve index to temp index
                _activeIndex = 1;//set Close Dialog
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
                console.log(response);
            });
            toggleActiveIndexWithTempIndex();
            closeActiveWindow();
        }

        /**
         *
         */
        function handleClick_backgroundClick() {
            if (!isBackgroundClicked) {
                isBackgroundClicked = true;
                _activeWindowNoticeJqueryObject.fadeOut(function () {
                    _activeWindowNoticeJqueryObject.remove();
                    _activeIndex = 0;//set background Click message
                    showActiveNotice();
                    hideNextAndPrevButton();
                });
                setTimeout(function () {
                    closeAllWindows();
                }, 5500);
            }
        }

        /**
         * Close and delete notice in current session. And after show next Notice
         */
        function closeActiveWindow() {
            _activeWindowNoticeJqueryObject.fadeOut(function () {
                _activeWindowNoticeJqueryObject.remove();
                _windowObjects.splice(_tempIndex, 1);
                _activeIndex = _tempIndex++;// set active index temp index +1 which came after last showed notice
                if (_activeIndex > _windowObjects.length - 1) _activeIndex = 2;//set first Notice
                showActiveNotice();
                showNextAndPrevButtonIfNecessary();
            });

        }

        /**
         *
         */
        function toggleActiveIndexWithTempIndex() {
            var temp = _activeIndex;
            _activeIndex = _tempIndex;
            _tempIndex = temp;
            setActiveNotice();
        }

        /**
         * Remove all window mode Notices
         */
        function closeAllWindows() {
            _background.remove();
            _windowBox.remove();
        }

        /**
         *
         */
        function locateNotice() {
            var maxHeight = window.innerHeight - 20;
            var top = (window.innerHeight - _activeWindowNoticeJqueryObject.height()) / 2;
            top = top < 15 ? 15 : top;
            var left = (window.innerWidth - _activeWindowNoticeJqueryObject.width()) / 2; //todo width() ve height() fonksiyonları burada çalışıyorda window.class da neden çalışmıyor tekrar denenecek.
            var maxWidth = window.innerWidth - 115;
            _windowBox.css({'top': top, 'left': left, 'max-width': maxWidth, 'max-height': maxHeight});
            _activeWindowNoticeJqueryObject.css({'max-height': maxHeight});
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
