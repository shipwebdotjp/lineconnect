<?php

use \Shipweb\LineConnect\Utilities\Condition;
class UtilConditionDestinationTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass( $factory ) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_evaluate_destination(){
        $this->assertTrue(Condition::evaluate_destination(['type'=>'user'], 'Ud2be13c6f39c97f05c683d92c696483b'), 'TYPE USER のみ指定 LINE USER ID');
        $this->assertFalse(Condition::evaluate_destination(['type'=>'user'], 'Cd2be13c6f39c97f05c683d92c696483b'), 'TYPE USER のみ指定 GROUP ID');

        $this->assertTrue(Condition::evaluate_destination(['type'=>'user','lineUserId'=>['Ud2be13c6f39c97f05c683d92c696483b']], 'Ud2be13c6f39c97f05c683d92c696483b'), 'LINE USER IDあり');
        $this->assertFalse(Condition::evaluate_destination(['type'=>'user','lineUserId'=>['U131aa592ec09610ca4d5e36f4b60ccdb']], 'Ud2be13c6f39c97f05c683d92c696483b'), 'LINE USER IDなし');

        $this->assertTrue(Condition::evaluate_destination(['type'=>'group'], 'Cd2be13c6f39c97f05c683d92c696483b'), 'TYPE GROUP のみ指定 GROUP ID');
        $this->assertFalse(Condition::evaluate_destination(['type'=>'group'], 'Ud2be13c6f39c97f05c683d92c696483b'), 'TYPE GROUP のみ指定 LINE USER ID');

        $this->assertTrue(Condition::evaluate_destination(['type'=>'group', 'groupId'=>['Cd2be13c6f39c97f05c683d92c696483b']], 'Cd2be13c6f39c97f05c683d92c696483b'), 'GROUP IDあり');
        $this->assertFalse(Condition::evaluate_destination(['type'=>'group', 'groupId'=>['C131aa592ec09610ca4d5e36f4b60ccdb']], 'Cd2be13c6f39c97f05c683d92c696483b'), 'GROUP IDなし');

        $this->assertTrue(Condition::evaluate_destination(['type'=>'room'], 'Rd2be13c6f39c97f05c683d92c696483b'), 'TYPE ROOM のみ指定 ROOM ID');
        $this->assertFalse(Condition::evaluate_destination(['type'=>'room'], 'Ud2be13c6f39c97f05c683d92c696483b'), 'TYPE ROOM のみ指定 LINE USER ID');

        $this->assertTrue(Condition::evaluate_destination(['type'=>'room', 'roomId'=>['Rd2be13c6f39c97f05c683d92c696483b']], 'Rd2be13c6f39c97f05c683d92c696483b'), 'ROOM IDあり');
        $this->assertFalse(Condition::evaluate_destination(['type'=>'room', 'roomId'=>['R131aa592ec09610ca4d5e36f4b60ccdb']], 'Rd2be13c6f39c97f05c683d92c696483b'), 'ROOM IDなし');

        $this->assertFalse(Condition::evaluate_condition(
            [
                'type' => 'destination',
                'destination' =>
                    ['type' => 'user', 'lineUserId' => ['Ud2be13c6f39c97f05c683d92c696483b']]
            ], '04f7', 'U131aa592ec09610ca4d5e36f4b60ccdb'), 'DESTINATIONの条件に従って評価失敗');
    }

}