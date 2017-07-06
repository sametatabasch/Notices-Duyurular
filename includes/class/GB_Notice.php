<?php

/**
 * Created by PhpStorm.
 * User: sametatabasch
 * Date: 10.06.2017
 * Time: 22:25
 */
class GB_Notice extends GB_Notices {
	/**
	 * Notice Post id
	 * @var integer $id
	 */
	public $id;

	/**
	 * Duyuru Başlığı
	 * Title of Notice
	 * @var string $title
	 */
	public $title;

	/**
	 * Duyuru metni
	 * Content of Notice
	 * @var string $content
	 */
	public $content;
	/**
	 * Duyuru CSS sınıfı
	 * CSS class of Notice
	 * @var string $htmlClass
	 */
	public $htmlClass;

	/**
	 * Duyurunun HTML çıktısı
	 * HTML output of Notice
	 * @var string $html
	 */
	public $html = '';

	/**
	 * Duyurunun HTML DOM id bilgisi
	 * HTML DOM id string of Notice
	 * @var string $htmlId
	 */
	public $htmlId;

	/**
	 * Type of Duyuru
	 * notice-white | notice-red | notice-green | notice-blue
	 * @var string $color
	 */
	public $color;

	/**
	 * Size of Notice which detected max and min width of notice
	 * @var string $size
	 */
	public $size;

	/**
	 * display mode of Notice window or bar
	 * @var string $displayMode
	 */
	public $displayMode;

	/**
	 * Display time in second of notice
	 * @var string $displayTime second
	 */
	public $displayTime = 5;

	/**
	 * Duyurunun yayınlanacağı son tarih
	 * Expire date of Notice
	 * @var string $expireDate 'Y-m-d H:i:s'
	 */
	public $expireDate;

	/**
	 * Wordpres Post Meta for Notice
	 * @var array
	 */
	public $postMeta;

	/**
	 * GB_Notice constructor.
	 *
	 * @param $id integer Wordpress Post id
	 */
	function __construct( $id ) {
		parent::__construct();
		$this->id = $id;
		$this->getPostMeta();
		$this->setExpireDate();
		if ( $this->isExpired() ) {
			$this->sendToTrash();
		} else {
			/*
			 * if notice is not read and everyone can see or user is logged in then set notice html
			 */
			if ( ! $this->isRead() && ( $this->postMeta['whoCanSee'] == 'everyone' || is_user_logged_in() ) ) {
				$this->setHtml();
			}
		}

	}

	/**
	 * Get all post meta data of notice to Notice->postMeta
	 * 'whoCanSee' => (everyone,onlyUser)
	 * 'displayMode' =>  (window,bar)
	 * 'type' => ('',alert-white,alert-error,alert-info,alert-success)
	 * 'lastDisplayDate' => 'Y-m-d H:i:s',
	 * 'noBorder' => (on,null)
	 */
	private function getPostMeta() {
		$this->postMeta = get_post_meta( $this->id, self::NOTICE_POST_META_KEY, true );
	}

	/**
	 * Set Notice title to Notice->title
	 */
	private function setTitle() {
		$this->title = get_the_title( $this->id ) != '' ? '<h4>' . ucfirst( get_the_title( $this->id ) ) . '</h4>' : null;
	}

	/**
	 * Set Notice type to Notice->color
	 * notice-white | notice-red | notice-green | notice-blue
	 */
	private function setColor() {
		$this->color = $this->postMeta['color'];
	}

	/**
	 * Set display mode of Notice to Notice->displayMode
	 */
	private function setDisplayMode() {
		$this->displayMode = $this->postMeta['displayMode'];
	}

	/**
	 * Set display time in second. Default 5 second
	 */
	private function setDisplayTime() {
		if ( intval( $this->postMeta['displayTime'] ) !== 0 ) {
			$this->displayTime = $this->postMeta['displayTime'];
		} else {
			$this->displayTime = parent::$defaultDisplayTime;
		}
	}

	/**
	 * Set size of Notice to Notice->size
	 */
	private function setSize() {
		$this->size = $this->postMeta['size'];
	}

	/**
	 * Set content of Notice to Notice->content
	 */
	private function setContent() {
		$this->content = do_shortcode( wpautop( get_post_field( 'post_content', $this->id ) ) );
	}

	/**
	 * set expire date of Notice to Notice->expireDate
	 */
	private function setExpireDate() {
		$this->expireDate = $this->postMeta['lastDisplayDate'];
	}

	/**
	 * Set HTML DOM id of Notice to Notice->htmlId
	 */
	private function setHtmlId() {
		$this->htmlId = $this->displayMode . '-' . $this->id;
	}

	/**
	 * Set html class of Totice to Notice->htmlClass
	 */
	private function setHtmlClass() {
		$border          = isset( $this->postMeta['noBorder'] ) ? 'noborder' : '';
		$this->htmlClass = '' . $this->displayMode . ' .md-effect-2 ' . $border;
	}

	/**
	 * Set HTML output of Notice to Notice->html
	 */
	private function setHtml() {
		$this->setDisplayMode();
		$this->setColor();
		$this->setSize();
		$this->setHtmlId();//must after setDisplayMode();
		$this->setHtmlClass();//must after setDisplayMode();
		$this->setTitle();
		$this->setContent();
		$this->setDisplayTime();

		$this->html = '
		<div id="' . $this->htmlId . '" class="' . $this->htmlClass . '" data-size="' . $this->size . '" data-color="' . $this->color . '" data-displayTime="' . $this->displayTime . '">
    		<div class="window-content">
    			' . $this->title . '
				<div>
    				' . $this->content . '
    				<button type="button" class="close">&times;</button>
    			</div>
			</div>
			<div class="window-footer">
              <progress value="100" max="100"></progress>
  			</div>
		</div>';

	}

	/**
	 * Check Notice is readed by current user
	 * @return bool
	 */
	public function isRead() {
		global $blog_id;
		if ( is_user_logged_in() ) {
			global $current_user;
			wp_get_current_user();
			$readedNoticesByCurrentUser = get_user_meta( $current_user->ID, 'GB_D_' . $blog_id . '_okunanDuyurular', true );

			return empty( $readedNoticesByCurrentUser ) ? false : in_array( $this->id, $readedNoticesByCurrentUser );
		} else {
			$cookieName = 'GB_D_' . $blog_id . '_' . md5( get_site_url( $blog_id ) . '|' . $this->id );

			return isset( $_COOKIE[ $cookieName ] );
		}
	}

	/**
	 * Check is notice expired
	 * todo 2iki saat zaman farkı oluyor ???
	 * @return bool
	 */
	public function isExpired() {
		$expireDate = new DateTime( $this->expireDate );
		$now        = new DateTime( 'now' );
		if ( $expireDate < $now ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Send notice to trash
	 */
	private function sendToTrash() {
		if ( get_post_type( $this->id ) === 'notice' ) {
			wp_trash_post( $this->id );
		}
		setLog( $this, 'sendToTrash' );
	}
}