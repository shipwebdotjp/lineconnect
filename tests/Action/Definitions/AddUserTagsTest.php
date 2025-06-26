<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class AddUserTagsTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }
    public function test_add_new_tags_to_none_tags_user() {
        $func = new \Shipweb\LineConnect\Action\Definitions\AddUserTags();
        $func->set_secret_prefix("2f38");
        $func->set_event((object) array("source" => (object) array("userId" => "U4123ab4ac2bd7bc6e23018a1996263d5")));
        $tags = ['new_tag1', 'new_tag2'];
        $result = $func->add_user_tags($tags);
        $this->assertTrue($result);
        // タグを取得して検証
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("2f38");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "U4123ab4ac2bd7bc6e23018a1996263d5")));
        $result = $getTagsFunc->get_user_tags();
        $this->assertEqualSets($tags, $result);
    }

    public function test_add_new_tags() {
        $func = new \Shipweb\LineConnect\Action\Definitions\AddUserTags();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));

        $tags = ['new_tag1', 'new_tag2'];
        $result = $func->add_user_tags($tags);
        $this->assertTrue($result);

        // タグを取得して検証
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("04f7");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));
        $current_tags = $getTagsFunc->get_user_tags();

        $this->assertContains('new_tag1', $current_tags);
        $this->assertContains('new_tag2', $current_tags);
        $this->assertContains('プレミアム', $current_tags);
        $this->assertCount(3, $current_tags); // 元の1つ + 追加2つ
    }

    public function test_add_duplicate_tags() {
        $func = new \Shipweb\LineConnect\Action\Definitions\AddUserTags();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));

        $tags = ['プレミアム', 'new_tag'];
        $result = $func->add_user_tags($tags);
        $this->assertTrue($result);

        // タグを取得して検証
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("04f7");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));
        $current_tags = $getTagsFunc->get_user_tags();

        $this->assertCount(2, $current_tags); // 元の1つ + 新規1つ
        $this->assertContains('プレミアム', $current_tags);
        $this->assertContains('new_tag', $current_tags);
    }

    public function test_skip_duplicate_tags() {
        $func = new \Shipweb\LineConnect\Action\Definitions\AddUserTags();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));

        $tags = ['プレミアム']; // 既存のタグ
        $result = $func->add_user_tags($tags);
        $this->assertFalse($result);

        // タグを取得して検証
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("04f7");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));
        $current_tags = $getTagsFunc->get_user_tags();

        $this->assertCount(1, $current_tags); // タグ数は変わらない
        $this->assertContains('プレミアム', $current_tags);
    }

    public function test_invalid_user_id() {
        $func = new \Shipweb\LineConnect\Action\Definitions\AddUserTags();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Unotfound")));

        $tags = ['test_tag'];
        $result = $func->add_user_tags($tags);
        // error_log(print_r($result, true));

        $this->assertFalse($result);
    }

    public function test_empty_tags() {
        $func = new \Shipweb\LineConnect\Action\Definitions\AddUserTags();
        $func->set_secret_prefix("04f7");
        $func->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));

        // 最初のタグ状態を取得
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("04f7");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "Ud2be13c6f39c97f05c683d92c696483b")));
        $initial_tags = $getTagsFunc->get_user_tags();

        $tags = [];
        $result = $func->add_user_tags($tags);
        $this->assertFalse($result);

        // タグが変更されていないことを確認
        $current_tags = $getTagsFunc->get_user_tags();
        $this->assertEquals($initial_tags, $current_tags);
    }
}
