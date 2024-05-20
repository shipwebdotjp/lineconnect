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

class lineconnectUtil {
	public static function is_empty( $var = null ) {
		if ( empty( $var ) && 0 !== $var && '0' !== $var ) { // 論理型のfalseを取り扱う場合は、更に「&& false !== $var」を追加する
			return true;
		} else {
			return false;
		}
	}

	/**
	 * パラメーターをアクションチェインの値で置き換えて返す
	 * 
	 * @param int $action_idx アクションのインデックス(1から始まる現在処理しているアクションの番号)
	 * @param array $action_parameters アクションのパラメーター。キーは引数名値はその引数に渡す値の配列
	 * @param array $chains アクションチェインの配列 各要素はtoとdataの連想配列
	 * @return array $action_parameters 置き換えられたパラメーター
	 */

	public static function inject_param($action_idx, $action_parameters, $chains) {
		if(is_array($chains)){
			foreach ($chains as $chain) {
				//error_log(print_r($chain, true));
				if (isset($chain['to']) && isset($chain['data'])) {
					// 'to': '1.param1' や '1.ary.0' や '1.atts.link.label' など
					// 'to' の値を '.' で分割してアクション番号と引数名(+添字のパス)を取得
					list($chain_action_idx, $param_path) = explode('.', $chain['to'], 2);				
					// アクション番号が現在のアクションインデックスと一致するか確認
					// $param_pathが指し示す$action_parametersの該当する位置に$chain['data']をセットしたい。
					if ((int)$chain_action_idx === $action_idx + 1) {
						// $param_pathが指し示す$action_parametersの該当する位置に$chain['data']をセットする
						$action_parameters = self::set_param_by_path($action_parameters, $param_path, $chain['data']);
					}
				}
			}
		}
		// error_log(print_r($action_parameters, true));
		return $action_parameters;
	}

	/**
	 * .で結合された形式のパスから、対象配列の該当場所へデータをセットする関数
	 * @param array $action_parameters 対象の配列
	 * @param string $param_path .で結合された形式のパス
	 * @param mixed $data セットするデータ
	 * @return array $action_parameters セットされた後の配列
	 */
	public static function set_param_by_path($action_parameters, $param_path, $data)
    {
        $keys = explode('.', $param_path); // パスパラメータを配列に変換
        $current_data = &$action_parameters; // 参照渡しで更新できるようにする

        foreach ($keys as $key) {
            if (!isset($current_data[$key])) {
                $current_data[$key] = []; // 存在しないキーの場合は空の配列を作成
            }
            $current_data = &$current_data[$key]; // 現在のデータオブジェクトを次のキーのデータオブジェクトに更新
        }

        $current_data = $data; // 最終的なキーでデータをセット
        return $action_parameters;
    }

	public static function prepare_arguments($parameters, $parameters_schemas, $injection_data){
		if( is_array($parameters_schemas) ){
			foreach ( $parameters_schemas as $idx => $parameter_schema ) {
				$parameter_schema['name'] = $parameter_schema['name'] ?? 'param' . $idx;
				if( isset( $parameters[ $parameter_schema['name']]) ){
					//Extract and replace placeholders contained within parameters
					// ex "Hello, {{$.return.0.name}}!" => "Hello, John!"
					// $replaced = $parameters[ $parameter_schema['name'] ];
					// if( preg_match('/^{{(.*?)}}$/', $replaced, $matches) ){
					// 	$replaced = self::replace_injection_data($matches[1], $injection_data);
					// }else{
					// 	$replaced = preg_replace_callback('/{{(.*?)}}/', function($matches) use (&$injection_data){
					// 		return self::replace_injection_data($matches[1], $injection_data);
					// 	},$replaced);
					// }
					$parameters[ $parameter_schema['name'] ] = self::replace_object_placeholder($parameters[ $parameter_schema['name'] ], $injection_data);
				}
			}
		}
		return $parameters;
	}

	public static function replace_object_placeholder($object, $injection_data){
		if(is_object($object)){
			foreach($object as $key => $value){
				$object->{$key} = self::replace_object_placeholder($value, $injection_data);
			}
		}else if(is_array($object)){
			foreach($object as $key => $value){
				$object[$key] = self::replace_object_placeholder($value, $injection_data);
			}
		}elseif(is_string($object)){
			$object = preg_replace_callback('/{{(.*?)}}/', function($matches) use (&$injection_data){
				return self::replace_injection_data($matches[1], $injection_data);
			}, $object);
		}
		return $object;
	}

	public static function replace_injection_data($injection_path, $injection_data){
		$injection_path = trim($injection_path);

		$value = $injection_data;
		foreach( explode( '.', $injection_path ) as $path_part ){
			if($path_part === '$'){
				continue;
			}elseif(isset($value[$path_part])){
				$value = $value[$path_part];
			}else{
				$value = null;
				break;
			}
		}
		// error_log("value:".print_r($value,true));
		return $value;
	}


	public static function arguments_object_to_array( $arguments_parsed, $parameters_array ) {
		$arguments_array = array();
		if( is_array($parameters_array) ){
			foreach ( $parameters_array as $idx => $parameter_schema ) {
				$parameter_schema['name'] = $parameter_schema['name'] ?? 'param' . $idx;
				if ( isset( $arguments_parsed[ $parameter_schema['name'] ] ) ) {
					$arguments_array[] = $arguments_parsed[ $parameter_schema['name'] ];
				}
			}
		}
		return $arguments_array;
	}
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
		$param = array_merge( $defaults, $args );
		$offset = get_option('gmt_offset') * 60 * 60;
		return mktime( $param[0], $param[1], $param[2], $param[3], $param[4], $param[5]);// + $offset;
	}

	public static function local_strtotime( $time, $now = null ) {
		$offset = get_option('gmt_offset') * 60 * 60;
		if( $now == null ) {
			$now = time();
		}
		
		return strtotime( $time, $now ) + $offset; 
	}

	public static function get_line_message_builder($source){
		if ( is_string( $source ) ) {
			return new \LINE\LINEBot\MessageBuilder\TextMessageBuilder( $source );
		} elseif ( $source instanceof \LINE\LINEBot\MessageBuilder ) {
			return $source;
		} else {
			return new \LINE\LINEBot\MessageBuilder\TextMessageBuilder( print_r( $source, true ) );
		}
	}

	public static function line_id_row($line_id, $secret_prefix){
		global $wpdb;
		$table_name_line_id = $wpdb->prefix . lineconnectConst::TABLE_LINE_ID;
		$line_id_row        = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table_name_line_id} WHERE line_id = %s and channel_prefix = %s",
			array(
				$line_id,
				$secret_prefix,
			)
		),
		'ARRAY_A'
		);
		return $line_id_row;
	}
}
