<?php

use Shipweb\LineConnect\Interaction\InputNormalizer;

class InputNormalizerTest extends WP_UnitTestCase {
    private $normalizer;

    public function setUp(): void {
        parent::setUp();
        $this->normalizer = new InputNormalizer();
    }

    public function test_normalize_non_string_input() {
        $this->assertEquals(123, $this->normalizer->normalize(123, (object)[]));
        $this->assertEquals(['a', 'b'], $this->normalizer->normalize(['a', 'b'], (object)[]));
        $this->assertEquals(null, $this->normalizer->normalize(null, (object)[]));
    }

    public function test_normalize_trim() {
        $rules = (object)['trim' => true];
        $this->assertEquals('hello', $this->normalizer->normalize('  hello  ', $rules));
    }

    public function test_normalize_omit() {
        $rules = (object)['omit' => '-,'];
        $this->assertEquals('0123456789', $this->normalizer->normalize('0123-456,789', $rules));
    }

    public function test_normalize_HanKatatoZenKata() {
        $rules = (object)['HanKatatoZenKata' => true];
        $this->assertEquals('アイウエオ', $this->normalizer->normalize('ｱｲｳｴｵ', $rules));
    }

    public function test_normalize_ZenKatatoZenKana() {
        $rules = (object)['ZenKatatoZenKana' => true];
        $this->assertEquals('あいうえお', $this->normalizer->normalize('アイウエオ', $rules));
    }

    public function test_normalize_ZenKanatoZenKata() {
        $rules = (object)['ZenKanatoZenKata' => true];
        $this->assertEquals('アイウエオ', $this->normalizer->normalize('あいうえお', $rules));
    }

    public function test_normalize_HanEisutoZenEisu() {
        $rules = (object)['HanEisutoZenEisu' => true];
        $this->assertEquals('ＡＢＣ１２３', $this->normalizer->normalize('ABC123', $rules));
    }

    public function test_normalize_ZenEisutoHanEisu() {
        $rules = (object)['ZenEisutoHanEisu' => true];
        $this->assertEquals('ABC123', $this->normalizer->normalize('ＡＢＣ１２３', $rules));
    }

    public function test_normalize_HanKatatoZenKana() {
        $rules = (object)['HanKatatoZenKana' => true];
        $this->assertEquals('あいうえお', $this->normalizer->normalize('ｱｲｳｴｵ', $rules));
    }

    public function test_normalize_combination() {
        $rules = (object)[
            'trim' => true,
            'omit' => '-',
            'ZenEisutoHanEisu' => true,
            'HanKatatoZenKata' => true,
        ];
        $this->assertEquals('ABC123 アイウエオ', $this->normalizer->normalize('  ＡＢＣ-１２３ ｱｲｳｴｵ  ', $rules));
    }

    public function test_normalize_no_rules() {
        $this->assertEquals('  hello  ', $this->normalizer->normalize('  hello  ', (object)[]));
    }

    public function test_normalize_empty_string() {
        $rules = (object)['trim' => true, 'omit' => 'a'];
        $this->assertEquals('', $this->normalizer->normalize('', $rules));
    }

    public function test_normalize_string_with_only_omitted_chars() {
        $rules = (object)['omit' => '-,'];
        $this->assertEquals('', $this->normalizer->normalize('--,,--', $rules));
    }

    public function test_normalize_multibyte_omit() {
        $rules = (object)['omit' => 'あ'];
        $this->assertEquals('いうえお', $this->normalizer->normalize('あいうえお', $rules));
    }

    public function test_normalize_with_emoji() {
        $rules = (object)['trim' => true];
        $this->assertEquals('hello😊', $this->normalizer->normalize('  hello😊  ', $rules));
    }
}