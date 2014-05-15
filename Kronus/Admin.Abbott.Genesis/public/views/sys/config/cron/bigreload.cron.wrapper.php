<?php

	require 'cron.helper.php';

	if( ( $pid = cronHelper::lock() ) !== FALSE )
	{
		$curl = curl_init( 'https://admin.pagcoregames.com/sys/config/cron/bigreload.php' );

		curl_setopt( $curl, CURLOPT_FRESH_CONNECT, FALSE );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 500 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $curl, CURLOPT_POST, FALSE );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $curl );

		$http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		curl_close( $curl );

		cronHelper::unlock();
	}

?>
