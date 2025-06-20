<?php

namespace Shipweb\LineConnect\Utilities;

class ActionParameterInjector {
    /**
     * パラメーターをアクションチェインの値で置き換えて返す
     * 
     * @param int $action_idx アクションのインデックス(1から始まる現在処理しているアクションの番号)
     * @param array $action_parameters アクションのパラメーター。キーは引数名値はその引数に渡す値の配列
     * @param array $chains アクションチェインの配列 各要素はtoとdataの連想配列
     * @return array $action_parameters 置き換えられたパラメーター
     */

    public static function inject_param($action_idx, $action_parameters, $chains) {
        if (is_array($chains)) {
            foreach ($chains as $chain) {
                //error_log(print_r($chain, true));
                if (isset($chain['to']) && isset($chain['data'])) {
                    // 'to': '1.param1' や '1.ary.0' や '1.atts.link.label' など
                    // 'to' の値を '.' で分割してアクション番号と引数名(+添字のパス)を取得
                    list($chain_action_idx, $param_path) = explode('.', $chain['to'], 2);
                    // アクション番号が現在のアクションインデックスと一致するか確認
                    // $param_pathが指し示す$action_parametersの該当する位置に$chain['data']をセットしたい。
                    // 'data': 文字列や、プレースホルダー 例)'{{$.return.1}}'
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
     * .で結合された形式のパスから、対象配列の該当場所へチェインデータをセットする関数
     * @param array $action_parameters 対象の配列
     * @param string $param_path .で結合された形式のパス
     * @param mixed $data セットするチェインデータ
     * @return array $action_parameters セットされた後の配列
     */
    private static function set_param_by_path($action_parameters, $param_path, $data) {
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
}
