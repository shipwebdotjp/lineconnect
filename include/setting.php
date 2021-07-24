<?php
/**
 * Lineconnect
 * 管理画面でのプラグイン設定画面
 */
class lineconnectSetting{
    /**
     * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
     * 管理画面のトップメニューページを追加する関数
     */
    static function set_plugin_menu() {
        // 設定のサブメニュー「LINE Connect」を追加
        $page_hook_suffix = add_options_page(
            // ページタイトル：
            'LINE Connect 設定',
            // メニュータイトル：
            'LINE Connect',
            // 権限：
            // manage_optionsは以下の管理画面設定へのアクセスを許可
            // ・設定 > 一般設定
            // ・設定 > 投稿設定
            // ・設定 > 表示設定
            // ・設定 > ディスカッション
            // ・設定 > パーマリンク設定
            'manage_options',
            // ページを開いたときのURL(slug)：
            lineconnect::SLUG__SETTINGS_FORM,
            // メニューに紐づく画面を描画するcallback関数：
            ['lineconnectSetting', 'show_settings']
        );
        add_action( "admin_print_styles-{$page_hook_suffix}", ['lineconnectSetting', 'wpdocs_plugin_admin_styles']);
        add_action( "admin_print_scripts-{$page_hook_suffix}", ['lineconnectSetting', 'wpdocs_plugin_admin_scripts']);
    }
    
    /**
     * 初期設定画面を表示
     */
    static function show_settings() {
        // プラグインのオプション
        $plugin_options = lineconnect::get_all_options();

        // 初期設定の保存完了メッセージ
        if (false !== ($complete_message = get_transient(lineconnect::TRANSIENT_KEY__SAVE_SETTINGS))) {
            $complete_message = lineconnect::getNotice($complete_message, lineconnect::NOTICE_TYPE__SUCCESS);
        }
        
        // nonceフィールドを生成・取得
        $nonce_field = wp_nonce_field(lineconnect::CREDENTIAL_ACTION__SETTINGS_FORM, lineconnect::CREDENTIAL_NAME__SETTINGS_FORM, true, false);

        // 開いておくタブ
        $active_tab = 0;
        echo <<< EOM
        {$complete_message}
        <form action="" method='post' id="line-auto-post-settings-form">
        <div class="wrap ui-tabs ui-corner-all ui-widget ui-widget-content" id="stabs">
            <ul class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
EOM;
        foreach(lineconnect::SETTINGS_OPTIONS as $tab_name => $tab_details){    
                echo "<li class='ui-tabs-tab ui-corner-top ui-state-default ui-tab'><a href='#stabs-{$tab_details['prefix']}'>{$tab_details['name']}</a></li>";
        }
        echo <<< EOM
                </ul>
EOM;
        foreach(lineconnect::SETTINGS_OPTIONS as $tab_name => $tab_details){
            switch($tab_name){   
                case 'channel': 
                    echo <<< EOM
                        <div id="stabs-1" class="ui-tabs-panel ui-corner-bottom ui-widget-content">
                        <h3>チャネル設定</h3>
                        <div class="metabox-holder">
                        {$nonce_field}
EOM;
                    //チャンネルリスト毎に出力
                    foreach(lineconnect::get_all_channels() as $channel_id => $channel){
                        
                        $ary_option = array();
                        foreach(lineconnect::CHANNEL_OPTION as $option_key => $option_name){
                            $options = array();
                            
                            // 不正メッセージ
                            if (false !== ($invalid = get_transient(lineconnect::INVALID_PREFIX.$option_key.$channel['prefix']))) {
                                $options['invalid'] = lineconnect::getErrorBar($invalid, lineconnect::NOTICE_TYPE__ERROR);
                            }
                            //パラメータ名
                            $options['param'] = lineconnect::PARAMETER_PREFIX.$option_key.$channel['prefix'];

                            //設定値
                            if (false === ($value = get_transient(lineconnect::TRANSIENT_PREFIX.$option_key.$channel['prefix']))) {
                                // 無ければoptionsテーブルから取得
                                $value = $channel[$option_key];
                            }
                            $options['value'] = esc_html($value);
                            $ary_option[$option_key] = $options;
                        }
                        //シークレットの先頭4文字
                        $secret_prefix = substr( $ary_option['channel-secret']['value'],0,4);
                        
                        //チャンネル名出力
                        echo "<div class='postbox'>";
                        echo "<h3 class='hndle'><span>{$ary_option['name']['value']}</span></h3>";
                        echo "<div class='inside'>";
                        echo "<div class='main'>";

                        //オプションごとにHTML INPUTフィールド出力
                        foreach(lineconnect::CHANNEL_OPTION as $option_key => $option_name){
                            if($option_key == 'role'){
                                // ロール選択セレクトボックスを出力
                                $role_select = "<select name=".$ary_option[$option_key]['param'].">";
                                $all_roles = array("slc_all"=>"すべての友達", "slc_linked"=>"連携済みの友達");
                                foreach (wp_roles()->get_names() as $role_name) {
                                    $all_roles[esc_attr($role_name)] = translate_user_role($role_name);
                                }
                                $role_select .= lineconnect::makeHtmlSelectOptions($all_roles, $ary_option[$option_key]['value']);
                                $role_select .= "</select>";
                                
                                if($ary_option[$option_key]['value']!="slc_all"){
                                    $role = $ary_option[$option_key]['value'];
                                    if($role == "slc_linked"){
                                        $role = "";
                                    }
                                    $args = array(
                                            'meta_query' => array(
                                                array(
                                                    'key'     => lineconnect::META_KEY__LINE,
                                                    'compare' => 'EXISTS'
                                                )
                                            ),
                                            'role' => $role,
                                            'fields'=>'ID'
                                        );
                                        $line_user_ids=array();
                                        $user_query = new WP_User_Query( $args );
                                        $users = $user_query->get_results();
                                        if(! empty( $users )){
                                            foreach($users as $user){
                                                $user_meta_line = get_user_meta ( $user, lineconnect::META_KEY__LINE, true  );
                                                if(isset($user_meta_line[$secret_prefix])){
                                                    $line_user_ids[] = $user_meta_line[$secret_prefix]['id'];
                                                }
                                            }
                                            $target_cnt = count($line_user_ids)."人";
                                            $target = "";
                                        }else{
                                            $target_cnt = "0人";
                                            $target = '条件にマッチするユーザーがいません。';
                                        }
                                }else{
                                    $target_cnt="不明";
                                    $target = '友達登録されているすべてのユーザーへ送られます。';
                                }
                                echo <<< EOM
                                <p>
                                    <label for="{$ary_option[$option_key]['param']}">{$option_name}：</label>
                                    {$role_select}
                                </p>
                                <p>
                                    通知の対象となる人数は{$target_cnt}です。
                                </p>
                                <p>
                                    {$target}
                                </p>
EOM;
                            }else{
                                //ロール選択以外の普通のフィールド
                                $error_class = $ary_option[$option_key]['invalid'] ? 'class="error-message" ':'';
                                echo <<< EOM
                                <p>
                                    <label for="{$ary_option[$option_key]['param']}" {$error_class}>{$option_name}：</label>
                                    <input type="text" name="{$ary_option[$option_key]['param']}" value="{$ary_option[$option_key]['value']}"/>
                                    {$ary_option[$option_key]['invalid']}
                                </p>
EOM;
                            }
                        }
                        $del_param = lineconnect::PARAMETER_PREFIX."delete_channel";
                        echo <<< EOM
                        <button type="submit" name="{$del_param}" value="{$channel_id}" class="button button-secondary button-large">このチャネルを削除</button>
                        </div>
                        </div>
                        </div>
EOM;
                    }
                    // チャネル追加フォーム
                    $new_channel_html = '';
                    $new_has_error = false;
                    $channel = array('prefix' => 'new');
                    foreach(lineconnect::CHANNEL_OPTION as $option_key => $option_name){

                        $param = lineconnect::PARAMETER_PREFIX.$option_key.$channel['prefix'];
                        $value = get_transient(lineconnect::TRANSIENT_PREFIX.$option_key.$channel['prefix']);

                        // 不正メッセージ
                        if (false !== ($invalid = get_transient(lineconnect::INVALID_PREFIX.$option_key.$channel['prefix']))) {
                            $invalid = lineconnect::getErrorBar($invalid, lineconnect::NOTICE_TYPE__ERROR);
                            $new_has_error = true;
                        }

                        if($option_key == 'role'){
                            // ロール選択セレクトボックスを出力
                            $role_select = "<select name=".$param.">";
                            $all_roles = array("slc_all"=>"すべての友達", "slc_linked"=>"連携済みの友達");
                            foreach (wp_roles()->get_names() as $role_name) {
                                $all_roles[esc_attr($role_name)] = translate_user_role($role_name);
                            }
                            $role_select .= lineconnect::makeHtmlSelectOptions($all_roles, $value);
                            $role_select .= "</select>";

                            $new_channel_html .= <<< EOM
                            <p>
                                <label for="{$param}">{$option_name}：</label>
                                {$role_select}
                            </p>
EOM;
                        }else{
                            $error_class = $invalid ? 'class="error-message" ':'';
                            $new_channel_html .=  <<< EOM
                            <p>
                                <label for="{$param}" {$error_class}>{$option_name}：</label>
                                <input type="text" name="{$param}" value="{$value}"/>
                                {$invalid}
                            </p>
EOM;
                        }

                    }
                    $display = $new_has_error ? '' : 'style="display: none;"'; 
                    $new_channel_html_before = <<< EOM
                    <div class='postbox hide' id='new-channel-box' {$display}>
                        <h3 class='hndle'><span>新規チャネル</span></h3>
                        <div class='inside'>
                            <div class='main'>
EOM;  
                    echo $new_channel_html_before;
                    echo $new_channel_html;
                    echo <<< EOM
                            </div>
                        </div>
                    </div>
                    <button type="button" id="newChannelBtn" onclick="showNewChannel()" class="button button-secondary button-large">新規チャネル追加</button>
EOM;

                    // 送信ボタンを生成・取得
                    $submit_button = get_submit_button('保存');
                    echo <<< EOM
                            </div>
                        </div>
EOM;
                    break;
                default:
                    //チャネル以外のタブ
                    echo <<< EOM
                    <div id="stabs-{$tab_details['prefix']}"  class="ui-tabs-panel ui-corner-bottom ui-widget-content">
                        <h3>{$tab_details['name']}</h3>
EOM;
                    $ary_option = array();
                    foreach($tab_details['fields'] as $option_key => $option_details){
                    
                        $options = array();
                        
                        // 不正メッセージ
                        if (false !== ($invalid = get_transient(lineconnect::INVALID_PREFIX.$option_key))) {
                            $options['invalid'] = lineconnect::getErrorBar($invalid, lineconnect::NOTICE_TYPE__ERROR);
                            $active_tab = intval($tab_details['prefix']) - 1;
                        }
                        //パラメータ名
                        $options['param'] = lineconnect::PARAMETER_PREFIX.$option_key.($option_details['isMulti']?"[]":"");

                        //設定値
                        if (false === ($value = get_transient(lineconnect::TRANSIENT_PREFIX.$option_key))) {
                            // 無ければoptionsテーブルから取得
                            $value = $plugin_options[$option_key];
                            // それでもなければデフォルト値
                        }
                        $options['value'] = is_array($value) ? $value : esc_html($value);
                        
                        // 特殊オプション
                        if($option_key == 'send_post_types'){
                            $args = array(
                                'public'   => true,
                                '_builtin' => false
                            );
                            $post_types = get_post_types( $args, 'objects', 'and' ); 
                            foreach ( $post_types as $post_type ) {
                                $option_details['list'][$post_type->name] = $post_type->label;
                            }
                        }

                        $error_class = $options['invalid'] ? 'class="error-message" ':'';
                        $required = $option_details['required'] ? "required" : "";
                        $hint =  $option_details['hint'] ? "<a href=# title='".$option_details['hint']."'><span class='ui-icon ui-icon-info'></span></a>" : "";
                        echo <<< EOM
                        <p>
                            <label for="{$options['param']}" {$error_class}>{$option_details['label']}：</label>
EOM;
                        switch($option_details['type']){
                            case 'select':
                            case 'multiselect':
                                // セレクトボックスを出力
                                $select = "<select name='{$options['param']}' ".($option_details['type']=='multiselect'?"multiple class='slc-multi-select' ":"").">";
                                $select .= lineconnect::makeHtmlSelectOptions($option_details['list'], $options['value']);
                                $select .= "</select>{$hint}";
                                echo $select;
                                break;
                            case 'color':
                                // カラーピッカーを出力
                                echo "<input type='text' name='{$options['param']}' value='{$options['value']}' class='slc-color-picker' data-default-color='{$option_details['default']}' {$required}/>{$hint}";
                                break;                            
                            case 'spinner':
                                // スピナーを出力
                                echo "<input type='number' name='{$options['param']}' value='{$options['value']}' {$required} />{$hint}";
                                break;
                            default:
                                //テキストボックス出力
                                echo "<input type='text' name='{$options['param']}' value='{$options['value']}' {$required} />{$hint}";
                        }                    
                        echo <<< EOM
                                {$options['invalid']}
                        </p>
EOM;

                    }
                    echo <<< EOM
                    </div>
EOM;
                    break;
            }
        }
        $slc_json = json_encode(array(
                            "active_tab" => $active_tab,
                        ));

        echo <<< EOM
                </div><!-- stabs -->
                {$submit_button}
            </form>
            <script>
                var slc_json = JSON.parse('{$slc_json}');
            </script>
EOM;
    }

    /**
     * 初期設定を保存するcallback関数
     */
    static function save_settings() {
        // nonceで設定したcredentialをPOST受信した場合
        if (isset($_POST[lineconnect::CREDENTIAL_NAME__SETTINGS_FORM]) && $_POST[lineconnect::CREDENTIAL_NAME__SETTINGS_FORM]) {
            // nonceで設定したcredentialのチェック結果が問題ない場合
            if (check_admin_referer(lineconnect::CREDENTIAL_ACTION__SETTINGS_FORM, lineconnect::CREDENTIAL_NAME__SETTINGS_FORM)) {
                $valid = true;
                $channel_value = array();
                $ary_channels = lineconnect::get_all_channels();
                $richmenes = array(
                    'linked-richmenu' => 'linked' ,
                    'unlinked-richmenu' => 'unlinked',
                );
                //新規チャネルのチェック
                if(!empty($_POST[lineconnect::PARAMETER_PREFIX."channel-access-token"."new"]) && !empty($_POST[lineconnect::PARAMETER_PREFIX."channel-secret"."new"])){
                    $new_key = substr($_POST[lineconnect::PARAMETER_PREFIX."channel-secret"."new"],0,4,);
                    $ary_channels[] = array('prefix' => $new_key);
                }
                //チャンネルリスト毎にチェック
                foreach($ary_channels as $channel_id => $channel){
                    if(isset($_POST[lineconnect::PARAMETER_PREFIX."delete_channel"]) && $_POST[lineconnect::PARAMETER_PREFIX."delete_channel"] == $channel_id){
                        //チャネル削除フラグON
                        $channel_value[$channel_id] = array('delete' => true);
                    }else{
                        $ary_option = array();

                        foreach(lineconnect::CHANNEL_OPTION as $option_key => $option_name){
                            $options = array();

                            //POSTされた値
                            if($channel['prefix'] == $new_key){
                                $options['value'] = trim(sanitize_text_field($_POST[lineconnect::PARAMETER_PREFIX.$option_key.'new']));
                            }else{
                                $options['value'] = trim(sanitize_text_field($_POST[lineconnect::PARAMETER_PREFIX.$option_key.$channel['prefix']]));
                            }
                            
                            $ary_option[$option_key] = $options;
                        }
                        $ary_option['prefix'] = array('value' => substr( $ary_option['channel-secret']['value'],0,4,));
                        $channel_value[$channel_id] = $ary_option;

                        foreach(lineconnect::CHANNEL_OPTION as $option_key => $option_name){
                            //入力値チェック
                            if(($option_key == 'channel-access-token' && !preg_match(lineconnect::REGEXP_CHANNEL_ACCESS_TOKEN, $ary_option[$option_key]['value'])) || 
                            ($option_key == 'channel-secret' && !preg_match(lineconnect::REGEXP_CHANNEL_SECRET, $ary_option[$option_key]['value']))){
                                // 不正な値であることを示すメッセージをTRANSIENTに5秒間保持
                                if($channel['prefix'] == $new_key){
                                    set_transient(lineconnect::INVALID_PREFIX.$option_key.'new', '新規チャネル'.": ".$option_name." が正しくありません。", lineconnect::TRANSIENT_TIME_LIMIT);
                                }else{
                                    set_transient(lineconnect::INVALID_PREFIX.$option_key.$channel['prefix'], $channel['name'].": ".$option_name." が正しくありません。", lineconnect::TRANSIENT_TIME_LIMIT);
                                }
                                // 有効フラグをFalse
                                $valid = false;                   
                            }elseif($valid && array_key_exists($option_key,$richmenes) && $ary_channels[$channel_id][$option_key] != $ary_option[$option_key]['value']){
                                //リッチメニューが変更されている場合、リッチメニューの存在チェック
                                $rech_result = lineconnectRichmenu::checkRichMenuId($channel, $ary_option[$option_key]['value']);
                                if(is_array($rech_result) && !$rech_result[0]){
                                    $valid = false;
                                    if($channel['prefix'] == $new_key){
                                        set_transient(lineconnect::INVALID_PREFIX.$option_key.'new', '新規チャネル'.": ".$option_name." が正しくありません。".$rech_result[1], lineconnect::TRANSIENT_TIME_LIMIT);
                                    }else{
                                        set_transient(lineconnect::INVALID_PREFIX.$option_key.$channel['prefix'], $channel['name'].": ".$option_name." が正しくありません。".$rech_result[1], lineconnect::TRANSIENT_TIME_LIMIT);
                                    }
                                }
                            }
                        }
                        //重複チェック
                        foreach($ary_channels as $channel_id_loop => $channel_loop){
                            if($channel_id != $channel_id_loop && $ary_option['prefix']['value'] == $channel_loop['prefix']){
                                if($channel['prefix'] == $new_key){
                                    set_transient(lineconnect::INVALID_PREFIX.$option_key.'new', '新規チャネル'.": 同じチャネルシークレットが既に登録されています。", lineconnect::TRANSIENT_TIME_LIMIT);
                                }else{
                                    set_transient(lineconnect::INVALID_PREFIX.$option_key.$channel['prefix'], $channel['name'].": 同じチャネルシークレットが既に登録されています。", lineconnect::TRANSIENT_TIME_LIMIT);
                                }
                                // 有効フラグをFalse
                                $valid = false;  
                            }
                        }
                    }
                }

                //チャンネル以外のオプション値チェック
                $plugin_options = array();
                foreach(lineconnect::SETTINGS_OPTIONS as $tab_name => $tab_details){
                    if($tab_name == 'channel'){
                        continue;
                    }
                    foreach($tab_details['fields'] as $option_key => $option_details){
                        if($option_details['isMulti']){
                            $value = $_POST[lineconnect::PARAMETER_PREFIX.$option_key];
                            foreach($value as $key => $tmp){
                                $value[$key] = trim(sanitize_text_field($tmp));
                            }
                        }else{
                            $value = trim(sanitize_text_field($_POST[lineconnect::PARAMETER_PREFIX.$option_key]));
                        }
                        if(!isset($value) && $option_details['required']){
                            set_transient(lineconnect::INVALID_PREFIX.$option_key,$option_details['label']."は必須項目です。", lineconnect::TRANSIENT_TIME_LIMIT);
                            $valid = false;
                        }else if(isset($option_details['regex']) && !preg_match($option_details['regex'], $value)){
                            set_transient(lineconnect::INVALID_PREFIX.$option_key,$option_details['label']."が正しくありません。", lineconnect::TRANSIENT_TIME_LIMIT);
                            $valid = false;
                        }else if($option_key == 'image_aspectrate'){
                            preg_match('/^([1-9]+):([1-9]+)$/', $value, $matches);
                            if($matches[2] > $matches[1] * 3){
                                set_transient(lineconnect::INVALID_PREFIX.$option_key,$option_details['label']."が正しくありません。高さには幅の3倍を超える値は指定できません", lineconnect::TRANSIENT_TIME_LIMIT);
                                $valid = false;                          
                            }
                        }
                        $plugin_options[$option_key] = $value;
                    }
                }

                // すべてのチャンネルの値をチェックして、なお有効フラグがTrueの場合
                if ($valid) {
                    $complete_message = "初期設定の保存が完了しました。";
                    $totalchanged = array();  //更新したリッチメニューリスト

                    $new_ary_channels = array();
                    //チャンネルリスト毎にチェック
                    foreach($ary_channels as $channel_id => $channel){
                        if($channel_value[$channel_id]['delete']){
                            continue;
                        }
                        $changed = array();  //チャンネルごとの更新したリッチメニューリスト
                        foreach(lineconnect::CHANNEL_OPTION as $option_key => $option_name){
                            
                            //リッチメニューIDの更新処理（各ロールに応じてメニューIDを関連付け）
                            if(array_key_exists($option_key,$richmenes)){
                                if($ary_channels[$channel_id][$option_key] != $channel_value[$channel_id][$option_key]['value']){
                                    //richmenu_idが変更されていたら
                                    $changed[] = lineconnectRichmenu::updateRichMenuId($channel, $richmenes[$option_key], $channel_value[$channel_id][$option_key]['value']);
                                }
                            }
                            // 保存処理
                            $ary_channels[$channel_id][$option_key] = $channel_value[$channel_id][$option_key]['value'];

                            //(一応)不正値メッセージをTRANSIENTから削除
                            delete_transient(lineconnect::INVALID_PREFIX.$option_key.$channel['prefix']);

                            //(一応)ユーザーが入力した値をTRANSIENTから削除
                            delete_transient(lineconnect::TRANSIENT_PREFIX.$option_key.$channel['prefix']);
                        }
                        //Prefix
                        $ary_channels[$channel_id]['prefix'] = $channel_value[$channel_id]['prefix']['value'];
                        //リッチメニュー変更メッセージがあれば
                        if(!empty($changed)){
                            $totalchanged[] = $channel_value[$channel_id]['name']['value'].": ".join(', ',$changed);
                        }
                        $new_ary_channels[] = $ary_channels[$channel_id];
                    }

                    if(!empty($totalchanged)){
                        $complete_message .= "次のリッチメニューIDを更新しました： ".join(' ',$totalchanged);
                    }
                    //チャンネルオプションを保存
                    update_option(lineconnect::OPTION_KEY__CHANNELS, $new_ary_channels);
                    //プラグインオプションを保存
                    update_option(lineconnect::OPTION_KEY__SETTINGS, $plugin_options);
                    // 保存が完了したら、完了メッセージをTRANSIENTに5秒間保持
                    set_transient(lineconnect::TRANSIENT_KEY__SAVE_SETTINGS, $complete_message, lineconnect::TRANSIENT_TIME_LIMIT);
                }else {
                    // 有効フラグがFalseの場合
                    foreach($ary_channels as $channel_id => $channel){
                        foreach(lineconnect::CHANNEL_OPTION as $option_key => $option_name){
                            // ユーザが入力した値を5秒間保持
                            if($channel['prefix'] == $new_key){
                                set_transient(lineconnect::TRANSIENT_PREFIX.$option_key.'new', $channel_value[$channel_id][$option_key]['value'], lineconnect::TRANSIENT_TIME_LIMIT);
                            }else{
                                set_transient(lineconnect::TRANSIENT_PREFIX.$option_key.$channel['prefix'], $channel_value[$channel_id][$option_key]['value'], lineconnect::TRANSIENT_TIME_LIMIT);
                            }
                        }
                    }
                    foreach(lineconnect::SETTINGS_OPTIONS as $tab_name => $tab_details){
                        if($tab_name == 'channel'){
                            continue;
                        }
                        foreach($tab_details['fields'] as $option_key => $option_details){
                            if($option_details['isMulti']){
                                $value = $_POST[lineconnect::PARAMETER_PREFIX.$option_key];
                                foreach($value as $key => $tmp){
                                    $value[$key] = trim(sanitize_text_field($tmp));
                                }
                            }else{
                                $value = trim(sanitize_text_field($_POST[lineconnect::PARAMETER_PREFIX.$option_key]));
                            }
                            set_transient(lineconnect::TRANSIENT_PREFIX.$option_key, $value, lineconnect::TRANSIENT_TIME_LIMIT);
                        }
                    }
                    // (一応)初期設定の保存完了メッセージを削除
                    delete_transient(lineconnect::TRANSIENT_KEY__SAVE_SETTINGS);
                }
                // 設定画面にリダイレクト
                wp_safe_redirect(menu_page_url(lineconnect::SLUG__SETTINGS_FORM), 303);
            }
        }
    }

    //管理画面用にスクリプト読み込み
    function wpdocs_plugin_admin_scripts(){
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core',false,array('jquery'));
        wp_enqueue_script('jquery-ui-tabs',false,array('jquery-ui-core'));
        wp_enqueue_script('jquery-ui-tooltip',false,array('jquery-ui-core'));
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery-ui-multiselect-widget',plugins_url("js/jquery.multiselect.min.js", dirname(__FILE__)),array('jquery-ui-core'),"3.0.1",true);
        $setting_js = "js/slc_setting.js";
        wp_enqueue_script(lineconnect::PLUGIN_PREFIX.'admin', plugins_url($setting_js, dirname(__FILE__)),array('jquery-ui-tabs','wp-color-picker','jquery-ui-multiselect-widget'),filemtime(plugin_dir_path(dirname(__FILE__)).$setting_js),true);
    }

    //管理画面用にスタイル読み込み
    function wpdocs_plugin_admin_styles(){
        $jquery_ui_css = "css/jquery-ui.css";
        wp_enqueue_style(lineconnect::PLUGIN_ID. '-admin-ui-css',plugins_url($jquery_ui_css, dirname(__FILE__)),array(),filemtime(plugin_dir_path(dirname(__FILE__)).$jquery_ui_css));
        wp_enqueue_style('wp-color-picker');
        $setting_css = "css/slc_setting.css";
        wp_enqueue_style(lineconnect::PLUGIN_PREFIX.'admin-css', plugins_url($setting_css, dirname(__FILE__)),array(),filemtime(plugin_dir_path(dirname(__FILE__)).$setting_css));
        $multiselect_css = "css/jquery.multiselect.css";
        wp_enqueue_style(lineconnect::PLUGIN_PREFIX.'multiselect-css', plugins_url($multiselect_css, dirname(__FILE__)),array(),filemtime(plugin_dir_path(dirname(__FILE__)).$multiselect_css));
    }
}