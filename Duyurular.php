<?php
//todo eklenti urlsi olarak gençbilişimde yazdığım yazının linki olacak
//todo Multi  site için uyumlu  hale gelecek #14
//todo Admin panelde  gözükmesi sağlanacak check box ile denetlenebilir.
//todo * duyuru  silindiği zaman  okundu  bilgileride  silinsin.
/*
    Plugin Name: Duyurular
    Plugin URI: http://www.gençbilişim.net
    Description: Gençbilişim Duyurular
    Author: Samet ATABAŞ
    Version: 1.0
    Author URI: http://www.gençbilişim.net
*/

class GB_Duyurular
{
    public $path;

    public $pathUrl;

    public $duyuruContent = '<div class="barContainer">';

    public function __construct()
    {
        $this->path = plugin_dir_path(__FILE__);
        $this->pathUrl = plugin_dir_url(__FILE__);
        add_action('init', array(&$this, 'GB_D_postTypeEkle'));
        add_action('add_meta_boxes', array(&$this, 'GB_D_metaBoxEkle'));
        add_action('save_post', array(&$this, 'GB_D_duyuruKaydet'));
        add_action('edit_post', array(&$this, 'GB_D_duyuruDuzenle'));
        add_action('wp_trash_post', array(&$this, 'GB_D_duyuruCopeTasi'));
        add_action('wp_footer', array(&$this, 'GB_D_duyuruGoster'));
        add_action('wp_enqueue_scripts', array(&$this, 'GB_D_addScriptAndStyle'));
        add_action('admin_enqueue_scripts', array(&$this, 'GB_D_addStyleToAdminPage'));
        add_action('template_redirect', array(&$this, 'GB_D_okunduIsaretle'));
        //add_action('init', array(&$this, 'GB_D_getDuyuru'));
    }

    /**
     * init action a Duyurular için yeni  post type ın  özelliklerini belirler.
     * add_action('init', array(&$this, 'GB_D_postTypeEkle'));
     */
    public function GB_D_postTypeEkle()
    {
        register_post_type('Duyuru',
            array(
                'labels' => array( /*labels kullanılan başlıkları belirlemeye yarıyor*/
                    'name' => 'Duyuru',
                    'singular_name' => 'Duyuru',
                    /*'add_new' => _x('Add New', 'book'), çoklu  dil  için örnek*/
                    'add_new' => 'Yeni Duyuru',
                    'add_new_item' => 'Yeni Duyuru Ekle',
                    'edit_item' => 'Duyuruyu Düzenle',
                    'new_item' => 'Yeni Duyuru',
                    'all_items' => 'Tüm Duyurular',
                    'view_item' => 'Duyuruyu Göster',
                    'search_items' => 'Duyuru Ara',
                    'not_found' => 'Duyuru Bulunamadı',
                    'not_found_in_trash' => 'Silinen Duyuru Yok',
                    'parent_item_colon' => '',
                    'menu_name' => 'Duyurular'
                ),
                'public' => false,
                'has_archive' => true,
                'show_ui' => true,
                'show_in_menu' => true,
            )
        );
    }

    /**
     * Duyuru meta box ekler
     * Duyuru oluşturma ve düzenleme sayfasına ayarlamalar için widget içeriği
     * add_action('add_meta_boxes', array(&$this, 'GB_D_metaBoxEkle'));
     */
    public function GB_D_metaBoxEkle()
    { //todo * duyuru son gösrerim tarihi  duyurunun yazıldığı tarihten bir ay sonra olarak belirlensin(öntanımlı) veya duyuru  yGB_D_ayınlanırken  son okuma tarihinin  şimdiki  zaman olmaması kontrol  edilsin .
        //todo #5
        function duyuruMetaBox()
        {
            global $post_id, $wp_locale;
            $GB_D_kimlerGorsun = get_post_meta($post_id, "GB_D_kimlerGorsun", 1);
            $GB_D_gosterimModu = get_post_meta($post_id, "GB_D_gosterimModu", 1);
            $GB_D_sonGosterimTarihi = get_post_meta($post_id, 'GB_D_sonGosterimTarihi', 1);
            $GB_D_tasarim = get_post_meta($post_id, 'GB_D_tasarim', true);
            empty($GB_D_sonGosterimTarihi) ? $date = GB_Duyurular::GB_D_getDate() : $date = GB_Duyurular::GB_D_getDate($GB_D_sonGosterimTarihi);
            $out = '
            <form>
                <div class="misc-pub-section">
                    <span><b>Kimler görsün:</b></span>
                    <select name="GB_D_kimlerGorsun">
                        <option ';
            if ($GB_D_kimlerGorsun == 'herkes') {
                $out .= 'selected=""';
            }
            $out .= ' value="herkes">Herkes</option>
                        <option ';
            if ($GB_D_kimlerGorsun == 'uyeler') {
                $out .= 'selected=""';
            }
            $out .= ' value="uyeler">Sadece Üyeler
            </option>
            </select>
            </div>
            <div class="misc-pub-section">
                <span><b>Gösterim Modu:</b></span>
                <select name="GB_D_gosterimModu">
                    <option ';
            if ($GB_D_gosterimModu == 'pencere') {
                $out .= 'selected=""';
            }
            $out .= ' value="pencere">Pencere
                    </option>
                    <option ';
            if ($GB_D_gosterimModu == 'bar') {
                $out .= 'selected=""';
            }
            $out .= ' value="bar">Uyarı Şeridi
                    </option>
                </select>
            </div>
            <div class="clear"></div>
            <div class="misc-pub-section curtime">
                <span id="timestamp">
                    <b>Son Gösterim Tarihi</b>
                </span><br/>
                <input type="text" maxlength="2" size="2" value="' . $date["GB_D_gun"] . '" name="GB_D_gun" id="jj">.
                <select name="GB_D_ay" id="mm">';
            $x = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12',); //get_date_from_gtm fonkisiyonun da 1 yerine 01 olması gerekiyor

            for ($i = 0; $i < 12; $i++) {
                $out .= '<option ';
                if ($x[$i] == $date['GB_D_ay']) $out .= 'selected="selected"';
                $out .= ' value="' . $x[$i] . '">' . $x[$i] . '-' . $wp_locale->get_month_abbrev($wp_locale->get_month($x[$i])) . '</option>';
            }
            $out .= '
                </select>.
                <input type="text" maxlength="4" size="4" value="' . $date["GB_D_yil"] . '" name="GB_D_yil" id="aa">@<input type="text" maxlength="2" size="2" value="' . $date["GB_D_saat"] . '" name="GB_D_saat" id="hh">:<input type="text" maxlength="2" size="2" value="' . $date["GB_D_dakika"] . '" name="GB_D_dakika" id="mn">
            </div>';
            $out .= '
            <div class="misc-pub-section misc-pub-section-last"xmlns="http://www.w3.org/1999/html">
                <span>
                <b>Tasarım:</b>
                </span>
                <div class="alert"><input type="radio" ';
            if ($GB_D_tasarim == "") {
                $out .= 'checked';
            }
            $out .= ' name="GB_D_tasarim" value="">Öntanımlı</div>
                <div class="alert alert-error"><input type="radio" ';
            if ($GB_D_tasarim == "alert-error") {
                $out .= 'checked';
            }
            $out .= ' name="GB_D_tasarim" value="alert-error">Hata</div>
                <div class="alert alert-info"><input type="radio" ';
            if ($GB_D_tasarim == "alert-info") {
                $out .= 'checked';
            }
            $out .= ' name="GB_D_tasarim" value="alert-info">Bilgi</div>
                <div class="alert alert-success"><input type="radio" ';
            if ($GB_D_tasarim == "alert-success") {
                $out .= 'checked';
            }
            $out .= ' name="GB_D_tasarim" value="alert-success">Başarı</div>

                <div class="clear"></div>
            </div>
            </form>';
            echo $out;
        }

        add_meta_box('GB_duyuruMetaBox', 'Duyuru ayarları', 'duyuruMetaBox', 'Duyuru', 'side', 'default');
    }

    /**
     * Duyuru  Meta box  içeriğindeki verileri alıp  işleyerek  duyuruyo oluştururken ek işlenmleri yapacak
     * Post ile verileri alacak
     *
     *  add_action('save_post', array(&$this, 'GB_D_duyuruKaydet'));
     */
    public function GB_D_duyuruKaydet()
    {
        global $post_id;
        @$GB_D_kimlerGorsun = $_POST["GB_D_kimlerGorsun"];
        @$GB_D_gosterimModu = $_POST["GB_D_gosterimModu"];
        @$GB_D_tasarim = $_POST['GB_D_tasarim'];
        @$GB_D_gun = $_POST['GB_D_gun'];
        @$GB_D_ay = $_POST['GB_D_ay'];
        @$GB_D_yil = $_POST['GB_D_yil'];
        @$GB_D_saat = $_POST['GB_D_saat'];
        @$GB_D_dakika = $_POST['GB_D_dakika'];
        @$GB_D_sonGosterimTarihi = $GB_D_yil . '-' . $GB_D_ay . '-' . $GB_D_gun . ' ' . $GB_D_saat . ':' . $GB_D_dakika . ':00';
        add_post_meta($post_id, "GB_D_kimlerGorsun", $GB_D_kimlerGorsun, true);
        add_post_meta($post_id, "GB_D_gosterimModu", $GB_D_gosterimModu, true);
        add_post_meta($post_id, 'GB_D_tasarim', $GB_D_tasarim, true);
        add_post_meta($post_id, "GB_D_sonGosterimTarihi", $GB_D_sonGosterimTarihi, true);
    }

    /**
     * duyuru  güncellendiği zaman yapılacak  olan düzenlemeler bu  fonksiyonile yapılıyor
     *
     * add_action('edit_post', array(&$this, 'GB_D_duyuruDuzenle'));
     */
    public function GB_D_duyuruDuzenle()
    {
        global $post_id;
        @$GB_D_kimlerGorsun = $_POST["GB_D_kimlerGorsun"];
        @$GB_D_gosterimModu = $_POST["GB_D_gosterimModu"];
        @$GB_D_tasarim = $_POST['GB_D_tasarim'];
        @$GB_D_gun = $_POST['GB_D_gun'];
        @$GB_D_ay = $_POST['GB_D_ay'];
        @$GB_D_yil = $_POST['GB_D_yil'];
        @$GB_D_saat = $_POST['GB_D_saat'];
        @$GB_D_dakika = $_POST['GB_D_dakika'];
        @$GB_D_sonGosterimTarihi = $GB_D_yil . '-' . $GB_D_ay . '-' . $GB_D_gun . ' ' . $GB_D_saat . ':' . $GB_D_dakika . ':00';
        update_post_meta($post_id, "GB_D_kimlerGorsun", $GB_D_kimlerGorsun);
        update_post_meta($post_id, "GB_D_gosterimModu", $GB_D_gosterimModu);
        update_post_meta($post_id, 'GB_D_tasarim', $GB_D_tasarim);
        update_post_meta($post_id, "GB_D_sonGosterimTarihi", $GB_D_sonGosterimTarihi);
    }

    /**
     *
     * add_action('wp_trash_post', array(&$this, 'GB_D_duyuruCopeTasi'));
     */
    public function GB_D_duyuruCopeTasi()
    {
        global $post_id;
        $this->GB_D_okunduIsaretiniKaldir($post_id);

    }

    /**
     * Duyuru bilgilerini  array olarak  getirir
     * array(7) {
     *  ["ID"]=>
     *  ["post_date_gmt"]=>
     *  ["post_content"]=>
     *  ["post_title"]=>
     *  ["GB_D_kimlerGorsun"]=>
     *  ["GB_D_gosterimModu"]=>
     *  ["GB_D_sonGosterimTarihi"]=>
     *  ["GB_D_tasarim"]=>
     *}
     *
     * @return array
     */
    public function GB_D_getDuyuru()
    {
        global $wpdb;
        $duyurular = $wpdb->get_results("SELECT ID,post_date_gmt,post_content,post_title FROM $wpdb->posts WHERE post_type='duyuru' AND post_status='publish' ORDER BY ID DESC", ARRAY_A);
        $out = array();
        foreach ($duyurular as $duyuru) {
            $duyuru['GB_D_kimlerGorsun'] = get_post_meta($duyuru['ID'], 'GB_D_kimlerGorsun', true);
            $duyuru['GB_D_gosterimModu'] = get_post_meta($duyuru['ID'], 'GB_D_gosterimModu', true);
            $duyuru['GB_D_sonGosterimTarihi'] = get_post_meta($duyuru['ID'], 'GB_D_sonGosterimTarihi', true);
            $duyuru['GB_D_tasarim'] = get_post_meta($duyuru['ID'], 'GB_D_tasarim', true);
            $out[] = $duyuru;
        }
        //echo '<pre>';print_r($out);echo '</pre>';
        return $out;
    }

    /**
     * Uygun duyuruları sayfaya basar
     *  add_action('wp_footer', array(&$this, 'GB_D_duyuruGoster'));
     */
    public function GB_D_duyuruGoster()
    {
        foreach ($this->GB_D_getDuyuru() as $duyuru):
            if ($duyuru['GB_D_sonGosterimTarihi'] < date_i18n('Y-m-d H:i:s')) { // Son gösterim tarihi geçen duyuru çöpe taşınır
                $duyuru['post_status'] = 'trash';
                wp_update_post($duyuru);
                continue;
            }
            if ($this->GB_D_duyuruOkundumu($duyuru['ID'])) continue;
            switch ($duyuru['GB_D_gosterimModu']) {
                case 'pencere':
                    if ($duyuru['GB_D_kimlerGorsun'] == 'herkes') {
                        $this->duyuruContent .= '<script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            $("#duyuruLink").trigger("click");
                        });</script>';
                        $this->duyuruContent .= '
                        <div id="fancy-' . $duyuru['ID'] . '" class="alert ' . $duyuru['GB_D_tasarim'] . '" style="displGB_D_ay:none;">
                                <h4>' . ucfirst(get_the_title($duyuru["ID"])) . '</h4>
                                ' . do_shortcode(wpautop($duyuru['post_content'])) . '
                                <p class="okundu"><a href="?GB_D_duyuruId=' . $duyuru["ID"] . '">Okundu</a></p>
                        </div>
                        <a href="#fancy-' . $duyuru['ID'] . '" id="duyuruLink" class="fancybox" style="displGB_D_ay:none;"> </a>';

                    } else {
                        if (is_user_logged_in()) {
                            $this->duyuruContent .= '<script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            $("#duyuruLink").trigger("click");
                        });</script>';
                            $this->duyuruContent .= '
                        <div id="fancy-' . $duyuru['ID'] . '" class="alert ' . $duyuru['GB_D_tasarim'] . '" style="displGB_D_ay:none;">
                                <h4>' . ucfirst(get_the_title($duyuru["ID"])) . '</h4>
                                ' . do_shortcode(wpautop($duyuru['post_content'])) . '
                                <p class="okundu"><a href="?GB_D_duyuruId=' . $duyuru["ID"] . '">Okundu</a></p>
                        </div>
                        <a href="#fancy-' . $duyuru['ID'] . '" id="duyuruLink" class="fancybox" style="displGB_D_ay:none;"> sdff</a>';
                        }
                    }

                    break;
                case 'bar':
                    if ($duyuru['GB_D_kimlerGorsun'] == 'herkes') {
                        $this->duyuruContent .= '
                            <div id="bar-' . $duyuru['ID'] . '" class="bar alert ' . $duyuru['GB_D_tasarim'] . '">
                                <button type="button" class="close" >&times;</button>
                                <h4>' . ucfirst(get_the_title($duyuru["ID"])) . '</h4>
                                ' . do_shortcode(wpautop($duyuru['post_content'])) . '
                                <p class="okundu"><a href="?GB_D_duyuruId=' . $duyuru["ID"] . '">Okundu</a></p>
                            </div>';
                    } else {
                        if (is_user_logged_in()) {
                            $this->duyuruContent .= '
                            <div id="bar-' . $duyuru['ID'] . '" class="bar alert ' . $duyuru['GB_D_tasarim'] . '">
                                <button type="button" class="close">&times;</button>
                                <h4>' . ucfirst(get_the_title($duyuru["ID"])) . '</h4>
                                ' . do_shortcode(wpautop($duyuru['post_content'])) . '
                                <p class="okundu"><a href="?GB_D_duyuruId=' . $duyuru["ID"] . '">Okundu</a></p>
                            </div>';
                        }
                    }
                    break;
            }
        endforeach;
        $this->GB_D_duyuruContent();
    }

    /**
     *
     * @param bool $echo
     * @return string
     */
    public function GB_D_duyuruContent($echo = true)
    {
        $this->duyuruContent .= '</div>';
        if ($echo) {
            echo $this->duyuruContent;
        } else {
            return $this->duyuruContent;
        }
    }

    /**
     * style ve script dosyalarını  yükler
     * add_action('wp_enqueue_scripts', array(&$this, 'GB_D_addScriptAndStyle'));
     */
    public function  GB_D_addScriptAndStyle()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('fancybox', plugins_url('/fancybox/source/jquery.fancybox.js?v=2.1.5', __FILE__), array('jquery'));
        wp_enqueue_style('fancybox_style', plugins_url('/fancybox/source/jquery.fancybox.css?v=2.1.5', __FILE__));
        wp_enqueue_style('duyuru_style', plugins_url('style.css', __FILE__));
        wp_enqueue_script('duyuru', plugins_url('default.js', __FILE__), array('jquery'));
    }

    /**
     * Admin paneline style dosyasını ekler
     * add_action('admin_enqueue_scripts', array(&$this, 'GB_D_addStyleToAdminPage'));
     */
    public function GB_D_addStyleToAdminPage()
    {
        wp_enqueue_style('duyuru_style', plugins_url('style.css', __FILE__));
    }

    /**
     * Duyurudaki  okundu linki tıklandığında ilgili duyuruyu okundu olarak kaydeder
     *
     * add_action('template_redirect','GB_D_okunduIsaretle');
     */
    public function GB_D_okunduIsaretle()
    {
        global $blog_id;
        if (isset($_REQUEST['GB_D_duyuruId'])) {
            $duyuruId = $_REQUEST['GB_D_duyuruId'];
        } else {
            return;
        }
        if (is_user_logged_in()) {
            global $current_user;
            get_currentuserinfo();
            $okunanDuyurular = get_user_meta($current_user->ID, 'GB_D_' . $blog_id . '_okunanDuyurular', true);
            $okunanDuyurular[] = $duyuruId;
            update_user_meta($current_user->ID, 'GB_D_' . $blog_id . '_okunanDuyurular', $okunanDuyurular);

        } else {
            //todo * #13
            $GB_D_sonGosterimTarihi = get_post_meta($duyuruId, 'GB_D_sonGosterimTarihi', true);
            $expire = $this->GB_D_getDate($GB_D_sonGosterimTarihi, true);
            //todo setcookie zaman dilimini  yanlış hesaplıyor 1 saat 30 dk  fazladan ekliyor bu yüzden cookie zaman aşımı yanlış oluyor #12
            setcookie("GB_D_" . $blog_id . "_okunanDuyurular[$duyuruId]", 'true', $expire);
        }
    }

    public function GB_D_okunduIsaretiniKaldir($duyuruId)
    {
        global $blog_id;
        //todo  usermeta tablosunda ki  bütün kullanıcıların kayıtlarından okundu  işaretini kaldırmak gerekiyor.
        if (isset($_COOKIE["GB_D_" . $blog_id . "_okunanDuyurular[$duyuruId]"])) {
            $expire = time() - 36000;
            setcookie("GB_D_" . $blog_id . "_okunanDuyurular[$duyuruId]", '', $expire);
        }
    }

    /**
     * ID numarası  belirtilen duyurunun okundu olarak işaretlenmiş olup olmadığını kontrol eder
     *
     * @param $id Kontrol edilecek duyurunun ID numarası
     * @return bool
     */
    public function GB_D_duyuruOkundumu($id)
    {
        global $blog_id;
        if (is_user_logged_in()) {
            global $current_user;
            get_currentuserinfo();
            $okunanDuyurular = get_user_meta($current_user->ID, 'GB_D_' . $blog_id . '_okunanDuyurular', true);
            return empty($okunanDuyurular) ? false : in_array($id, $okunanDuyurular);
        } else {
            if (isset($_COOKIE['GB_D_' . $blog_id . '_okunanDuyurular'])) {
                $okunanDuyurular = $_COOKIE['GB_D_' . $blog_id . '_okunanDuyurular'];
                return array_key_exists($id, $okunanDuyurular);
            } else {
                return false;
            }
        }
    }

    /**
     * ('Y-m-d H:i:s') Formatındaki tarihi dizi değişkeni olarak döndürür
     * Eğer mktime true ise mktime işleminin sonucunu  döndürür
     * @param null $date
     * @param bool $mktime
     * @return array|int
     */
    public static function GB_D_getDate($date = null, $mktime = false)
    {
        if (is_null($date)) $date = date_i18n('Y-m-d H:i:s');
        $datearr = array(
            'GB_D_yil' => substr($date, 0, 4),
            'GB_D_ay' => substr($date, 5, 2),
            'GB_D_gun' => substr($date, 8, 2),
            'GB_D_saat' => substr($date, 11, 2),
            'GB_D_dakika' => substr($date, 14, 2),
            'saniye' => substr($date, 17, 2)
        );
        if ($mktime) {
            return mktime($datearr['GB_D_saat'], $datearr['GB_D_dakika'], $datearr['saniye'], $datearr['GB_D_ay'], $datearr['GB_D_gun'], $datearr['GB_D_yil']);
        } else {
            return $datearr;
        }
    }
}

$GB_Duyurular = new GB_Duyurular();
?>