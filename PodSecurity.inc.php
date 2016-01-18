<?php

/**
 *
 * @file PODSecurity.inc.php
 *
 * Copyright (c) 2012 Aldi Alimucaj
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * This Class helps building an HMAC for the request which charges the user for usin POD.
 * Some code was contributed from Epubli (www.epubli.de)
 * @author a
 *
 */

class PodSecurity {

	const ALGORITHM = 'sha256';
	const FIELDTIMESTAMP = 'timestamp';
	const FIELDSIGNATURE = 'signature';

	const HASH_ALGORITHM = 'sha256';
	const FIELD_timestamp = 'timestamp';
	const DEFAULT_TOLERANCE_INTERVAL = 300; # seconds
	const FLATTEN_SEPARATOR = '&';
	const MINIMUM_PHP_VERSION = '5.2.1';
	const FLOAT_PRECISION = 6;

	const ERR_OK                =   0;
	const ERR_INVALID           =   1; //                             Bit 0
	const ERR_SIGNATURE         =   3; //   2 | self::ERR_INVALID,    Bit 0,1
	const ERR_SIGNATURE_MISSING =   7; //   4 | self::ERR_SIGNATURE   Bit 0,1,2
	const ERR_SIGNATURE_WRONG   =  11; //   8 | self::ERR_SIGNATURE   Bit 0,1,3
	const ERR_TIMESTAMP         =  17; //  16 | self::ERR_INVALID     Bit 0,4
	const ERR_TIMESTAMP_LATE    =  49; //  32 | self::ERR_TIMESTAMP   Bit 0,4,5
	const ERR_TIMESTAMP_EARLY   =  81; //  64 | self::ERR_TIMESTAMP   Bit 0,4,6
	const ERR_NOKEY             = 129; // 128 | self::ERR_INVALID     Bit 0,7



	static public function flatten( Array $data ) {
		// Please don't use http_build_query(), since it uses '+' for spaces,
		// which is not strictly RFC 1738 compliant, and will produce invalid
		// signatures.
		return join( self::FLATTEN_SEPARATOR,
		self::_flattenDescend( $data, null )
		);
	}

	// recursion helper:
	private static function _flattenDescend( $hash, $parentvar=null ) {
		$pairs = array();
		foreach ( $hash as $key => $value ) {
			// map empty values to the empty string
			if ( in_array( $key, array( null, false, '' ), true ) ) {
				$pairs[] = rawurlencode( $key ) . '=';
				continue;
			}
			if ( is_array( $value ) ) {
				$pairs = array_merge( $pairs, self::_flattenDescend(
				$value,
				( $parentvar === null ) ? $key : sprintf('%s[%s]',$parentvar,$key)
				)
				);
			}
			elseif ( is_scalar( $value ) ) {
				$pairs[] = sprintf(
          '%s=%s',
				( $parentvar === null ) ? rawurlencode( $key)
				: sprintf( '%s[%s]', $parentvar, $key ),
				rawurlencode( $value )
				);
			}
			else {
				throw new Exception( 'Cannot flatten object of type ' . gettype( $value ) );
			}
		}
		ksort( $pairs );
		return $pairs;
	}


	static public function getTimestamp( $t = null ) {
		$t = microtime( true );

		$format = sprintf(
	      	'%s:%02d.%s',
			gmdate('Y-m-d\TH:i',$t),
			gmdate('s',$t),
			substr( self::bcsub( $t, intval( $t ), self::FLOAT_PRECISION), 2 )
		);

		// remove trailing zeroes, add utc/gmt-Timezone 'Z':
		return preg_replace( '/(\.)?0+$/', '', $format ) . 'Z';
	}
	
	private function bcsub($num1, $num2) {
		return $num1 - $num2;
	}

	public function sign(Array $data, $key)
	{
		if ( ! array_key_exists( self::FIELD_timestamp, $data ) ) {
			self::array_unshift_assoc($data, self::FIELD_timestamp, self::getTimestamp());
		}

		$data['signature'] = self::getSignature( $data, $key );
		return $data;
	}

	public function array_unshift_assoc(&$arr, $key, $val)
	{
		$arr = array_reverse($arr, true);
		$arr[$key] = $val;
		return array_reverse($arr, true);
	}

	public function getSignature(Array $data, $key)
	{
		unset($data['signature']);
		ksort( $data );
		return base64_encode( hash_hmac( self::HASH_ALGORITHM,
		self::flatten( $data ),
		$key,
		true
		)
		);
	}
}
?>