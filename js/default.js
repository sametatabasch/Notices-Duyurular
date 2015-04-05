//todo gözükme süresi
//todo arka plana tıklayınca uyarı ve kapatma #35
jQuery(document).ready(function ($) {
	$.fn.GBWindow = function (parameters) {
		var param = $.extend({
			'noticesClass': 'window'
		}, parameters);

		var notices = $('.' + param.noticesClass, this).hide();
		var activeIndex = 0;
		var widths = [];
		/**
		 * Duyuru kapatılmadan önce tekrar gösterilip gösterilmeyeceğinin belirelemek için gösterilecek mesaj
		 * @type {string}
		 */
		var isShowAgain = $('<div class="alert window alert-info" style="width: 100%">' +
		'<p>' + message.content + '</p>' +
		'<div id="closeButtons" class="center">' +
		'<button id="dontShow" class="btn">' + message.dontShow + '</button> - <button id="closeNotice" class="btn">' + message.close + '</button>' +
		'</div>' +
		'</div>');
		/**
		 * duyuruların ilk genişlik bilgilerini widths dizisine aktarıyorum
		 * bu sayede bir önceki duyurunun boyutlarından etkilenmeden kendi boyutlarında gösteriliyorlar
		 */
		notices.each(function (index, value) {
			setTimeout(function () {
				widths[index] = $(value).width();
				console.log($(value).width());
			}, 1);//güncel boyutların belirlenmesi için beklenen süre
		});
		/**
		 * bir mili saniye erteleme sonrası sayfa boyutlarına göre maksimum ve minimum boyutları belirler ve uygular
		 */
		function reLocate() {
			setTimeout(function () {
				var maxHeight = window.innerHeight - 80; //
				$('#windowBox .window *').css({'max-height': maxHeight})
				var top = (window.innerHeight - notices.eq(activeIndex).height()) / 2;
				var maxWidth = window.innerWidth - 115;
				$('#windowBox').css({'top': top, 'max-width': maxWidth});
				$('#windowBox .window').css({'max-width': maxWidth});
			}, 2);//güncel boyutların belirlenmesi için beklenen süre
		}

		/**
		 * windowBox id sine sahip nesnenin genişliğini duyurunun genişliğine ayarlayıp duyuruyu windowBox nesnesine ekler
		 * konumlandırır ve fade in animasyonu ile gösterir
		 */
		function showNotice() {
			$('#windowBox').width(widths[activeIndex]).append(notices.eq(activeIndex));
			reLocate();
			notices.eq(activeIndex).fadeIn();
		}

		/**
		 * body etiketi içine duyuruların gözükmesini sağlayan arka plan ekleniyor.
		 */
		$('body').append(
				'<div id="GBWindow">' +
				'<div class="windowBackground"></div>' +
				'<div id="windowBox">' +
				'</div>' +
				'</div>'
		);
		/**
		 * eğer birden fazla duyuru varsa ileri ve geri butonları ekleniyor
		 */
		if (notices.length > 1) {
			var previousButton = $('<a title="Previous" class="window-nav window-nav-previous" href="javascript:;"><span></span></a>');
			var nextButton = $('<a title="Next" class="window-nav window-nav-next" href="javascript:;"><span></span></a>');
			$('#windowBox').append(nextButton);
			$('#windowBox').append(previousButton);
			/**
			 * İleri butonuna tıklandığında aktif index numarasını bir artırarak sonraki duyuruyu gösterir
			 */
			nextButton.click(function () {
				notices.eq(activeIndex).fadeOut(function () {
					activeIndex++;
					if (activeIndex > notices.length - 1) activeIndex = 0;
					showNotice()
				});
			});
			/**
			 * Geri butonuna basıldığında aktif index numarasını bir azaltıp önceki duyuruyu gösterir
			 */
			previousButton.click(function () {
				notices.eq(activeIndex).fadeOut(function () {
					activeIndex--;
					if (activeIndex < 0) activeIndex = notices.length - 1;
					showNotice()
				});
			});

		}
		/**
		 *  kapat butonuna basıldığında bir daha gösterilsin mi uyarısı gösterir ve sonrasında gelen yanıta göre
		 *  duyuruyu kapatır ve varsa sonraki duyuruyu gösterir
		 */
		$('.close', this).click(function () {
			notices.eq(activeIndex).replaceWith(isShowAgain);
			$('#windowBox').width(350);
			reLocate();
			nextButton.hide();
			previousButton.hide();
			$('#closeButtons #dontShow').click(function () {
				var currentId = notices.eq(activeIndex).attr('id');
				var reg = /\d/g;
				currentId = currentId.match(reg).join(''); // sadece sayı kısmı alınıyor
				$.post('', {GB_D_noticeId: +currentId}, 'json'); //okundu olarak işaretleme yapılıyor
				close();
			});
			$('#closeButtons #closeNotice').click(function () {
				close();
			});
			/**
			 * duyuruyu kapatıp varsa sonraki duyuruyu gösterir
			 */
			function close() {
				notices.eq(activeIndex).remove();
				notices.splice(activeIndex, 1);// duyurulardan kapatılan duyuru kaldırılıyor
				widths.splice(activeIndex, 1);// duyuruların genişliklerinden kapatılan duyur kaldırılıyor.
				if (notices.length > 0) {
					if (notices.length == 1) {// eğer tek bir duyuru kaldıysa ileri ve geri butonları kaldırılıyor.
						nextButton.remove();
						previousButton.remove();
					} else {
						nextButton.show();
						previousButton.show();
					}

					activeIndex++;
					if (activeIndex > notices.length - 1) activeIndex = 0;
					isShowAgain.remove();
					showNotice()

				} else {//eğer duyuru kalmadıysa duyuru penceresi kapatılıyor.
					$('#GBWindow').remove();
				}
			}
		});
		/**
		 * ekran yeniden boyutlandırıldığında duyuruyu sayfada yeniden konumlandırır
		 */
		$(window).resize(function () {
			reLocate();
		});

		showNotice();
	};
});

jQuery(document).ready(function () {
	//adminbar yüksekiliği notice container e aktarılıyor
	jQuery('.noticeContainer').css({'top': jQuery('#wpadminbar').height()});

	jQuery('.bar .close').click(function () {
		//Aktif duyurunun id bilgisi  alınıyor
		var currentId = jQuery(this).parent()[0].id;

		var reg = /\d/g;
		currentId = currentId.match(reg).join(''); //id  değerinin sadece sayı olduğu doğrulanıyor.

		// çoklu  dil desteği için message nesnesi kullanılıyor ilgili fonksiyon: GB_D_addScriptAndStyle
		var icerik =
				'<div class="bar alert alert-info">' +
						'<h4></h4>' +
						'<p>' + message.content + '</p>' +
						'<button id="yes" class="btn">' + message.dontShow + '</button> - <button id="no" class="btn">' + message.close + '</button>' +
				'</div>';

		jQuery('.noticeContainer').find('#bar-' + currentId).replaceWith(icerik);

		jQuery('#yes').click(function () {
			jQuery.ajax({
				type: "GET",
				data: "GB_D_noticeId=" + currentId
			});
			close(jQuery(this).parent());
		});

		jQuery('#no').click(function () {
			close(jQuery(this).parent());
		});
	});
});
