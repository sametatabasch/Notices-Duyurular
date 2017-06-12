<?php
/**
 * Created by PhpStorm.
 * User: sametatabasch
 * Date: 11.06.2017
 * Time: 14:59
 */

/**
 * @param null $dateString
 *
 * @return array
 */
function dateStringToArray( $dateString = null ) {
	/*
	 * Eğer tarih belirtilmemişse şuanki zaman alınıyor
	 */
	if ( is_null( $dateString ) ) {
		$dateString = date_i18n( 'Y-m-d H:i:s' );
	}
	$datearr = array(
		'year'   => substr( $dateString, 0, 4 ),
		'month'  => substr( $dateString, 5, 2 ),
		'day'    => substr( $dateString, 8, 2 ),
		'hour'   => substr( $dateString, 11, 2 ),
		'minute' => substr( $dateString, 14, 2 ),
		'second' => substr( $dateString, 17, 2 )
	);

	return $datearr;
}

/**
 * @param $notice GB_Notice
 * @param $action string
 */
function setLog( $notice, $action = '' ) {
	if ( true === WP_DEBUG ) {
		$data = "
        {
            'ip': '" . $_SERVER['REMOTE_ADDR'] . "',
            'user_agent': '" . $_SERVER['HTTP_USER_AGENT'] . "',
            'date': '" . date_i18n( 'Y-m-d H:i:s' ) . "',
            'noticeId': '" . $notice->id . "',
            'noticeTitle':'" . $notice->title . "',
            'noticeContent:'" . $notice->content . "',
            'expireDate': '" . $notice->expireDate . "',
            'logedinUser': '" . wp_get_current_user()->user_login . "',
            'action':'" . $action . "'
        }";
		file_put_contents( GB_Notices_Plugin::$path . "/log.txt", $data, FILE_APPEND );
	}
}

/**
 * Create select option list for month
 *
 * @param $selectedMonth string january => 1
 *
 * @return string
 */
function createMonthOptionList( $selectedMonth ) {
	global $wp_locale;
	$monthOptionList = '';
	for ( $i = 1; $i <= 12; $i ++ ) {
		$monthOptionList .= '
			  <option ' . selected( zeroise( $i, 2 ), $selectedMonth, false ) . ' value="' . zeroise( $i, 2 ) . '">'
		                    . zeroise( $i, 2 ) . ' - ' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . '
			  </option>';
	}

	return $monthOptionList;
}