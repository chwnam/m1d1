<?php

if ( ! class_exists( 'M1D1_Playlist' ) ) {
	class M1D1_Playlist {
		public int       $id           = 0;
		public string    $fb_id        = '';
		public string    $yt_id        = '';
		public string    $artist       = '';
		public string    $title        = '';
		public string    $length       = '';
		public string    $description  = '';
		public string    $rating       = '';
		public int       $sequence     = 0;
		public ?DateTime $created_time = null;
		public ?DateTime $updated_time = null;

		public static function from_fb_post( M1D1_FB_Post $post ): static {
			$instance = new static();

			$parsed = $post->parse_message();

			$instance->fb_id        = $post->id;
			$instance->artist       = $parsed->artist;
			$instance->title        = $parsed->title;
			$instance->description  = $parsed->description;
			$instance->rating       = $parsed->rating;
			$instance->sequence     = $parsed->sequence;

			$instance->created_time = M1D1_FB_Post::parse_time_string( $post->created_time );
			$instance->updated_time = M1D1_FB_Post::parse_time_string( $post->updated_time );

			return $instance;
		}
	}
}
