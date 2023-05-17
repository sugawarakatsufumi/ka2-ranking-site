<?php
//管理画面

//順位を管理画面一覧に表示
function add_custom_column( $defaults ) {
  $defaults['bf_count_ttl'] = '投票数'; //項目名
  return $defaults;
}
function add_custom_column_id($column_name, $id) {
  $post = get_post($id); 
  if ($column_name == 'bf_count_ttl') {
    $bf_count = $post->count;
    if($bf_count){
      echo $bf_count;
    }else{
      echo "0";
    }    
  }
}
add_filter( 'manage_posts_columns', 'add_custom_column' );
add_action( 'manage_posts_custom_column', 'add_custom_column_id', 10, 2 );