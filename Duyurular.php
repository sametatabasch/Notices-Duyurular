<?php
//todo eklenti urlsi olarak gençbilişimde yazdığım yazının linki olacak
//todo Multi  site için uyumlu  hale gelecek #14
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
        add_action('wp_footer', array(&$this, 'GB_D_duyuruGoster'));
        add_action('wp_enqueue_scripts', array(&$this, 'GB_D_addScriptAndStyle'));
        add_action('template_redirect', array(&$this, 'GB_D_okunduIsaretle'));
        //add_action('init', array(&$this, 'GB_D_getDuyuru'));
    }

    /**
     * init action a Duyurular için yeni  post type ın  özelliklerini belirler.
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
     *
     */
    public function GB_D_metaBoxEkle()
    { //todo duyuru son gösrerim tarihi  duyurunun yazıldığı tarihten bir ay sonra olarak belirlensin(öntanımlı) veya duyuru  yayınlanırken  son okuma tarihinin  şimdiki  zaman olmaması kontrol  edilsin .
        function duyuruMetaBox()
        {
            global $post_id, $wp_locale;
            $kimlerGorsun = get_post_meta($post_id, "kimlerGorsun", 1);
            $gosteriModu = get_post_meta($post_id, "gosteriModu", 1);
            $sonGosterimTarihi = get_post_meta($post_id, 'sonGosterimTarihi', 1);
            empty($sonGosterimTarihi) ? $gdate = gmdate('Y-m-d H:i:s') : $gdate = $sonGosterimTarihi;
            $date = array(
                'year' => substr(get_date_from_gmt($gdate), 0, 4),
                'ay' => substr(get_date_from_gmt($gdate), 5, 2),
                'mday' => substr(get_date_from_gmt($gdate), 8, 2),
                'hours' => substr(get_date_from_gmt($gdate), 11, 2),
                'minutes' => substr(get_date_from_gmt($gdate), 14, 2)
            );
            $out = '
            <form>
                <div class="misc-pub-section">
                    <span><b>Kimler görsün:</b></span>
                    <select name="kimlerGorsun">
                        <option ';
            if ($kimlerGorsun == 'herkes') {
                $out .= 'selected=""';
            }
            $out .= ' value="herkes">Herkes</option>
                        <option ';
            if ($kimlerGorsun == 'uyeler') {
                $out .= 'selected=""';
            }
            $out .= ' value="uyeler">Sadece Üyeler
            </option>
            </select>
            </div>
            <div class="misc-pub-section">
                <span><b>Gösterim Modu:</b></span>
                <select name="gosterimModu">
                    <option ';
            if ($gosteriModu == 'pencere') {
                $out .= 'selected=""';
            }
            $out .= ' value="pencere">Pencere
                    </option>
                    <option ';
            if ($gosteriModu == 'bar') {
                $out .= 'selected=""';
            }
            $out .= ' value="bar">Uyarı Şeridi
                    </option>
                </select>
            </div>
            <div class="clear"></div>
            <div class="misc-pub-section misc-pub-section-last curtime">
                <span id="timestamp">
                    <b>Son Gösterim Tarihi</b>
                </span><br/>
                <input type="text" maxlength="2" size="2" value="' . $date["mday"] . '" name="gun" id="jj">.
                <select name="ay" id="mm">';
            $x = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12',); //get_date_from_gtm fonkisiyonun da 1 yerine 01 olması gerekiyor

            for ($i = 0; $i < 12; $i++) {
                $out .= '<option ';
                if ($x[$i] == $date['ay']) $out .= 'selected="selected"';
                $out .= ' value="' . $x[$i] . '">' . $x[$i] . '-' . $wp_locale->get_month_abbrev($wp_locale->get_month($x[$i])) . '</option>';
            }
            $out .= '
                </select>.
                <input type="text" maxlength="4" size="4" value="' . $date["year"] . '" name="yil" id="aa"> @
                <input type="text" maxlength="2" size="2" value="' . $date["hours"] . '" name="saat" id="hh"> :
                <input type="text" maxlength="2" size="2" value="' . $date["minutes"] . '" name="dakika" id="mn">
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
     * add_action('save_post', array(&$this, 'GB_D_duyuruKaydet'));
     */
    public function GB_D_duyuruKaydet()
    {
        global $post_id;
        @$kimlerGorsun = $_POST["kimlerGorsun"];
        @$gosteriModu = $_POST["gosterimModu"];
        @$gun = $_POST['gun'];
        @$ay = $_POST['ay'];
        @$yil = $_POST['yil'];
        @$saat = $_POST['saat'];
        @$dakika = $_POST['dakika'];
        @$sonGosterimTarihi = $yil . '-' . $ay . '-' . $gun . ' ' . $saat . ':' . $dakika . ':00';
        add_post_meta($post_id, "kimlerGorsun", $kimlerGorsun, true);
        add_post_meta($post_id, "gosterimModu", $gosteriModu, true);
        add_post_meta($post_id, "sonGosterimTarihi", $sonGosterimTarihi, true);
    }

    /**
     * duyuru  güncellendiği zaman yapılacak  olan düzenlemeler bu  fonksiyonile yapılıyor
     *
     * add_action('edit_post', array(&$this, 'GB_D_duyuruDuzenle'));
     */
    public function GB_D_duyuruDuzenle()
    {
        global $post_id;
        @$kimlerGorsun = $_POST["kimlerGorsun"];
        @$gosteriModu = $_POST["gosterimModu"];
        @$gun = $_POST['gun'];
        @$ay = $_POST['ay'];
        @$yil = $_POST['yil'];
        @$saat = $_POST['saat'];
        @$dakika = $_POST['dakika'];
        @$sonGosterimTarihi = $yil . '-' . $ay . '-' . $gun . ' ' . $saat . ':' . $dakika . ':00';
        update_post_meta($post_id, "kimlerGorsun", $kimlerGorsun);
        update_post_meta($post_id, "gosterimModu", $gosteriModu);
        update_post_meta($post_id, "sonGosterimTarihi", $sonGosterimTarihi);
    }

    /**
     * Duyuru bilgilerini  array olarak  getirir
     * array(7) {
     *  ["ID"]=>
     *  ["post_date_gmt"]=>
     *  ["post_content"]=>
     *  ["post_title"]=>
     *  ["kimlerGorsun"]=>
     *  ["gosterimModu"]=>
     *  ["sonGosterimTarihi"]=>
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
            $duyuru['kimlerGorsun'] = get_post_meta($duyuru['ID'], 'kimlerGorsun', true);
            $duyuru['gosterimModu'] = get_post_meta($duyuru['ID'], 'gosterimModu', true);
            $duyuru['sonGosterimTarihi'] = get_post_meta($duyuru['ID'], 'sonGosterimTarihi', true);
            $out[] = $duyuru;
        }
        //echo '<pre>';print_r($out);echo '</pre>';
        return $out;
    }

    public function GB_D_duyuruGoster()
    {
        //todo diğer css seçenekleri  eklenecek alert-danger gibi
        foreach ($this->GB_D_getDuyuru() as $duyuru):
            if ($duyuru['sonGosterimTarihi'] < gmdate('Y-m-d H:i:s')) { // Son gösterim tarihi geçen duyuru çöpe taşınır
                $duyuru['post_status'] = 'trash';
                wp_update_post($duyuru);
                continue;
            }
            if ($this->GB_D_duyuruOkundumu($duyuru['ID'])) continue;
            switch ($duyuru['gosterimModu']) {
                case 'pencere':
                    if ($duyuru['kimlerGorsun'] == 'herkes') {
                        $this->duyuruContent .= '<script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            $("#duyuruLink").trigger("click");
                        });</script>';
                        $this->duyuruContent .= '
                        <div id="fancy-' . $duyuru['ID'] . '" class="alert" style="display:none;">
                                <h4>' . ucfirst(get_the_title($duyuru["ID"])) . '</h4>
                                ' . do_shortcode(wpautop($duyuru['post_content'])) . '
                                <p class="okundu"><a href="?GB_D_duyuruId=' . $duyuru["ID"] . '">Okundu</a></p>
                        </div>
                        <a href="#fancy-' . $duyuru['ID'] . '" id="duyuruLink" class="fancybox" style="display:none;"> </a>';

                    } else {
                        if (is_user_logged_in()) {
                            $this->duyuruContent .= '<script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            $("#duyuruLink").trigger("click");
                        });</script>';
                            $this->duyuruContent .= '
                        <div id="fancy-' . $duyuru['ID'] . '" class="alert" style="display:none;">
                                <h4>' . ucfirst(get_the_title($duyuru["ID"])) . '</h4>
                                ' . do_shortcode(wpautop($duyuru['post_content'])) . '
                                <p class="okundu"><a href="?GB_D_duyuruId=' . $duyuru["ID"] . '">Okundu</a></p>
                        </div>
                        <a href="#fancy-' . $duyuru['ID'] . '" id="duyuruLink" class="fancybox" style="display:none;"> sdff</a>';
                        }
                    }

                    break;
                case 'bar':
                    if ($duyuru['kimlerGorsun'] == 'herkes') {
                        $this->duyuruContent .= '
                            <div id="bar-' . $duyuru['ID'] . '" class="bar alert">
                                <button type="button" class="close" >&times;</button>
                                <h4>' . ucfirst(get_the_title($duyuru["ID"])) . '</h4>
                                ' . do_shortcode(wpautop($duyuru['post_content'])) . '
                                <p class="okundu"><a href="?GB_D_duyuruId=' . $duyuru["ID"] . '">Okundu</a></p>
                            </div>';
                    } else {
                        if (is_user_logged_in()) {
                            $this->duyuruContent .= '
                            <div id="bar-' . $duyuru['ID'] . '" class="bar alert">
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

    public function GB_D_duyuruContent($echo = true)
    {
        $this->duyuruContent .= '</div>';
        if ($echo) {
            echo $this->duyuruContent;
        } else {
            return $this->duyuruContent;
        }
    }


    public function  GB_D_addScriptAndStyle()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('fancybox', plugins_url('/fancybox/source/jquery.fancybox.js?v=2.1.5', __FILE__), array('jquery'));
        wp_enqueue_style('fancybox_style', plugins_url('/fancybox/source/jquery.fancybox.css?v=2.1.5', __FILE__));
        wp_enqueue_style('duyuru', plugins_url('style.css', __FILE__));
        wp_enqueue_script('duyuru_style', plugins_url('default.js', __FILE__), array('jquery'));
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
            $okunanDuyurular = get_user_meta($current_user->ID, 'GB_D_'.$blog_id.'_okunanDuyurular',true);
            $okunanDuyurular[] = $duyuruId;
            update_user_meta($current_user->ID, 'GB_D_'.$blog_id.'_okunanDuyurular', $okunanDuyurular);

        } else {
            //todo #13
            if (isset($_COOKIE['GB_D_'.$blog_id.'_okunanDuyurular'])) $okunanDuyurular = $_COOKIE['GB_D_'.$blog_id.'_okunanDuyurular'];
            $okunanDuyurular[$duyuruId] = 'true';
            //todo duyuru  son  gösterim tarihi expire olarak  ayarlanmalı #12
            $expire = time() + 60 * 60 * 24 * 30;
            setcookie("GB_D_".$blog_id."_okunanDuyurular[$duyuruId]", 'true', $expire);

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
            $okunanDuyurular = get_user_meta($current_user->ID, 'GB_D_'.$blog_id.'_okunanDuyurular', true);
            return in_array($id, $okunanDuyurular);
        } else {
            if (isset($_COOKIE['GB_D_'.$blog_id.'_okunanDuyurular'])) $okunanDuyurular = $_COOKIE['GB_D_'.$blog_id.'_okunanDuyurular'];
            return array_key_exists($id, $okunanDuyurular);
        }
    }
}

$GB_Duyurular = new GB_Duyurular();
?>