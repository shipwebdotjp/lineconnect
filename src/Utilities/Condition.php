<?php
/**
 * 条件比較
 */

namespace Shipweb\LineConnect\Utilities;

use \lineconnect;
use \lineconnectFunctions;

/**
 * 条件を比較する関数を集めたクラス
 */
class Condition
{

    /**
     * 複数の条件に一致するかどうかを検証
     * 
     * @param array $conditions 条件の配列
     * @param string $secret_prefix channel prefix
     * @param string $lineUserId LINEユーザーID
     * @return bool 条件に一致するかどうか
     */
    public static function evaluate_conditions(array $condition_grouped, string $secret_prefix, string $lineUserId): bool
    {
        $condition_matches = array();
        foreach ( $condition_grouped['conditions'] as $condition ) {
            $condition_matches[] = self::evaluate_condition($condition, $secret_prefix, $lineUserId);
        }
        if ( isset( $condition_grouped['operator'] ) && $condition_grouped['operator'] === 'or' && ! in_array( true, $condition_matches, true ) ) {
            return false;
        } elseif ((!isset($condition_grouped['operator']) || $condition_grouped['operator'] === 'and') && in_array(false, $condition_matches, true)) {
            return false;
        }
        return true;
    }

    /**
     * 単一の条件に一致するかどうかを検証
     * 
     * @param array $condition 条件データ
     * @param string $secret_prefix channel prefix
     * @param string $lineUserId LINEユーザーID
     * @return bool 条件に一致するかどうか
     */
    public static function evaluate_condition(array $condition, string $secret_prefix, string $lineUserId): bool
    {
        $isConditionMatched = true;
        
        if (!isset($condition['type'])) {
            return true;
        }

        switch ($condition['type']) {
            case 'channel':
                $isConditionMatched = self::evaluate_channel($condition['secret_prefix'], $secret_prefix);
                break;
            case 'destination':
                $isConditionMatched = self::evaluate_destination($condition['destination'], $lineUserId);
                break;
            case 'link':
                $isConditionMatched = self::evaluate_link($condition['link'], $secret_prefix, $lineUserId);
                break;
            case 'role':
                $isConditionMatched = self::evaluate_role($condition['role'], $secret_prefix, $lineUserId);
                break;
            case 'usermeta':
                $isConditionMatched = self::evaluate_usermeta($condition['usermeta'], $secret_prefix, $lineUserId);
                break;
            case 'profile':
                $isConditionMatched = self::evaluate_profile($condition['profile'], $secret_prefix, $lineUserId);
                break;
            case 'group':
                $isConditionMatched = self::evaluate_conditions($condition['condition'], $secret_prefix, $lineUserId);
                break;
            default:
                // 未定義の条件タイプの場合は true を返す
                return true;
        }
        if (isset($condition['not']) && $condition['not'] === true) {
            return !$isConditionMatched; // not condition reverses the match result
        }
        return $isConditionMatched;
    }

    /**
     * 対象のチャネルかどうかを検証
     * 
     * @param array $secret_prefixs secret_prefixs
     * @param string $secret_prefix secret_prefix
     * @return bool
     */
    public static function evaluate_channel(array $secret_prefixs, string $secret_prefix): bool
    {
        if(!empty($secret_prefixs) && !in_array($secret_prefix, $secret_prefixs)){
            return false;
        }
        return true;
    }

    /**
     * 対象が指定されたタイプかどうか、LINEユーザーIDが指定されている場合は、対象が含まれているかどうかを検証
     *
     * @param array $destination destination
     * @param string $lineUserId LINEユーザーID
     * @return bool
     */
    public static function evaluate_destination(array $destination, string $lineUserId): bool
    {
        switch ($destination['type']) {
            case 'user':
                // return if $lineUserId starts with U and in $destination['lineUserId'] if $destination['lineUserId'] is not empty
                if(strpos($lineUserId, 'U') !== 0 || (!empty($destination['lineUserId']) && !in_array($lineUserId, $destination['lineUserId']))){
                    return false;
                }
                return true;
            case 'group':
                if(strpos($lineUserId, 'C') !== 0 || (!empty($destination['groupId']) && !in_array($lineUserId, $destination['groupId']))){
                    return false;
                }
                return true;
            case 'room':
                if(strpos($lineUserId, 'R') !== 0 || (!empty($destination['roomId']) && !in_array($lineUserId, $destination['roomId']))){
                    return false;
                }
                return true;
        }
    }

        /**
     * LINEユーザーの連携状態が条件に一致するかどうかを検証
     * 
     * @param string $link_condition 'linked' または 'unlinked'
     * @param string $secret_prefix
     * @param string $lineUserId
     * @return bool 一致すれば true、そうでなければ false
     */
    public static function evaluate_link(string $link_condition, string $secret_prefix, string $lineUserId): bool
    {
        // lineconnect::get_wpuser_from_line_id() は、LINEユーザーIDからWordPressユーザーを取得する関数と仮定
        $user = lineconnect::get_wpuser_from_line_id($secret_prefix, $lineUserId);

        // 'linked' の場合はユーザーが存在していること、'unlinked' の場合は存在していないことが条件
        if ($link_condition === 'linked' && $user === false) {
            return false;
        }
        if ($link_condition === 'unlinked' && $user !== false) {
            return false;
        }
        return true;
    }

    /**
     * LINEユーザーが指定のロールを持っているかどうかを検証
     * 
     * @param array  $roles         チェックするロールの配列
     * @param string $secret_prefix
     * @param string $lineUserId
     * @return bool ユーザーがいずれかのロールを持っていれば true、そうでなければ false
     */
    public static function evaluate_role(array $roles, string $secret_prefix, string $lineUserId): bool
    {
        // ユーザー取得
        $user = lineconnect::get_wpuser_from_line_id($secret_prefix, $lineUserId);
        if ($user === false) {
            return false;
        }

        // ユーザーが持っているロールを配列に変換
        $user_roles = (array) $user->roles;
        // 指定されたロールとユーザーのロールに共通するものがあるかチェック
        $common_roles = array_intersect($roles, $user_roles);
        if (empty($common_roles)) {
            return false;
        }
        return true;
    }

    /**
     * LINEユーザーのユーザーメタが指定の条件に一致するかどうかを検証
     * 
     * @param array $usermeta_conditions ユーザーメタの条件配列
     * @param string $secret_prefix チャンネルのプレフィックス
     * @param string $lineUserId LINEユーザーID
     * @return bool すべての条件に一致すればtrue、そうでなければfalse
     */
    public static function evaluate_usermeta(array $usermeta_conditions, string $secret_prefix, string $lineUserId): bool
    {
        // ユーザー取得 連携していない場合メタキーは撮れないので、全てスキップ(trueを返す)
        $user = lineconnect::get_wpuser_from_line_id($secret_prefix, $lineUserId);
        if ($user === false) {
            return true;
        }

        // 各メタデータの条件をチェック
        foreach ($usermeta_conditions as $condition) {
            $meta_value = get_user_meta($user->ID, $condition['key'], true);
            
            // 比較演算子が指定されていない場合は=をデフォルトとする
            if (!isset($condition['compare'])) {
                $condition['compare'] = '=';
            }

            // EXISTS/NOT EXISTSの処理
            if ($condition['compare'] === 'EXISTS') {
                if ($meta_value === '') {
                    return false;
                }
                continue;
            }
            if ($condition['compare'] === 'NOT EXISTS') {
                if ($meta_value !== '') {
                    return false;
                }
                continue;
            }

            // EXISTS/NOT EXISTS以外の場合、メタキーがない場合は比較自体を行わない(次の条件へ)
            if ($meta_value === '') {
                continue;
            }


            // 配列を使用する比較演算子の処理
            if (in_array($condition['compare'], ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])) {
                // valuesがなければ比較を行わず、次の条件へ
                if (isset($condition['values']) && !self::compare($condition['compare'], $meta_value, $condition['values'])) {
                    return false;
                }
                continue;
            }

            // 単一値を使用する比較演算子の処理 valueがなければ比較を行わず、次の条件へ
            if (!isset($condition['value'])) {
                continue;
            }
            if (!self::compare($condition['compare'], $meta_value, $condition['value'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * LINEユーザーのプロフィールが指定の条件に一致するかどうかを検証
     * 
     * @param array $profile_conditions プロフィールの条件配列
     * @param string $secret_prefix チャンネルのプレフィックス
     * @param string $lineUserId LINEユーザーID
     * @return bool すべての条件に一致すればtrue、そうでなければfalse
     */
    public static function evaluate_profile(array $profile_conditions, string $secret_prefix, string $lineUserId): bool
    {
        $functions = new lineconnectFunctions();
        $functions->set_secret_prefix($secret_prefix);

        // 各プロフィールデータの条件をチェック
        foreach ($profile_conditions as $condition) {
            $profile_value = $functions->get_user_profile_value($condition['key'], $lineUserId, $secret_prefix);

            // 比較演算子が指定されていない場合は=をデフォルトとする
            if (!isset($condition['compare'])) {
                $condition['compare'] = '=';
            }
            
            // EXISTS/NOT EXISTSの処理
            if ($condition['compare'] === 'EXISTS') {
                if ($profile_value === null) {
                    return false;
                }
                continue;
            }
            if ($condition['compare'] === 'NOT EXISTS') {
                if ($profile_value !== null) {
                    return false;
                }
                continue;
            }

            // EXISTS/NOT EXISTS以外の場合、プロフィール値が存在しない場合は比較自体を行わない
            if ($profile_value === null) {
                continue;
            }

            // 配列を使用する比較演算子の処理
            if (in_array($condition['compare'], ['IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'])) {
                if (isset($condition['values']) && !self::compare($condition['compare'], $profile_value, $condition['values'])) {
                    return false;
                }
                continue;
            }

            // 単一値を使用する比較演算子の処理
            if (!isset($condition['value'])) {
                continue;
            }
            if (!self::compare($condition['compare'], $profile_value, $condition['value'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $operator 比較演算子
     * @param string $value 比較する値
     * @param string|array $target 比較対象
     * @return bool 比較結果
     */
    public static function compare($operator, $value, $target)
    {
        switch ($operator) {
            case '=':
                return $value == $target;
            case '!=':
                return $value != $target;
            case '>':
                return $value > $target;
            case '<':
                return $value < $target;
            case '>=':
                return $value >= $target;
            case '<=':
                return $value <= $target;
            case 'IN':
                return is_array($target) && in_array($value, $target);
            case 'NOT IN':
                return is_array($target) && !in_array($value, $target);
            case 'LIKE':
            case 'REGEXP':
                return preg_match('/' . $target . '/', $value) === 1;
            case 'NOT LIKE':
            case 'NOT REGEXP':
                return preg_match('/' . $target . '/', $value) !== 1;
            case 'BETWEEN':
                return is_array($target) && $value >= $target[0] && $value <= $target[1];
            case 'NOT BETWEEN':
                return is_array($target) && ($value < $target[0] || $value > $target[1]);
            default:
                return false;
        }
    }
}
