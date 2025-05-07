<?php

/**
 * Lineconnect Schedule Class
 *
 * Schedule Class
 *
 * @category Components
 * @package  Schedule
 * @author ship
 * @license GPLv3
 * @link https://blog.shipweb.jp/lineconnect/
 */

use \Shipweb\LineConnect\Scenario\Scenario;
use \Shipweb\LineConnect\Core\Stats;

class lineconnectSchedule {
    static function schedule_event() {
        $last_run = get_option(lineconnect::CRON_EVENT_LAST_TIMESTAMP);
        $current_time = time();
        //update last run time
        update_option(lineconnect::CRON_EVENT_LAST_TIMESTAMP, $current_time, false);
        // error_log("last run:". wp_date("Y-m-d H:i:s",$last_run). " thistime: ". wp_date("Y-m-d H:i:s",time()));
        $response = self::run_schedule($last_run, $current_time);
        if (isset($response['success']) && $response['success']) {
            //error_log("Cron Success". print_r($response, true));
        } else {
            error_log("Cron Failed" . print_r($response, true));
            //liceconnectError::error_logging($response);
        }
        // if between $last_run and now day changed
        if (empty($last_run) || wp_date('Ymd', $last_run) != wp_date('Ymd')) {
            //$response = self::send_error_log();
        }
        if (empty($last_run) || wp_date('YmdH', $last_run) != wp_date('YmdH')) {
            //1時間ごとに実行
            if (version_compare(lineconnect::get_current_db_version(), '1.4', '>=')) {
                Stats::fetch_line_message_stats();
            }
        }
    }

    static function run_schedule($last_run, $current_time) {
        if (empty($last_run)) {
            $last_run = time() - 3600;
        }
        // $current_time = time();
        $success = true;
        //トリガー実行
        $triggers = self::get_schedules();
        foreach ($triggers as $trigger) {
            $matched_array = array();
            if (!isset($trigger['triggers'])) {
                continue;
            }
            foreach ($trigger['triggers'] as $schedule) {
                $matched_array[] = self::check_schedule_condition($schedule, $last_run, $current_time);
            }
            // $trigger['triggers']の各条件のいずれかに一致する場合
            if (! in_array(true, $matched_array)) {
                // error_log( 'no trigger  match:' );
                continue;
            }
            // error_log( 'trigger type match:' . print_r( $trigger, true ) );

            if (isset($trigger['action'])) {
                $action_return = lineconnectAction::do_action($trigger['action'], $trigger['chain']);
                error_log('trigger action result: ' . print_r($action_return, true));
            }
        }
        //シナリオ実行
        $messages = array();
        $response = array();
        $scenarios = self::get_scenarios($last_run, $current_time);
        foreach ($scenarios as $scenario) {
            $scenario_result = Scenario::execute_step($scenario['id'], $scenario['next'], $scenario['line_id'], $scenario['channel_prefix']);
            $response[] = $scenario_result;
            error_log('Scenario Result: ' . print_r($scenario_result, true));
            if (!$scenario_result) {
                $messages[] = array(
                    'scenario_id' => $scenario['id'],
                    'step' => $scenario['next'],
                    'line_id' => $scenario['line_id'],
                    'channel_prefix' => $scenario['channel_prefix'],
                    'status' => 'failed',
                    'message' => __('Failed to execute scenario', 'lineconnect'),
                );
            }
        }
        if (in_array(false, $response)) {
            $success = false;
        }

        return array(
            'success' => $success,
            'messages' => $messages,
        );
    }

    static function get_schedules() {
        $triggers = array();
        $args     = array(
            'post_type'      => lineconnectConst::POST_TYPE_TRIGGER,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );
        $posts    = get_posts($args);
        foreach ($posts as $post) {
            $form = get_post_meta($post->ID, lineconnect::META_KEY__TRIGGER_DATA, true);
            if (isset($form[0]['type']) && $form[0]['type'] === 'schedule') {
                $triggers[] = $form[1];
            }
        }
        return $triggers;
    }

    static function check_schedule_condition($schedule, $last_run, $current_time) {
        if ($schedule['type'] === 'once') {
            $sheduled_time = strtotime($schedule['once']['datetime']);
            if ($last_run < $sheduled_time && $sheduled_time <= $current_time) {
                return true;
            }
        } elseif ($schedule['type'] === 'repeat') {
            return self::check_repeat_schedule($schedule, $last_run, $current_time);
        }
    }

    static function check_repeat_schedule($schedule, $last_run, $current_time) {
        $next_run = 0;
        $base_time = isset($schedule['repeat']['start']) ? strtotime($schedule['repeat']['start']) : 0;
        $end_time = isset($schedule['repeat']['end']) ? strtotime($schedule['repeat']['end']) : null;
        $lag_second = isset($schedule['repeat']['lag']) ? $schedule['repeat']['lag'] * 60 : 0;
        $target_time = $current_time + $lag_second;
        $offset = get_option('gmt_offset') * 60 * 60;
        if ($schedule['repeat']['every'] === 'hour') {
            foreach ($schedule['repeat']['hour'] as $hour) {
                $calced_time = mktime($hour, wp_date('i', $base_time), wp_date('s', $base_time), wp_date('m',  $target_time), wp_date('d',  $target_time), wp_date('Y',  $target_time)) - $offset;
                $schedule_time = $calced_time - $lag_second;
                if (self::check_on_time($calced_time, $schedule_time, $last_run, $current_time, $base_time, $end_time)) {
                    return true;
                }
            }
        } elseif ($schedule['repeat']['every'] === 'day') {
            foreach ($schedule['repeat']['day'] as $day) {
                $dayoftheweekinthemonth = $day['number'];
                $dayoftheweek = $day['day'];
                $calctype = $day['type'];
                $startdayofweek = $day['startdayofweek'] ?? 0;
                if (count($dayoftheweekinthemonth) === 0) {
                    $dayoftheweekinthemonth = [1, 2, 3, 4, 5];
                }
                if (count($dayoftheweek) === 0) {
                    $dayoftheweek = [0, 1, 2, 3, 4, 5, 6];
                }
                foreach ($dayoftheweekinthemonth as $weekinmonth) {
                    foreach ($dayoftheweek as $d) {
                        if (self::calc_time_by_weekinmonth_day($last_run, $base_time, $end_time, $current_time, $lag_second, $weekinmonth, $d, $calctype, $startdayofweek, $offset)) {
                            return true;
                        }
                    }
                }
            }
        } elseif ($schedule['repeat']['every'] === 'date') {
            foreach ($schedule['repeat']['date'] as $date) {
                if ($date == 0) {
                    // last day of month
                    $date = wp_date('t', $target_time);
                }
                $calced_time = mktime(wp_date('H', $base_time), wp_date('i', $base_time), wp_date('s', $base_time), wp_date('m', $target_time), $date, wp_date('Y', $target_time)) - $offset;
                if (self::check_if_same_month($target_time, $calced_time)) {
                    $schedule_time = $calced_time - $lag_second;
                    if (self::check_on_time($calced_time, $schedule_time, $last_run, $current_time, $base_time, $end_time)) {
                        return true;
                    }
                }
            }
        } elseif ($schedule['repeat']['every'] === 'week') {
            foreach ($schedule['repeat']['week'] as $week) {
                $day_of_week = date('N', $base_time);
                $week_start_date = new DateTime();
                $week_start_date->setISODate(date('Y', $target_time), $week, $day_of_week);
                $week_start_date->setTime(date('H', $base_time), date('i', $base_time), date('s', $base_time));
                $calced_time = $week_start_date->getTimestamp();
                $schedule_time = $calced_time - $lag_second;
                if (self::check_on_time($calced_time, $schedule_time, $last_run, $current_time, $base_time, $end_time)) {
                    return true;
                }
            }
        } elseif ($schedule['repeat']['every'] === 'month') {
            foreach ($schedule['repeat']['month'] as $month) {
                $calced_time = mktime(wp_date('H', $base_time), wp_date('i', $base_time), wp_date('s', $base_time), $month, wp_date('d', $base_time), wp_date('Y', $target_time)) - $offset;
                $schedule_time = $calced_time - $lag_second;
                if (self::check_on_time($calced_time, $schedule_time, $last_run, $current_time, $base_time, $end_time)) {
                    return true;
                }
            }
        } elseif ($schedule['repeat']['every'] === 'year') {
            foreach ($schedule['repeat']['year'] as $year) {
                $calced_time = mktime(wp_date('H', $base_time), wp_date('i', $base_time), wp_date('s', $base_time), wp_date('m', $base_time), wp_date('d', $base_time), $year) - $offset;
                $schedule_time = $calced_time - $lag_second;
                if (self::check_on_time($calced_time, $schedule_time, $last_run, $current_time, $base_time, $end_time)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 基準時間の時刻はそのままに、指定された「$nthday回目」の「$day曜日」に基づいてUnixTimeを算出し、時間差を減じたタイムスタンプを返す
     * 
     * @param $last_run : 最後に実行した時間
     * @param $base_time : 基準時間
     * @param $end_time : 終了時間
     * @param $current_time : 実行時間
     * @param $lag_second : 時間差
     * @param $nthday : 第何曜日か(Day of the week in the month) 取りうる値:1-5
     * @param $day :　曜日 0:日, 1:月, 2:火, 3:水, 4:木, 5:金, 6:土
     * @param $calctype : 算出方法 nthday:何回目の何曜日 nthweek:第何週の何曜日
     * @param $startdayofweek : 何曜日を週の初めとするか(0:日, 1:月, 2:火, 3:水, 4:木, 5:金, 6:土)
     * @return $schedule_time : 実行時間
     */
    static function calc_time_by_weekinmonth_day($last_run, $base_time, $end_time, $current_time, $lag_second, $nthday, $day, $calctype, $startdayofweek, $offset) {
        $target_time = $current_time + $lag_second;
        $firstdate = mktime(wp_date('H', $target_time), wp_date('i', $target_time), wp_date('s', $target_time), wp_date('m', $target_time), 1, wp_date('Y', $target_time)) - $offset;
        $firstdate_day = wp_date('w', $firstdate);
        if ($calctype === 'nthday') {
            $target_date = 1 + ($nthday - 1) * 7 + (7 + $day - $firstdate_day) % 7;
        } elseif ($calctype === 'nthweek') {
            switch ($startdayofweek) {
                case 0: //日曜始まり
                    $target_date = 1 + ($nthday - 1) * 7 + ($day - $firstdate_day) % 7;
                    break;
                case 1: //月曜始まり
                    $new_day = $day == 0 ? 7 : $day;
                    $target_date = 1 + ($nthday - 1) * 7 + ($new_day - wp_date('N', $firstdate)) % 7;
                    break;
            }
        }
        $calced_time = mktime(wp_date('H', $base_time), wp_date('i', $base_time), wp_date('s', $base_time), wp_date('m', $firstdate), $target_date, wp_date('Y', $firstdate)) - $offset;
        // error_log( "calced_time". wp_date("Y-m-d H:i:s",$calced_time)." target_date;".$target_date );
        if (!self::check_if_same_month($target_time, $calced_time)) {
            return false;
        }
        $schedule_time = $calced_time - $lag_second;
        if (!self::check_on_time($calced_time, $schedule_time, $last_run, $current_time, $base_time, $end_time)) {
            return false;
        }
        return true;
        /*
        switch($day){
            case 0:
                // Sunday 0:1 1:7 2:6 3:5 4:4 5:3 6:2
                $target_date = $firstdate_day > 0 ? 7 - $firstdate_day + 1 : 1 - $firstdate_day;
                break;
            case 1:
                // Monday 0:2 1:1 2:7 3:6 4:5 5:4 6:3  
                $target_date = $firstdate_day > 1 ? 7 - $firstdate_day + 2 : 2 - $firstdate_day;
                break;
            case 2:
                // Tuesday 0:3 1:2 2:1 3:7 4:6 5:5 6:4
                $target_date = $firstdate_day > 2 ? 7 - $firstdate_day + 3 : 3 - $firstdate_day;
                break;
            case 3:
                // Wednesday 0:4 1:3 2:2 3:1 4:7 5:6 6:5
                $target_date = $firstdate_day > 3 ? 7 - $firstdate_day + 4 : 4 - $firstdate_day;
                break;
            case 4:
                // Thursday 0:5 1:4 2:3 3:2 4:1 5:7 6:6
                $target_date = $firstdate_day > 4 ? 7 - $firstdate_day + 5 : 5 - $firstdate_day;
                break;
            case 5:
                // Friday 0:6 1:5 2:4 3:3 4:2 5:1 6:7
                $target_date = $firstdate_day > 5 ? 7 - $firstdate_day + 6 : 6 - $firstdate_day;
                break;
            case 6:
                // Saturday 0:7 1:6 2:5 3:4 4:3 5:2 6:1
                $target_date = $firstdate_day > 6 ? 7 - $firstdate_day + 7 : 7 - $firstdate_day;
        }
        */
    }

    /**
     * 同じ月に属しているかを返す
     * 
     * @param $target_time : UnixTime
     * @param $calced_time : UnixTime
     * @return bool : 同じ月に属しているか
     */
    static function check_if_same_month($target_time, $calced_time) {
        if (wp_date('m', $target_time) === wp_date('m', $calced_time)) {
            return true;
        }
        return false;
    }

    static function check_on_time($calced_time, $schedule_time, $last_run, $current_time, $base_time, $end_time) {
        /*
        error_log(
            ' last_run: ' .  wp_date("Y-m-d H:i:s",$last_run) .
            ' schedule_time: ' .  wp_date("Y-m-d H:i:s",$schedule_time) .
            ' current_time: ' .  wp_date("Y-m-d H:i:s",$current_time) .
            ' calced_time: ' .  wp_date("Y-m-d H:i:s",$calced_time) .
            ' base_time: ' .  wp_date("Y-m-d H:i:s",$base_time) .
            ' end_time: ' .  wp_date("Y-m-d H:i:s",$end_time)
        );
        */
        if (
            $last_run < $schedule_time &&
            $schedule_time <= $current_time &&
            $calced_time >= $base_time &&
            (!$end_time || $calced_time <= $end_time)
        ) {
            return true;
        }
        return false;
    }

    /**
     * 実行予定時刻が前回実行時刻と現在の時刻の間のシナリオを取得
     * @param int $last_run 前回実行時刻
     * @param int $current_time 現在の時刻
     * @return array $scenarios シナリオ
     */
    static function get_scenarios($last_run, $current_time) {
        global $wpdb;
        $table_name_line_id = $wpdb->prefix . lineconnectConst::TABLE_LINE_ID;

        $query = "SELECT 
            line_id.line_id,
            line_id.channel_prefix,
            scenario_data.id,
            scenario_data.next
        FROM $table_name_line_id as line_id,
        JSON_TABLE(scenarios, '$.*' COLUMNS (
            id INT PATH '$.id',
            next VARCHAR(255) PATH '$.next',
            next_date DATETIME PATH '$.next_date',
            status VARCHAR(50) PATH '$.status'
        )) AS scenario_data
        WHERE scenario_data.next_date > %s
        AND scenario_data.next_date <= %s
        AND scenario_data.status = 'active'
        ORDER BY scenario_data.next_date ASC;";

        $results = $wpdb->get_results(
            $wpdb->prepare(
                $query,
                array(
                    wp_date('Y-m-d H:i:s', $last_run),
                    wp_date('Y-m-d H:i:s', $current_time)
                )
            ),
            ARRAY_A
        );
        /*
        if (empty($results)) {
            error_log('No active scenarios found after last run: ' . wp_date("Y-m-d H:i:s", $last_run) . ' and before current time: ' . wp_date("Y-m-d H:i:s", $current_time));
        } else {
            error_log(print_r($results, true));
        }
*/
        return $results ? $results : array();
    }
}
