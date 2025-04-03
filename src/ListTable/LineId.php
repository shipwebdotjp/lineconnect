<?php

/**
 * LINE IDをリスト表示する画面
 * WordPressのList Table Classを利用
 */

namespace Shipweb\LineConnect\ListTable;

use \LineConnect;
use \lineconnectConst;
use \lineconnectUtil;
use \Shipweb\LineConnect\Scenario\Scenario;

class LineId extends \WP_List_Table {
    /**
     * 初期設定画面を表示
     */
    function show_list() {
?>
        <wrap id="wrap-lineid-list-table">
            <h2><?php echo __('LINE ID List', lineconnect::PLUGIN_NAME); ?></h2>
            <form method="post" id="bulk-action-form">
                <?php
                $this->prepare_items();
                $this->search_box(__('Search', lineconnect::PLUGIN_NAME), 'search');
                $this->display();
                ?>
            </form>
        </wrap>
        <?php
    }

    /**
     * 初期化時の設定を行う
     */
    public function __construct($args = array()) {
        parent::__construct(
            array(
                'plural' => 'lineids',
                'screen' => isset($args['screen']) ? $args['screen'] : null,
            )
        );
    }

    /**
     * 表で使用されるカラム情報の連想配列を返す
     *
     * @return array
     */
    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'channel' => __('Channel', lineconnect::PLUGIN_NAME),
            'line_id' => __('LINE ID', lineconnect::PLUGIN_NAME),
            'follow' => __('Follow', lineconnect::PLUGIN_NAME),
            'profile' => __('Profile', lineconnect::PLUGIN_NAME),
            'tags' => __('Tags', lineconnect::PLUGIN_NAME),
            'interactions' => __('Interactions', lineconnect::PLUGIN_NAME),
            'scenarios' => __('Scenarios', lineconnect::PLUGIN_NAME),
            'stats' => __('Stats', lineconnect::PLUGIN_NAME),
            'created_at' => __('Created', lineconnect::PLUGIN_NAME),
            'updated_at' => __('Updated', lineconnect::PLUGIN_NAME),
        );
    }

    /**
     * プライマリカラム名を返す
     *
     * @return string
     */
    protected function get_primary_column_name() {
        return 'id';
    }
    /**
     * ソート可能なカラムを返す
     *
     * @return array
     */
    protected function get_sortable_columns() {
        return array(
            'id' => array('id', false),
            'timestamp' => array('timestamp', false),
        );
    }
    /**
     * 表示するデータを準備する
     */
    public function prepare_items() {
        global $wpdb;

        $orderby = (! empty($_GET['orderby'])) ? $_GET['orderby'] : 'id';
        $order   = (! empty($_GET['order'])) ? $_GET['order'] : 'desc';
        // sanitize $orderby
        $allowed_keys = array_keys($this->get_sortable_columns());
        if (! in_array($orderby, $allowed_keys)) {
            $orderby = 'id';
        }
        // sanitize $order
        if (! in_array($order, array('asc', 'desc'))) {
            $order = 'desc';
        }

        $per_page     = (int) 20;
        $current_page = (int) $this->get_pagenum();
        $start_from   = ($current_page - 1) * $per_page;

        $keyvalues = array();
        if (! lineconnectUtil::is_empty($_REQUEST['s'] ?? null)) {
            $keyvalues[] = array(
                'key' => 'AND (profile LIKE %s OR tags LIKE %s OR line_id LIKE %s)',
                'value' => array(
                    '%' . $wpdb->esc_like($_REQUEST['s']) . '%',
                    '%' . $wpdb->esc_like($_REQUEST['s']) . '%',
                    '%' . $wpdb->esc_like($_REQUEST['s']) . '%',
                ),
            );
        }

        if (! lineconnectUtil::is_empty($_REQUEST['channel'] ?? null)) {
            $keyvalues[] = array(
                'key' => 'AND channel_prefix = %s',
                'value' => array($_REQUEST['channel']),
            );
        }
        $addtional_query = '';

        if (! empty($keyvalues)) {
            $keys   = '';
            $values = array();
            foreach ($keyvalues as $keyval) {
                $keys  .= $keyval['key'];
                $values = array_merge($values, $keyval['value']);
            }
            // cut first "AND"
            $keys            = 'WHERE' . substr($keys, 3);
            $addtional_query = $wpdb->prepare($keys, $values);
        }
        // error_log($addtional_query);
        $table_name = $wpdb->prefix . lineconnectConst::TABLE_LINE_ID;
        $query      = "
            SELECT COUNT(id) 
            FROM {$table_name}
            {$addtional_query}";

        $total_items = $wpdb->get_var($query);
        $lineids = array();
        if ($total_items > 0) {
            $query = "
                SELECT id, channel_prefix, line_id, follow, profile, tags, interactions, scenarios, stats, created_at, updated_at
                FROM {$table_name}
                {$addtional_query}
                ORDER BY {$orderby} {$order} 
				LIMIT %d, %d";
            $query = $wpdb->prepare($query, array($start_from, $per_page));
            $lineids = $wpdb->get_results($query, ARRAY_A);
        }
        $this->items = $lineids;
        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil($total_items / $per_page),
            )
        );
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'channel':
            case 'line_id':
            case 'follow':
            case 'profile':
            case 'tags':
            case 'interactions':
            case 'scenarios':
            case 'stats':
            case 'created_at':
            case 'updated_at':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="lineids[]" value="%s" />',
            $item['id']
        );
    }

    public function column_channel($item) {
        $channel = lineconnect::get_channel($item['channel_prefix']);
        return empty($channel) ? $item['channel_prefix'] : $channel['name'];
    }

    public function column_follow($item) {
        return $item['follow'] ?
            '<span class="dashicons dashicons-yes"></span>' :
            '<span class="dashicons dashicons-no"></span>';
    }

    public function column_profile($item) {
        $profile = json_decode($item['profile'] ?? '', true);
        $profile_text = '';
        if (is_array($profile)) {
            if (isset($profile['pictureUrl'])) {
                $profile_text  .= '<img src="' . $profile['pictureUrl'] . '" alt="Profile Picture" style="width: 50px; height: 50px; border-radius: 50%;" />';
            }
            $profile_text .= isset($profile['displayName']) ? $profile['displayName'] : '';
        } else {
            $profile = '';
        }

        return $profile_text;
    }

    public function column_tags($item) {
        $tags = json_decode($item['tags'] ?? '', true);
        if (is_array($tags)) {
            $tags = implode(', ', $tags);
        } else {
            $tags = '';
        }
        return $tags;
    }

    public function column_interactions($item) {
        $interactions = json_decode($item['interactions'] ?? '', true);
        if (is_array($interactions)) {
            $interactions = count($interactions);
        } else {
            $interactions = 0;
        }
        return $interactions;
    }

    public function column_scenarios($item) {
        $scenario_status_name = [
            Scenario::STATUS_ACTIVE => __('Active', lineconnect::PLUGIN_NAME),
            Scenario::STATUS_COMPLETED => __('Completed', lineconnect::PLUGIN_NAME),
            Scenario::STATUS_ERROR => __('Error', lineconnect::PLUGIN_NAME),
            Scenario::STATUS_PAUSED => __('Paused', lineconnect::PLUGIN_NAME),
        ];
        $scenarios = json_decode($item['scenarios'] ?? '', true);
        if (is_array($scenarios)) {
            $scenario_count = [];
            foreach ($scenarios as $key => $scenario) {
                if (isset($scenario['status'])) {
                    $status = $scenario['status'];
                    if (isset($scenario_status_name[$status])) {
                        $scenario_count[$scenario_status_name[$status]] = isset($scenario_count[$scenario_status_name[$status]]) ? $scenario_count[$scenario_status_name[$status]] + 1 : 1;
                    }
                }
            }
            $scenarios = '';
            foreach ($scenario_count as $status => $count) {
                $scenarios .= '<span class="scenario-status">' . $status . ': ' . $count . '</span>';
            }
        } else {
            $scenarios = '';
        }
        return $scenarios;
    }

    public function column_created_at($item) {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['created_at']));
    }
    public function column_updated_at($item) {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['updated_at']));
    }

    protected function extra_tablenav($which) {
        if ($which == 'top') {
        ?>
            <div class="alignleft actions bulkactions">
                <select name="channel" id="channel">
                    <option value=""><?php echo __('All Channels', lineconnect::PLUGIN_NAME); ?></option>
                    <?php
                    foreach (lineconnect::get_all_channels() as $key => $value) {
                        echo '<option value="' . $value['prefix'] . '" ' . (isset($_REQUEST['channel']) && $value['prefix'] === $_REQUEST['channel'] ? 'selected="selected"' : '') . '>' . $value['name'] . '</option>';
                    }
                    ?>
                </select>
                <?php submit_button(__('Filter'), '', 'filter_action', false, array('id' => 'post-query-submit')); ?>
            </div>
<?php
        }
    }
}
