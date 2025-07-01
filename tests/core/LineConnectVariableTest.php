<?php
/*
 * LineConnectクラスの変数関連のテストクラス
 * @package LineConnect
 */

use Shipweb\LineConnect\Core\LineConnect;

class LineConnectVariableTest extends WP_UnitTestCase {

    /**
     * Test for set_variable() and get_variable()
     */
    public function test_get_set_variable() {
        $variable_name = 'test_variable';
        $variable_value = 'test_value';
        $default_value = 'default';

        // 1. set_variableで値を設定
        LineConnect::set_variable($variable_name, $variable_value);

        // 2. get_variableで値を取得し、設定した値と一致することを確認
        $retrieved_value = LineConnect::get_variable($variable_name, $default_value);
        $this->assertEquals($variable_value, $retrieved_value);

        // 3. 存在しない変数を取得しようとした場合、デフォルト値が返されることを確認
        $non_existent_value = LineConnect::get_variable('non_existent_variable', $default_value);
        $this->assertEquals($default_value, $non_existent_value);

        // 4. 後処理：テストで設定したオプションを削除
        $variables = get_option(LineConnect::OPTION_KEY__VARIABLES);
        unset($variables[$variable_name]);
        update_option(LineConnect::OPTION_KEY__VARIABLES, $variables);
    }
}
