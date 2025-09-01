<?php

use Shipweb\LineConnect\Interaction\InputNormalizer;

class InputNormalizerTest extends WP_UnitTestCase {
    private $normalizer;

    public function setUp(): void {
        parent::setUp();
        $this->normalizer = new InputNormalizer();
    }

    public function test_normalize_non_string_input() {
        $this->assertEquals(123, $this->normalizer->normalize(123, []));
        $this->assertEquals(['a', 'b'], $this->normalizer->normalize(['a', 'b'], []));
        $this->assertEquals(null, $this->normalizer->normalize(null, []));
    }

    public function test_normalize_trim() {
        $rules = ['trim'];
        $this->assertEquals('hello', $this->normalizer->normalize('  hello  ', $rules));
    }

    public function test_normalize_omit_comma_and_hyphen() {
        $rules = ['omit_hyphen', 'omit_comma'];
        $this->assertEquals('0123456789', $this->normalizer->normalize('0123-456,789', $rules));
    }

    public function test_normalize_HanKatatoZenKata() {
        $rules = ['HanKatatoZenKata'];
        $this->assertEquals('アイウエオ', $this->normalizer->normalize('ｱｲｳｴｵ', $rules));
    }

    public function test_normalize_ZenKatatoZenKana() {
        $rules = ['ZenKatatoZenKana'];
        $this->assertEquals('あいうえお', $this->normalizer->normalize('アイウエオ', $rules));
    }

    public function test_normalize_ZenKanatoZenKata() {
        $rules = ['ZenKanatoZenKata'];
        $this->assertEquals('アイウエオ', $this->normalizer->normalize('あいうえお', $rules));
    }

    public function test_normalize_HanEisutoZenEisu() {
        $rules = ['HanEisutoZenEisu'];
        $this->assertEquals('ＡＢＣ１２３', $this->normalizer->normalize('ABC123', $rules));
    }

    public function test_normalize_ZenEisutoHanEisu() {
        $rules = ['ZenEisutoHanEisu'];
        $this->assertEquals('ABC123', $this->normalizer->normalize('ＡＢＣ１２３', $rules));
    }

    public function test_normalize_HanKatatoZenKana() {
        $rules = ['HanKatatoZenKana'];
        $this->assertEquals('あいうえお', $this->normalizer->normalize('ｱｲｳｴｵ', $rules));
    }

    public function test_normalize_combination() {
        // Order: trim -> ZenEisutoHanEisu -> omit_hyphen -> HanKatatoZenKata
        $rules = ['trim', 'ZenEisutoHanEisu', 'omit_hyphen', 'HanKatatoZenKata'];
        $this->assertEquals('ABC123 アイウエオ', $this->normalizer->normalize('  ＡＢＣ-１２３ ｱｲｳｴｵ  ', $rules));
    }

    public function test_normalize_no_rules() {
        $this->assertEquals('  hello  ', $this->normalizer->normalize('  hello  ', []));
    }

    public function test_normalize_empty_string() {
        $rules = ['trim'];
        $this->assertEquals('', $this->normalizer->normalize('', $rules));
    }

    public function test_normalize_string_with_only_omitted_chars() {
        $rules = ['omit_hyphen', 'omit_comma'];
        $this->assertEquals('', $this->normalizer->normalize('--,,--', $rules));
    }

    public function test_normalize_omit_inner_fullwidth_space() {
        $rules = ['omit_inner_fullwidth_space'];
        $this->assertEquals('あいう', $this->normalizer->normalize('あ　い　う', $rules));
    }

    public function test_normalize_with_emoji() {
        $rules = ['trim'];
        $this->assertEquals('hello😊', $this->normalizer->normalize('  hello😊  ', $rules));
    }
}
