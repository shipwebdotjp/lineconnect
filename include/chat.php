<?php
/**
 * Lineconnect
 * 管理画面でのチャット画面
 */
class lineconnectChat{
    /**
     * 管理画面メニューの基本構造が配置された後に実行するアクションにフックする、
     * 管理画面のトップメニューページを追加する関数
     */
    static function set_plugin_menu() {
        // 設定のサブメニュー「LINE Connect」を追加
        $page_hook_suffix = add_menu_page(
            // ページタイトル：
            'LINE Connect チャット',
            // メニュータイトル：
            'LINE チャット',
            // 権限：
            // manage_optionsは以下の管理画面設定へのアクセスを許可
            // ・設定 > 一般設定
            // ・設定 > 投稿設定
            // ・設定 > 表示設定
            // ・設定 > ディスカッション
            // ・設定 > パーマリンク設定
            'manage_options',
            // ページを開いたときのURL(slug)：
            lineconnect::SLUG__CHAT_FORM,
            // メニューに紐づく画面を描画するcallback関数：
            ['lineconnectChat', 'show_chat'],
            'dashicons-format-status'
        );
        add_action( "admin_print_styles-{$page_hook_suffix}", ['lineconnectChat', 'wpdocs_plugin_admin_styles']);
        add_action( "admin_print_scripts-{$page_hook_suffix}", ['lineconnectChat', 'wpdocs_plugin_admin_scripts']);
    }
    
    /**
     * 初期設定画面を表示
     */
    static function show_chat() {
        // プラグインのオプション
        $plugin_options = lineconnect::get_all_options();
        echo <<< EOM
<div id="line_chat_root"></div>
EOM;
    }

    //管理画面用にスクリプト読み込み
    static function wpdocs_plugin_admin_scripts(){
        /*
        $chat_js = "line-chat/build/static/js/2.55a144b5.chunk.js";
        wp_enqueue_script(lineconnect::PLUGIN_PREFIX.'chat-2', plugins_url($chat_js, dirname(__FILE__)),array('wp-element'),filemtime(plugin_dir_path(dirname(__FILE__)).$chat_js),true);
        $chat_js = "line-chat/build/static/js/main.baed2f09.chunk.js";
        wp_enqueue_script(lineconnect::PLUGIN_PREFIX.'chat', plugins_url($chat_js, dirname(__FILE__)),array('wp-element'),filemtime(plugin_dir_path(dirname(__FILE__)).$chat_js),true);
        */
        $chat_js = "line-chat/dist/slc_chat.js";
        wp_enqueue_script(lineconnect::PLUGIN_PREFIX.'chat', plugins_url($chat_js, dirname(__FILE__)),array('wp-element'),filemtime(plugin_dir_path(dirname(__FILE__)).$chat_js),true);
        

    }

    //管理画面用にスタイル読み込み
    static function wpdocs_plugin_admin_styles(){
        $chat_css = "css/slc_chat.css";
        wp_enqueue_style(lineconnect::PLUGIN_PREFIX.'admin-css', plugins_url($chat_css, dirname(__FILE__)),array(),filemtime(plugin_dir_path(dirname(__FILE__)).$chat_css));
    }
}