<?php

if ( ! class_exists( 'M1D1_FB_Post' ) ) {
	class M1D1_FB_Post {
		public function __construct(
			public string $id = '',
			public string $created_time = '',
			public string $message = '',
			public string $link = '',
			public string $permalink_url = '',
			public string $updated_time = '',
		) {
		}

		public static function parse_time_string( string $input ): DateTime|null {
			return date_create_from_format( 'Y-m-d\TH:i:sO', $input ) ?: null;
		}

		/**
		 * @return object{sequence: int, artist: string, title: string, description: string, rating: string}
		 */
		public function parse_message(): stdClass {
			$output = (object) [
				'sequence'    => 0,
				'artist'      => '',
				'title'       => '',
				'description' => '',
				'rating'      => '',
			];

			if ( ! $this->message ) {
				return $output;
			}

			$lines = array_filter( array_map( 'trim', explode( "\n", $this->message ) ) );
			if ( ! $lines ) {
				return $output;
			}

			$head = 0;
			$tail = count( $lines );

			// Sequence
			if ( preg_match( '/^#1일1메탈 (\d+)번째/', $lines[0], $matches ) ) {
				$output->sequence = (int) $matches[1];

				$head += 1;
			}

			// Artist, and title.
			if ( isset( $lines[1] ) ) {
				$pos = strpos( $lines[1], '-' );

				if ( false !== $pos ) {
					$output->artist = trim( substr( $lines[1], 0, $pos ) );
					$output->title  = trim( substr( $lines[1], $pos + 1 ) );

					$head += 1;
				}
			}

			// Rating
			$last_line = $lines[ count( $lines ) - 1 ];

			if ( preg_match( '/^[0-5]\.?[0-9]*$/', $last_line ) ) {
				$output->rating = $last_line;

				$tail -= 1;
			}

			if ( $head < $tail ) {
				$output->description = implode( "\n", array_slice( $lines, $head, $tail ) );
			}

			return $output;
		}
	}
}
