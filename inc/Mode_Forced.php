<?php
/**
 * Forced Transliteration Mode
 *
 * @link              http://infinitumform.com/
 * @since             1.0.0
 * @package           Serbian_Transliteration
 */
class Serbian_Transliteration_Mode_Forced extends Serbian_Transliteration
{
	private $options;
	
	function __construct($options){
		$this->options = $options;
		
		$filters = array(
			'the_content' => 'content',
			'the_title' => 'content',
			'wp_nav_menu_items' => 'content',
			'wp_title' => 'content',
			'default_post_metadata' => 'content',
			'get_comment_metadata' => 'content',
			'get_term_metadata' => 'content',
			'get_user_metadata' => 'content',
			'gettext' => 'content',
			'widget_text' => 'content',
			'widget_title' => 'content',
			'widget_text_content' => 'content',
			'widget_custom_html_content' => 'content',
			'sanitize_title' => 'content',
			'wp_unique_post_slug' => 'content',
			'document_title_parts' => 'title_parts'
		);
		
		if(isset($options['media-transliteration']) && $options['media-transliteration'] == 'yes'){
			$filters['wp_handle_upload_prefilter'] = 'upload_filter';
		}
		
		if(isset($options['permalink-transliteration']) && $options['permalink-transliteration'] == 'yes' && !$this->already_cyrillic()){
			$filters['sanitize_title'] = 'force_permalink_to_latin';
			$filters['the_permalink'] = 'force_permalink_to_latin';
			$filters['wp_unique_post_slug'] = 'force_permalink_to_latin';
		}
		
		if(isset($this->options['avoid-admin']) && $this->options['avoid-admin'] == 'yes')
		{
			if(!is_admin())
			{
				foreach($filters as $filter=>$function) $this->add_filter($filter, $function, 9999999, 1);
				$this->add_action('wp_loaded', 'output_buffer_start');
				$this->add_action('shutdown', 'output_buffer_end');
			}
		}
		else
		{
			foreach($filters as $filter=>$function) $this->add_filter($filter, $function, 9999999, 1);
			$this->add_action('wp_loaded', 'output_buffer_start');
			$this->add_action('shutdown', 'output_buffer_end');
		}
	}
	
	function output_buffer_start() { 
		ob_start(array(&$this, "output_callback")); 
	}
	
	function output_buffer_end() { 
		ob_get_clean(); 
	}
	
	public function output_callback ($buffer='') {
		if(empty($buffer)) return $buffer;
		
		if(!(defined('DOING_AJAX') && DOING_AJAX))
		{
			$buffer = preg_replace_callback('/(?=<div(.*?)>)(.*?)(?<=<\/div>)/s', function($matches) {
				switch(isset($this->options['transliteration-mode']) ? $this->options['transliteration-mode'] : NULL)
				{
					case 'cyr_to_lat' :
						$matches[2] = str_replace($this->cyr, $this->lat, $matches[2]);
						break;
						
					case 'lat_to_cyr' :
						$matches[2] = str_replace($this->lat, $this->cyr, $matches[2]);
						$matches[2] = $this->fix_html($matches[2]);
						break;
				}
				return $matches[2];
			}, $buffer);
			
			if(isset($this->options['transliteration-mode']) && $this->options['transliteration-mode'] == 'lat_to_cyr'){
				$buffer = $this->fix_html($buffer);
			}
		}
		
		return $buffer;
	}
	
	public function content ($content='') {
		if(empty($content)) return $content;
		
		switch(isset($this->options['transliteration-mode']) ? $this->options['transliteration-mode'] : NULL)
		{
			case 'cyr_to_lat' :
				$content = str_replace($this->cyr, $this->lat, $content);
				break;
				
			case 'lat_to_cyr' :
				$content = str_replace($this->lat, $this->cyr, $content);
				$content = $this->fix_html($content);				
				break;
		}
		
		return $content;
	}
	
	public function title_parts($titles=array()){
		switch(isset($this->options['transliteration-mode']) ? $this->options['transliteration-mode'] : NULL)
		{
			case 'cyr_to_lat' :
				foreach($titles as $key => $val)
				{
					$titles[$key]= str_replace($this->cyr, $this->lat, $titles[$key]);
				}
				break;
				
			case 'lat_to_cyr' :
				foreach($titles as $key => $val)
				{
					$titles[$key]= str_replace($this->lat, $this->cyr, $titles[$key]);
					$titles[$key]= $this->fix_html($titles[$key]);	
				}
				break;
		}
		
		return $titles;
	}
	
	public function upload_filter ($file) {
		$file['name']= str_replace($this->cyr, $this->lat, $file['name']);
		return $file;
	}
	
	public function force_permalink_to_latin ($permalink) {
		$permalink= str_replace($this->cyr, $this->lat, $permalink);
		return $permalink;
	}
}