$ = jQuery; //$ kullanabilmek için jQuery ataması

/**
 * pencere modundaki duyuruları gösterecek fonksiyon
 */
function showWindowType() {
	var notices = $.makeArray($('.window'));// window class ına sahip nesneleri diziye çevirip notices değişkenine atadık
	$('.window').remove();//window class ına sahip nesneleri sayfadan temizledik
	$('body').append('<div id="windowBackground"><div class="windowBackground"></div></div>');//body etiketine window tipli duyuruların gözükmesi için arkaplan div i ekledik
	$('#windowBackground').fadeIn();
	$('#windowBackground .windowBackground').click(function () {
		$(this).parent().fadeOut('slow', function () {
			$(this).remove()
		});
	});//arka plana tıklayınca silinsin
	$('#windowBackground').append('<div id="windowBox" class=""></div>');//window class lı nesnenin ekleneceği div eklendi
	var i = 0;
	$('#windowBox').append(notices[0]);//ilk duyuru windowBox id li  div içine eklendi
	if (notices.length > 1) {
		$('#windowBox').append('<a href="javascript:;" class="window-nav window-nav-previous" title="Previous"><span></span></a>');
		$('#windowBox').append('<a href="javascript:;" class="window-nav window-nav-next" title="Next"><span></span></a>');
		$('.window-nav-previous').click(function () {
			i--;
			if (i < 0) i = (notices.length - 1);
			$('#windowBox .window').fadeOut(function () {
				$(this).css({'display': 'block'});
				$(this).replaceWith(notices[i])
			});
		});
		$('.window-nav-next').click(function () {
			i++;
			if (i > (notices.length - 1)) i = 0;
			//$('#windowBox .window').replaceWith(notices[i]);
			$('#windowBox .window').fadeOut(function () {
				$(this).css({'display': 'block'});
				$(this).replaceWith(notices[i])
			});
		});
	}
	reLocate();
}

/**
 * sayfadaki  konumu  yeniden  düzenler
 */
function reLocate() {
	var windowBoxWidth = $('#windowBox').width();
	var windowBoxHeight = $('#windowBox').height();
	var windowBoxLeft = (window.innerWidth - windowBoxWidth) / 2;
	var windowBoxTop = (window.innerHeight - windowBoxHeight) / 2;
	$('#windowBox').css({
		'left': windowBoxLeft,
		'top' : windowBoxTop
	});
}

$(document).ready(function ($) {
	/* çarpıya  basınca  uyarıyı  ekrandan kaldırma işlemi */
	$('.close').click(function () {
		$(this).parent().remove();
	});
	/*fancy box yerine kullanılacak fonksiyon */
	showWindowType();
});

$(window).resize(function () {
	reLocate();
});