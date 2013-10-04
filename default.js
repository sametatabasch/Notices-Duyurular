$ = jQuery; //$ kullanabilmek için jQuery ataması

/**
 * pencere modundaki duyuruları gösterecek fonksiyon
 */
function showWindowType() {
	$('body').append('<div id="windowBackground"></div>')
	$('#windowBackground').click(function () {
		$(this).remove()
	});//arka plana tıklayınca silinsin
	$('#windowBackground').append('<div id="windowBox" class=""></div>')

	$('.window').each(function () {
		$('#windowBox').append($(this));
	});
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