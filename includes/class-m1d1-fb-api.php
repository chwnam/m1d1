<?php

if ( ! class_exists( 'M1D1_FB_API' ) ) {
	class M1D1_FB_API {
		const BASE_URL = 'https://graph.facebook.com';
		const API_VER  = 'v18.0';
		const SCOPE    = 'public_profile,user_posts';

		public function __construct(
			private string $app_id,
			private string $app_secret,
			private string $client_token,
		) {
		}

		/**
		 * Step 1: start device login
		 *
		 * @return object{
		 *      code: string,
		 *      user_code: string,
		 *      verification_uri: string,
		 *      expires_in: int,
		 *      interval: int,
		 * }
		 */
		public function device_login(): stdClass {
			return static::request(
				static::get_url( 'v2.6/device/login' ),
				[
					'access_token' => $this->get_access_token(),
					'scope'        => static::SCOPE,
				],
				'post'
			);
		}

		/**
		 * Step 2: verify authorization
		 *
		 * @param string $code
		 *
		 * @return object{
		 *     error: object{
		 *         message: string,
		 *         code: int,
		 *         error_subcode: int,
		 *         error_user_title: int,
		 *         error_user_msg: int,
		 *     }
		 * }|object{
		 *     access_token: string,
		 *     data_access_expiration_time: int,
		 *     expires_in: int,
		 * }
		 */
		public function device_login_status( string $code ): stdClass {
			return static::request(
				static::get_url( 'v2.6/device/login_status' ),
				[
					'access_token' => $this->get_access_token(),
					'code'         => $code,
				],
				'post'
			);
		}

		/**
		 * Verify access token.
		 *
		 * @param string $access_token
		 *
		 * @return object{
		 *     data: object{
		 *         app_id: string,
		 *         application: string,
		 *         data_access_expires_at: int,
		 *         expires_at: int,
		 *         is_valid: bool,
		 *         issued_at: int,
		 *         scopes: string[],
		 *         type: string,
		 *         user_id: string,
		 *     }
		 * }
		 */
		public function debug_token( string $access_token ): stdClass {
			return static::request(
				static::get_url( 'debug_token' ),
				[
					'input_token'  => $access_token,
					'access_token' => $access_token,
				]
			);
		}

		/**
		 * Refresh token
		 *
		 * @param string $access_token
		 *
		 * @return object{
		 *     access_token: string,
		 *     token_type: string,
		 *     expires_in: int,
		 * }
		 */
		public function refresh_token( string $access_token ): stdClass {
			return static::request(
				static::get_url( static::API_VER . '/oauth/access_token' ),
				[
					'grant_type'        => 'fb_exchange_token',
					'client_id'         => $this->app_id,
					'client_secret'     => $this->app_secret,
					'fb_exchange_token' => $access_token,
				]
			);
		}

		/**
		 * Get posts.
		 *
		 * @param string $access_token
		 * @param string $user_id
		 * @param int    $max_loop
		 *
		 * @return M1D1_FB_Post[]
		 */
		public function get_posts( string $access_token, string $user_id, int $max_loop = 1 ): array {
			$fields = implode(
				',',
				[
					'id',
					'created_time',
					'message',
					'link',
					'permalink_url',
					'updated_time',
				]
			);

			$max_loop = max( 1, $max_loop );
			$loop     = 0;

			$url = add_query_arg(
				[
					'access_token' => $access_token,
					'fields'       => $fields,
				],
				static::get_url( static::API_VER . "/$user_id/posts" )
			);

			$posts = [];

			while ( $loop < $max_loop ) {
				/**
				 * @var object{
				 *     data: object{
				 *         id: string,
				 *         created_time: string,
				 *         message: string,
				 *         link: string,
				 *         permalink_url: string,
				 *         updated_time: string
				 *     }[],
				 *     paging: object{previous: string,next: string}
				 * } $response
				 */
				$response = static::request( $url );
				$url      = $response->paging->next;

				foreach ( $response->data as $post ) {
					if ( str_starts_with( trim( $post->message ), '#1일1메탈' ) ) {
						$posts[] = new M1D1_FB_Post(
							$post->id,
							$post->created_time,
							$post->message,
							$post->link,
							$post->permalink_url,
							$post->updated_time,
						);
					}
				}

				++ $loop;
			}

			return $posts;
		}

		protected function get_access_token(): string {
			return "$this->app_id|$this->client_token";
		}

		protected static function get_url( string $path = '' ): string {
			if ( $path && ! str_starts_with( $path, '/' ) ) {
				$path = '/' . $path;
			}

			return static::BASE_URL . $path;
		}

		protected static function request( string $url, array $data = [], string $method = 'GET' ): object {
			$method = strtoupper( $method );

			if ( 'GET' === $method && $data ) {
				$url = add_query_arg( urlencode_deep( $data ), $url );
			}

			$args = [ 'method' => $method ];

			if ( 'POST' === $method ) {
				$args['body'] = $data;
			}

			$response = wp_remote_request( $url, $args );
			$code     = wp_remote_retrieve_response_code( $response );
			$body     = wp_remote_retrieve_body( $response );

			if ( 200 !== $code ) {
				throw new RuntimeException( $body, $code );
			}

			$decoded = json_decode( $body );
			if ( false === $decoded ) {
				return new RuntimeException( 'JSON decoding failed: ' . $body );
			}

			return $decoded;
		}
	}
}