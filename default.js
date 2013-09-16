/**
 * Created with JetBrains PhpStorm.
 * User: sametatabasch
 * Date: 08.09.2013
 * Time: 16:35
 * To change this template use File | Settings | File Templates.
 */

jQuery(document).ready(function ($) {
    $('.fancybox').fancybox({margin: 0, padding: 0, autoCenter: true, autoResize: true, closeBtn: true, minHeight: 0});

    /* çarpıya  basınca  uyarıyı  ekrandan kaldırma işlemi */
    $('.close').click(function(){
        $(this).parent().remove();
    });
});