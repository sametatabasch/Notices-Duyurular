/**
 * Created with JetBrains PhpStorm.
 * User: sametatabasch
 * Date: 08.09.2013
 * Time: 16:35
 * To change this template use File | Settings | File Templates.
 */

jQuery(document).ready(function ($) {
    $('.fancybox').fancybox({padding: 0, maxWidth: 960});

    /* çarpıya  basınca  uyarıyı  ekrandan kaldırma işlemi */
    $('.close').click(function () {
        $(this).parent().remove();
    });
});