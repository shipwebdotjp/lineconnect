<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class UpdateUserTagsTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_update_tags_with_new_values() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserTags();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));

        $new_tags = ['updated_tag1', 'updated_tag2'];
        $result = $func->update_user_tags($new_tags);
        $this->assertTrue($result);

        // タグを取得して検証
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("04f7");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));
        $result = $getTagsFunc->get_user_tags();
        $this->assertEqualSets($new_tags, $result);
    }

    public function test_clear_all_tags() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserTags();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));

        $result = $func->update_user_tags([]);
        $this->assertTrue($result);

        // タグが空になっていることを確認
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("04f7");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));
        $current_tags = $getTagsFunc->get_user_tags();
        $this->assertEmpty($current_tags);
    }

    public function test_invalid_user_id() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserTags();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Unotfound")));

        $tags = ['test_tag'];
        $result = $func->update_user_tags($tags);
        $this->assertFalse($result);
    }

    public function test_update_tags_for_user_without_tags() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserTags();
        $func->set_secret_prefix("2f38");
        $func->set_event((object) array("source" => (object) array("userId" => "U4123ab4ac2bd7bc6e23018a1996263d5")));

        $tags = ['new_tag1', 'new_tag2'];
        $result = $func->update_user_tags($tags);
        $this->assertTrue($result);

        // タグを取得して検証
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("2f38");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "U4123ab4ac2bd7bc6e23018a1996263d5")));
        $result = $getTagsFunc->get_user_tags();
        $this->assertEqualSets($tags, $result);
    }

    public function test_no_changes_when_same_tags_provided() {
        $func = new \Shipweb\LineConnect\Action\Definitions\UpdateUserTags();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));

        // 最初のタグ状態を取得
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("04f7");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));
        $initial_tags = $getTagsFunc->get_user_tags();

        // 同じタグで更新を試みる
        $result = $func->update_user_tags($initial_tags);
        $this->assertFalse($result);

        // タグが変更されていないことを確認
        $current_tags = $getTagsFunc->get_user_tags();
        $this->assertEquals($initial_tags, $current_tags);
    }
}
