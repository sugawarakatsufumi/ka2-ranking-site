    <?php //$yt_idはincludeもとから継承 ?>
    <div id="player"></div>
    <script>
      // 2. This code loads the IFrame Player API code asynchronously.
      var tag = document.createElement('script');

      tag.src = "https://www.youtube.com/iframe_api";
      var firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

      // 3. This function creates an <iframe> (and YouTube player)
      //    after the API code downloads.
      var player;
      function onYouTubeIframeAPIReady() {
        player = new YT.Player('player', {
          height: '400',
          width: '100%',
          //videoId: 'KOoUIMZYz3Q',
          videoId: "<?php echo $yt_id; ?>",
          events: {
            'onStateChange': onPlayerStateChange
          },
        });
      }

      // 4. The API will call this function when the video player is ready.
      function onPlayerReady(event) {
        event.target.playVideo();
      }

      // 5. The API calls this function when the player's state changes.
      //    The function indicates that when playing a video (state=1),
      //    the player should play for six seconds and then stop.
      var done = false;
      function onPlayerStateChange(event) {
        var s="";
         /* プレーヤーのステータスが変更される度に発生 */
         /* 整数値 */
         s+="PlayerState:"+event.data;
         //$("#a").html(s);
        if(s=="PlayerState:0"){
          console.log("再生終了");
          clickFlg = jQuery.cookie('clickFlg');
          if(clickFlg != "true"){
            jQuery('.bf-click-counter').removeClass('disabled');
          }
        }
      }
      function stopVideo() {
        player.stopVideo();
      }
    </script>