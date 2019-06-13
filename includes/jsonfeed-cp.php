<?php


// Flush the rewrite rules to enable the json feed permalink
class _ui_JsonFeed_CP extends _ui_JsonFeed_CP_Base {
	
	public static function init( ) {
		new self();
	}
	
	function __construct( $init_plugin = true ) {
		// custom actions
		add_action( 'jsonfeed_cp/before_load_template/feed', array( $this, 'send_headers' ) );
		add_action( 'jsonfeed_cp/before_load_template/comment_feed', array( $this, 'send_headers' ) );
		

		add_action( 'init', array( $this, 'json_feed_setup_feed' ) );	
		add_filter( 'feed_content_type', array( $this, 'json_feed_content_type' ), 10, 2 );
	
		add_action( 'wp_head', array( $this, 'json_feed_link' ) );
		add_filter( 'wp_head', array( $this, 'json_feed_links_extra' ) );
		
		add_filter( 'pubsubhubbub_feed_urls', array( $this, 'json_feed_websub' ) );
		

	}
	
	
	

	function json_feed_setup_feed() {
		add_feed( 'json', array( $this, 'get_feed_json' ) );
	}

	// Register the json feed rewrite rules
	function get_feed_json( $for_comments ) {
		$action_name = 'feed';
		$load_file = 'feed-json.php';
		
		if ( $for_comments ) {
			$load_file = 'feed-json-comments.php';
			$action_name = 'comment_feed';
		} else {
			//$load_file = 'feed-json.php';
			
			//load_template( dirname( __FILE__ ) . '/feed-json.php' );
		}
		
		
		
		// override via theme template
		$load_template = locate_template( array( $load_file ) );
		
		// send headers
		
		/*
		new __debug( array(
			'action_name' => $action_name,
			'load_file' => $load_file,
			'load_template' => $load_template,
			'plugin_template_path' => _UI_JSONFEED_PLUGIN_PATH . 'templates/' . $load_file,
		), 'params - ' . __METHOD__ );
		*/
		
		do_action( 'jsonfeed_cp/before_load_template/' . $action_name );
		//$this->send_headers();
		
		
		// load template
		if( !empty( $load_template ) ) {
			load_template( $load_template );
		} else {
			include_once( _UI_JSONFEED_PLUGIN_PATH . 'templates/' . $load_file );
		}
		do_action( 'jsonfeed_cp/after_load_template/' . $action_name );
	}
	
	/**
	 * Send CORS headers, among others
	 * Also see @link https://wordpress.org/support/topic/cors-headers/
	 */

	function send_headers() {
		$headers = apply_filters( 'jsonfeed_cp/get_headers', array(
			'Access-Control-Allow-Origin: *',
			'Access-Control-Allow-Methods: GET',
		) );
	
		if( !empty( $headers ) ) {
		
			foreach( $headers as $strHeaderLine ) {
				header( $strHeaderLine );
			}
			
		}
		
		/*
	
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET');
		*/
	}

	function json_feed_content_type( $content_type, $type ) {
		$return = $content_type;
		
		if ( 'json' === $type ) {
			//return 'application/json';
			$return = 'application/json';
		}
		
		return $return;
	}

	function json_feed_link() {
		printf(
			'<link rel="alternate" type="application/json" title="%s &raquo; JSON Feed" href="%s" />' . PHP_EOL,
			esc_attr( get_bloginfo( 'name' ) ),
			esc_url( get_feed_link( 'json' ) )
		);
	}

	/**
	 * Display the links to the extra feeds such as category feeds.
	 *
	 *
	 * @param array $args Optional arguments.
	 */
	function json_feed_links_extra( $args = array() ) {
		
		$defaults = array(
			/* translators: Separator between blog name and feed type in feed links */
			'separator'     => _x( '&raquo;', 'feed link', 'jsonfeed' ),
			/* translators: 1: blog name, 2: separator(raquo), 3: post title */
			'singletitle'   => __( '%1$s %2$s %3$s Comments Feed', 'jsonfeed' ),
			/* translators: 1: blog name, 2: separator(raquo), 3: category name */
			'cattitle'      => __( '%1$s %2$s %3$s Category Feed', 'jsonfeed' ),
			/* translators: 1: blog name, 2: separator(raquo), 3: tag name */
			'tagtitle'      => __( '%1$s %2$s %3$s Tag Feed', 'jsonfeed' ),
			/* translators: 1: blog name, 2: separator(raquo), 3: term name, 4: taxonomy singular name */
			'taxtitle'      => __( '%1$s %2$s %3$s %4$s Feed', 'jsonfeed' ),
			/* translators: 1: blog name, 2: separator(raquo), 3: author name  */
			'authortitle'   => __( '%1$s %2$s Posts by %3$s Feed', 'jsonfeed' ),
			/* translators: 1: blog name, 2: separator(raquo), 3: search phrase */
			'searchtitle'   => __( '%1$s %2$s Search Results for &#8220;%3$s&#8221; Feed', 'jsonfeed' ),
			/* translators: 1: blog name, 2: separator(raquo), 3: post type name */
			'posttypetitle' => __( '%1$s %2$s %3$s Feed', 'jsonfeed' ),
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		
		if ( is_singular() ) {
			$id   = 0;
			$post = get_post( $id );
			if ( comments_open() || pings_open() || $post->comment_count > 0 ) {
				$title = sprintf( $args['singletitle'], get_bloginfo( 'name' ), $args['separator'], the_title_attribute( array( 'echo' => false ) ) );
				$href  = get_post_comments_feed_link( $post->ID, 'json' );
			}
		} elseif ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			$post_type_obj = get_post_type_object( $post_type );
			$title         = sprintf( $args['posttypetitle'], get_bloginfo( 'name' ), $args['separator'], $post_type_obj->labels->name );
			$href          = get_post_type_archive_feed_link( $post_type_obj->name, 'json' );
		} elseif ( is_category() ) {
			$term = get_queried_object();
			if ( $term ) {
				$title = sprintf( $args['cattitle'], get_bloginfo( 'name' ), $args['separator'], $term->name );
				$href  = get_category_feed_link( $term->term_id, 'json' );
			}
		} elseif ( is_tag() ) {
			$term = get_queried_object();
			if ( $term ) {
				$title = sprintf( $args['tagtitle'], get_bloginfo( 'name' ), $args['separator'], $term->name );
				$href  = get_tag_feed_link( $term->term_id, 'json' );
			}
		} elseif ( is_tax() ) {
			$term  = get_queried_object();
			$tax   = get_taxonomy( $term->taxonomy );
			$title = sprintf( $args['taxtitle'], get_bloginfo( 'name' ), $args['separator'], $term->name, $tax->labels->singular_name );
			$href  = get_term_feed_link( $term->term_id, $term->taxonomy, 'json' );
		} elseif ( is_author() ) {
			$author_id = intval( get_query_var( 'author' ) );
			$title     = sprintf( $args['authortitle'], get_bloginfo( 'name' ), $args['separator'], get_the_author_meta( 'display_name', $author_id ) );
			$href      = get_author_feed_link( $author_id, 'json' );
		} elseif ( is_search() ) {
			$title = sprintf( $args['searchtitle'], get_bloginfo( 'name' ), $args['separator'], get_search_query( false ) );
			$href  = get_search_feed_link( '', 'json' );
		} elseif ( is_post_type_archive() ) {
			$title         = sprintf( $args['posttypetitle'], get_bloginfo( 'name' ), $args['separator'], post_type_archive_title( '', false ) );
			$post_type_obj = get_queried_object();
			if ( $post_type_obj ) {
				$href = get_post_type_archive_feed_link( $post_type_obj->name, 'json' );
			}
		}
		
		if ( isset( $title ) && isset( $href ) ) {
			printf( '<link rel="alternate" type="%s" title="%s" href="%s" />', esc_attr( feed_content_type( 'json' ) ), esc_attr( $title ), esc_url( $href ) );
			echo PHP_EOL;
		}
	}


	function json_feed_websub( $feeds = array() ) {
		$feeds[] = get_feed_link( 'json' );
		return $feeds;
	}
}
