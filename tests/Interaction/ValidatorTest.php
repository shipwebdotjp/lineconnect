<?php

use Shipweb\LineConnect\Interaction\Validator;
use Shipweb\LineConnect\Interaction\ValidationResult;

class ValidatorTest extends WP_UnitTestCase {
    public function test_time_valid() {
        $v = new Validator();
        $rules = [(object)['time' => true]];
        $res = $v->validate('09:30', $rules);
        $this->assertTrue($res->isValid());
    }

    public function test_time_invalid() {
        $v = new Validator();
        $rules = [(object)['time' => true]];
        $res = $v->validate('25:00', $rules);
        $this->assertFalse($res->isValid());
        $this->assertStringContainsString('Invalid time format', $res->getErrors()[0]);
    }

    public function test_datetime_valid() {
        $v = new Validator();
        $rules = [(object)['datetime' => true]];
        $res = $v->validate('2025-08-16 09:30', $rules);
        $this->assertTrue($res->isValid());
    }

    public function test_datetime_invalid_date_part() {
        $v = new Validator();
        $rules = [(object)['datetime' => true]];
        $res = $v->validate('2025-02-30 09:30', $rules); // Feb 30 is invalid
        $this->assertFalse($res->isValid());
        $this->assertStringContainsString('Invalid datetime (date part is not valid)', $res->getErrors()[0]);
    }

    public function test_url_valid() {
        $v = new Validator();
        $rules = [(object)['url' => true]];
        $res = $v->validate('https://example.com/path?x=1', $rules);
        $this->assertTrue($res->isValid());
    }

    public function test_url_invalid_scheme() {
        $v = new Validator();
        $rules = [(object)['url' => true]];
        $res = $v->validate('ftp://example.com', $rules);
        $this->assertFalse($res->isValid());
        $this->assertStringContainsString('Only http and https are allowed', $res->getErrors()[0]);
    }

    public function test_enum_valid_and_invalid() {
        $v = new Validator();
        $rules = [(object)['enum' => ['red', 'blue']]];

        $res1 = $v->validate('red', $rules);
        $this->assertTrue($res1->isValid());

        $res2 = $v->validate('green', $rules);
        $this->assertFalse($res2->isValid());
        $this->assertStringContainsString('Must be one of', $res2->getErrors()[0]);
    }

    public function test_forbidden_words_and_patterns() {
        $v = new Validator();

        // Forbidden words
        $rules_words = [(object)['forbidden' => (object)['words' => ['bad', 'evil']]]];
        $res_ok = $v->validate('this is good', $rules_words);
        $this->assertTrue($res_ok->isValid());

        $res_bad = $v->validate('this is bad content', $rules_words);
        $this->assertFalse($res_bad->isValid());
        $this->assertStringContainsString('Contains forbidden content', $res_bad->getErrors()[0]);
        $this->assertStringContainsString('bad', $res_bad->getErrors()[0]);

        // Forbidden patterns
        $rules_patterns = [(object)['forbidden' => (object)['patterns' => ['^abc']]]];
        $res_pat = $v->validate('abcdef', $rules_patterns);
        $this->assertFalse($res_pat->isValid());
        $this->assertStringContainsString('pattern matched', $res_pat->getErrors()[0]);
        $this->assertStringContainsString('^abc', $res_pat->getErrors()[0]);
    }

    // --- Additional tests for remaining validators in Validator.php ---

    public function test_required_present_and_empty() {
        $v = new Validator();
        $rules = [(object)['required' => true]];

        $res_ok = $v->validate('value', $rules);
        $this->assertTrue($res_ok->isValid());

        $res_empty = $v->validate('', $rules);
        $this->assertFalse($res_empty->isValid());
        $this->assertStringContainsString('required', strtolower($res_empty->getErrors()[0]));

        $res_null = $v->validate(null, $rules);
        $this->assertFalse($res_null->isValid());
    }

    public function test_email_valid_and_invalid() {
        $v = new Validator();
        $rules = [(object)['email' => true]];

        $res_ok = $v->validate('user@example.com', $rules);
        $this->assertTrue($res_ok->isValid());

        $res_bad = $v->validate('not-an-email', $rules);
        $this->assertFalse($res_bad->isValid());
        $this->assertStringContainsString('Invalid email', $res_bad->getErrors()[0]);
    }

    public function test_number_checks_and_bounds() {
        $v = new Validator();
        $rules = [(object)['number' => (object)['min' => 1, 'max' => 10]]];

        $res_ok = $v->validate('5', $rules);
        $this->assertTrue($res_ok->isValid());

        $res_too_small = $v->validate('0', $rules);
        $this->assertFalse($res_too_small->isValid());
        $this->assertStringContainsString('at least', $res_too_small->getErrors()[0]);

        $res_too_large = $v->validate('11', $rules);
        $this->assertFalse($res_too_large->isValid());
        $this->assertStringContainsString('at most', $res_too_large->getErrors()[0]);

        $res_not_number = $v->validate('abc', $rules);
        $this->assertFalse($res_not_number->isValid());
        $this->assertStringContainsString('number', $res_not_number->getErrors()[0]);
    }

    public function test_length_min_and_max() {
        $v = new Validator();
        $rules = [(object)['length' => (object)['minlength' => 2, 'maxlength' => 5]]];

        $res_short = $v->validate('a', $rules);
        $this->assertFalse($res_short->isValid());
        $this->assertStringContainsString('at least', $res_short->getErrors()[0]);

        $res_ok = $v->validate('abc', $rules);
        $this->assertTrue($res_ok->isValid());

        $res_long = $v->validate('abcdef', $rules);
        $this->assertFalse($res_long->isValid());
        $this->assertStringContainsString('at most', $res_long->getErrors()[0]);
    }

    public function test_regex_valid_and_invalid() {
        $v = new Validator();
        $rules = [(object)['regex' => '/^[a-z]+$/u']];

        $res_ok = $v->validate('abcxyz', $rules);
        $this->assertTrue($res_ok->isValid());

        $res_bad = $v->validate('abc123', $rules);
        $this->assertFalse($res_bad->isValid());
        $this->assertStringContainsString('Invalid format', $res_bad->getErrors()[0]);

        // null pattern should be skipped
        $rules_none = [(object)['regex' => null]];
        $res_skip = $v->validate('anything', $rules_none);
        $this->assertTrue($res_skip->isValid());
    }

    public function test_phone_valid_and_invalid() {
        $v = new Validator();

        $rules = [(object)['phone' => true]];

        $res_ok1 = $v->validate('03-1234-5678', $rules); // Tokyo landline -> 0312345678 (10 digits)
        $this->assertTrue($res_ok1->isValid());

        $res_ok2 = $v->validate('090-1234-5678', $rules); // mobile -> 09012345678 (11 digits)
        $this->assertTrue($res_ok2->isValid());

        $res_bad = $v->validate('1234', $rules);
        $this->assertFalse($res_bad->isValid());
        $this->assertStringContainsString('phone', strtolower($res_bad->getErrors()[0]));
    }

    public function test_date_valid_and_invalid() {
        $v = new Validator();
        $rules = [(object)['date' => true]];

        $res_ok = $v->validate('2025-08-16', $rules);
        $this->assertTrue($res_ok->isValid());

        $res_invalid_format = $v->validate('16-08-2025', $rules);
        $this->assertFalse($res_invalid_format->isValid());

        $res_invalid_date = $v->validate('2025-02-30', $rules);
        $this->assertFalse($res_invalid_date->isValid());
    }

    // --- Additional tests for skip-on-empty behavior and edge cases ---

    public function test_empty_values_are_skipped_for_optional_validators() {
        $v = new Validator();

        $rules_number = [(object)['number' => (object)[]]];
        $this->assertTrue($v->validate('', $rules_number)->isValid(), 'Empty number input should be skipped');

        $rules_email = [(object)['email' => true]];
        $this->assertTrue($v->validate('', $rules_email)->isValid(), 'Empty email input should be skipped');

        $rules_url = [(object)['url' => true]];
        $this->assertTrue($v->validate('', $rules_url)->isValid(), 'Empty url input should be skipped');

        $rules_enum = [(object)['enum' => ['a', 'b']]];
        $this->assertTrue($v->validate('', $rules_enum)->isValid(), 'Empty enum input should be skipped');

        $rules_forbidden_empty = [(object)['forbidden' => (object)[]]];
        $this->assertTrue($v->validate('anything', $rules_forbidden_empty)->isValid(), 'Empty forbidden params should be skipped');
    }

    public function test_japanese_hiragana_valid_and_invalid() {
        $v = new Validator();
        $rules = [(object)['japanese' => 'hiragana']];

        $res_ok = $v->validate('こんにちは', $rules); // hiragana
        $this->assertTrue($res_ok->isValid());

        $res_bad = $v->validate('コンニチハ', $rules); // katakana should fail
        $this->assertFalse($res_bad->isValid());
        $this->assertStringContainsString('hiragana', strtolower($res_bad->getErrors()[0]));

        //kanji should fail
        $res_bad_kanji = $v->validate('漢字を含む文章', $rules);
        $this->assertFalse($res_bad_kanji->isValid());
        $this->assertStringContainsString('hiragana', strtolower($res_bad_kanji->getErrors()[0]));
    }

    public function test_japanese_katakana_valid_and_invalid() {
        $v = new Validator();
        $rules = [(object)['japanese' => 'katakana']];

        $res_ok = $v->validate('コンニチハ', $rules); // katakana
        $this->assertTrue($res_ok->isValid());

        // allow long vowel (ー) and middle dot (・)
        $res_ok_with_symbols = $v->validate('スーパー・マーケットー', $rules);
        $this->assertTrue($res_ok_with_symbols->isValid());

        $res_good_halfwidth = $v->validate('ｺﾝﾆﾁﾊ', $rules);
        $this->assertTrue($res_good_halfwidth->isValid());

        $res_bad_kanji = $v->validate('カンジを含む文章', $rules);
        $this->assertFalse($res_bad_kanji->isValid());
        $this->assertStringContainsString('katakana', strtolower($res_bad_kanji->getErrors()[0]));
    }
}
