<?php

/**
 * シナリオの次回実行日時を算出する
 * 
 */

/*
	次回実行
		絶対日付時刻
			日付時刻
		相対日付時刻
			インターバル値
			インターバル値の単位(分、時間、日、週、月、年)
			ぴったりその時刻に実行する:
			インターバル値を実際の時間ではなく翌(単位)とする
				インターバル値を実際の時間ではなく翌(単位)とする場合の指定日付や時刻
				インターバル値の単位が
					時間: X分
					日: X時:X分
					週: X曜日 X時:X分
					月: X日 X時:X分
					年: X月 X日 X時:X分
			
		例) 当ステップの実行時刻 2025/02/09 20:45
		インターバル値:1、単位:時間 の場合、
		ぴったりその時刻に実行する→次回実行は2025/02/09 21:45
		インターバルを次の単位まで進め、指定されたタイミングに実行する　5分→2025/02/09 21:05
		
		例)
		当ステップの実行時刻 2025/02/09 20:45
		インターバル値:1、単位:日 の場合、
		ぴったりその時刻に実行する→次回実行は2025/02/10 20:45
		インターバルを次の単位まで進め、指定されたタイミングに実行する　12時10分→2025/02/10 12:10
		
		例)
		当ステップの実行時刻 2025/02/09 20:45
		インターバル値:1、単位:週 の場合、
		ぴったりその時刻に実行する→次回実行は2025/02/16 20:45
		インターバルを次の単位まで進め、指定されたタイミングに実行する　土曜日 18時00分→2025/02/22 18:00(次の次の土曜日)
		
		例)
		当ステップの実行時刻 2025/02/09 20:45
		インターバル値:1、単位:月 の場合、
		ぴったりその時刻に実行する→次回実行は2025/03/09 20:45
インターバルを次の単位まで進め、指定されたタイミングに実行する　5日 18時00分→2025/03/05 18:00
*/

namespace Shipweb\LineConnect\Scenario;

use \DateTime;

/**
 * シナリオの次回実行日時を算出するクラス
 * @package Shipweb\LineConnect\Scenario
 */
class Schedule {
    /**
     * 次回実行日時を算出する
     *
     * @param array $schedule
     * @param string $lastExecuted
     * @return string
     */
    public static function getNextSchedule(array $schedule, string $lastExecuted): string {
        // absoluteがある場合はその日時を返す
        if (isset($schedule['absolute'])) {
            return self::formatDate($schedule['absolute']);
        }
        if (isset($schedule['relative'])) {
            return self::getRelativeSchedule($schedule, $lastExecuted);
        }

        $datetime = new \DateTime();
        return $datetime->format('Y-m-d H:i:s');
    }

    /**
     * 相対日時の次回実行日時を算出する
     *
     * @param array $schedule
     * @param string $lastExecuted
     * @return string
     */
    public static function getRelativeSchedule(array $schedule, string $lastExecuted): string {
        $relative = $schedule['relative'] ?? 0;
        $unit = $schedule['unit'] ?? 'day';
        $timing = $schedule['type'] ?? 'exact';
        $nextExecuted = new \DateTime($lastExecuted);

        switch ($unit) {
            case 'minutes':
                $nextExecuted->modify("+{$relative} minute");
                break;
            case 'hours':
                $nextExecuted->modify("+{$relative} hour");
                if ($timing === 'base' && isset($schedule['minute'])) {
                    $minute = $schedule['minute'];
                    $nextExecuted->setTime($nextExecuted->format('H'), $minute); // 指定された分に設定
                }
                break;
            case 'days':
                $nextExecuted->modify("+{$relative} day");
                break;
            case 'weeks':
                $nextExecuted->modify("+{$relative} week");
                if ($timing === 'base' && isset($schedule['weekday'])) {
                    $weekday = $schedule['weekday']; //0: Sunday, 1: Monday, ...
                    $ary_week = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    // 同じ曜日の場合、当日になる。
                    $nextExecuted->modify("this {$ary_week[$weekday]}");
                }
                break;
            case 'months':
                if ($timing === 'base' && isset($schedule['day'])) {
                    $day = $schedule['day']; //1-31 if 0, Last day of the month
                    if ($day == 0) {
                        $nextExecuted->modify("last day of +{$relative} month");
                    } else {
                        $nextExecuted->modify("first day of this month +{$relative} month");
                        $nextExecuted->setDate($nextExecuted->format('Y'), $nextExecuted->format('m'), $day); // 指定された日に設定
                    }
                } else {
                    $nextExecuted->modify("+{$relative} month");
                }
                break;
            case 'years':
                $nextExecuted->modify("+{$relative} year");
                if ($timing === 'base' && isset($schedule['month']) && isset($schedule['day'])) {
                    $month = $schedule['month']; //1-12
                    $day = $schedule['day']; //1-31
                    $nextExecuted->setDate($nextExecuted->format('Y'), $month, $day); // 指定された日に設定
                }
                break;
        }

        if (isset($schedule['time'])) {
            $time = $schedule['time']; //HH:mm
            $nextExecuted->setTime(substr($time, 0, 2), substr($time, 3, 2)); // 指定された時刻に設定
        }


        return $nextExecuted->format('Y-m-d H:i:s');
    }

    /**
     * 日付をフォーマットして返す
     *
     * @param string $date
     * @param string $format
     * @return string|bool
     */
    public static function formatDate(string $date, string $format = 'Y-m-d H:i:s'): string {
        try {
            $datetime = new \DateTime($date);
            return $datetime->format($format);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
