<?php

use \Shipweb\LineConnect\Trigger\Webhook;

class WebhookDateTimeTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_evaluate_equals() {
        $this->assertTrue(Webhook::check_datetime_match('2023-10-26T10:00:00', '2023-10-26 10:00', 'equals'), '同じ日付時刻');
        $this->assertFalse(Webhook::check_datetime_match('2023-10-26T10:00:00', '2023-10-26 10:01', 'equals'), '同じ日付時刻');
        $this->assertTrue(Webhook::check_datetime_match('2023-10-26', '2023-10-26 00:00:00', 'equals'), '同じ日付');
        $this->assertTrue(Webhook::check_datetime_match('10:00:00', date('Y-m-d') . ' 10:00:00', 'equals'), '同じ時刻');
        // compareが空の場合は equals と同等
        $this->assertTrue(Webhook::check_datetime_match('2023-10-26T10:00:00', '2023-10-26 10:00', ''), 'compareが空: 同じ日付時刻');
        $this->assertFalse(Webhook::check_datetime_match('2023-10-26T10:00:00', '2023-10-26 10:01', ''), 'compareが空: 違う日付時刻');
        // standardが空の場合は常にtrue (現在のロジックに基づく)
        $this->assertTrue(Webhook::check_datetime_match('', '2023-10-26 10:00', 'equals'), 'standardが空');
    }

    public function test_evaluate_before_or_equal() {
        $standard = '2023-10-26T10:00:00';
        $this->assertTrue(Webhook::check_datetime_match($standard, '2023-10-26 09:59:59', 'before_or_equal'), '以前または同じ: valueがstandardより前');
        $this->assertTrue(Webhook::check_datetime_match($standard, '2023-10-26 10:00:00', 'before_or_equal'), '以前または同じ: valueがstandardと同じ');
        $this->assertFalse(Webhook::check_datetime_match($standard, '2023-10-26 10:00:01', 'before_or_equal'), '以前または同じ: valueがstandardより後');
        // standardが空の場合は常にtrue
        $this->assertTrue(Webhook::check_datetime_match('', '2023-10-26 10:00', 'before_or_equal'), 'standardが空');
    }

    public function test_evaluate_before() {
        $standard = '2023-10-26T10:00:00';
        $this->assertTrue(Webhook::check_datetime_match($standard, '2023-10-26 09:59:59', 'before'), '以前: valueがstandardより前');
        $this->assertFalse(Webhook::check_datetime_match($standard, '2023-10-26 10:00:00', 'before'), '以前: valueがstandardと同じ');
        $this->assertFalse(Webhook::check_datetime_match($standard, '2023-10-26 10:00:01', 'before'), '以前: valueがstandardより後');
        // standardが空の場合は常にtrue
        $this->assertTrue(Webhook::check_datetime_match('', '2023-10-26 10:00', 'before'), 'standardが空');
    }

    public function test_evaluate_after() {
        $standard = '2023-10-26T10:00:00';
        $this->assertTrue(Webhook::check_datetime_match($standard, '2023-10-26 10:00:01', 'after'), '以降: valueがstandardより後');
        $this->assertFalse(Webhook::check_datetime_match($standard, '2023-10-26 10:00:00', 'after'), '以降: valueがstandardと同じ');
        $this->assertFalse(Webhook::check_datetime_match($standard, '2023-10-26 09:59:59', 'after'), '以降: valueがstandardより前');
        // standardが空の場合は常にtrue
        $this->assertTrue(Webhook::check_datetime_match('', '2023-10-26 10:00', 'after'), 'standardが空');
    }

    public function test_evaluate_after_or_equal() {
        $standard = '2023-10-26T10:00:00';
        $this->assertTrue(Webhook::check_datetime_match($standard, '2023-10-26 10:00:01', 'after_or_equal'), '以降または同じ: valueがstandardより後');
        $this->assertTrue(Webhook::check_datetime_match($standard, '2023-10-26 10:00:00', 'after_or_equal'), '以降または同じ: valueがstandardと同じ');
        $this->assertFalse(Webhook::check_datetime_match($standard, '2023-10-26 09:59:59', 'after_or_equal'), '以降または同じ: valueがstandardより前');
        // standardが空の場合は常にtrue
        $this->assertTrue(Webhook::check_datetime_match('', '2023-10-26 10:00', 'after_or_equal'), 'standardが空');
    }

    public function test_various_formats() {
        // strtotimeが解釈できる様々なフォーマットでのテスト
        $this->assertTrue(Webhook::check_datetime_match('2023-10-26 10:00', '10/26/2023 10:00 AM', 'equals'), '異なる有効なフォーマットで同じ日時');
        $this->assertTrue(Webhook::check_datetime_match('today 10:00', date('Y-m-d') . ' 10:00:00', 'equals'), '相対日付 today');
        $this->assertTrue(Webhook::check_datetime_match('now', date('Y-m-d H:i:s'), 'equals'), '相対日付 now (秒単位の誤差許容のため注意)');

        $standard = '2023-11-15 14:30:00';
        $value_before = '2023-11-15 14:29:59';
        $value_after = '2023-11-15 14:30:01';

        // YYYY/MM/DD HH:MM
        $this->assertTrue(Webhook::check_datetime_match('2023/11/15 14:30', $standard, 'equals'), 'YYYY/MM/DD HH:MM形式');
        $this->assertTrue(Webhook::check_datetime_match($standard, '2023/11/15 14:29', 'before_or_equal'), 'YYYY/MM/DD HH:MM形式 - before_or_equal');

        // 日付のみ
        $this->assertTrue(Webhook::check_datetime_match('2023-10-26', '2023-10-27', 'after'), '日付のみ比較 - after');
        $this->assertTrue(Webhook::check_datetime_match('2023-10-26', '2023-10-26', 'equals'), '日付のみ比較 - equals');
        $this->assertTrue(Webhook::check_datetime_match('2023-10-26', '2023-10-25', 'before'), '日付のみ比較 - before');

        // 時刻のみ (当日の日付として解釈される)
        $today_10am = date('Y-m-d') . ' 10:00:00';
        $today_11am = date('Y-m-d') . ' 11:00:00';
        $this->assertTrue(Webhook::check_datetime_match('10:00', $today_10am, 'equals'), '時刻のみ比較 - equals');
        $this->assertTrue(Webhook::check_datetime_match('10:00', $today_11am, 'after'), '時刻のみ比較 - after (10:00 < 11:00)');
    }
}
