/**
 * Created by sametatabasch on 12.06.2017.
 */
(function ($) {
    window.GB_D_noticeWindow = function (windowObject) {
        /**
         * instance
         * @type {GB_D_noticeWindow}
         * @private
         */
        var _this = this;

        /**
         * show time of Notice window default 10 second
         * @var integer second
         */
        this._showTime = 10;

        /**
         * HTML output of Notice window created by Notice->setHtml() php class
         * @var string
         */
        this._jObject = '';

        /**
         * window mode notice size cilass data
         * @type {string}
         */
        this._sizeClass = 'medium';

        /**
         * close button of window mode notice
         * @type {string|jQuery}
         * @private
         */
        this._closeButton = '';

        /**
         * Notice post id number
         * @type {number}
         * @private
         */
        this._postId = 0;

        /**
         *
         * @private
         */
        this._construct = function () {
            _this._jObject = windowObject;
            _this._sizeClass = _this._jObject.attr('data-size');
            setPostId();
        };

        function setPostId() {
            var currentId = _this._jObject.attr('id');
            var reg = /(\d)+/g;
            if (!(typeof currentId === 'undefined')) {
                if (!(null === currentId.match(reg))) {
                    _this._postId = currentId.match(reg)[0]; // get only number
                    console.log(_this._postId);
                }
            }


        }

        _this._construct();
    };

})(jQuery);

