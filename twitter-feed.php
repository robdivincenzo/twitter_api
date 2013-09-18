<?php 
// If the twitter feed isn't cached, grab it and cache it!
if ( false === ( $twitter_feed = get_transient ( 'twitter_feed' ) ) ):
	ob_start();	

	// get the twitter data
	$tweets = get_tweets( );	

	// For each tweet
	foreach ( $tweets as $tweet ) {
		//if it contains a media file, we need to handle it differently
		if(isset($tweet->entities->media)) {
			foreach($tweet->entities->media as $media){
				// echo the tweet and media file
				echo '<div class="story index-tweet white-box image">';?>
				<a href="<?php echo $media->media_url_https; ?>" class="twitter-media fancybox"><img src="<?php echo $media->media_url_https; ?>" /></a>
				<?php echo '</div>';
				}
		} else { // else it's a standard tweet, so just echo it out
			echo '<div class="story index-tweet standard">';
			echo twitterify( $tweet->text );
			echo '</div>';
		}
	}
	// get the output buffer
	$twitter_feed = ob_get_contents();
	// store the feed in our cache
	set_transient( 'twitter_feed', $twitter_feed, TRANSIENT_EXPIRES );
	// end the output buffer and flush
	ob_end_flush();
else:
	// if it's cached, just echo it out!
	echo $twitter_feed;
endif;