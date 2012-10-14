<?php
/*
    Plugin Name: Duyurular
    Plugin URI: http://www.gençbilişim.net
    Description: Gençbilişim Duyurular
    Author: Samet ATABAŞ
    Version: 1.0
    Author URI: http://www.gençbilişim.net
*/

/**
* Duyurular class ı 
* @author Samet ATABAŞ
*
*/
class Duyurular{
	/**
	* eklenti dizinini tutar
	* @var path string
	*/
	private $path;
	function __construct() {
		//eklenti dizinini tanımla
		$this->path = plugin_dir_url(__FILE__);
		//duyurular için Duyuru  post type ını ekle
		add_action( 'init', array(&$this , 'postTypeOlustur'));
		// ayar sayfasını ekle
		add_action('admin_menu', array(&$this, 'ayarSayfası'));
		// yazı  editorü sayfasına widget ekleme
		add_action( 'add_meta_boxes', array(&$this, 'duyuruMetaBoxEkle'));
		add_action( 'save_post', array(&$this, 'duyuruMetaBoxIsle'));// yazı kaydedildiği zaman meta box taki  verileri işlemek için kullanılır
	}
	
	/**
	* Post Type oluşturan fonksiyon
	*
	* @return bollean
	*/
	public function postTypeOlustur() {
		register_post_type( 'Duyuru',
			array(
				'labels' => array(/*labels kullanılan başlıkları belirlemeye yarıyor*/
					'name' =>  'Duyuru' ,
					'singular_name' =>  'Duyuru',
					/*'add_new' => _x('Add New', 'book'), çoklu  dil  için örnek*/
					'add_new' => 'Yeni Duyuru',
    				'add_new_item' => 'Yeni Duyuru Ekle',
    				'edit_item' => 'Duyuruyu Düzenle',
    				'new_item' => 'Yeni Duyuru',
    				'all_items' => 'Tüm Duyurular',
    				'view_item' => 'Duyuruyu Göster',
    				'search_items' => 'Duyuru Ara',
    				'not_found' =>  'Duyuru Bulunamadı',
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
	* Duyuru bilgilerini veritabanından alan fonksiyon
	*
	* Qreturn array
	*/
	public function duyuruMeta() {
		global $wpdb;
		return $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type='duyuru' AND post_status='publish' ORDER BY post_date_gmt DESC", 'ARRAY_A');
		
	}
	/**
	* Duyuruyu metnini gösterecek fonksiyon
	*
	* @return string
	*/
	public function metni() {
		global $wpdb;
		$metin=$wpdb->get_results("SELECT post_content FROM $wpdb->posts WHERE post_type='duyuru' AND post_status='publish' ORDER BY post_date_gmt DESC", 'ARRAY_A');
		echo $metin[0]['post_content'];
	}/**
	* Duyuruyu tarihi gösterecek fonksiyon
	*
	* @return string
	*/
	public function tarihi() {
		global $wpdb;
		$metin=$wpdb->get_results("SELECT post_date_gmt FROM $wpdb->posts WHERE post_type='duyuru' AND post_status='publish' ORDER BY post_date_gmt DESC", 'ARRAY_A');
		$metin[0]['post_date_gmt']=str_replace('-', '', $metin[0]['post_date_gmt']);
		echo substr($metin[0]['post_date_gmt'],6,2).'.'.substr($metin[0]['post_date_gmt'],4,2).'.'.substr($metin[0]['post_date_gmt'],0,4);
	}
	/**
	* Ayarsayfası oluştur
	* 
	* @return void
	*/
	public function ayarSayfası() {
		add_options_page('Duyurular ', 'Duyurular ', 'manage_options', 'duyurular', array(&$this,'ayarSayfasiIcerik'));
	}
	/**
	* Ayar sayfasının içeriği bu sayfa üzerinden belirleinyor
	*
	*
	*
	*/
	public function ayarSayfasiIcerik() {
		echo 'Ayar sayfası';
	}
	/**
	* Duyuru meta box ekler
	*
	*/
	public function duyuruMetaBoxEkle() {
		add_meta_box( 'duyuruMetaBox', 'Duyuru ayarları', array(&$this,'duyuruMetaBox'), 'Duyuru', 'side', 'default', $callback_args );
	}
	/**
	* duyuruMetaBox fonksiyonu 
	* Duyuru oluşturma ve düzenleme sayfasına ayarlamalar için widget içeriği
	*
	*/
	public function duyuruMetaBox() {
		?> 
		<form>
		Kimler görsün:
		<select name="kimlerGorsun">
			<option value="herkes">Herkes</option>
			<option value="uyeler">Sadece Üyeler</option>
		</select>
		Gösterim Modu:
		<select name="gösterimModu">
			<option value="pencere">Pencere</option>
			<option value="bar">Uyarı Şeridi</option>
		</select>
		</form>
		<?php
	}
	/**
	* Duyuru  Meta box  içeriğindeki verileri alıp  işlemek için 
	* Post ile verileri alacak
	*
	*/
	public function duyuruMetaBoxIsle() {
		
	}
}
$duyuru= new Duyurular();
?>