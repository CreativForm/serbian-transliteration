<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Active Plugin: WooCommerce
 *
 * @link              http://infinitumform.com/
 * @since             1.2.4
 * @package           Serbian_Transliteration
 * @author            Ivijan-Stefan Stipic
 */
if(!class_exists('Serbian_Transliteration__Plugin__wordpress_seo')) :
	class Serbian_Transliteration__Plugin__wordpress_seo extends Serbian_Transliteration
	{
		
		/* Run this script */
		public static function run() {
			global $rstr_cache;
			if ( !$rstr_cache->get('Serbian_Transliteration__Plugin__wordpress_seo') ) {
				$rstr_cache->set('Serbian_Transliteration__Plugin__wordpress_seo', new self());
			}
			return $rstr_cache->get('Serbian_Transliteration__Plugin__wordpress_seo');
		}
		
		function __construct(){
			$this->add_filter('rstr/transliteration/exclude/filters', array(get_class(), 'filters'));
		} 
		
		public static function filters ($filters=array()) {
			
			$classname = self::run(false);
			$filters = array_merge($filters, array(
				'wpseo_breadcrumb_links' => array($classname, 'content')
			));
			asort($filters);
			return $filters;
		}
		
		public function content ($content='') {
			if(empty($content)) return $content;
			
			
			
			if(is_array($content))
			{
				$content = $this->transliterate_objects($content);
			}
			else if(is_string($content) && !is_numeric($content))
			{
					
				switch($this->get_current_script($this->get_options()))
				{
					case 'cyr_to_lat' :
						$content = $this->cyr_to_lat($content);
						break;
						
					case 'lat_to_cyr' :
						$content = $this->lat_to_cyr($content);			
						break;
				}
			}
			return $content;
		}
		
	}
endif;