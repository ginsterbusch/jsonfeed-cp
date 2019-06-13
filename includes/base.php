<?php
/** 
 * Helpers base class
 * Provides a few helping base / utility methods
 */
 
class _ui_JsonFeed_CP_Base {

	function get_methods( $prefix = '' ) {
		return $this->get_methods_by( 'prefix', $prefix );
		
	}
	
	public static function get_post_id( $default = 0, $in_loop = false ) {
		$helper = new self();
		
		if( empty( $in_loop ) ) {
			return $helper->_get_post_id( $default );
		} else {
			return $helper->get_post_object_id( $default );
		}
	}
	
	
	/**
	 * Uses global $post to retrieve the post ID
	 *
	 * @param int $post_id		Current post ID (used as fallback ).
	 */
	
	
	function get_post_object_id( $post_id = 0 ) {
		$return = $post_id;
		global $post;
		
		if( !empty( $post ) && isset( $post->ID ) ) {
			$return = $post->ID;
		}
		
		
		return $return;
	}
	
	function _get_post_id( $default = 0 ) {
		$return = $default;
		
		$current_post = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
		
		if( !empty( $current_post ) && !empty( $current_post->ID ) ) {
			$return = $current_post->ID;
		}
		
		
		return $return;
	}
	
	function load_config( $path = '', $var_name = '' ) {
		$return = false;
		
		if( !empty( $path ) && file_exists( $path ) ) {
			
			$strExt = pathinfo( $path, PATHINFO_EXTENSION );
			switch( $strExt ) {
				case 'php':
				default:
					
					include( $path );
					
					break;
				case 'json':
				case 'js':
				case 'config':
					$strContent = file_get_contents( $path );
					if( $this->is_json( $strContent ) ) {
						$config = json_decode( $strContent );
						
					}
					
					break;
			}
			
			if( !empty( $config ) && is_array( $config ) && $this->is_assoc( $config ) ) {
				$strVarName = ( !empty( $var_name ) && is_string( $var_name ) ? $var_name : 'config' );
				
				$this->$strVarName = $config;
				$return = true;
			}
				
		}
		
		return $return;
	}

	function is_assoc( $array = false ) {
		$return = false;
	 
		if( !empty( $array ) && is_array( $array ) ) {
			foreach($array as $key => $value) {
				if ($key !== (int) $key) {
					$return = true;
					break;
				}
			}
		}
		return $return;
	}


	
	function is_json( $data = '', $doublequotes = false ) {
		$return = false;
		
		/**
		 * Also see @link https://api.jquery.com/jQuery.parseJSON/
		 * NOTE: Optional requirement of double quotes
		 */
		
		if( !empty( $data ) ) {
			if( ( strpos( $data, '[' ) !== false && strpos( $data, ']' ) !== false ) ||
			( strpos( $data, '{' ) !== false && strpos( $data, '}' ) !== false ) ) {
				$return = true;
			}
			
			if( !empty( $doublequotes ) ){
				$return = ( strpos( $data, '"' ) !== false ? true : false);
			}
		}
		
		return $return;
	}
	
	
	/**
	 * Filter existing class methods by prefix, suffix or search string.
	 * 
	 * @param string $type		Defaults to 'prefix'. Allowed types: 'prefix', 'suffix', 'search'.
	 * @param string $search	Value to search for.
	 * @param mixed	$subject	Detect class methods of a different object than the current one.
	 * @param array $exclude	Exclude list. Defaults to false.
	 */
	
	function get_methods_by( $type = 'prefix', $search = '', $subject = false, $exclude = false ) {
		$return = false;
		
		
		if( !empty( $search ) ) {
			$arrExclude = array( 'get_methods_by', 'get_methods', '__construct' );
			if( !empty( $exclude ) && is_array( $exclude ) ) {
				$arrExclude = wp_parse_args( $arrExclude, $exclude );
			}
			
			
			if( !empty( $subject ) ) {
				$arrMethods = get_class_methods( $subject );
			} else {
				$arrMethods = get_class_methods( $this );
			}
			
			
			
			foreach( $arrMethods as $strMethod ) {
				switch( $type ) {
					case 'prefix':
						if( substr( $strMethod, 0, strlen( $strMethod ) ) == $search ) {
							$arrReturn[] = $strMethod;
						}

						break;
					case 'postfix':
					case 'suffix':
						if( substr( $strMethod, -strlen( $strMethod ) ) == $search ) {
							$arrReturn[] = $strMethod;
						}
					
						break;
					case 'needle':
					case 'search':
					case 'find':
						if( strpos( $strMethod, $search ) !== false ) {
							$arrReturn[] = $strMethod;
						}
						break;
				}
				
			}
			
			if( !empty( $arrReturn ) ) {
				$return = $arrReturn;
			}
			
			
		}
		
		return $return;
	}
	
	function get_post_by( $type = 'slug', $value = '', $post_type = 'product_industry' ) {
		$return = false;
		global $wpdb;
		
		if( !empty( $value ) && !empty( $type ) ) {
			switch( $type ) {
				case 'slug':
					$strQuery = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_name LIKE '%%%s%%' AND post_type = %s LIMIT 1", sanitize_title( $value ), $post_type );
					$result = $wpdb->get_results( $strQuery );
					
					if( !empty( $result ) ) {
						$return = reset( $result );
					}
					
					break;
				case 'title':
				case 'post_title':
					$strQuery = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_title LIKE '%%%s%%' AND post_type = %s LIMIT 1", $value, $post_type );
					
					$result = $wpdb->get_results( $strQuery );
					
					if( !empty( $result ) ) {
						$return = reset( $result );
					}
				
					break;
				case 'id':
					if( is_numeric( $value ) && absint( $value ) > 0 ) {
						$return = get_post( absint( $value ) );
					}
					break;
			}
			
			/*
			new __debug( 
				array(
					
					'type' => $type,
					'value' => $value,
					'post_type' => $post_type,
					'query' => $strQuery,
					'result' => $result,
				),
				__METHOD__
			);
			*/
		}
		
		return $return;
	}
	
	function get_template_path( $template_name = '', $fallback_template = '' ) {
		$return = $fallback_template;
		
		if( !empty( $template_name ) ) {
			if( strpos( $template_name, get_template_directory() ) && file_exists( $template_name ) ) {
				$return = $template_name;
			} elseif( file_exists( trailingslashit( get_template_directory() ) . $template_name ) ) {
				$return = trailingslashit( get_template_directory() ) . $template_name;
			}
		}

		return $return;
	}
	
	
	function is_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || $this->is_plugin_active_for_network( $plugin );
	}
	
	function is_plugin_inactive( $plugin ) {
		return ! $this->is_plugin_active( $plugin );	
	}
	
	function is_plugin_active_for_network( $plugin ) {
		if ( !is_multisite() )
			return false;

		$plugins = get_site_option( 'active_sitewide_plugins');
		if ( isset($plugins[$plugin]) )
			return true;

		return false;
	}
	
		
	/**
	 * fetch the longest key of an array
	 * 
	 * @see @link https://stackoverflow.com/questions/36101755/php-associative-array-sort-and-get-key-with-highest-length#comment89929383_36101920
	 */
	 
	function _get_longest_key( $array = array( ), $return_length = false ) {
		$return = max(array_keys($array, max($array)));
		
		if( !empty( $return_length ) ) {
			$return = strlen( $return );
		}
		
		return $return;
	}
}
