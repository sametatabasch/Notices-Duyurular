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
        var _tempIndex = 0;

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
                _background.removeClass('notice-white notice-red notice-green notice-blue'); // remove old color class
                _background.addClass(_activeWindowNoticeObject._color);
                _windowBox.removeClass('xLarge large medium small');
                _windowBox.addClass(_activeWindowNoticeObject._size );
                $('#wpadminbar').hide();
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
                        handleDisplayTime();
                    });
            } else console.log('there is no notice');
        }

        function handleDisplayTime() {
            if (false !== _activeWindowNoticeObject._displayTime) {
                _activeWindowNoticeObject._progress.animate(
                    {value: 0},
                    {
                        duration: _activeWindowNoticeObject._displayTime * 1000,
                        done: function () {
                            if (_activeWindowNoticeJqueryObject.attr('id') === 'backgroundClickMessage') {
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
            _activeWindowNoticeObject._progress.stop(true, false);// fail display time Animation
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
            _activeWindowNoticeObject._progress.stop(true, false);// fail display time Animation
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
                _activeWindowNoticeJqueryObject.fadeOut(function () {
                    _activeWindowNoticeJqueryObject.remove();
                    _activeIndex = 0;//set background Click message
                    showActiveNotice();
                    hideNextAndPrevButton();
                });
            }
        }

        /**
         * Close and delete notice in current session. And after show next Notice
         */
        function closeActiveWindow() {
            _activeWindowNoticeJqueryObject.fadeOut(function () {
                _activeWindowNoticeJqueryObject.remove();
                toggleActiveIndexWithTempIndex();
                _windowObjects.splice(_activeIndex, 1);
                if (_activeIndex > _windowObjects.length - 1) {
                    _activeIndex = 2;//set first Notice
                }
                showActiveNotice();
                showNextAndPrevButtonIfNecessary();
            });

        }

        /**
         *
         */
        function toggleActiveIndexWithTempIndex() {
            if (_tempIndex !== 0) {
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
            // delete all window mode notice from notice container
            _windowObjects.forEach(function (active, index, allObject) {
                active._jObject.remove();
            });
            _windowBox.remove();
            $('#wpadminbar').show();
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
            _activeWindowNoticeJqueryObject.css({'max-height': maxHeight - _activeWindowNoticeJqueryObject.find('.window-footer').height()});
            if (_activeWindowNoticeJqueryObject.hasClass('noborder')) {
                _activeWindowNoticeJqueryObject.find('img').css({'max-height': maxHeight - _activeWindowNoticeJqueryObject.find('.window-footer').height()});
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
