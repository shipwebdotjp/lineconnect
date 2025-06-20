<?php

/**
 * Lineconnect Util Class
 *
 * Util Class
 *
 * @category Components
 * @package  Util
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

use \Shipweb\LineConnect\Scenario\Scenario;
use Shipweb\LineConnect\PostType\Audience\Audience as Audience;
use Shipweb\LineConnect\PostType\Message\Message as SLCMessage;
use Shipweb\LineConnect\Message\LINE\Builder;
use Shipweb\LineConnect\RichMenu\RichMenu;


class lineconnectUtil {




	/*

	public static function local_mktime() {
		$defaults = array(
			date("H"),
			date("i"),
			date("s"),
			date("n"),
			date("j"),
			date("Y"),
		);
		$args = func_get_args();
		$param = array_merge($defaults, $args);
		$offset = get_option('gmt_offset') * 60 * 60;
		return mktime($param[0], $param[1], $param[2], $param[3], $param[4], $param[5]); // + $offset;
	}

	public static function local_strtotime($time, $now = null) {
		$offset = get_option('gmt_offset') * 60 * 60;
		if ($now == null) {
			$now = time();
		}

		return strtotime($time, $now) + $offset;
	}
*/











	/**
	 * オブジェクトを再帰的に捜査してプレースホルダーを置換する関数
	 * @param array $obj 捜査対象のオブジェクト
	 * @param array $args 置換用のデータ
	 * @return array $object 置換後のオブジェクト
	 */
	/*
	public static function replacePlaceHolder($obj, $args) {
		if (is_object($obj)) {
			foreach ($obj as $key => $value) {
				$obj->{$key} = self::replacePlaceHolder($value, $args);
			}
		} elseif (is_array($obj)) {
			foreach ($obj as $key => $value) {
				$obj[$key] = self::replacePlaceHolder($value, $args);
			}
		} elseif (is_string($obj)) {
			if (is_array($args)) {
				foreach ($args as $key => $value) {
					if (is_string($value)) {
						$obj = str_replace('{{' . $key . '}}', $value, $obj);
					}
				}
			}
		}
		return $obj;
	}
		*/
}
