<?php
//todo eklenti urlsi olarak gençbilişimde yazdığım yazının linki olacak
//todo Multi  site için uyumlu  hale gelecek #14
//todo Admin panelde  gözükmesi sağlanacak check box ile denetlenebilir.
//todo Çöpe taşınıca metaların boşalması #11
/*
    Plugin Name: Notices-Duyurular
    Plugin URI: http://www.gençbilişim.net
    Description: Gençbilişim Duyurular
    Author: Samet ATABAŞ
    Version: 1.0
    Author URI: http://www.gençbilişim.net
*/

class GB_Duyurular {

	public $pathUrl;

	public $duyuruContent = '<div class="duyuruContainer">';

	public $textDomainString = 'Notices-Duyurular';
	/**
	 * Duyuruya ait meta bilgilerini tutar
	 * @var array
	 */
	private $meta = array();

	public function __construct() {
		$this->path    = plugin_dir_path( __FILE__ );
		$this->pathUrl = plugin_dir_url( __FILE__ );
		load_plugin_textDomain( $this->textDomainString, false, basename( dirname( __FILE__ ) ) . '/lang' );
		add_action( 'init', array( &$this, 'GB_D_addPostType' ) );
		add_action( 'add_meta_boxes', array( &$this, 'GB_D_addMetaBox' ) );
		add_action( 'save_post', array( &$this, 'GB_D_saveDuyuru' ) );
		add_action( 'edit_post', array( &$this, 'GB_D_editDuyuru' ) );
		add_action( 'wp_trash_post', array( &$this, 'GB_D_moveTrashDuyuru' ) );
		add_action( 'wp_footer', array( &$this, 'GB_D_showDuyuru' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'GB_D_addScriptAndStyle' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'GB_D_addStyleToAdminPage' ) );
		add_action( 'template_redirect', array( &$this, 'GB_D_markAsRead' ) );
		//add_action('init', array(&$this, 'GB_D_getDuyuru'));
	}

	/**
	 * init action a Duyurular için yeni  post type ın  özelliklerini belirler.
	 * add_action('init', array(&$this, 'GB_D_postTypeEkle'));
	 */
	public function GB_D_addPostType() {
		register_post_type( 'Duyuru',
			array(
				'labels'       => array(
					'name'               => __( 'Notice', $this->textDomainString ),
					'singular_name'      => __( 'Notice', $this->textDomainString ),
					'add_new'            => __( 'New Notice', $this->textDomainString ),
					'add_new_item'       => __( 'Add New Notice', $this->textDomainString ),
					'edit_item'          => __( 'Edit Notice', $this->textDomainString ),
					'new_item'           => __( 'New Notice', $this->textDomainString ),
					'all_items'          => __( 'All Notice', $this->textDomainString ),
					'view_item'          => __( 'View Notice', $this->textDomainString ),
					'search_items'       => __( 'Search Notice', $this->textDomainString ),
					'not_found'          => __( 'Notice Not Found', $this->textDomainString ),
					'not_found_in_trash' => __( 'Notice Not Found In Trash', $this->textDomainString ),
					'parent_item_colon'  => '',
					'menu_name'          => __( 'Notices', $this->textDomainString )
				),
				'public'       => false,
				'has_archive'  => true,
				'show_ui'      => true,
				'show_in_menu' => true,
				'menu_icon'    => $this->pathUrl . 'duyuru.ico'
			)
		);
	}

	/**
	 * Duyuru meta box ekler
	 * Duyuru oluşturma ve düzenleme sayfasına ayarlamalar için widget içeriği
	 * add_action('add_meta_boxes', array(&$this, 'GB_D_metaBoxEkle'));
	 */
	public function GB_D_addMetaBox() { //todo #5
		add_meta_box( 'GB_duyuruMetaBox', __( 'Notice Settings', $this->textDomainString ), array( &$this, 'duyuruMetaBox' ), 'Duyuru', 'side', 'default' );
	}

	/**
	 * Metabox içeriğini  oluşturan fonksiyon
	 */
	public function duyuruMetaBox() {
		global $post_id, $wp_locale;
		$this->GB_D_getMeta( $post_id );
		if ( empty( $this->meta['lastDisplayDate'] ) ) {
			$date = $this->GB_D_getDate();
			$date['GB_D_ay'] ++;
		}
		else {
			$date = $this->GB_D_getDate( $this->meta['lastDisplayDate'] );
		}
		$out = '
            <form>
                <div class="misc-pub-section">
                    <span><b>' . __( 'Who can see:', $this->textDomainString ) . '</b></span>
                    <select name="GB_D_meta[whoCanSee]">
                        <option ';
		if ( $this->meta['whoCanSee'] == 'herkes' ) {
			$out .= 'selected=""';
		}
		$out .= ' value="herkes">' . __( 'Everybody', $this->textDomainString ) . '</option>
                        <option ';
		if ( $this->meta['whoCanSee'] == 'uyeler' ) {
			$out .= 'selected=""';
		}
		$out .= ' value="uyeler">' . __( 'Only User', $this->textDomainString ) . '
            </option>
            </select>
            </div>
            <div class="misc-pub-section">
                <span><b>' . __( 'Display Mode:', $this->textDomainString ) . '</b></span>
                <select name="GB_D_meta[displayMode]">
                    <option ';
		if ( $this->meta['displayMode'] == 'pencere' ) {
			$out .= 'selected=""';
		}
		$out .= ' value="pencere">' . __( 'Window', $this->textDomainString ) . '
                    </option>
                    <option ';
		if ( $this->meta['displayMode'] == 'bar' ) {
			$out .= 'selected=""';
		}
		$out .= ' value="bar">' . __( 'Bar', $this->textDomainString ) . '
                    </option>
                </select>
            </div>
            <div class="clear"></div>
            <div class="misc-pub-section curtime">
                <span id="timestamp">
                    <b>' . __( 'Last display date', $this->textDomainString ) . '</b>
                </span><br/>
                <input type="text" maxlength="2" size="2" value="' . $date["GB_D_gun"] . '" name="GB_D_gun" id="jj">.
                <select name="GB_D_ay" id="mm">';
		$x = array( '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', ); //get_date_from_gtm fonkisiyonun da 1 yerine 01 olması gerekiyor

		for ( $i = 0; $i < 12; $i ++ ) {
			$out .= '<option ';
			if ( $x[$i] == $date['GB_D_ay'] ) $out .= 'selected="selected"';
			$out .= ' value="' . $x[$i] . '">' . $x[$i] . '-' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $x[$i] ) ) . '</option>';
		}
		$out .= '
                </select>.
                <input type="text" maxlength="4" size="4" value="' . $date["GB_D_yil"] . '" name="GB_D_yil" id="aa">@<input type="text" maxlength="2" size="2" value="' . $date["GB_D_saat"] . '" name="GB_D_saat" id="hh">:<input type="text" maxlength="2" size="2" value="' . $date["GB_D_dakika"] . '" name="GB_D_dakika" id="mn">
            </div>';
		$out .= '
            <div class="misc-pub-section misc-pub-section-last"xmlns="http://www.w3.org/1999/html">
                <span>
                <b>' . __( 'Type:', $this->textDomainString ) . '</b>
                </span>
                <div class="alert"><input type="radio" ';
		if ( $this->meta['type'] == "" ) {
			$out .= 'checked';
		}
		$out .= ' name="GB_D_meta[type]" value="">' . __( 'Default', $this->textDomainString ) . '</div>
                <div class="alert alert-error"><input type="radio" ';
		if ( $this->meta['type'] == "alert-error" ) {
			$out .= 'checked';
		}
		$out .= ' name="GB_D_meta[type]" value="alert-error">' . __( 'Error', $this->textDomainString ) . '</div>
                <div class="alert alert-info"><input type="radio" ';
		if ( $this->meta['type'] == "alert-info" ) {
			$out .= 'checked';
		}
		$out .= ' name="GB_D_meta[type]" value="alert-info">' . __( 'Info', $this->textDomainString ) . '</div>
                <div class="alert alert-success"><input type="radio" ';
		if ( $this->meta['type'] == "alert-success" ) {
			$out .= 'checked';
		}
		$out .= ' name="GB_D_meta[type]" value="alert-success">' . __( 'Success', $this->textDomainString ) . '</div>

                <div class="clear"></div>
            </div>
            </form>';
		echo $out;
	}

	/**
	 * Duyuru  Meta box  içeriğindeki verileri alıp  işleyerek  duyuruyo oluştururken ek işlenmleri yapacak
	 * Post ile verileri alacak
	 *
	 *  add_action('save_post', array(&$this, 'GB_D_duyuruKaydet'));
	 */
	public function GB_D_saveDuyuru() {
		global $post_id;
		@$this->meta = $_POST['GB_D_meta'];
		@$GB_D_gun = $_POST['GB_D_gun'];
		@$GB_D_ay = $_POST['GB_D_ay'];
		@$GB_D_yil = $_POST['GB_D_yil'];
		@$GB_D_saat = $_POST['GB_D_saat'];
		@$GB_D_dakika = $_POST['GB_D_dakika'];
		@$this->meta['lastDisplayDate'] = $GB_D_yil . '-' . $GB_D_ay . '-' . $GB_D_gun . ' ' . $GB_D_saat . ':' . $GB_D_dakika . ':00';
		add_post_meta( $post_id, "GB_D_meta", $this->meta, true );
	}

	/**
	 * duyuru  güncellendiği zaman yapılacak  olan düzenlemeler bu  fonksiyonile yapılıyor
	 *
	 * add_action('edit_post', array(&$this, 'GB_D_duyuruDuzenle'));
	 */
	public function GB_D_editDuyuru() {
		global $post_id;
		@$this->meta = $_POST['GB_D_meta'];
		@$GB_D_gun = $_POST['GB_D_gun'];
		@$GB_D_ay = $_POST['GB_D_ay'];
		@$GB_D_yil = $_POST['GB_D_yil'];
		@$GB_D_saat = $_POST['GB_D_saat'];
		@$GB_D_dakika = $_POST['GB_D_dakika'];
		@$this->meta['lastDisplayDate'] = $GB_D_yil . '-' . $GB_D_ay . '-' . $GB_D_gun . ' ' . $GB_D_saat . ':' . $GB_D_dakika . ':00';
		update_post_meta( $post_id, "GB_D_meta", $this->meta );
	}

	/**
	 *
	 * add_action('wp_trash_post', array(&$this, 'GB_D_duyuruCopeTasi'));
	 */
	public function GB_D_moveTrashDuyuru() {
		global $post_id, $post_type;
		if ( $post_type != 'duyuru' ) return;
		$this->GB_D_unmarkAsRead( $post_id );
	}

	/**
	 * İd numarası  verilen duyurunun meta değerleri $this->meta değişkenine aktarılır.
	 *
	 * @param $id meta bilgileri alınan duyurunun id numarası
	 */
	public function GB_D_getMeta( $id ) {
		$this->meta = get_post_meta( $id, 'GB_D_meta', true );
	}

	/**
	 * Duyuru bilgilerini  array olarak  getirir
	 * array(8) {
	 *  ["ID"]=>
	 *  ["post_date_gmt"]=>
	 *  ["post_content"]=>
	 *  ["post_title"]=>
	 *  ["whoCanSee"]=>
	 *  ["displayMode"]=>
	 *  ["lastDisplayDate"]=>
	 *  ["type"]=>
	 *}
	 *
	 * @return array
	 */
	public function GB_D_getDuyuru() {
		global $wpdb;
		$duyurular = $wpdb->get_results( "SELECT ID,post_date_gmt,post_content,post_title FROM $wpdb->posts WHERE post_type='duyuru' AND post_status='publish' ORDER BY ID DESC", ARRAY_A );
		$out       = array();
		foreach ( $duyurular as $duyuru ) {
			$this->GB_D_getMeta( $duyuru['ID'] );
			$duyuru = array_merge( $duyuru, $this->meta );
			$out[]  = $duyuru;
		}
		//echo '<pre>';print_r( $out );echo '</pre>';
		return $out;
	}

	/**
	 * Uygun duyuruları sayfaya basar
	 *  add_action('wp_footer', array(&$this, 'GB_D_duyuruGoster'));
	 */
	public function GB_D_showDuyuru() {
		foreach ( $this->GB_D_getDuyuru() as $duyuru ):
			if ( $duyuru['lastDisplayDate'] < date_i18n( 'Y-m-d H:i:s' ) ) { // Son gösterim tarihi geçen duyuru çöpe taşınır
				$duyuru['post_status'] = 'trash';
				wp_update_post( $duyuru );
				continue;
			}
			if ( $this->GB_D_isRead( $duyuru['ID'] ) ) continue;
			switch ( $duyuru['displayMode'] ) {
				case 'pencere':
					if ( $duyuru['whoCanSee'] == 'herkes' ) {
						$this->duyuruContent .= '
                        <div id="fancy-' . $duyuru['ID'] . '" class="alert ' . $duyuru['type'] . '" style="display:none;">
                                <h4>' . ucfirst( get_the_title( $duyuru["ID"] ) ) . '</h4>
                                ' . do_shortcode( wpautop( $duyuru['post_content'] ) ) . '
                                <p class="okundu"><a href="?GB_D_duyuruId=' . $duyuru["ID"] . '">Okundu</a></p>
                        </div>
                        <a rel="gallery" href="#fancy-' . $duyuru['ID'] . '" id="duyuruLink[' . $duyuru['ID'] . ']" class="fancybox" style="display:none;"></a>';
					}
					else {
						if ( is_user_logged_in() ) {
							$this->duyuruContent .= '
                        <div id="fancy-' . $duyuru['ID'] . '" class="alert ' . $duyuru['type'] . '" style="display:none;">
                                <h4>' . ucfirst( get_the_title( $duyuru["ID"] ) ) . '</h4>
                                ' . do_shortcode( wpautop( $duyuru['post_content'] ) ) . '
                                <p class="okundu"><a href="?GB_D_duyuruId=' . $duyuru["ID"] . '">Okundu</a></p>
                        </div>
                        <a rel="gallery" href="#fancy-' . $duyuru['ID'] . '" id="duyuruLink[' . $duyuru['ID'] . ']" class="fancybox" style="display:none;"></a>';
						}
					}
					break;
				case 'bar':
					if ( $duyuru['whoCanSee'] == 'herkes' ) {
						$this->duyuruContent .= '
                            <div id="bar-' . $duyuru['ID'] . '" class="bar alert ' . $duyuru['type'] . '">
                                <button type="button" class="close" >&times;</button>
                                <h4>' . ucfirst( get_the_title( $duyuru["ID"] ) ) . '</h4>
                                ' . do_shortcode( wpautop( $duyuru['post_content'] ) ) . '
                                <p class="okundu"><a href="?GB_D_duyuruId=' . $duyuru["ID"] . '">Okundu</a></p>
                            </div>';
					}
					else {
						if ( is_user_logged_in() ) {
							$this->duyuruContent .= '
                            <div id="bar-' . $duyuru['ID'] . '" class="bar alert ' . $duyuru['type'] . '">
                                <button type="button" class="close">&times;</button>
                                <h4>' . ucfirst( get_the_title( $duyuru["ID"] ) ) . '</h4>
                                ' . do_shortcode( wpautop( $duyuru['post_content'] ) ) . '
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
	 * Duyuruların içine yazıldığı <div class="duyuruContainer"> tağını döner/yazdırır.
	 *
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function GB_D_duyuruContent( $echo = true ) {
		$this->duyuruContent .= '</div>';
		if ( $echo ) {
			echo $this->duyuruContent;
		}
		else {
			return $this->duyuruContent;
		}
	}

	/**
	 * style ve script dosyalarını  yükler
	 * add_action('wp_enqueue_scripts', array(&$this, 'GB_D_addScriptAndStyle'));
	 */
	public function  GB_D_addScriptAndStyle() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'fancybox', plugins_url( '/fancybox/source/jquery.fancybox.js?v=2.1.5', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_style( 'fancybox_style', plugins_url( '/fancybox/source/jquery.fancybox.css?v=2.1.5', __FILE__ ) );
		wp_enqueue_style( 'duyuru_style', plugins_url( 'style.css', __FILE__ ) );
		wp_enqueue_script( 'duyuru', plugins_url( 'default.js', __FILE__ ), array( 'jquery' ) );
	}

	/**
	 * Admin paneline style dosyasını ekler
	 * add_action('admin_enqueue_scripts', array(&$this, 'GB_D_addStyleToAdminPage'));
	 */
	public function GB_D_addStyleToAdminPage() {
		wp_enqueue_style( 'duyuru_style', plugins_url( 'style.css', __FILE__ ) );
	}

	/**
	 * Duyurudaki  okundu linki tıklandığında ilgili duyuruyu okundu olarak kaydeder
	 *
	 * add_action('template_redirect','GB_D_markAsRead');
	 */
	public function GB_D_markAsRead() {
		global $blog_id;
		if ( isset( $_REQUEST['GB_D_duyuruId'] ) ) {
			$duyuruId = $_REQUEST['GB_D_duyuruId'];
		}
		else {
			return;
		}
		if ( is_user_logged_in() ) {
			global $current_user;
			get_currentuserinfo();
			$okunanDuyurular   = get_user_meta( $current_user->ID, "GB_D_{$blog_id}_okunanDuyurular", true );
			$okunanDuyurular[] = $duyuruId;
			update_user_meta( $current_user->ID, "GB_D_{$blog_id}_okunanDuyurular", $okunanDuyurular );

		}
		else {
			$this->GB_D_getMeta( $duyuruId );
			$expire = $this->GB_D_getDate( $this->meta['lastDisplayDate'], true );
			//todo setcookie zaman dilimini  yanlış hesaplıyor 1 saat 30 dk  fazladan ekliyor bu yüzden cookie zaman aşımı yanlış oluyor #12
			setcookie( "GB_D_{$blog_id}_okunanDuyurular[$duyuruId]", 'true', $expire );
		}
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) wp_redirect( $_SERVER['HTTP_REFERER'] );
	}

	public function GB_D_unmarkAsRead( $duyuruId ) {
		global $blog_id, $wpdb;
		$user_ids = $wpdb->get_col( "SELECT user_id FROM $wpdb->usermeta where meta_key='GB_D_{$blog_id}_okunanDuyurular'" );
		foreach ( $user_ids as $user_id ) {
			$okunanDuyurular = get_user_meta( $user_id, "GB_D_{$blog_id}_okunanDuyurular", true );
			if ( array_search( $duyuruId, $okunanDuyurular ) !== false ) {
				unset( $okunanDuyurular[array_search( $duyuruId, $okunanDuyurular )] );
				$okunanDuyurular= array_merge($okunanDuyurular);//indexler  yeniden düzenleniyor
				update_user_meta( $user_id, "GB_D_{$blog_id}_okunanDuyurular", $okunanDuyurular );
			}
			else continue;
		}
		//todo * cookie silme işlemi çalışmıyor.#12
		if ( isset( $_COOKIE["GB_D_{$blog_id}_okunanDuyurular[$duyuruId]"] ) ) {
			$expire = time() - 36000;
			setcookie( "GB_D_{$blog_id}_okunanDuyurular[$duyuruId]", '', $expire );
		}
	}

	/**
	 * ID numarası  belirtilen duyurunun okundu olarak işaretlenmiş olup olmadığını kontrol eder
	 *
	 * @param $id Kontrol edilecek duyurunun ID numarası
	 *
	 * @return bool
	 */
	public function GB_D_isRead( $id ) {
		global $blog_id;
		if ( is_user_logged_in() ) {
			global $current_user;
			get_currentuserinfo();
			$okunanDuyurular = get_user_meta( $current_user->ID, 'GB_D_' . $blog_id . '_okunanDuyurular', true );
			return empty( $okunanDuyurular ) ? false : in_array( $id, $okunanDuyurular );
		}
		else {
			if ( isset( $_COOKIE['GB_D_' . $blog_id . '_okunanDuyurular'] ) ) {
				$okunanDuyurular = $_COOKIE['GB_D_' . $blog_id . '_okunanDuyurular'];
				return array_key_exists( $id, $okunanDuyurular );
			}
			else {
				return false;
			}
		}
	}

	/**
	 * ('Y-m-d H:i:s') Formatındaki tarihi dizi değişkeni olarak döndürür
	 * Eğer mktime true ise mktime işleminin sonucunu  döndürür
	 *
	 * @param null $date
	 * @param bool $mktime
	 *
	 * @return array|int
	 */
	public function GB_D_getDate( $date = null, $mktime = false ) {
		if ( is_null( $date ) ) $date = date_i18n( 'Y-m-d H:i:s' );
		$datearr = array(
			'GB_D_yil'    => substr( $date, 0, 4 ),
			'GB_D_ay'     => substr( $date, 5, 2 ),
			'GB_D_gun'    => substr( $date, 8, 2 ),
			'GB_D_saat'   => substr( $date, 11, 2 ),
			'GB_D_dakika' => substr( $date, 14, 2 ),
			'saniye'      => substr( $date, 17, 2 )
		);
		if ( $mktime ) {
			return mktime( $datearr['GB_D_saat'], $datearr['GB_D_dakika'], $datearr['saniye'], $datearr['GB_D_ay'], $datearr['GB_D_gun'], $datearr['GB_D_yil'] );
		}
		else {
			return $datearr;
		}
	}
}

$GB_Duyurular = new GB_Duyurular();
?>