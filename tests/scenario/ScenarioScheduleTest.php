<?php

use \Shipweb\LineConnect\Scenario\Schedule;

class ScenarioScheduleTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_absolute() {
        $schedule = [
            'absolute' => '2025-02-09 20:45:00'
        ];
        $lastExecuted = '2025-02-09 20:45:00';

        $this->assertEquals('2025-02-09 20:45:00', Schedule::getNextSchedule($schedule, $lastExecuted), '絶対日付時刻');
    }

    public function test_relative_minutes_exact() {
        $schedule = [
            'relative' => 1,
            'unit' => 'minutes',
        ];
        $lastExecuted = '2025-02-09 20:45:00';
        $this->assertEquals('2025-02-09 20:46:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ 1分後'); // included seconds in the expected value
    }

    public function test_relative_hours_exact() {
        $schedule = [
            'relative' => 1,
            'unit' => 'hours',
        ];
        $lastExecuted = '2025-02-09 20:45:00';
        $this->assertEquals('2025-02-09 21:45:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ 1時間後'); // included seconds in the expected value
    }

    public function test_relative_hours_base_earlier_time() {
        $schedule = [
            'relative' => 1,
            'unit' => 'hours',
            'type' => 'base',
            'minute' => 30
        ];
        $lastExecuted = '2025-02-09 20:45:00';
        $this->assertEquals('2025-02-09 21:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 1時間後'); // included seconds in the expected value
    }

    public function test_relative_hours_base_later_time() {
        $schedule = [
            'relative' => 1,
            'unit' => 'hours',
            'type' => 'base',
            'minute' => 50
        ];
        $lastExecuted = '2025-02-09 20:45:00';
        $this->assertEquals('2025-02-09 21:50:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 1時間後'); // updated expected time
    }


    public function test_relative_hours_base_over_day() {
        $schedule = [
            'relative' => 1,
            'unit' => 'hours',
            'type' => 'base',
            'minute' => 10
        ];
        $lastExecuted = '2025-02-09 23:45:00';
        $this->assertEquals('2025-02-10 00:10:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 翌日1時間後'); // updated expected time for next day
    }

    // days
    public function test_relative_days_exact() {
        $schedule = [
            'relative' => 1,
            'unit' => 'days',
        ];
        $lastExecuted = '2025-02-09 20:45:00';
        $this->assertEquals('2025-02-10 20:45:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ 1日後'); // included seconds in the expected value
    }

    // days
    public function test_relative_days_exact_over_month() {
        $schedule = [
            'relative' => 1,
            'unit' => 'days',
        ];
        $lastExecuted = '2025-02-28 20:45:00';
        $this->assertEquals('2025-03-01 20:45:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ 1日後'); // updated expected time for next month
    }

    // days base earlier time
    public function test_relative_days_base_earlier_time() {
        $schedule = [
            'relative' => 1,
            'unit' => 'days',
            'type' => 'base',
            'time' => '10:30',
        ];
        $lastExecuted = '2025-02-09 20:45:00';
        $this->assertEquals('2025-02-10 10:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 1日後'); // base time next day
    }

    // days base later time
    public function test_relative_days_base_later_time() {
        $schedule = [
            'relative' => 1,
            'unit' => 'days',
            'type' => 'base',
            'time' => '22:00',
        ];
        $lastExecuted = '2025-02-09 20:45:00';
        $this->assertEquals('2025-02-10 22:00:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 1日後'); // base time next day
    }

    // weeks exact
    public function test_relative_weeks_exact() {
        $schedule = [
            'relative' => 1,
            'unit' => 'weeks'
        ];
        $lastExecuted = '2025-02-09 20:45:00'; // Sunday
        $this->assertEquals('2025-02-16 20:45:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ 1週間後');
    }

    public function test_relative_weeks_base_specific_weekday() {
        $schedule = [
            'relative' => 1,
            'unit' => 'weeks',
            'type' => 'base',
            'weekday' => 3, // Wednesday
            'time' => '15:30'
        ];
        $lastExecuted = '2025-02-09 20:45:00'; // Sunday
        $this->assertEquals('2025-02-19 15:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 次の水曜日');
    }

    public function test_relative_weeks_base_specific_this_same_weekday() {
        // インターバルが0で、同じ曜日の場合、当日になる。
        $schedule = [
            'relative' => 0,
            'unit' => 'weeks',
            'type' => 'base',
            'weekday' => 0,
            'time' => '10:00'
        ];
        $lastExecuted = '2025-02-09 20:45:00';
        $this->assertEquals('2025-02-09 10:00:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ この日曜日');
    }

    public function test_relative_weeks_base_same_weekday() {
        // インターバルが1で、同じ曜日の場合、次の週の同じ曜日になる。
        $schedule = [
            'relative' => 1,
            'unit' => 'weeks',
            'type' => 'base',
            'weekday' => 0, // Sunday
            'time' => '10:00'
        ];
        $lastExecuted = '2025-02-09 20:45:00'; // Sunday
        $this->assertEquals('2025-02-16 10:00:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 次の日曜日');
    }

    public function test_relative_weeks_exact_over_month() {
        $schedule = [
            'relative' => 1,
            'unit' => 'weeks'
        ];
        $lastExecuted = '2025-02-25 20:45:00'; // Tuesday
        $this->assertEquals('2025-03-04 20:45:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ 1週間後（月跨ぎ）');
    }

    public function test_relative_weeks_base_specific_this_weekday() {
        $schedule = [
            'relative' => 0,
            'unit' => 'weeks',
            'type' => 'base',
            'weekday' => 3,
            'time' => '15:30'
        ];
        $lastExecuted = '2025-02-09 20:45:00';
        $this->assertEquals('2025-02-12 15:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ この水曜日');
    }

    // months - exact type
    public function test_relative_months_exact() {
        $schedule = [
            'relative' => 1,
            'unit' => 'months'
        ];
        $lastExecuted = '2025-01-15 20:45:00';
        $this->assertEquals('2025-02-15 20:45:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ 1ヶ月後');
    }

    public function test_relative_months_exact_month_end() {
        $schedule = [
            'relative' => 1,
            'unit' => 'months'
        ];
        $lastExecuted = '2025-01-31 20:45:00';
        $this->assertEquals('2025-03-03 20:45:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ 1ヶ月後（月末からの移行）');
    }

    public function test_relative_months_base_specific_day() {
        $schedule = [
            'relative' => 1,
            'unit' => 'months',
            'type' => 'base',
            'day' => 15,
            'time' => '10:30'
        ];
        $lastExecuted = '2025-01-31 20:45:00';
        $this->assertEquals('2025-02-15 10:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 翌月15日');
    }

    public function test_relative_months_base_last_day() {
        $schedule = [
            'relative' => 1,
            'unit' => 'months',
            'type' => 'base',
            'day' => 0,  // 0は月末を表す
            'time' => '10:30'
        ];
        $lastExecuted = '2025-01-31 20:45:00';
        $this->assertEquals('2025-02-28 10:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 翌月末日');
    }

    public function test_relative_months_base_same_day_number() {
        $schedule = [
            'relative' => 1,
            'unit' => 'months',
            'type' => 'base',
            'day' => 31,
            'time' => '10:30'
        ];
        $lastExecuted = '2025-01-31 20:45:00';
        $this->assertEquals('2025-03-03 10:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 31日から次の31日');
    }

    public function test_relative_months_base_february_to_march() {
        $schedule = [
            'relative' => 1,
            'unit' => 'months',
            'type' => 'base',
            'day' => 30,
            'time' => '10:30'
        ];
        $lastExecuted = '2025-02-28 20:45:00';
        $this->assertEquals('2025-03-30 10:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 2月末から3月30日');
    }

    public function test_relative_months_exact_over_year() {
        $schedule = [
            'relative' => 1,
            'unit' => 'months'
        ];
        $lastExecuted = '2025-12-31 20:45:00';
        $this->assertEquals('2026-01-31 20:45:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ 年跨ぎ');
    }

    // years - exact type
    public function test_relative_years_exact() {
        $schedule = [
            'relative' => 1,
            'unit' => 'years'
        ];
        $lastExecuted = '2025-02-15 20:45:00';
        $this->assertEquals('2026-02-15 20:45:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ 1年後');
    }

    public function test_relative_years_exact_leap_year() {
        $schedule = [
            'relative' => 1,
            'unit' => 'years'
        ];
        $lastExecuted = '2024-02-29 20:45:00';
        $this->assertEquals('2025-03-01 20:45:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - exactタイプ うるう年から次年');
    }

    public function test_relative_years_base_specific_month_day() {
        $schedule = [
            'relative' => 1,
            'unit' => 'years',
            'type' => 'base',
            'month' => 6,
            'day' => 15,
            'time' => '10:30'
        ];
        $lastExecuted = '2025-02-15 20:45:00';
        $this->assertEquals('2026-06-15 10:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 翌年6月15日');
    }

    public function test_relative_years_base_month_end() {
        $schedule = [
            'relative' => 1,
            'unit' => 'years',
            'type' => 'base',
            'month' => 2,
            'day' => 31,  // 2月は28日まで
            'time' => '10:30'
        ];
        $lastExecuted = '2025-12-31 20:45:00';
        $this->assertEquals('2026-03-03 10:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 存在しない日付の繰り越し');
    }

    public function test_relative_years_base_same_date() {
        $schedule = [
            'relative' => 1,
            'unit' => 'years',
            'type' => 'base',
            'month' => 12,
            'day' => 31,
            'time' => '10:30'
        ];
        $lastExecuted = '2025-12-31 20:45:00';
        $this->assertEquals('2026-12-31 10:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 同じ月日');
    }

    public function test_relative_years_base_this_year() {
        $schedule = [
            'relative' => 0,
            'unit' => 'years',
            'type' => 'base',
            'month' => 12,
            'day' => 31,
            'time' => '10:30'
        ];
        $lastExecuted = '2025-02-15 20:45:00';
        $this->assertEquals('2025-12-31 10:30:00', Schedule::getRelativeSchedule($schedule, $lastExecuted), '相対日付時刻 - baseタイプ 今年の指定日');
    }
}
