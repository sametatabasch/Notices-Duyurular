<?php
/*
* Plugin Name: Announcements-Duyurular
* Plugin URI: http://gencbilisim.net/notices-duyurular-eklentisi/
* Description: Easy way to publish announcements in Wordpress
* Author: Samet ATABAŞ
* Version: 1.8
* Author URI: http://www.gencbilisim.net
* Text Domain: Notices-Duyurular
* Domain Path: /lang
*/
//todo Multi  site için uyumlu  hale gelecek #14
//todo Admin panel duyuruları ekleme işlemi yapılacak check box ile denetlenebilir.#48
//todo * Çöpe taşınıca metaların boşalması #11

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Define Plugin text Domain String globally
 */
if ( ! defined( 'GB_D_textDomainString' ) ) {
	define( 'GB_D_textDomainString', 'Notices-Duyurular' );
}

if ( ! class_exists( 'GB_Notices_Plugin' ) ):

	class GB_Notices_Plugin {

		/**
		 * Eklenti  dizinini tutar
		 * @var string
		 */
		public static $path;

		/**
		 * eklenti  dizinini  url olarak  tutar
		 * @var string
		 */
		public $pathUrl;

		/**
		 *
		 * @var GB_Notices
		 */
		public $notices;

		/**
		 * The single instance of the class
		 * @var null
		 */
		protected static $_instance = null;

		/**
		 * @return GB_Notices_Plugin|null
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * GB_Notices_Plugin constructor.
		 */
		public function __construct() {

			self::$path    = plugin_dir_path( __FILE__ );
			$this->pathUrl = plugin_dir_url( __FILE__ );
			load_plugin_textdomain( GB_D_textDomainString, false, basename( dirname( __FILE__ ) ) . '/lang' );
			add_action( 'init', array( &$this, 'addPostType' ) );
			add_action( 'add_meta_boxes', array( &$this, 'addMetaBox' ) );
			add_action( 'after_setup_theme', array( &$this, 'addScriptAndStyle' ) );
			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array( &$this, 'addStyleAndScriptToAdminPage' ) );
			}

			$this->includes();
			$this->notices = new GB_Notices();
		}

		/**
		 * includes
		 */
		public function includes() {
			include_once dirname( __FILE__ ) . "/includes/class/GB_Notices.php";
			include_once dirname( __FILE__ ) . "/includes/class/GB_Notice.php";
			include_once dirname( __FILE__ ) . "/includes/functions.php";
		}

		/**
		 * init action a Duyurular için yeni  post type ın  özelliklerini belirler.
		 * add_action('init', array(&$this, 'addPostType'));
		 */
		public function addPostType() {
			register_post_type( 'Notice',
				array(
					'labels'       => array(
						'name'               => __( 'Notice', GB_D_textDomainString ),
						'singular_name'      => __( 'Notice', GB_D_textDomainString ),
						'add_new'            => __( 'New Notice', GB_D_textDomainString ),
						'add_new_item'       => __( 'Add New Notice', GB_D_textDomainString ),
						'edit_item'          => __( 'Edit Notice', GB_D_textDomainString ),
						'new_item'           => __( 'New Notice', GB_D_textDomainString ),
						'all_items'          => __( 'All Notice', GB_D_textDomainString ),
						'view_item'          => __( 'View Notice', GB_D_textDomainString ),
						'search_items'       => __( 'Search Notice', GB_D_textDomainString ),
						'not_found'          => __( 'Notice Not Found', GB_D_textDomainString ),
						'not_found_in_trash' => __( 'Notice Not Found In Trash', GB_D_textDomainString ),
						'parent_item_colon'  => '',
						'menu_name'          => __( 'Notices', GB_D_textDomainString )
					),
					'public'       => false,
					'has_archive'  => true,
					'show_ui'      => true,
					'show_in_menu' => true,
				)
			);
		}

		/**
		 * Duyuru ayarlarını  belirlemek için Meta Box ekler
		 *
		 * add_action('add_meta_boxes', array(&$this, 'addMetaBox'));
		 */
		public function addMetaBox() {
			add_meta_box(
				'GB_noticeMetaBox',
				__( 'Notice Settings', GB_D_textDomainString ),
				array( &$this, 'noticeMetaBox' ),
				'Notice',
				'side',
				'default'
			);
		}

		/**
		 * Duuru ayarları için  metabox içeriğini  oluşturur
		 */
		public function noticeMetaBox() {
			/**
			 * Güncellenen duyuru için Duyuru sınıfı oluşturuyoruz
			 */
			$notice = new GB_Notice( get_the_ID() );

			$date = dateStringToArray( $notice->expireDate );

			global $wp_locale;

			include_once dirname( __FILE__ ) . "/views/metabox.php";
		}

		/**
		 * Admin paneline style dosyasını ekler
		 * add_action('admin_enqueue_scripts', array(&$this, 'addStyleToAdminPage'));
		 */
		public function addStyleAndScriptToAdminPage() {
			/**
			 * Admin paneline eklenecek style dosyasını wordpress e kaydediyorum
			 */
			wp_register_style( 'notice_admin_style', plugins_url( 'css/admin.css', __FILE__ ) );
			/**
			 * Admin paneline eklenecek style dosyasını wordpress e ekliyorum
			 */
			wp_enqueue_style( 'notice_admin_style' );
			/**
			 *
			 */
			wp_register_style( 'jquery-ui-datepicker-style', plugins_url( 'css/jquery-ui.datepicker.min.css', __FILE__ ) );
			/**
			 *
			 */
			wp_enqueue_style( 'jquery-ui-datepicker-style' );
			/**
			 * Admin paneline jQueryUI datepicker js dosyası kuyruğa ekleniyor.
			 */
			wp_enqueue_script( 'jquery-ui-datepicker' );
		}

		/**
		 * Tema yüklendikten sonra script ve style  dosyalarını  ekler
		 * add_action( 'after_setup_theme', array( &$this, 'addScriptAndStyle' ) );
		 */
		public function addScriptAndStyle() {
			if ( $this->notices->isThereAnyNotice() ) {
				add_action( 'wp_enqueue_scripts', array( &$this, 'enqueueScriptAndStyle' ) );
			}
		}

		/**
		 * style ve script dosyalarını kuyruğa ekler
		 * add_action('wp_enqueue_scripts', array(&$this, 'enqueueScriptAndStyle'));
		 */
		public function enqueueScriptAndStyle() {
			/* Register Scripts */
			wp_register_script(
				'notice_script_window.class',
				plugins_url( 'js/window.class.js', __FILE__ ),
				array( 'jquery' )
			);
			wp_register_script(
				'notice_script_GBWindow',
				plugins_url( 'js/GBWindow.js', __FILE__ ),
				array( 'jquery', 'imagesloaded', 'notice_script_window.class' ) );
			wp_register_script(
				'notice_script_default',
				plugins_url( 'js/default.js', __FILE__ ),
				array( 'jquery', 'imagesloaded', 'notice_script_GBWindow', 'notice_script_window.class' )
			);
			/* Register Styles */
			wp_register_style(
				'notice_styleReset',
				plugins_url( 'css/styleReset.css', __FILE__ )
			);
			wp_register_style(
				'notice_style',
				plugins_url( 'css/style.css', __FILE__ ),
				array( 'notice_styleReset' )
			);
			wp_register_style(
				'notice_alert_style',
				plugins_url( 'css/alert.css', __FILE__ ),
				array( 'notice_styleReset' )
			);

			/* Add registered styles to wordpress style queue  */
			wp_enqueue_style( 'notice_style' );
			wp_enqueue_style( 'notice_alert_style' );
			wp_enqueue_style( 'notice_styleReset' );

			/* Add registered script to wordpress script queue  */
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'imagesloaded' );
			wp_enqueue_script( 'notice_script_window.class' );
			wp_enqueue_script( 'notice_script_GBWindow' );
			wp_enqueue_script( 'notice_script_default' );

			$this->addLanguageSupportToScript();
			$ajaxurl = admin_url( 'admin-ajax.php' );
			wp_localize_script(
				'notice_script_default',
				'ajaxData_default',
				array(
					// URL to wp-admin/admin-ajax.php to process the request
					'ajaxurl'                         => $ajaxurl,
					// generate a nonce with a unique ID "myajax-post-comment-nonce"
					// so that you can check it later when an AJAX request is sent
					'securityFor_getNoticesContainer' => wp_create_nonce( 'getNoticesContainer' ),
					'securityFor_markAsReadNotice'    => wp_create_nonce( 'markAsReadNotice' ),
				)
			);
			wp_localize_script(
				'notice_script_GBWindow',
				'ajaxData_GBWindow',
				array(
					// URL to wp-admin/admin-ajax.php to process the request
					'ajaxurl'                               => $ajaxurl,
					// generate a nonce with a unique ID "myajax-post-comment-nonce"
					// so that you can check it later when an AJAX request is sent
					'securityFor_markAsReadNotice'          => wp_create_nonce( 'markAsReadNotice' ),
					'securityFor_getSingleWindowModeNotice' => wp_create_nonce( 'getSingleWindowModeNotice' )
				)
			);
		}

		/**
		 * Add language support to scritp with wp_localize_script()
		 */
		public function addLanguageSupportToScript() {
			/*
			 *  Javascript dosyasında çoklu  dil  desteği
			 * <?php wp_localize_script( $handle, $name, $data ); ?>
			 * $handle -> Çoklu  dil  desteğinin  sağlanacağı js dosyasının enqueue kayıt ismi
			 * $name   -> Dizeleri  taşıyan java nesnesinin  adı
			 * $data   -> Dil desteği  sağlanan dizeler
			 */
			$noticeLocalizeMessage_translation_array = array(
				'closeMessage'           => __( 'If you do not want to see again this notice,click &#34;do not show again&#34;.', GB_D_textDomainString ),
				'dontShow'               => __( 'Do not show again', GB_D_textDomainString ),
				'close'                  => __( 'Close', GB_D_textDomainString ),
				'backgroundClickMessage' => __( 'if you close notices with click background, you see notices again and again. İf you dont want see notices again, close notices with close button.', GB_D_textDomainString )
			);
			wp_localize_script( 'notice_script_GBWindow', 'noticeLocalizeMessage', $noticeLocalizeMessage_translation_array );

		}

	}

	add_action( 'plugins_loaded', array( 'GB_Notices_Plugin', 'instance' ) );
endif;