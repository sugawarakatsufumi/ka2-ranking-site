<?php
/*
Plugin Name: ランキングサイト構築用プラグイン
Plugin URI: 
Description: ランキングサイト構築用プラグイン。youtubeは動画が終了すると投票できます。ACFプラグインに依存。ボタンパーツ類はbootstrapに依存
Version: 0.11
*/

class Syoubi_plugin {
  public function __construct(){
    add_action( 'wp_enqueue_scripts', array($this,'theme_name_scripts' ));
    add_shortcode('status_alert',  array($this,'status_alert'));
    add_shortcode('badge_count',  array($this,'badge_count'));
    add_shortcode('embed_yt',  array($this,'embed_yt'));
    add_shortcode('count_btn',  array($this,'count_btn'));
    add_filter('posts_join', array($this,'count_posts_table_join'));
    add_filter('posts_fields', array($this,'count_add_fields'));
    //add_action( 'posts_orderby', array($this,'change_sort_order'));
  }

  //ライブラリ系のJS,CSSはここで読み込む
  public function theme_name_scripts() {
    wp_enqueue_script( 'syoubi-jquery-cookie', plugin_dir_url( __FILE__ ).'js/jquery-cookie.js', array('jquery-core'), '1.0.0' );
    wp_enqueue_script( 'syoubi-bootstrap', plugin_dir_url( __FILE__ ).'js/bootstrap.min.js', array('jquery-core'), '1.0.0' );
    wp_enqueue_script( 'syoubi-script', plugin_dir_url( __FILE__ ).'js/syoubi-script.js', array('jquery-core'), '1.0.0' );
    wp_enqueue_style( 'syoubi-bootstrap', plugin_dir_url( __FILE__ ).'css/bootstrap.min.css', array(), '1.0.0' );
  }
  
  //postsとカウンターのテーブルを結合
  public function count_posts_table_join($join){
    global $wpdb;
    $bf_click_table_name = bf_click_counter_get_table_name();
    $join .= "LEFT JOIN $bf_click_table_name ON $wpdb->posts.ID = $bf_click_table_name.keyname";
    return $join;
  }
  
  //取得したいデータのフィールドを追加
  public function count_add_fields( $fields ) {
    //var_dump($fields);
    $bf_click_table_name = bf_click_counter_get_table_name();
    $fields .= ", $bf_click_table_name.count";
    return $fields;
  }

  //投票の順位でソート
  public function change_sort_order($query=''){
    //Set the order ASC or DESC
    //$query->set( 'orderby', array('count'=>'DESC') );
    global  $wpdb;
    $query =  "count DESC";
    //var_dump($where);
    return $query;
  }
  
  //投票状態のアラート
  public function status_alert(){
    ob_start();
    include( plugin_dir_path( __FILE__ ).'status-alert.php' );
    $status_alert = ob_get_contents();
     ob_end_clean();
    return $status_alert;
  }
  
  public function badge_count(){
    global $post;
    $badge = '';
    if($post->count){
      $badge = '<span class="badge-count badge-pill badge badge-secondary not-empty"><span class="rank">'.get_field('rank',$post->ID).'</span>'.$post->count.'</span>';
    }else{
      $badge = '<span class="badge-count badge-pill badge badge-secondary">0</span>';
    }
    return $badge;
  }
  
  //YT動画呼び出し
  public function embed_yt($atts) {
    $atts = shortcode_atts(array(
      "yt_id" => '',
    ),$atts);
    $yt_id = $atts['yt_id'];
    $post_id = get_the_ID();
    $bf_click_counter = do_shortcode("[bfcc id='".$post_id."' ]");
    ob_start();
    include( plugin_dir_path( __FILE__ ).'youtube-emebed.php' );
    $ytemb = ob_get_contents();
    ob_end_clean();
    
    /*$table_name = bf_click_counter_get_table_name();
    $counter_results = $wpdb->get_results("SELECT * FROM $table_name WHERE keyname=".$post_id);
    if(!$counter_results[0]->count){
      $counter = "0票";
    }else{
      $counter = $counter_results[0]->count."票";
    }*/
    $ytemb;
    return $ytemb;
  }
  //カウンターボタン
  public function count_btn() {
    $post_id = get_the_ID();
    $bf_click_counter = do_shortcode("[bfcc id='".$post_id."' ]");
    return $bf_click_counter;
  }

}
$base_information = new Syoubi_plugin();

//クリックカウンター本体
include(plugin_dir_path( __FILE__ ).'bf-click-counter.php');
//管理画面関連の表示
include(plugin_dir_path( __FILE__ ).'admin.php');