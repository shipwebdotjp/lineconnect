<?php

use Shipweb\LineConnect\PostType\Audience\Audience as Audience;

class RemoveUserTagsTest extends WP_UnitTestCase {
    protected static $result;
    public static function wpSetUpBeforeClass($factory) {
        self::$result = lineconnectTest::init();
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function test_remove_tags() {
        // First, add some tags to a user
        $addTagsFunc = new \Shipweb\LineConnect\Action\Definitions\AddUserTags();
        $addTagsFunc->set_secret_prefix("04f7");
        $addTagsFunc->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $tags_to_add = ['tag1', 'tag2', 'tag3'];
        $addTagsFunc->add_user_tags($tags_to_add);

        // Now, remove some of the tags
        $removeTagsFunc = new \Shipweb\LineConnect\Action\Definitions\RemoveUserTags();
        $removeTagsFunc->set_secret_prefix("04f7");
        $removeTagsFunc->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $tags_to_remove = ['tag1', 'tag3'];
        $result = $removeTagsFunc->remove_user_tags($tags_to_remove);
        $this->assertTrue($result);

        // Verify the tags are removed
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("04f7");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $current_tags = $getTagsFunc->get_user_tags();
        $this->assertNotContains('tag1', $current_tags);
        $this->assertContains('tag2', $current_tags);
        $this->assertNotContains('tag3', $current_tags);
        $this->assertContains('プレミアム', $current_tags);
        $this->assertCount(2, $current_tags);
    }

    public function test_remove_non_existent_tags() {
        // Get the initial tags
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("04f7");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $initial_tags = $getTagsFunc->get_user_tags();

        // Attempt to remove a tag that doesn't exist
        $removeTagsFunc = new \Shipweb\LineConnect\Action\Definitions\RemoveUserTags();
        $removeTagsFunc->set_secret_prefix("04f7");
        $removeTagsFunc->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $tags_to_remove = ['non_existent_tag'];
        $result = $removeTagsFunc->remove_user_tags($tags_to_remove);
        $this->assertTrue($result);

        // Verify that the tags haven't changed
        $current_tags = $getTagsFunc->get_user_tags();
        $this->assertEquals($initial_tags, $current_tags);
    }

    public function test_remove_empty_tags_array() {
        // Get the initial tags
        $getTagsFunc = new \Shipweb\LineConnect\Action\Definitions\GetUserTags();
        $getTagsFunc->set_secret_prefix("04f7");
        $getTagsFunc->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $initial_tags = $getTagsFunc->get_user_tags();

        // Attempt to remove an empty array of tags
        $removeTagsFunc = new \Shipweb\LineConnect\Action\Definitions\RemoveUserTags();
        $removeTagsFunc->set_secret_prefix("04f7");
        $removeTagsFunc->set_event((object) array("source" => (object) array("userId" => "U_PLACEHOLDER_USERID4e7a9902e5e7d")));
        $tags_to_remove = [];
        $result = $removeTagsFunc->remove_user_tags($tags_to_remove);
        $this->assertTrue($result);

        // Verify that the tags haven't changed
        $current_tags = $getTagsFunc->get_user_tags();
        $this->assertEquals($initial_tags, $current_tags);
    }

    public function test_invalid_user_id() {
        $removeTagsFunc = new \Shipweb\LineConnect\Action\Definitions\RemoveUserTags();
        $removeTagsFunc->set_secret_prefix("04f7");
        $removeTagsFunc->set_event((object) array("source" => (object) array("userId" => "Unotfound")));
        $tags_to_remove = ['test_tag'];
        $result = $removeTagsFunc->remove_user_tags($tags_to_remove);
        $this->assertFalse($result);
    }
}
