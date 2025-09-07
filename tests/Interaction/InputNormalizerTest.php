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
        $this->assertEquals('日本語', $this->normalizer->normalize('　日本語　', $rules));
        $this->assertEquals('不満', $this->normalizer->normalize('不満', $rules));
    }

    public function test_normalize_omit_comma_and_hyphen() {
        $rules = ['omit_hyphen', 'omit_comma'];
        $this->assertEquals('0123456789', $this->normalizer->normalize('0123-456,789', $rules));
    }

    public function test_normalize_omit_comma_fullwidth() {
        $rules = ['omit_comma'];
        $this->assertEquals('0123456789', $this->normalizer->normalize('0123，456、789', $rules));
        $this->assertEquals('あいうえお', $this->normalizer->normalize('あ、い、う、え、お', $rules));
        $this->assertEquals('テスト文字列', $this->normalizer->normalize('テ，スト，文，字，列', $rules));
    }

    public function test_normalize_omit_hyphen_fullwidth() {
        $rules = ['omit_hyphen'];
        $this->assertEquals('0123456789', $this->normalizer->normalize('0123ー456－789', $rules));
        $this->assertEquals('0123456789', $this->normalizer->normalize('0123—456‑789', $rules));
        $this->assertEquals('0123456789', $this->normalizer->normalize('0123−456ー789', $rules));
        $this->assertEquals('カタカナ', $this->normalizer->normalize('カタカーナ', $rules));
    }

    public function test_normalize_omit_comma_and_hyphen_mixed() {
        $rules = ['omit_hyphen', 'omit_comma'];
        $this->assertEquals('0123456789', $this->normalizer->normalize('0123-456，789', $rules));
        $this->assertEquals('0123456789', $this->normalizer->normalize('0123ー456,789', $rules));
        $this->assertEquals('0123456789', $this->normalizer->normalize('0123－456、789', $rules));
        $this->assertEquals('あいうえお', $this->normalizer->normalize('あ-い、うーえ、お', $rules));
    }

    public function test_normalize_omit_comma_and_hyphen_edge_cases() {
        $rules = ['omit_hyphen', 'omit_comma'];
        $this->assertEquals('先頭末尾', $this->normalizer->normalize('、先頭末尾ー', $rules));
        $this->assertEquals('文字列', $this->normalizer->normalize('ー文字列、', $rules));
        $this->assertEquals('', $this->normalizer->normalize('ー，ー，', $rules));
    }

    public function test_normalize_HanKatatoZenKata() {
        $rules = ['HanKatatoZenKata'];
        $this->assertEquals('アイウエオ', $this->normalizer->normalize('ｱｲｳｴｵ', $rules));
        // 半角カタカナの濁点・半濁点もテスト
        $this->assertEquals('ガギグゲゴ', $this->normalizer->normalize('ｶﾞｷﾞｸﾞｹﾞｺﾞ', $rules));
        $this->assertEquals('パピプペポ', $this->normalizer->normalize('ﾊﾟﾋﾟﾌﾟﾍﾟﾎﾟ', $rules));
        // 混合文字列
        $this->assertEquals('アイウエオ123', $this->normalizer->normalize('ｱｲｳｴｵ123', $rules));
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

    public function test_normalize_omit_inner_halfwidth_space() {
        $rules = ['omit_inner_halfwidth_space'];
        $this->assertEquals('あいう', $this->normalizer->normalize('あ い う', $rules));
        $this->assertEquals('ABC123', $this->normalizer->normalize('A B C 1 2 3', $rules));
        // 先頭と末尾のスペースは保持される（trimルールと組み合わせる必要がある）
        $this->assertEquals(' あいう ', $this->normalizer->normalize(' あ い う ', $rules));
    }

    public function test_normalize_with_emoji() {
        $rules = ['trim'];
        $this->assertEquals('hello😊', $this->normalizer->normalize('  hello😊  ', $rules));
    }
}
