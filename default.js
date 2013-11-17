/**
 * pencere modundaki duyuruları gösterecek fonksiyon
 */
var Window = function (content, isClass) {
	getContent = function () {
		isClass ? content = jQuery.makeArray(jQuery(content)) : content = content;
		console.log(content);
		if (isClass) jQuery(content).remove();//window class ına sahip nesneleri sayfadan temizledik
	}
	/**
	 * sayfadaki  konumu  yeniden  düzenler
	 */
	reLocate = function () {
		jQuery('.window').css({'max-height': (window.innerHeight / 2), 'max-width': (window.innerWidth / 2)});
		var windowBoxWidth = jQuery('#windowBox').width();
		var windowBoxHeight = jQuery('#windowBox').height();
		var windowBoxLeft = (window.innerWidth - windowBoxWidth) / 2;
		var windowBoxTop = (window.innerHeight - windowBoxHeight) / 2;
		jQuery('#windowBox').css({
			'left': windowBoxLeft,
			'top' : windowBoxTop
		});
	}
	this.Locate = function () {
		reLocate()
	};
	/**
	 * pencereyi ekranda gösterir
	 */
	this.show = function () {
		getContent();
		jQuery('body').append('<div id="windowBackground"><div class="windowBackground"></div></div>');
		jQuery('#windowBackground').append('<div id="windowBox" class=""></div>');//window class lı nesnenin ekleneceği div eklendi
		var i = 0;
		var max = content.length - 1;
		jQuery('#windowBox').append(content[0]);//ilk içerik windowBox id li  div içine eklendi
		if (content.length > 1) {
			jQuery('#windowBox').append('<a href="javascript:;" class="window-nav window-nav-previous" title="Previous"><span></span></a>');
			jQuery('#windowBox').append('<a href="javascript:;" class="window-nav window-nav-next" title="Next"><span></span></a>');
			jQuery('.window-nav-previous').click(function () {
				i--;
				if (i < 0) i = max;
				jQuery('#windowBox .window').fadeOut(function () {
					jQuery(this).css({'display': 'block'});
					jQuery(this).replaceWith(content[i]);
					reLocate();
				});
			});
			jQuery('.window-nav-next').click(function () {
				i++;
				if (i > max) i = 0;
				jQuery('#windowBox .window').fadeOut(function () {
					jQuery(this).css({'display': 'block'});
					jQuery(this).replaceWith(content[i]);
					reLocate();
				});
			});
		}
		reLocate();
	}

	/**
	 *
	 */
	close = function (obj) {
		if (undefined == !obj) {
			console.log(obj);
			obj.fadeOut('slow', function () {
				jQuery(this).remove();
			});
		}
		;
	};
	jQuery('#windowBackground .windowBackground').click(close());//arka plana tıklayınca silinsin
	jQuery('.close').click(close(jQuery('#windowBackground')));
}

var duyuruWindow = new Window(".window", true);
jQuery(document).ready(function () {
	/* çarpıya  basınca  uyarıyı  ekrandan kaldırma işlemi */
	/*jQuery('.close').click(function () {
	 jQuery(this).parent().fadeOut(function () {
	 jQuery(this).remove()
	 });
	 });*/
	jQuery('.noticeContainer').css({'top': jQuery('#wpadminbar').height()});//adminbar yüksekiliği notice container e aktarılıyor
	duyuruWindow.show();

});

jQuery(window).resize(function () {
	duyuruWindow.Locate();
});