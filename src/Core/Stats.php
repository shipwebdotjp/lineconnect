<?php

namespace Shipweb\LineConnect\Core;

use Shipweb\LineConnect\Core\LineConnect;
use \lineconnectConst;

class Stats {
    static function fetch_line_message_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . lineconnectConst::TABLE_LINE_STATS;
        foreach (\LineConnect::get_all_channels() as $channel_id => $channel) {
            $channel_prefix =  $channel['prefix'];
            $channel_access_token = $channel['channel-access-token'];
            $today = wp_date('Ymd'); // 今日
            $yesterday = wp_date('Ymd', strtotime('-1 day')); // 前日
            $day_before_yesterday = wp_date('Ymd', strtotime('-2 days')); // 前々日
            $dates_to_check = [$day_before_yesterday, $yesterday, $today];

            foreach ($dates_to_check as $date) {
                $is_merged = false;
                $stats = [
                    'channel_prefix'  => $channel_prefix,
                    'date'            => wp_date('Y-m-d', strtotime($date)),
                ];
                // 既存データの確認
                $existing = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM $table_name WHERE channel_prefix = %s AND date = %s",
                        $channel_prefix,
                        wp_date('Y-m-d', strtotime($date)) // Updated to use wp_date()
                    ),
                    ARRAY_A
                );
                //DBの既存データを$statsにマージ
                if ($existing) {
                    $stats = array_merge($stats, $existing);
                }

                if ($date == $today) {
                    //連携済みユーザー数を取得
                    $args          = array(
                        'meta_query' => array(
                            array(
                                'key'     => \LineConnect::META_KEY__LINE,
                                'compare' => 'EXISTS',
                            ),
                        ),
                        'fields'     => 'ID',
                    );
                    $line_user_ids = array();
                    $user_query    = new \WP_User_Query($args);
                    $users         = $user_query->get_results();
                    if (! empty($users)) {
                        foreach ($users as $user) {
                            $user_meta_line = get_user_meta($user, \LineConnect::META_KEY__LINE, true);
                            if (isset($user_meta_line[$channel_prefix])) {
                                $line_user_ids[] = $user_meta_line[$channel_prefix]['id'];
                            }
                        }
                        $target_cnt = count($line_user_ids);
                        if (!$existing || is_null($existing['linked']) || $existing['linked'] != $target_cnt) {
                            $stats['linked'] = $target_cnt;
                            $is_merged = true;
                        }
                    }
                    //認識済みユーザー数を取得
                    $table_name_line_id = $wpdb->prefix . lineconnectConst::TABLE_LINE_ID;

                    $results = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(line_id) as cnt FROM {$table_name_line_id} WHERE channel_prefix = %s",
                            $channel_prefix
                        )
                    );
                    if ($results) {
                        $recognized_cnt = $results;
                        if (!$existing || is_null($existing['recognized']) || $existing['recognized'] != $recognized_cnt) {
                            $stats['recognized'] = $recognized_cnt;
                            $is_merged = true;
                        }
                    }
                } else {

                    if (!$existing || is_null($existing['broadcast'])) {
                        $result = self::fetch_line_message_stats_delivery($channel_access_token, $date);
                        if ($result) {
                            $stats = array_merge($stats, $result);
                            $is_merged = true;
                        }
                    }

                    if (!$existing || is_null($existing['followers'])) {
                        $result = self::fetch_line_message_stats_followers($channel_access_token, $date);
                        if ($result) {
                            $stats = array_merge($stats, $result);
                            $is_merged = true;
                        }
                    }

                    if (!$existing || is_null($existing['demographic'])) {
                        $result = self::fetch_line_message_stats_demographic($channel_access_token, $date);
                        if ($result) {
                            $stats = array_merge($stats, $result);
                            $is_merged = true;
                        }
                    }
                }
                if ($is_merged) {
                    $stats['updated_at'] = current_time('mysql');
                    $wpdb->replace($table_name, $stats);
                }
            }
        }
    }

    /*
    * LINEメッセージ配信数の取得
    * @param string $channel_access_token
    * @param string $date
    * @return array|null
    */
    public static function fetch_line_message_stats_delivery($channel_access_token, $date) {
        // APIリクエスト
        $url = "https://api.line.me/v2/bot/insight/message/delivery?date=$date";
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => "Bearer " . $channel_access_token,
            ],
        ]);

        if (is_wp_error($response)) {
            error_log("LINE API request failed for $date: " . $response->get_error_message());
            return null;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!$data || !isset($data['status'])) {
            error_log("Invalid LINE API response for $date");
            return null;
        }

        if ($data['status'] === 'ready') {
            return [
                'broadcast' => $data['broadcast'] ?? 0,
                'targeting' => $data['targeting'] ?? 0,
                'autoResponse' => $data['autoResponse'] ?? 0,
                'welcomeResponse' => $data['welcomeResponse'] ?? 0,
                'chat' => $data['chat'] ?? 0,
                'apiBroadcast' => $data['apiBroadcast'] ?? 0,
                'apiPush' => $data['apiPush'] ?? 0,
                'apiMulticast' => $data['apiMulticast'] ?? 0,
                'apiNarrowcast' => $data['apiNarrowcast'] ?? 0,
                'apiReply' => $data['apiReply'] ?? 0,
            ];
        }

        return null;
    }

    /*
    * LINEメッセージフォロワー数の取得
    * @param string $channel_access_token
    * @param string $date
    * @return array|null
    */
    public static function fetch_line_message_stats_followers($channel_access_token, $date) {
        // APIリクエスト
        $url = "https://api.line.me/v2/bot/insight/followers?date=$date";
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => "Bearer " . $channel_access_token,
            ],
        ]);

        if (is_wp_error($response)) {
            error_log("LINE API request failed for $date: " . $response->get_error_message());
            return null;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!$data || !isset($data['status'])) {
            error_log("Invalid LINE API response for $date");
            return null;
        }

        if ($data['status'] === 'ready') {
            return [
                'followers' => $data['followers'] ?? null,
                'targetedReaches' => $data['targetedReaches'] ?? null,
                'blocks' => $data['blocks'] ?? null,
            ];
        }

        return null;
    }

    /*
    * LINEメッセージデモグラフィック情報の取得
    * @param string $channel_access_token
    * @param string $date
    * @return array|null
    */
    public static function fetch_line_message_stats_demographic($channel_access_token, $date) {
        // APIリクエスト
        $url = "https://api.line.me/v2/bot/insight/demographic";
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => "Bearer " . $channel_access_token,
            ],
        ]);

        if (is_wp_error($response)) {
            error_log("LINE API request failed for $date: " . $response->get_error_message());
            return null;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (!$data || !isset($data['available'])) {
            error_log("Invalid LINE API response for $date");
            return null;
        }

        $demographic = [
            'available' => $data['available'],
            'genders' => $data['genders'] ?? null,
            'ages' => $data['ages'] ?? null,
            'areas' => $data['areas'] ?? null,
            'appTypes' => $data['appTypes'] ?? null,
            'subscriptionPeriods' => $data['subscriptionPeriods'] ?? null,
        ];

        return [
            'demographic' => json_encode($demographic, JSON_UNESCAPED_UNICODE),
        ];
    }

    /*
    * デイリー連携数を増加させる
    * @param string $channel_prefix
    * @return void
    */
    public static function increase_daily_link($channel_prefix) {
        global $wpdb;
        $table_name_line_daily = $wpdb->prefix . lineconnectConst::TABLE_LINE_DAILY;
        $today                = wp_date('Y-m-d');
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$table_name_line_daily} 
            (channel_prefix, date, link) 
            VALUES (%s, %s, 1)
            ON DUPLICATE KEY UPDATE 
            link = link + 1",
            $channel_prefix,
            $today
        ));
    }

    /*
    * デイリーアンリンク数を増加させる
    * @param string $channel_prefix
    * @return void
    */
    public static function increase_daily_unlink($channel_prefix) {
        global $wpdb;
        $table_name_line_daily = $wpdb->prefix . lineconnectConst::TABLE_LINE_DAILY;
        $today                = wp_date('Y-m-d');
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$table_name_line_daily} 
            (channel_prefix, date, unlink) 
            VALUES (%s, %s, 1)
            ON DUPLICATE KEY UPDATE 
            unlink = unlink + 1",
            $channel_prefix,
            $today
        ));
    }

    /*
    * メッセージの送信数を増加させる
    * @param string $channel_prefix
    * @param string $type
    * @param int   $count
    * @return void
    */
    public static function increase_stats_message($channel_prefix, $type, $count) {
        global $wpdb;
        $table_name = $wpdb->prefix . lineconnectConst::TABLE_LINE_STATS;
        $today      = wp_date('Y-m-d');
        if ($type === 'apiBroadcast') {
            $targetedReaches = self::get_targeted_reaches($channel_prefix);
            if (!$targetedReaches) {
                return;
            }
            $count = $targetedReaches;
        }
        /*
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$table_name} 
            (channel_prefix, date, {$type}) 
            VALUES (%s, %s, %d)
            ON DUPLICATE KEY UPDATE 
            {$type} = {$type} + %d",
            $channel_prefix,
            $today,
            $count,
            $count
        ));
        */
        $wpdb->query($wpdb->prepare(
            "SELECT {$type} FROM {$table_name} WHERE channel_prefix = %s AND date = %s",
            $channel_prefix,
            $today
        ));
        $existing = $wpdb->get_row($wpdb->last_query, ARRAY_A);
        if ($existing) {
            $current_count = $existing[$type];
            $new_count = $current_count ? $current_count + $count : $count;
            $wpdb->update(
                $table_name,
                array($type => $new_count),
                array('channel_prefix' => $channel_prefix, 'date' => $today)
            );
        } else {
            $wpdb->insert(
                $table_name,
                array(
                    'channel_prefix' => $channel_prefix,
                    'date'           => $today,
                    $type            => $count
                )
            );
        }
    }

    /*
    * 指定された年月のチャンネルごとの集計値を返す
    * @param string $year_month 'YYYY-MM'形式
    * @return array
    */
    public static function get_monthly_summary($year_month) {
        global $wpdb;
        //format: YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $year_month)) {
            $year_month = wp_date('Y-m');
        }
        $start_date = $year_month . '-01';
        $end_date = wp_date('Y-m-01', strtotime($year_month . ' +1 month'));
        error_log('start_date:' . $start_date . ' end_date:' . $end_date);
        $table_name = $wpdb->prefix . lineconnectConst::TABLE_LINE_STATS;

        $sql = "SELECT t1.channel_prefix, t1.followers, t1.targetedReaches, t1.blocks, t1.date, t1.linked, t1.recognized
            FROM {$table_name} t1
            INNER JOIN (
                SELECT channel_prefix, MAX(date) as latest_date
                FROM {$table_name}
                WHERE followers IS NOT NULL 
                AND date >= %s AND date < %s
                GROUP BY channel_prefix
            ) t2 ON t1.channel_prefix = t2.channel_prefix AND t1.date = t2.latest_date";

        $results = $wpdb->get_results(
            $wpdb->prepare($sql, $start_date, $end_date),
            ARRAY_A
        );

        // $start_dateが今月の場合、今日のデータを取得
        if ($start_date == wp_date('Ym') . '01') {
            $daily_table_name = $wpdb->prefix . lineconnectConst::TABLE_LINE_DAILY;
            $daily_results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT channel_prefix, follow, unfollow, link, unlink FROM {$daily_table_name} WHERE date = %s",
                    wp_date('Y-m-d')
                ),
                ARRAY_A
            );
            foreach ($daily_results as $daily_result) {
                $channel_prefix = $daily_result['channel_prefix'];
                $key = array_search($channel_prefix, array_column($results, 'channel_prefix'));
                if ($key !== false) {
                    $results[$key]['followers'] += $daily_result['follow'];
                    $results[$key]['blocks'] += $daily_result['unfollow'];
                    $results[$key]['linked'] += $daily_result['link'];
                    $results[$key]['linked'] -= $daily_result['unlink'];
                }
            }
        }

        //broadcastの合計数を取得
        $sql = "SELECT channel_prefix,SUM(broadcast) as broadcast,
            SUM(targeting) as targeting,
            SUM(autoResponse) as autoResponse,
            SUM(welcomeResponse) as welcomeResponse,
            SUM(chat) as chat,
            SUM(apiBroadcast) as apiBroadcast,
            SUM(apiPush) as apiPush,
            SUM(apiMulticast) as apiMulticast,
            SUM(apiNarrowcast) as apiNarrowcast,
            SUM(apiReply) as apiReply
            FROM {$table_name}
            WHERE date >= %s AND date < %s
            GROUP BY channel_prefix";


        $message_count_results = $wpdb->get_results(
            $wpdb->prepare($sql, $start_date, $end_date),
            ARRAY_A
        );


        // チャンネルプレフィックスをキーとする連想配列に変換
        $summary = [];

        foreach (\LineConnect::get_all_channels() as $channel_id => $channel) {
            $channel_info = self::get_channel_info($channel);
            $summary[$channel['prefix']] = [
                'channel_prefix' => $channel['prefix'],
                'name'          => $channel['name'],
                'followers'      => 0,
                'targetedReaches' => 0,
                'blocks'         => 0,
                'recognized'     => 0,
                'linked'         => 0,
                'broadcast'      => 0,
                'targeting'      => 0,
                'autoResponse'   => 0,
                'welcomeResponse' => 0,
                'chat'           => 0,
                'apiBroadcast'   => 0,
                'apiPush'        => 0,
                'apiMulticast'   => 0,
                'apiNarrowcast'  => 0,
                'apiReply'       => 0
            ];
            $summary[$channel['prefix']] = array_merge($summary[$channel['prefix']], $channel_info);
        }

        foreach ($results as $result) {
            $summary[$result['channel_prefix']] = array_merge($summary[$result['channel_prefix']], $result);
        }

        foreach ($message_count_results as $message_count_result) {
            $channel_prefix = $message_count_result['channel_prefix'];
            $summary[$channel_prefix] = array_merge($summary[$channel_prefix] ?? [], $message_count_result);
        }

        return $summary;
    }

    /**
     * 指定されたチャンネルの指定された年月の日毎の集計データを返す
     * @param string $year_month 'YYYY-MM'形式
     * @param string $channel_prefix チャンネルプレフィックス
     * @return array
     */
    public static function get_daily_summary($year_month, $channel_prefix) {
        global $wpdb;

        // Format validation: YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $year_month)) {
            $year_month = wp_date('Y-m');
        }

        // Calculate start and end dates for the given month
        $start_date = $year_month . '-01';
        $end_date = wp_date('Y-m-d', strtotime($start_date . ' +1 month'));

        $table_name_stats = $wpdb->prefix . lineconnectConst::TABLE_LINE_STATS;
        $table_name_daily = $wpdb->prefix . lineconnectConst::TABLE_LINE_DAILY;

        // Get all dates in the month
        $dates = [];
        $current_date = $start_date;
        while ($current_date < $end_date) {
            $dates[] = $current_date;
            $current_date = wp_date('Y-m-d', strtotime($current_date . ' +1 day'));
        }

        // Initialize result array with all dates
        $daily_data = [];
        foreach ($dates as $date) {
            $daily_data[$date] = [
                'date' => $date,
                'channel_prefix' => $channel_prefix,
                'followers' => 0,
                'targetedReaches' => 0,
                'blocks' => 0,
                'recognized' => 0,
                'linked' => 0,
                'broadcast' => 0,
                'targeting' => 0,
                'autoResponse' => 0,
                'welcomeResponse' => 0,
                'chat' => 0,
                'apiBroadcast' => 0,
                'apiPush' => 0,
                'apiMulticast' => 0,
                'apiNarrowcast' => 0,
                'apiReply' => 0,
                'follow' => 0,
                'unfollow' => 0,
                'link' => 0,
                'unlink' => 0
            ];
        }

        // Get data from LINE_STATS table
        $stats_results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name_stats} 
            WHERE channel_prefix = %s 
            AND date >= %s AND date < %s
            ORDER BY date ASC",
                $channel_prefix,
                $start_date,
                $end_date
            ),
            ARRAY_A
        );

        // Get data from LINE_DAILY table
        $daily_results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name_daily} 
            WHERE channel_prefix = %s 
            AND date >= %s AND date < %s
            ORDER BY date ASC",
                $channel_prefix,
                $start_date,
                $end_date
            ),
            ARRAY_A
        );

        // Merge LINE_STATS data
        foreach ($stats_results as $result) {
            $date = $result['date'];
            if (isset($daily_data[$date])) {
                $daily_data[$date] = array_merge($daily_data[$date], $result);
            }
        }

        // Merge LINE_DAILY data
        foreach ($daily_results as $result) {
            $date = $result['date'];
            if (isset($daily_data[$date])) {
                // Only merge specific fields from daily table
                $daily_fields = [
                    'follow',
                    'unfollow',
                    'link',
                    'unlink'
                ];

                foreach ($daily_fields as $field) {
                    if (isset($result[$field])) {
                        $daily_data[$date][$field] = $result[$field];
                    }
                }
            }
        }


        // Convert associative array to indexed array for easier client-side processing
        $result = array_values($daily_data);

        return $result;
    }


    /**
     * チャネル情報を取得
     * @param array $channel
     * @return array
     */
    public static function get_channel_info($channel) {
        $channel_access_token = $channel['channel-access-token'];
        $channel_secret       = $channel['channel-secret'];
        $transient_key = 'channel_info_' . $channel['prefix'];
        $channel_info = get_transient($transient_key);
        if ($channel_info === false) {
            // LINE BOT
            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
            $bot        = new \LINE\LINEBot($httpClient, array('channelSecret' => $channel_secret));
            $response   = $bot->getBotInfo();
            if ($response->isSucceeded()) {
                $channel_info = $response->getJSONDecodedBody();
            } else {
                error_log('LINE API request failed: ' . $response->getHTTPStatus() . ' ' . $response->getRawBody());
                return null;
            }
            // キャッシュに保存
            set_transient($transient_key, $channel_info, DAY_IN_SECONDS);
        }
        // チャンネル情報を取得
        return $channel_info;
    }

    /**
     * チャンネルのターゲットリーチ数を取得
     * @param string $channel_prefix
     * @return int|null
     */
    public static function get_targeted_reaches($channel_prefix) {
        global $wpdb;
        $table_name = $wpdb->prefix . lineconnectConst::TABLE_LINE_STATS;
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT targetedReaches FROM {$table_name} WHERE channel_prefix = %s AND targetedReaches IS NOT NULL ORDER BY date DESC LIMIT 1",
                $channel_prefix
            )
        );
        return $result ? (int) $result->targetedReaches : null;
    }
}
