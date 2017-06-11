<?php

/**
 * Created by PhpStorm.
 * User: sametatabasch
 * Date: 11.06.2017
 * Time: 01:11
 */
class GB_Notices {
	/**
	 * Store all available Notice
	 * @var array
	 */
	public $notices;
	/**
	 * Notices Count
	 * @var integer
	 */
	private $count;
	/**
	 * Notice meta data
	 * @var array
	 */
	public $noticeMeta;

	/**
	 * Wp_postmeta key constant
	 */
	const NOTICE_POST_META_KEY = 'GB_D_meta';

	/**
	 * The single instance of the class
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * @return GB_Notices|null
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		/*
		 * Add saveNotice to save_post action
		 */
		add_action( 'save_post', array( &$this, 'saveNotice' ) );
		/*
		 * Add editNotice to adit_post action
		 */
		add_action( 'edit_post', array( &$this, 'editNotice' ) );
		/*
		 * Add moveTrashNotice to wp_trash_post action
		 */
		add_action( 'wp_trash_post', array( &$this, 'moveTrashNotice' ) );
		/*
		 * Add trashToPublishNotice to trash_to_publish action
		 */
		add_action( 'trash_to_publish', array( &$this, 'trashToPublishNotice' ) );
		if ( is_admin() ) {
			/*
			 * Add createNoticesContainerHtml action
			 */
			add_action( 'wp_ajax_getNoticesContainer', array( &$this, 'createNoticesContainerHtml' ) );
			add_action( 'wp_ajax_nopriv_getNoticesContainer', array( &$this, 'createNoticesContainerHtml' ) );
			/*
			 * Add mark as read ajax action
			 */
			add_action( 'wp_ajax_markAsReadNotice', array( &$this, 'markAsRead' ) );
			add_action( 'wp_ajax_nopriv_markAsReadNotice', array( &$this, 'markAsRead' ) );

		}

		$this->getAllNotice();
	}

	/**
	 * Get all Notice to Notices->notices
	 */
	private function getAllNotice() {
		$arg = array(
			'numberposts' => - 1,
			'post_type'   => 'notice',
			'post_status' => 'publish'

		);

		$this->notices = get_posts( $arg );
		$this->count   = count( $this->notices );
	}

	/**
	 * Check available Notices
	 * @return bool
	 */
	public function isThereAnyNotice() {
		if ( $this->count > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Meta box dan  gelen duyuru ayarlarını  kaydeder
	 *
	 *  add_action('save_post', array(&$this, 'saveNotice'));
	 */
	public function saveNotice() {
		$noticeId       = get_the_ID();
		$noticePostType = get_post_type( $noticeId );
		if ( $noticePostType != 'notice' ) {
			return;
		}
		if(isset($_POST['noticeMetaData'])){
			$this->noticeMeta                    = $_POST['noticeMetaData'];
		}
		if(isset($_POST['noticeExpireDate'])){
			$noticeExpireDate                    = $_POST['noticeExpireDate'];
			$this->noticeMeta['lastDisplayDate'] = $noticeExpireDate['year'] . '-' . $noticeExpireDate['month'] . '-' . $noticeExpireDate['day'] . ' ' . $noticeExpireDate['hour'] . ':' . $noticeExpireDate['minute'] . ':00';
		}
		add_post_meta( $noticeId, self::NOTICE_POST_META_KEY, $this->noticeMeta, true );

	}

	/**
	 * Duyuru güncellendiğinde meta box daki  verileri ile duyuru ayarlarını günceller
	 *
	 * add_action('edit_post', array(&$this, 'editNotice'));
	 */
	public function editNotice() {
		$noticePostId   = get_the_ID();
		$noticePostType = get_post_type( $noticePostId );
		if ( $noticePostType != 'notice' ) {
			return;
		}
		if(isset($_POST['noticeMetaData'])){
			$this->noticeMeta                    = $_POST['noticeMetaData'];
		}
		if(isset($_POST['noticeExpireDate'])){
			$noticeExpireDate                    = $_POST['noticeExpireDate'];
			$this->noticeMeta['lastDisplayDate'] = $noticeExpireDate['year'] . '-' . $noticeExpireDate['month'] . '-' . $noticeExpireDate['day'] . ' ' . $noticeExpireDate['hour'] . ':' . $noticeExpireDate['minute'] . ':00';
		}
		update_post_meta( $noticePostId, "GB_D_meta", $this->noticeMeta );
	}

	/**
	 * Duyuru Çöpe yollandığında çöpe yollanan duyurunun okundu  bilgileri silinir.
	 * add_action('wp_trash_post', array(&$this, 'moveTrashNotice'));
	 */
	public function moveTrashNotice() {
		$noticePostId   = get_the_ID();
		$noticePostType = get_post_type( $noticePostId );
		if ( $noticePostType != 'notice' ) {
			return;
		}
		$this->unmarkAsRead( $noticePostId );
	}

	/**
	 * Çöpten çıkarılan duyurunun meta bilgisini öntanımlı ayarlara döndürüyor.
	 * add_action( 'trash_to_publish', array( &$this, 'trashToPublishNotice' ) );
	 */
	public function trashToPublishNotice() {
		$noticePostId = get_the_ID();
		$expireDate   = dateStringToArray();
		$expireDate['month'] ++;
		$lastDisplayDate  = $expireDate['year'] . '-' . $expireDate['month'] . '-' . $expireDate['day'] . ' ' . $expireDate['hour'] . ':' . $expireDate['minute'] . ':00';
		$this->noticeMeta = array(
			'whoCanSee'       => 'everyone',
			'displayMode'     => 'window',
			'lastDisplayDate' => $lastDisplayDate,
			'type'            => ''
		);
		update_post_meta( $noticePostId, self::NOTICE_POST_META_KEY, $this->noticeMeta );
	}

	/**
	 * İd numarası verilmiş duyuru için;
	 * Giriş yapmış kullanıcı için user_meta tablosuna Duyurunun okundu bilgisini kaydeder
	 * Giriş yapmamış kullanıcı için duyurunun okundu bilgisini içeren çerez oluşturur
	 *
	 *
	 */
	public function markAsRead() {
		check_ajax_referer( 'getNoticesContainer', 'security' );
		if ( is_null( $_POST['noticeId'] ) && ! is_int( $_POST['noticeId'] ) ) {
			return;
		} else {
			$notice = new GB_Notice( $_POST['noticeId'] );
		}

		/*
		 * For multisite
		 */
		$blog_id = get_current_blog_id();
		/*
		 * if user loged in then add meta to user_meta else create a cookie
		 */
		if ( is_user_logged_in() ) {
			global $current_user;
			wp_get_current_user();
			$readedNoticesByCurrentUser   = get_user_meta( $current_user->ID, "GB_D_{$blog_id}_okunanDuyurular", true );
			$readedNoticesByCurrentUser[] = $notice->id;
			update_user_meta( $current_user->ID, "GB_D_{$blog_id}_okunanDuyurular", $readedNoticesByCurrentUser );

		} else {
			$expire = new DateTime( $notice->expireDate );
			$expire = $expire->getTimestamp();
			$name   = 'GB_D_' . $blog_id . '_' . md5( get_site_url( $blog_id ) . '|' . $notice->id );
			setcookie( $name, true, $expire, '/', $_SERVER['HTTP_HOST'], is_ssl(), true );
		}
		echo __( 'Notice mark as read successfully', GB_D_textDomainString );
	}

	/**
	 * Okundu  olarak  işaretlenen Duyurunun okundu  işaretini  kaldırır
	 *
	 * @param $noticeId
	 */
	public function unmarkAsRead( $noticeId ) {
		global $wpdb;
		$blog_id = get_current_blog_id();
		/**
		 * Users ids which has Notice meta data
		 */
		$user_ids = $wpdb->get_col( "SELECT user_id FROM $wpdb->usermeta where meta_key='GB_D_{$blog_id}_okunanDuyurular'" );

		foreach ( $user_ids as $user_id ) {
			$readedNoticesByCurrentUser = get_user_meta( $user_id, "GB_D_{$blog_id}_okunanDuyurular", true );
			if ( array_search( $noticeId, $readedNoticesByCurrentUser ) !== false ) {
				unset( $readedNoticesByCurrentUser[ array_search( $noticeId, $readedNoticesByCurrentUser ) ] );
				$readedNoticesByCurrentUser = array_merge( $readedNoticesByCurrentUser ); //for renumbered indexs
				update_user_meta( $user_id, "GB_D_{$blog_id}_okunanDuyurular", $readedNoticesByCurrentUser );
			} else {
				continue;
			}
		}
	}

	/**
	 * Create notice container with availeble Notices
	 * this function created for ajax response
	 */
	public function createNoticesContainerHtml() {
		if ( $this->isThereAnyNotice() ) {
			check_ajax_referer( "getNoticesContainer", 'security' );
			/**
			 * div which contain notices html. This div add wp_footer
			 * @var string
			 */
			$noticesHtmlContainer    = '<div class="noticeContainer notice-class">';
			$isThereWindowModeNotice = false;
			foreach ( $this->notices as $noticePost ) {
				$notice               = new GB_Notice( $noticePost->ID );
				$noticesHtmlContainer .= $notice->html;
				if ( $notice->displayMode === 'window' ) {
					$isThereWindowModeNotice = true;
				}
			}

			$noticesHtmlContainer .= '</div>';
			wp_send_json(array('noticesContainer'=>$noticesHtmlContainer,'isThereWindowModeNotice'=>$isThereWindowModeNotice));
		}
	}
}
