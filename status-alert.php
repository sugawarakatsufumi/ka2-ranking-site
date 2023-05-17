<?php
    //投票状態を表示するアラートのショートコード
    $alert = '';
    if($_COOKIE['clickFlgPostId']){
      $alert = '<div class="status-alert alert alert-success alert-dismissible fade show" role="alert">
      あなたは<strong>'.get_the_title($_COOKIE['clickFlgPostId']).'</strong>に投票済みです！
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
      </button>
      </div>';
    }else{
      $alert = '<div class="status-alert alert alert-secondary alert-dismissible fade show" role="alert">
      あなたはまだ誰にも投票してません
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
      </button>
      </div>';
    }
    echo $alert;