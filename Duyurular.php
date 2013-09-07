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
		add_action('admin_menu', array(&$this, 'ayarSayfasi'));
		// yazı  editorü sayfasına widget ekleme
		add_action( 'add_meta_boxes', array(&$this, 'duyuruMetaBoxEkle'));
		// duyuru kaydedildiği zaman meta box taki  verileri işlemek için kullanılır
		add_action( 'save_post', array(&$this, 'duyuruOlustur'));
		// duyuru düzenlerdiği zaman meta box taki  verileri işlemek için kullanılır
		add_action( 'edit_post', array(&$this, 'duyuruDuzenle'));
		add_action('init',array(&$this,'duyuruGoster'));
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
	* tüm duyuruları enson yazılan ilk olacak şekilde dizi içinde saklar
	* Qreturn array
	*/
	public static function duyuruMeta() {
		global $wpdb;
		return  $wpdb->get_results("SELECT * FROM $wpdb->posts  WHERE post_type='duyuru' AND post_status='publish' ORDER BY ID DESC", 'ARRAY_A');
		
	}
	/**
	* Duyuruyu metnini gösterecek fonksiyon
	*
	* @param bool $echo true ise çıktı yapar false ise değer döndürür
	* @return string
	*/
	public static function duyuruMetni($echo=true) {
		$metin=self::duyuruMeta();
		if($echo) {
			echo $metin[0]['post_content'];
		}else {
			return $metin[0]['post_content'];
		}
	}
	/**
	* Duyuruyu tarihi gösterecek fonksiyon
	*
	* @return string
	*/
	public static function duyuruTarihi() {
		$tarih=self::duyuruMeta();
		$tarih=str_replace('-', '', $tarih[0]["post_date_gmt"]);
		echo substr($tarih,6,2).'.'.substr($tarih,4,2).'.'.substr($tarih,0,4);
	}
	/**
	 * duyuruGoster fonksiyonu 
	 * duyurunun gösterim tarihine kimlerin göreceğine ve nasıl görüneceğine göre duyuruyu gösteren fonksiyon
	 *
	 */
	public function duyuruGoster(){
		$duyuru=self::duyuruMeta();
		if(get_post_meta($duyuru[0]['ID'],"kimlerGorsun",1)=="herkes") {
			add_action('wp_head',array(&$this,'duyuruFancbox'));	
		}
	}
	/**
	 * Duyuruyu için fancyboy ı  yükler ve duyuruyu ekranda gösterir
	 *
	 *
	 *
	 */
	function duyuruFancbox(){ 
		$mtn=self::duyuruMetni(false).'<br><input type="checkbox" name="okundu">Bir daha gösterme';
		echo "
			<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js\" type=\"text/javascript\"></script>
			<script src=\"".plugins_url('/fancybox/jquery.fancybox-1.3.4.js', __FILE__)."\" type=\"text/javascript\"></script>
			<link media=\"screen\" href=\"".plugins_url('/fancybox/jquery.fancybox-1.3.4.css', __FILE__)."\" type=\"text/css\" rel=\"stylesheet\">
			<script type=\"text/javascript\">
				$(document).ready(function() {
					$.fancybox( '".$mtn."' );
				});
			</script>";
	}
	/**
	* Ayarsayfası oluştur
	* 
	* @return void
	*/
	public function ayarSayfasi() {
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
		global $post_id;
		$kimlerGorsun=get_post_meta($post_id,"kimlerGorsun",1);
		$gosteriModu=get_post_meta($post_id,"gosteriModu",1);
		?> 
		<form>
		<b>Kimler görsün:</b><br/>
		<select name="kimlerGorsun">
			<option  <?php  if($kimlerGorsun=='herkes') {echo 'selected=""';} ?>  value="herkes">Herkes</option>
			<option <?php  if($kimlerGorsun=='uyeler') {echo 'selected=""';} ?> value="uyeler">Sadece Üyeler</option>
		</select><br/>
		<b>Gösterim Modu:</b><br/>
		<select name="gosterimModu">
			<option <?php  if($gosterimModu=='pencere') {echo 'selected=""';} ?> value="pencere">Pencere</option>
			<option <?php  if($gosteriModu=='bar') {echo 'selected=""';} ?> value="bar">Uyarı Şeridi</option>
		</select><br/>
		<b>Son Gösterim Tarihi:</b><br/>
		<select name="mm" id="mm">
			<option value="01">01-Oca</option>
			<option value="02">02-Şub</option>
			<option value="03">03-Mar</option>
			<option value="04">04-Nis</option>
			<option value="05">05-May</option>
			<option value="06">06-Haz</option>
			<option value="07">07-Tem</option>
			<option value="08">08-Ağu</option>
			<option value="09">09-Eyl</option>
			<option value="10">10-Eki</option>
			<option value="11">11-Kas</option>
			<option selected="selected" value="12">12-Ara</option>
		</select>
		<input type="text" maxlength="2" size="2" value="31" name="jj" id="jj">, 
		<input type="text" maxlength="4" size="4" value="2012" name="aa" id="aa"> @ 
		<input type="text" maxlength="2" size="2" value="17" name="hh" id="hh"> : 
		<input type="text" maxlength="2" size="2" value="49" name="mn" id="mn">
		</form>
		<?php
	}
	/**
	* Duyuru  Meta box  içeriğindeki verileri alıp  işleyerek  duyuruyo oluştururken ek işlenmleri yapacak 
	* Post ile verileri alacak
	*
	*/
	public function duyuruOlustur() {
		global $post_id;
		$kimlerGorsun=$_POST["kimlerGorsun"];
		$gosteriModu=$_POST["gosterimModu"];
		add_post_meta($post_id, "kimlerGorsun", $kimlerGorsun,true);
		add_post_meta($post_id, "gosteriModu", $gosteriModu,true);
	}
	/**
	 * duyuru  güncellendiği zaman yapılacak  olan düzenlemeler bu  fonksiyonile yapılıyor
	 *
	 */
	public function duyuruDuzenle() {
		global $post_id;
		$kimlerGorsun=$_POST["kimlerGorsun"];
		$gosteriModu=$_POST["gosterimModu"];
		update_post_meta($post_id, "kimlerGorsun", $kimlerGorsun);
		update_post_meta($post_id, "gosteriModu", $gosteriModu);
	}
}
$duyuru= new Duyurular();
?>