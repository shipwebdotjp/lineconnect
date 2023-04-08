<?php
/**
 * Lineconnect
 * ショートコード
 */
class lineconnectShortcodes{
    static function show_chat_log($atts, $content = null, $tag = ''){
        global $wpdb;
        $table_name = $wpdb->prefix . lineconnect::TABLE_BOT_LOGS; 
        $assets_svg_url = plugins_url( lineconnect::ASSETS_SVG_FILENAME, dirname(__FILE__) );
        
        wp_enqueue_style( 'slc-chat' );

        $atts = wp_parse_args($atts, array(
            'user_id'  => null,
            'bot_id'  => null,
            'date'  => null,
            'max_num' => 20,
        ));
        $output = <<<EOL
        <svg aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <defs>
        <symbol id="icon-chatgpt" viewBox="0 0 32 32">
        <path fill="#10a37f" style="fill: var(--color1, #10a37f)" d="M6.512 0h18.977c3.596 0 6.512 2.939 6.512 6.565v18.87c0 3.626-2.915 6.565-6.512 6.565h-18.977c-3.596 0-6.512-2.939-6.512-6.565v-18.87c0-3.626 2.915-6.565 6.512-6.565z"></path>
        <path fill="#fff" style="fill: var(--color2, #fff)" d="M23.668 14.376c0.145-0.42 0.228-0.905 0.228-1.409v-0c-0-0.831-0.227-1.609-0.623-2.275l0.011 0.021c-0.804-1.4-2.297-2.265-3.912-2.265-0.335 0-0.662 0.037-0.977 0.106l0.030-0.006c-0.821-0.922-2.011-1.499-3.336-1.499h-0.028l-0.011 0c-1.956 0-3.691 1.262-4.292 3.123-1.286 0.27-2.347 1.063-2.97 2.142l-0.011 0.022c-0.383 0.649-0.609 1.43-0.61 2.264v0c0 1.166 0.442 2.228 1.168 3.028l-0.003-0.004c-0.145 0.42-0.228 0.905-0.228 1.409v0c0 0.831 0.227 1.609 0.623 2.275l-0.011-0.021c0.793 1.363 2.248 2.265 3.912 2.265 0.335 0 0.661-0.037 0.976-0.106l-0.030 0.006c0.821 0.922 2.011 1.499 3.336 1.5h0.028l0.012-0c1.957 0 3.691-1.262 4.293-3.125 1.286-0.27 2.347-1.064 2.97-2.142l0.011-0.022c0.383-0.648 0.608-1.429 0.608-2.262 0-0 0-0 0-0.001v0c-0-1.166-0.442-2.228-1.168-3.028l0.003 0.004-0-0zM16.939 23.781h-0.005c-0.818-0-1.567-0.294-2.148-0.781l0.005 0.004c0.050-0.027 0.084-0.046 0.118-0.067l-0.013 0.007 3.564-2.059c0.176-0.102 0.293-0.289 0.293-0.504v0-5.029l1.507 0.87c0.016 0.008 0.027 0.023 0.029 0.041l0 0v4.162c-0.002 1.85-1.501 3.351-3.352 3.355zM9.731 20.702c-0.283-0.48-0.45-1.058-0.45-1.675v-0c0-0.192 0.017-0.384 0.049-0.573 0.027 0.016 0.073 0.044 0.106 0.063l3.565 2.059c0.084 0.050 0.185 0.079 0.293 0.079s0.209-0.029 0.296-0.081l-0.003 0.001 4.352-2.513v1.74l0 0.003c0 0 0 0 0 0 0 0.018-0.008 0.033-0.021 0.043l-0 0-3.603 2.081c-0.481 0.282-1.059 0.448-1.676 0.448h-0c-1.235-0-2.315-0.667-2.898-1.66l-0.009-0.016v0zM8.793 12.921c0.394-0.676 0.999-1.191 1.723-1.463l0.023-0.008c0 0.031-0.002 0.085-0.002 0.123v4.118l-0 0.003c0 0.214 0.117 0.402 0.29 0.502l0.003 0.002 4.352 2.512-1.507 0.87c-0.008 0.006-0.019 0.009-0.030 0.009-0.008 0-0.015-0.002-0.021-0.004l0 0-3.604-2.082c-1.009-0.592-1.675-1.671-1.675-2.906v-0c0-0.617 0.166-1.194 0.456-1.691l-0.009 0.016-0-0zM21.171 15.801l-4.352-2.513 1.507-0.87c0.008-0.006 0.019-0.009 0.030-0.009 0.008 0 0.015 0.002 0.021 0.004l-0-0 3.604 2.080c1.010 0.591 1.677 1.671 1.677 2.906v0c0 1.406-0.877 2.663-2.196 3.149v-4.241c0-0.002 0-0.003 0-0.005-0-0.214-0.116-0.4-0.288-0.501l-0.003-0.001zM22.671 13.544c-0.012-0.009-0.047-0.030-0.082-0.050l-0.024-0.013-3.564-2.059c-0.084-0.050-0.185-0.079-0.293-0.079v0c-0.108 0-0.209 0.029-0.295 0.081l0.003-0.001-4.352 2.513v-1.74l-0-0.003c0-0.017 0.008-0.033 0.022-0.043l3.603-2.079c0.481-0.282 1.059-0.449 1.676-0.449v0c1.853 0 3.355 1.502 3.355 3.355-0 0.2-0.018 0.397-0.051 0.588l0.003-0.020v0zM13.244 16.645l-1.507-0.87c-0.016-0.008-0.027-0.023-0.029-0.041l-0-0v-4.162c0.001-1.852 1.503-3.353 3.355-3.353 0.819 0 1.569 0.294 2.152 0.781l-0.005-0.004c-0.027 0.015-0.074 0.041-0.106 0.060l-3.564 2.059c-0.176 0.102-0.293 0.289-0.293 0.504v0 0.003l-0.002 5.024zM14.062 14.881l1.938-1.12 1.938 1.119v2.238l-1.938 1.119-1.938-1.119v-2.238z"></path>
        </symbol>
        <symbol id="icon-user" viewBox="0 0 64 64">
        <path fill="#666" d="M0 0h64v64H0z"/><path fill="#FFF" d="M46.6 41.2c-3.3-.9-5.7-2.2-8-3.1 0 .5-.1.9-.5 1.3-1.4 1.5-3.6 2.4-6.1 2.4-2.5 0-4.8-.9-6.1-2.4-.5-.5-.6-.9-.5-1.4-2.3 1-4.7 2.2-8 3.1 0 0-3.1 1.8-3.4 8.8h36c-.2-7.1-3.4-8.7-3.4-8.7z"/><path fill="#FFF" d="M32 34.3c-1.6 0-3.2-.9-4.5-2.2-.1 5.4-1.7 5.9-.9 6.7 2.5 2.7 8.1 2.9 10.8 0 .8-.8-.6-1.3-.9-6.8-1.3 1.3-2.9 2.3-4.5 2.3z"/><path fill="#FFF" d="M32 34.3c-3.8 0-7.7-5.2-7.7-8.8v-5c0-3.6 3.2-6.5 6.8-6.5h1.8c3.8 0 6.8 2.9 6.8 6.5v5c-.1 3.6-3.9 8.8-7.7 8.8z"/>
        </symbol>
        </defs>
        </svg>
        <section class='chatbox'>
        <section class='chat-window'>
EOL;
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $start = ($paged - 1) * $atts['max_num'];

        $keyvalues = array();
        if(isset($atts['user_id'])){
            $keyvalues[] = ["key" => "AND user_id = %s ", "value" => $atts['user_id']];
        }
        if(isset($atts['bot_id'])){
            $keyvalues[] = ["key" => "AND bot_id = %s ", "value" => $atts['bot_id']];
        }
        if(isset($atts['date']) && ($timestamp = strtotime($atts['date'])) !== false){
            $keyvalues[] = ["key" => "AND DATE(timestamp) = %s ", "value" => date("Y-m-d", $timestamp)];
        }

        $addtional_query = "";

        if(!empty($keyvalues)){
            $keys = "";
            $values=[];
            foreach($keyvalues as $keyval){
                $keys .= $keyval["key"];
                $values[] = $keyval["value"];
            }
            $addtional_query = $wpdb->prepare( $keys, $values );            
        }

        $query = "
            SELECT COUNT(id) 
            FROM {$table_name}
            WHERE event_type = 1 
            {$addtional_query}";        
        
        $convasation_count = $wpdb->get_var( $query );

        $convasations = $wpdb->get_results( 
			$wpdb->prepare( 
			"
			SELECT id,event_type,source_type,user_id,message_type,message,UNIX_TIMESTAMP(timestamp) as timestamp 
			FROM {$table_name}
			WHERE event_type = 1
            {$addtional_query}
            ORDER BY id desc
			LIMIT %d, %d
			",
			[$start, $atts['max_num']]
			)
		);
        //error_log("start:".$start);

        foreach( array_reverse($convasations) as $convasation){

            $msg_type = $convasation->source_type == 11 ? "msg-remote" : "msg-self";
            $msg_name = $convasation->source_type == 11 ? "Chat GPT" : substr($convasation->user_id,-4);
            $msg_time = date("Y/m/d H:i:s",$convasation->timestamp);
            $msg_text = null;
            $message_object = json_decode($convasation->message,false);
            if(json_last_error() == JSON_ERROR_NONE){
                if($convasation->message_type == 1 && isset($message_object->text)){
                    $msg_text = nl2br( $message_object->text );
                }
            }
            if(empty($msg_text)){
                continue;
            }

            /*
            $user_img = $convasation->source_type != 11 ? 
            "<img class=\"user-img\" src=\"/wp-content/plugins/ultimate-member/assets/img/default_avatar.jpg\">" : '';
            $ai_img = $convasation->source_type == 11 ? 
            '<svg class="user-img" xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 512 512"><rect fill="#10A37F" width="512" height="512" rx="104.187" ry="105.042"/><path fill="#fff" fill-rule="nonzero" d="M378.68 230.011a71.432 71.432 0 003.654-22.541 71.383 71.383 0 00-9.783-36.064c-12.871-22.404-36.747-36.236-62.587-36.236a72.31 72.31 0 00-15.145 1.604 71.362 71.362 0 00-53.37-23.991h-.453l-.17.001c-31.297 0-59.052 20.195-68.673 49.967a71.372 71.372 0 00-47.709 34.618 72.224 72.224 0 00-9.755 36.226 72.204 72.204 0 0018.628 48.395 71.395 71.395 0 00-3.655 22.541 71.388 71.388 0 009.783 36.064 72.187 72.187 0 0077.728 34.631 71.375 71.375 0 0053.374 23.992H271l.184-.001c31.314 0 59.06-20.196 68.681-49.995a71.384 71.384 0 0047.71-34.619 72.107 72.107 0 009.736-36.194 72.201 72.201 0 00-18.628-48.394l-.003-.004zM271.018 380.492h-.074a53.576 53.576 0 01-34.287-12.423 44.928 44.928 0 001.694-.96l57.032-32.943a9.278 9.278 0 004.688-8.06v-80.459l24.106 13.919a.859.859 0 01.469.661v66.586c-.033 29.604-24.022 53.619-53.628 53.679zm-115.329-49.257a53.563 53.563 0 01-7.196-26.798c0-3.069.268-6.146.79-9.17.424.254 1.164.706 1.695 1.011l57.032 32.943a9.289 9.289 0 009.37-.002l69.63-40.205v27.839l.001.048a.864.864 0 01-.345.691l-57.654 33.288a53.791 53.791 0 01-26.817 7.17 53.746 53.746 0 01-46.506-26.818v.003zm-15.004-124.506a53.5 53.5 0 0127.941-23.534c0 .491-.028 1.361-.028 1.965v65.887l-.001.054a9.27 9.27 0 004.681 8.053l69.63 40.199-24.105 13.919a.864.864 0 01-.813.074l-57.66-33.316a53.746 53.746 0 01-26.805-46.5 53.787 53.787 0 017.163-26.798l-.003-.003zm198.055 46.089l-69.63-40.204 24.106-13.914a.863.863 0 01.813-.074l57.659 33.288a53.71 53.71 0 0126.835 46.491c0 22.489-14.033 42.612-35.133 50.379v-67.857c.003-.025.003-.051.003-.076a9.265 9.265 0 00-4.653-8.033zm23.993-36.111a81.919 81.919 0 00-1.694-1.01l-57.032-32.944a9.31 9.31 0 00-4.684-1.266 9.31 9.31 0 00-4.684 1.266l-69.631 40.205v-27.839l-.001-.048c0-.272.129-.528.346-.691l57.654-33.26a53.696 53.696 0 0126.816-7.177c29.644 0 53.684 24.04 53.684 53.684a53.91 53.91 0 01-.774 9.077v.003zm-150.831 49.618l-24.111-13.919a.859.859 0 01-.469-.661v-66.587c.013-29.628 24.053-53.648 53.684-53.648a53.719 53.719 0 0134.349 12.426c-.434.237-1.191.655-1.694.96l-57.032 32.943a9.272 9.272 0 00-4.687 8.057v.053l-.04 80.376zm13.095-28.233l31.012-17.912 31.012 17.9v35.812l-31.012 17.901-31.012-17.901v-35.8z"/></svg>':"";
            */

            $user_img = $convasation->source_type != 11 ? "<svg class=\"user-img\"><use xlink:href=\"#icon-user\"></use></svg>":"";
            $ai_img = $convasation->source_type == 11 ? "<svg class=\"user-img\"><use xlink:href=\"#icon-chatgpt\"></use></svg>":"";
        
            $output .= <<<EOL
        
        <article class="msg-container {$msg_type}" id="msg-{$convasation->id}">
        <div class="msg-box">
          {$ai_img}
          <div class="flr">
            <div class="messages">
              <p class="msg">
                {$msg_text}
              </p>
            </div>
            <span class="timestamp"><span class="username">{$msg_name}</span>&bull;<span class="posttime">{$msg_time}</span></span>
          </div>
          {$user_img}
        </div>
      </article>
EOL;
        }
        $output .= "</section></section>";

        $output .= "<div class=\"pnavi\">";
        $paginate_base = get_pagenum_link(1);
        $paginate_format = (substr($paginate_base,-1,1) == '/' ? '' : '/') . user_trailingslashit('page/%#%/','paged');
        $paginate_base .= '%_%';
        $total_page_cnt = ceil($convasation_count /  $atts['max_num'] );

        $output .=  paginate_links(array(
           'base' => $paginate_base,
           'format' => $paginate_format,
           'total' => $total_page_cnt,
           'mid_size' => 1,
           'current' => ($paged ? $paged : 1),
           'prev_text' => '&lt;',
           'next_text' => '&gt;',
       ));
       
       $output .= "</div>";

        return $output;
    }

    //ショートコード用にスクリプト登録
    static function enqueue_script(){
        wp_register_style( 'slc-chat', plugins_url( 'css/slc_chat.css' , dirname(__FILE__) ) );
    }
}