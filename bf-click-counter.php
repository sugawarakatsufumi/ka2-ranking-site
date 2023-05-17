<?php

/*cookieでuserIDを生成記録*/
function my_setcookie() {
  if(!$_COOKIE['userId']){
	  setcookie( 'userId', uniqid(), time() + (10 * 365 * 24 * 60 * 60), '/' );
  }
}
add_action( 'get_header', 'my_setcookie');

global $wpdb;
global $bf_click_counter;		// IDをキーにしてカウント数を格納
global $bf_click_ip;			// IDをキーにしてIPアドレスを格納

/**
 * DBで使うテーブル名を返す
 * 
 * @access public
 * @return void
 */
function bf_click_counter_get_table_name() {

	global $wpdb;
	return $wpdb->prefix . "bf_click_counter";

}

/**
 * アクティベーション。テーブルの作成を行う。
 * 
 * @access public
 * @return void
 */
function bf_click_counter_activation() {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = bf_click_counter_get_table_name();	

	$sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  keyname text NOT NULL,
	  count int NOT NULL,
	  ipaddress text NOT NULL,
	  register_datetime datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  update_datetime datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );	

}

register_activation_hook(__FILE__, 'bf_click_counter_activation');

/**
 * ロード時に変数を初期化する.
 * 
 * @access public
 * @return void
 */
function bf_click_counter_initialize() {
	global $wpdb, $bf_click_counter, $bf_click_ip;
	$bf_click_counter = array();
	$table_name = bf_click_counter_get_table_name();	

	$results = $wpdb->get_results("SELECT * FROM $table_name");
	foreach($results as $one) {
		$bf_click_counter[$one->keyname] = $one->count;
		$bf_click_ip[$one->keyname] = $one->ipaddress;
	}
}
add_action('init', 'bf_click_counter_initialize');

/**
 * ショートコードの処理。ボタン（カウント数）を表示する
 * 
 * @access public
 * @param mixed $atts
 * @return void
 */
function bf_click_counter_display($atts) {
	global $bf_click_counter;

    extract(shortcode_atts(array(
        'id' => '0',
    ), $atts));
  
	// カウンターがすでにある場合
	if (array_key_exists($id, $bf_click_counter)) {
	  if($_COOKIE['clickFlg']=='true'){//すでに投票してる場合をcookieのclickFlgでチェック
	    return '<button href="javascript:void(0);" class="disabled btn btn-primary btn-block btn-lg bf-click-counter" data-id="' . $id . '">あなたは投票済み(<span class="count">現在 ' . $bf_click_counter[$id] . '票</span>)</button>';
	  }else{
  		return '<button href="javascript:void(0);" class="disabled btn btn-primary btn-block btn-lg bf-click-counter" data-id="' . $id . '">投票する(<span class="count">現在 ' . $bf_click_counter[$id] . '票</span>)</button>';
		}
	}else{
	  if($_COOKIE['clickFlg']=='true'){//すでに投票してる場合をcookieのclickFlgでチェック
	    return '<button href="javascript:void(0);" class="disabled btn btn-primary btn-block btn-lg bf-click-counter" data-id="' . $id . '">あなたは投票済み(<span class="count">現在 0票</span>)</button>';
	  }else{
  	  // カウンターがない場合はカウント数ゼロでボタンを表示
  	  return '<button href="javascript:void(0);" class="disabled btn btn-primary btn-block btn-lg bf-click-counter" data-id="' . $id . '">投票する(<span class="count">現在 0票</span>)</button>';
    }
  }
}
add_shortcode('bfcc', 'bf_click_counter_display');

/**
 * JavaScript(Ajax)の出力（いいねボタンの押下を受け付ける）
 * 
 * @access public
 * @return void
 */
function bf_click_counter_ajax() {
?>
    <script>
        var bf_ajaxurl = '<?php echo admin_url( 'admin-ajax.php'); ?>';

		jQuery(function() {
			jQuery('.bf-click-counter').click(function() {
			  if (window.confirm('投票は一度だけです、本当に投票しますか？')){
				  var self = this;
			    jQuery.ajax({
			        type: 'POST',
			        url: bf_ajaxurl,
			        data: {
			            'id' : jQuery(this).attr('data-id'),
						      'action' : 'bf_click_counter_countup',
			        },
			        success: function( response ){
			         	//jQuery(self).find('.count').html("現在 "+response+"票");
			         	//cookieに投票済みフラグを立てる
			         	jQuery.cookie('clickFlg', 'true',  { expires: 10*365, path: "/" });
			         	jQuery.cookie('clickFlgPostId', '<?php echo get_the_ID(); ?>',  { expires: 10*365, path: "/" });
			         	jQuery(self).addClass('disabled');
			         	jQuery(self).html("あなたは投票済み<span class='count'>現在 "+response+"票</span>");
			         	jQuery('.status-alert').removeClass('alert-secondary').addClass('alert-success');
			         	jQuery('.status-alert').html('投票済み');
			         	jQuery('.badge-count').html(response);
			        }
			    });				
				  return false;
				}
			});
		})

    </script>
<?php
}
add_action( 'wp_head', 'bf_click_counter_ajax');

/**
 * Ajaxの受付処理
 * 
 * @access public
 * @return void
 */
function bf_click_counter_countup(){
	bf_click_counter_initialize();
	global $wpdb, $bf_click_counter, $bf_click_ip;
	$id = $_POST['id'];
	//$ipaddr = $_SERVER["REMOTE_ADDR"];
	$ipaddr = $_COOKIE['userId']? $_COOKIE['userId']: "errorFlg";
	$nowdate = date('Y-m-d h:m:s');	// 登録日付

	// カウンターがすでにある場合、インクリメントしてDBをアップデート
	if (array_key_exists($id, $bf_click_counter)) {
		// 同じIPからの連続いいねは阻止
		//if ($bf_click_ip[$id] != $ipaddr) {
		if( !strstr($bf_click_ip[$id], $ipaddr) ){
			$bf_click_counter[$id]++;
			$wpdb->update(bf_click_counter_get_table_name(), array('count' => $bf_click_counter[$id], 'ipaddress' => $ipaddr.=",".$bf_click_ip[$id] , 'update_datetime' => $nowdate), array('keyname' => $id));
		}
	// カウンターがない場合、DBにインサート
	} else {
		$bf_click_counter[$id] = 1;		// 初期値は1
		$wpdb->insert(bf_click_counter_get_table_name(), array('keyname' => $id, 'count' => 1, 'ipaddress' => $ipaddr, 'register_datetime' => $nowdate));
	}
	echo $bf_click_counter[$id];
    die();
}
add_action( 'wp_ajax_bf_click_counter_countup', 'bf_click_counter_countup' );
add_action( 'wp_ajax_nopriv_bf_click_counter_countup', 'bf_click_counter_countup' );