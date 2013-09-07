<?php
//todo eklenti urlsi olarak gençbilişimde yazdığım yazının linki olacak
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

    public function __construct()
    {
        $this->path = plugin_dir_path(__FILE__);
        $this->pathUrl = plugin_dir_url(__FILE__);
        add_action('init', array(&$this, 'GB_D_postTypeEkle'));
        add_action('add_meta_boxes', array(&$this, 'GB_D_metaBoxEkle'));
        add_action('save_post', array(&$this, 'GB_D_duyuruKaydet'));
        add_action('edit_post', array(&$this, 'GB_D_duyuruDuzenle'));
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
    {
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
            $out .= ' value=dd"uyeler">Sadece Üyeler
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
        add_post_meta($post_id, "gosteriModu", $gosteriModu, true);
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
        $kimlerGorsun = $_POST["kimlerGorsun"];
        $gosteriModu = $_POST["gosterimModu"];
        $gun = $_POST['gun'];
        $ay = $_POST['ay'];
        $yil = $_POST['yil'];
        $saat = $_POST['saat'];
        $dakika = $_POST['dakika'];
        $sonGosterimTarihi = $yil . '-' . $ay . '-' . $gun . ' ' . $saat . ':' . $dakika . ':00';
        update_post_meta($post_id, "kimlerGorsun", $kimlerGorsun);
        update_post_meta($post_id, "gosteriModu", $gosteriModu);
        update_post_meta($post_id, "sonGosterimTarihi", $sonGosterimTarihi, true);
    }

    /**
     * @return array
     */
    public function GB_D_getDuyuru()
    {
        global $wpdb;
        $duyurular = $wpdb->get_results("SELECT $wpdb->posts.ID,$wpdb->posts.post_date_gmt,$wpdb->posts.post_content,$wpdb->posts.post_title,$wpdb->postmeta.meta_value FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->posts.post_type='duyuru' AND $wpdb->posts.post_status='publish' AND $wpdb->postmeta.meta_key='sonGosterimTarihi' ORDER BY $wpdb->posts.ID DESC", ARRAY_A);
        for ($i = 0; $i < count($duyurular); $i++) {
            $duyurular[$i]['sonGosterimTarihi'] = $duyurular[$i]['meta_value'];
            unset($duyurular[$i]['meta_value']);
        }
        return $duyurular;
    }
}

$GB_Duyurular = new GB_Duyurular();
?>