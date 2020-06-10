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
	$datearr         = array(
		'altDate' => substr( $dateString, 0, 10 ),
		'year'    => substr( $dateString, 0, 4 ),
		'month'   => substr( $dateString, 5, 2 ),
		'day'     => substr( $dateString, 8, 2 ),
		'hour'    => substr( $dateString, 11, 2 ),
		'minute'  => substr( $dateString, 14, 2 ),
		'second'  => substr( $dateString, 17, 2 )
	);
	$datearr['date'] = $datearr['day'] . '.' . $datearr['month'] . '.' . $datearr['year'];

	return $datearr;
}

/**
 * if wordress in debuh mode write to log file log data
 *
 * @param GB_Notice $notice
 * @param string $action
 * @param array $additionalData
 */
function setLog( $notice, $action = '' , $additionalData = array()) {
	if ( true === WP_DEBUG ) {
		$logData = new stdClass();
		$logData->ip =$_SERVER['REMOTE_ADDR'];
		$logData->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$logData->date = date_i18n( 'Y-m-d H:i:s' );
		$logData->noticeId = $notice->id;
		$logData->noticeTitle = $notice->title;
		$logData->noticeContent = $notice->content;
		$logData->expireDate = $notice->expireDate;
		$logData->logedinUser = wp_get_current_user()->user_login;
		$logData->action = $action;
		$logData = (object) array_merge( (array)$logData, $additionalData );

		file_put_contents( GB_Notices_Plugin::$path . "/log.txt", json_encode($logData)."\n", FILE_APPEND );
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