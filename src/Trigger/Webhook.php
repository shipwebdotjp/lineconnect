<?php

/**
 * Webhookトリガーの条件
 */

namespace Shipweb\LineConnect\Trigger;

use \lineconnect;
use \lineconnectUtil;
use Shipweb\LineConnect\Utilities\Condition;

class Webhook {

    public static function check_trigger_condition($trigger, $event, $secret_prefix) {
        if ($trigger['type'] !== $event->{'type'}) {
            return false;
        }

        if ($trigger['type'] === 'message') {
            if ($trigger['message']['type'] !== $event->{'message'}->{'type'}) {
                return false;
            }
            if ($trigger['message']['type'] === 'text') {
                $result = self::check_webhook_message_text_condition($trigger['message']['text'], $event->{'message'}->{'text'});
                if (! $result) {
                    // error_log("check_webhook_message_text_condition:".$result);
                    return false;
                }
            }
        } elseif ($trigger['type'] === 'postback') {
            if (! \Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($trigger['postback']['data'])) {
                $result = self::check_webhook_message_text_condition($trigger['postback']['data'], $event->{'postback'}->{'data'});
                if (! $result) {
                    return false;
                }
            }
            if (! \Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($trigger['postback']['params'])) {
                $result = self::check_webhook_message_postback_param_condition($trigger['postback']['params'], $event->{'postback'}->{'params'});
                if (! $result) {
                    return false;
                }
            }
        } elseif ($trigger['type'] === 'follow') {
            if ($trigger['follow']['isUnblocked'] === 'add' && $event->{'follow'}->{'isUnblocked'} === true) {
                return false;
            } elseif ($trigger['follow']['isUnblocked'] === 'unblocked' && $event->{'follow'}->{'isUnblocked'} === false) {
                return false;
            }
        }
        // error_log(print_r($trigger['condition'],true));
        if (! empty($trigger['condition'])) {
            $result = self::check_webhook_condition($trigger['condition'], $event, $secret_prefix);
            if (! $result) {
                // error_log("check_webhook_condition:".$result);

                return false;
            }
        }
        return true;
    }

    public static function check_webhook_message_text_condition($message, $data) {
        $condition_results = array();
        foreach ($message['conditions'] as $condition) {
            if (isset($condition['type']) && $condition['type'] === 'source') {
                $result_bool         = self::check_webhook_message_text_keyword_condition($condition['source'], $data);
                $condition_results[] = isset($condition['not']) && $condition['not'] === true  ? ! $result_bool : $result_bool;
            } elseif (isset($condition['type']) && $condition['type'] === 'group') {
                $condition_results[] = self::check_webhook_message_text_condition($condition['condition'], $data);
            }
        }
        // error_log(print_r($condition_results, true));
        if (isset($message['operator'])  && $message['operator'] === 'or' && ! empty($condition_results) && ! in_array(true, $condition_results, true)) {
            return false;
        } elseif ((! isset($message['operator']) || $message['operator'] === 'and') && ! empty($condition_results) && in_array(false, $condition_results, true)) {
            return false;
        }
        return true;
    }

    /**
     * キーワード条件をチェックする
     * @param array $keyword_condition キーワード条件
     * @param string $data チェック対象のデータ
     * @param string $keyword_key キーワードが格納されているキー名 (デフォルト: 'keyword')
     * @return bool 条件に一致すればtrue、そうでなければfalse
     */
    public static function check_keyword_match($keyword_condition, $data, $keyword_key = 'keyword') {
        if (! isset($keyword_condition['match']) || $keyword_condition['match'] === 'contains') {
            if (isset($keyword_condition[$keyword_key]) && strpos($data, $keyword_condition[$keyword_key]) === false) {
                return false;
            }
        } elseif (isset($keyword_condition['match']) && $keyword_condition['match'] === 'equals') {
            if (isset($keyword_condition[$keyword_key]) && $keyword_condition[$keyword_key] !== $data) {
                return false;
            }
        } elseif (isset($keyword_condition['match']) && $keyword_condition['match'] === 'startsWith') {
            if (isset($keyword_condition[$keyword_key]) && strpos($data, $keyword_condition[$keyword_key]) !== 0) {
                return false;
            }
        } elseif (isset($keyword_condition['match']) && $keyword_condition['match'] === 'endsWith') {
            if (isset($keyword_condition[$keyword_key]) && substr($data, -strlen($keyword_condition[$keyword_key])) !== $keyword_condition[$keyword_key]) {
                return false;
            }
        } elseif (isset($keyword_condition['match']) && $keyword_condition['match'] === 'regexp') {
            if (isset($keyword_condition[$keyword_key]) && ! preg_match('/' . $keyword_condition[$keyword_key] . '/', $data)) {
                return false;
            }
        }
        return true;
    }

    /**
     * クエリ条件をチェックする
     * @param array $query_condition クエリ条件
     * @param string $data チェック対象のデータ (クエリ文字列)
     * @return bool 条件に一致すればtrue、そうでなければfalse
     */
    public static function check_query_match($query_condition, $data) {
        $query_array = array();
        foreach ($query_condition['parameters'] as $param) {
            if (isset($param['key']) && isset($param['value'])) {
                $query_array[$param['key']] = $param['value'];
            }
        }
        $data_array = array();
        parse_str($data, $data_array);

        if (! isset($query_condition['match']) || $query_condition['match'] === 'contains') {
            if (count(array_intersect_assoc($query_array, $data_array)) !== count($query_array)) {
                return false;
            }
        } elseif ($query_condition['match'] === 'equals') {
            ksort($query_array);
            ksort($data_array);
            if ($query_array !== $data_array) {
                return false;
            }
        }
        return true;
    }

    public static function check_webhook_message_text_keyword_condition($source, $data) {
        if (isset($source['type']) && $source['type'] === 'keyword') {
            return self::check_keyword_match($source['keyword'], $data, 'keyword');
        } elseif (isset($source['type']) && $source['type'] === 'query') {
            return self::check_query_match($source['query'], $data);
        }
        return true; // typeがkeywordでもqueryでもない場合はtrue（条件なしとみなす）
    }

    /**
     * ポストバックパラメーターが指定された条件の通りかどうかをチェックする
     * @param $param object 条件の配列
     * @param $data object 実際のデータ
     * @return bool 
     */
    public static function check_webhook_message_postback_param_condition($param, $data) {
        if (empty($param['conditions'])) {
            return true;
        }
        $condition_results = array();
        foreach ($param['conditions'] as $condition) {
            if (isset($condition['type']) && $condition['type'] === 'source') {
                $result_bool         = self::check_webhook_message_postback_param_source_condition($condition['source'], $data);
                $condition_results[] = isset($condition['not']) && $condition['not'] === true  ? ! $result_bool : $result_bool;
            } elseif (isset($condition['type']) && $condition['type'] === 'group') {
                $condition_results[] = self::check_webhook_message_postback_param_condition($condition['condition'], $data);
            }
        }
        if (isset($param['operator'])  && $param['operator'] === 'or' && ! empty($condition_results) &&  ! in_array(true, $condition_results, true)) {
            return false;
        } elseif ((! isset($param['operator']) || $param['operator'] === 'and') && ! empty($condition_results) && in_array(false, $condition_results, true)) {
            return false;
        }
        return true;
    }

    /**
     * ポストバックパラメーターソースが指定された条件の通りかどうかをチェックする
     * @param $param object 条件の配列
     * @param $data object 実際のデータ
     * @return bool 
     */
    public static function check_webhook_message_postback_param_source_condition($source, $data) {
        if (isset($source['type']) && $source['type'] === 'newRichMenuAliasId') {
            if (!isset($source['newRichMenuAliasId']) || !isset($data->{'newRichMenuAliasId'})) {
                return false;
            }
            return self::check_keyword_match($source['newRichMenuAliasId'], $data->{'newRichMenuAliasId'}, 'newRichMenuAliasId');
        } elseif (isset($source['type']) && $source['type'] === 'status') {
            if (!empty($source['status']) && is_array($source['status']) && isset($data->{'status'}) && !in_array($data->{'status'}, $source['status'])) {
                return false;
            }
        } elseif (isset($source['type']) && in_array($source['type'], ['date', 'time', 'datetime'])) {
            if (!isset($source[$source['type']][$source['type']]) || !isset($data->{$source['type']})) {
                return false;
            }
            return self::check_datetime_match($source[$source['type']][$source['type']] ?? null, $data->{$source['type']}, $source[$source['type']]['compare'] ?? 'equals');
        }
        return true;
    }

    /**
     * 日付、時刻、日付時刻が指定された条件に合うかをチェックする
     * @param $standard string 比較の基準値
     * @param $value string 比較の対象となる値
     * @param $compare string 比較メソッド: equals,before_or_equal,before,after,after_or_equal
     * @return bool
     */
    public static function check_datetime_match($standard, $value, $compare) {
        // error_log(print_r(array('standard' => $standard, 'value' => $value, 'compare' => $compare), true));
        // error_log(print_r(array('standard' => strtotime($standard), 'value' => strtotime($value)), true));
        if ((empty($compare) || $compare === 'equals') && ! \Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($standard) && strtotime($standard) !== strtotime($value)) {
            return false;
        } else if ($compare === 'before_or_equal' && ! \Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($standard) && strtotime($value) > strtotime($standard)) {
            return false;
        } else if ($compare === 'before' && ! \Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($standard) && strtotime($value) >= strtotime($standard)) {
            return false;
        } else if ($compare === 'after' && ! \Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($standard) && strtotime($value) <= strtotime($standard)) {
            return false;
        } else if ($compare === 'after_or_equal' && ! \Shipweb\LineConnect\Utilities\SimpleFunction::is_empty($standard) && strtotime($value) < strtotime($standard)) {
            return false;
        }
        return true;
    }

    /**
     * 条件に合うかどうかをチェックする
     * 
     * @param $condition_object array 条件の配列
     * @param $event object イベントオブジェクト
     * @param $secret_prefix string チャネルプリフィックス
     * @return bool
     */
    public static function check_webhook_condition($condition_object, $event, $secret_prefix) {
        if (empty($condition_object['conditions'])) {
            return true;
        }
        $condition_results = array();
        foreach ($condition_object['conditions'] as $condition) {
            // json compare with $event->{'source'}
            if (isset($condition['type']) && $condition['type'] === 'source') {
                $result_bool         = self::check_webhook_source_condition($condition['source'], $event, $secret_prefix);
                $condition_results[] = isset($condition['not']) && $condition['not'] === true  ? ! $result_bool : $result_bool;
            } elseif (isset($condition['type']) && $condition['type'] === 'channel') {
                $result_bool         = self::check_webhook_secret_prefix_condition($condition['secret_prefix'], $event, $secret_prefix);
                $condition_results[] = isset($condition['not']) && $condition['not'] === true  ? ! $result_bool : $result_bool;
            } elseif (isset($condition['type']) && $condition['type'] === 'group') {
                $condition_results[] = self::check_webhook_condition($condition['condition'], $event, $secret_prefix);
            }
        }
        // error_log(print_r($condition_results, true));
        if (isset($condition_object['operator']) && $condition_object['operator'] === 'or' && ! in_array(true, $condition_results, true)) {
            return false;
        } elseif ((! isset($condition_object['operator']) || $condition_object['operator'] === 'and') && in_array(false, $condition_results, true)) {
            return false;
        }

        return true;
    }

    public static function check_webhook_source_condition($condition, $event, $secret_prefix) {

        if (isset($condition['type']) && isset($event->{'source'}->{'type'}) && $condition['type'] !== $event->{'source'}->{'type'}) {
            return false;
        }
        if ($condition['type'] === 'group' && ! empty($condition['groupId']) && isset($event->{'source'}->{'groupId'}) && ! in_array($event->{'source'}->{'groupId'}, $condition['groupId'])) {
            return false;
        }
        if ($condition['type'] === 'room' && ! empty($condition['roomId']) && isset($event->{'source'}->{'roomId'}) && ! in_array($event->{'source'}->{'roomId'}, $condition['roomId'])) {
            return false;
        }
        if (! empty($condition['userId']) && isset($event->{'source'}->{'userId'}) && ! in_array($event->{'source'}->{'userId'}, $condition['userId'])) {
            return false;
        }
        if ($condition['type'] === 'user') {
            if (! empty($condition['link'])) {
                $isConditionMatched = Condition::evaluate_link($condition['link'], $secret_prefix, $event->{'source'}->{'userId'});
                if (!$isConditionMatched) {
                    return false;
                }
            }
            if (! empty($condition['role'])) {
                $isConditionMatched = Condition::evaluate_role($condition['role'], $secret_prefix, $event->{'source'}->{'userId'});
                if (!$isConditionMatched) {
                    return false;
                }
                /*
                $user = lineconnect::get_wpuser_from_line_id($secret_prefix, $event->{'source'}->{'userId'});
                if ($user === false) {
                    return false;
                }
                $user_roles        = (array) $user->roles;
                $user_roles_result = array_intersect($condition['role'], $user_roles);
                if (empty($user_roles_result)) {
                    return false;
                }
                */
            }
            //usermeta,profileに関しても同様に条件をチェック
            if (! empty($condition['usermeta'])) {
                $isConditionMatched = Condition::evaluate_usermeta($condition['usermeta'], $secret_prefix, $event->{'source'}->{'userId'});
                if (!$isConditionMatched) {
                    return false;
                }
            }
            if (! empty($condition['profile'])) {
                $isConditionMatched = Condition::evaluate_profile($condition['profile'], $secret_prefix, $event->{'source'}->{'userId'});
                if (!$isConditionMatched) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function check_webhook_secret_prefix_condition($secret_prefixs, $event, $secret_prefix) {
        if (!empty($secret_prefixs) && ! in_array($secret_prefix, $secret_prefixs)) {
            return false;
        }
        return true;
    }
}
