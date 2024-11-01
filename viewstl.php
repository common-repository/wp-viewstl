<?php
/*
Plugin Name: View STL
Plugin URI: http://viewstl.com/
Description: A plugin to allow uploading and viewing STL files through the viewstl.com embed
Version: 1.0
Author: falldeaf
Author URI: http://falldeaf.com
License: GPL
*/

wp_register_script( 'embedjs', plugins_url('/js/embed.js',__FILE__ ), array( 'jquery' ));
wp_register_script( 'spectrumjs', plugins_url('/js/spectrum.js',__FILE__ ), array( 'jquery' ));
wp_register_style('embedcss', plugins_url('/css/embed_style.css',__FILE__ ));
wp_register_style('spectrumcss', plugins_url('/css/spectrum.css',__FILE__ ));

wp_enqueue_style('embedcss');
wp_enqueue_style('spectrumcss');
wp_enqueue_script('spectrumjs');
wp_enqueue_script('embedjs');

/////MIME TYPE MADNESS! Add STL functionality with viewstl.com embed viewer

function wpvstl_enable_extended_upload ( $mime_types =array() ) {
 	$mime_types['stl'] = 'application/sla';
 	return $mime_types;
}
add_filter('upload_mimes', 'wpvstl_enable_extended_upload');

function wpvstl_modify_post_mime_types( $post_mime_types ) {
     $post_mime_types['application/sla'] = array( __( 'STLs' ), __( 'Manage STLs' ), _n_noop( 'PDF <span class="count">(%s)</span>', 'STLs <span class="count">(%s)</span>' ) );
    return $post_mime_types;
}
add_filter( 'post_mime_types', 'wpvstl_modify_post_mime_types' );

//Let user filter by STL type only in MM
function wpvstl_filter_stl($html, $id) {
    $attachment = get_post($id); //fetching attachment by $id passed through
    $mime_type = $attachment->post_mime_type; //getting the mime-type
    if ($mime_type == 'application/sla') { //checking mime-type
        $src = wp_get_attachment_url( $id );
        $html = '[viewstl id='.$id.']';
        return $html; // return new $html    
    }
        return $html;
}
add_filter('media_send_to_editor', 'wpvstl_filter_stl', 20, 3);

function wpvstl_default_values() {

	$defaults = array(
		'embed_width' => '100%',
		'embed_height' => '450px',
		'color' => 'red',
		'background_color' => '#FFFFFF',
		'rotation' => '1',
		'clean' => '0',
		'border' => '0',
		'show_color' => '1',
		'show_render' => '1',
		'show_capture' => '1',
		'show_download' => '1',
	);
	
	return $defaults;
}

//Add the shortcode for embedding STL's!
function wpvstl_stlembed_func( $atts ) {

	
	
	$options = wp_parse_args(get_option( 'viewstl_option_name'), wpvstl_default_values() );

	$a = shortcode_atts( array(
		'url' => '',
		'id' => '',
		'height' => $options['embed_height'],
		'width' => $options['embed_width'],
		'color' => $options['color'],
		'shading' => $options['shading'],
		'rotation' => ($options['rotation'] ? 'yes' : 'no'),
		'clean' => ($options['clean'] ? 'yes' : 'no'),
		'border' => ($options['border'] ? 'yes' : 'no')
	), $atts );

	$ids = explode(',',$a['id']);

	if($a['url'] == '') {
		$src = wp_get_attachment_url( $ids[0] );	

		foreach($ids as $id) {    
		    $stl_files .= '<li class="stl_select" url="'. wp_get_attachment_url($id) .'">'. get_the_title($id) .'</li>';    
		}
	} else {
		$src = $a['url'];
	}

    //return "foo = {$a['']}";
	return '
<div id="stl_view_container" style="height:'.$a['height'].';width:'.$a['width'].';">

<div id="stl_view" style="width:80%; height:'.$a['height'].'; float:left;">
<iframe id="vs_iframe" src="http://www.viewstl.com/?embeded&url='. wp_get_attachment_url($ids[0]) .'&color='.$a['color'].'&noborder='.$a['border'].'&shading='.$a['shading'].'&rotation='.$a['rotation'].'&clean='.$a['clean'].'" style="border:0;margin:0;width:100%;height:'.$a['height'].'"></iframe>
</div>

<div id="stl_control" style="width:20%; height:'.$a['height'].'; float:right; color:black;">
 
<div class="stl_file_list stl_item">
<div class="stl_title">STL Files</div>
 <ul>' . $stl_files . '</ul>
</div>
' . 

($options['show_color'] ? '
	<div class="cpal stl_item">
		<div id="stl_color" color="'.$a['color'].'" class="stl_title">
			Color: 
			<div class="stl_control">
				<input type="text" class="colorpicker"/>
			</div>
		</div>
	</div>
' : '') .

($options['show_render'] ? '
	<div class="stl_item">
		<div class="stl_title">
			Render:
			<select name="render" class="render_select stl_control">
				<option value="flat" selected>flat</option>
				<option value="smooth">smooth</option>
				<option value="wireframe">wire</option>
			</select>
		</div>
	</div>
' : '') .

($options['show_capture'] ? '
	<div class="stl_item">
		<div id="capture_div" camsnd="' .  plugin_dir_url( __FILE__ ) . 'camsnd.wav" class="image_capture stl_title">
			Capture:
			<button class="capture_image stl_control">image</button>
		</div>
	</div>
' : '') .

($options['show_download'] ? '
	<a id="stl_dl_link" href="'.wp_get_attachment_url( $ids[0] ).'"><div class="stl_download stl_item">Download STL</div></a>
' : '') .

'
</div>	

</div>';
}
add_shortcode( 'viewstl', 'wpvstl_stlembed_func' );


/* TRYING TO ADD GALLERY ABILITY FOR STL's :/
add_action('print_media_templates', function(){

  // define your backbone template;
  // the "tmpl-" prefix is required,
  // and your input field should have a data-setting attribute
  // matching the shortcode name
  ?>
  <script type="text/html" id="tmpl-my-custom-gallery-setting">
    <label class="setting">
      <span><?php _e('My setting'); ?></span>
      <select data-setting="my_custom_attr">
        <option value="foo"> Foo </option>
        <option value="bar"> Bar </option>
        <option value="default_val"> Default Value </option>
      </select>
    </label>
  </script>

  <script>

    jQuery(document).ready(function(){

      // add your shortcode attribute and its default value to the
      // gallery settings list; $.extend should work as well...
      _.extend(wp.media.gallery.defaults, {
        my_custom_attr: 'default_val'
      });

      // merge default gallery settings template with yours
      wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
        template: function(view){
          return wp.media.template('gallery-settings')(view)
               + wp.media.template('my-custom-gallery-setting')(view);
        }
      });

    });

  </script>
  <?php

});


// add the tab
add_filter('media_upload_tabs', 'my_upload_tab');
function my_upload_tab($tabs) {
    $tabs['mytabname'] = "My Tab Name";
    return $tabs;
}

// call the new tab with wp_iframe
add_action('media_upload_mytabname', 'add_my_new_form');
function add_my_new_form() {
    wp_iframe( 'my_new_form' );
}

// the tab content
function my_new_form() {
    echo media_upload_header(); // This function is used for print media uploader headers etc.
    echo '<p>Example HTML content goes here.</p>';
}
*/

/////SETTINGS PAGE/////////////////
class wpvstl_ViewSTLSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'ViewSTL Settings', 
            'manage_options', 
            'viewstl-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'viewstl_option_name' );
        ?>
        <div class="wrap">
            <h2>ViewSTL Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'viewstl_option_group' );   
                do_settings_sections( 'viewstl-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'viewstl_option_group', // Option group
            'viewstl_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Default Embed settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'viewstl-setting-admin' // Page
        );  

        add_settings_field(
            'embed_width', // ID
            'Width', // Title 
            array( $this, 'width_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'embed_height', // ID
            'Height', // Title 
            array( $this, 'height_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'color', // ID
            'Color', // Title 
            array( $this, 'color_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'background_color', // ID
            'Background Color', // Title 
            array( $this, 'background_color_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'shading', // ID
            'Shading', // Title 
            array( $this, 'shading_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'rotation', // ID
            'Rotation', // Title 
            array( $this, 'rotation_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'clean', // ID
            'Clean Mode (no texts/progress bars)', // Title 
            array( $this, 'clean_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'border', // ID
            'Border', // Title 
            array( $this, 'border_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'show_color', // ID
            'Show Color Option', // Title 
            array( $this, 'show_color_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );

        add_settings_field(
            'show_render', // ID
            'Show Render Option', // Title 
            array( $this, 'show_render_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );

        add_settings_field(
            'show_capture', // ID
            'Show Capture Option', // Title 
            array( $this, 'show_capture_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );

        add_settings_field(
            'show_download', // ID
            'Show Download Option', // Title 
            array( $this, 'show_download_callback' ), // Callback
            'viewstl-setting-admin', // Page
            'setting_section_id' // Section           
        );
		
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
	/*
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );
	*/

        return $input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values : WIDTH
     */
    public function width_callback()
    {
        printf(
            '<input type="text" id="width_options" name="viewstl_option_name[embed_width]" value="%s" />',
            isset( $this->options['embed_width'] ) ? esc_attr( $this->options['embed_width']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values : HEIGHT
     */
    public function height_callback()
    {
        printf(
            '<input type="text" id="height_options" name="viewstl_option_name[embed_height]" value="%s" />',
            isset( $this->options['embed_height'] ) ? esc_attr( $this->options['embed_height']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function color_callback()
    {
	$options = get_option( 'viewstl_option_name' );
     
	$html = '<select id="color_options" name="viewstl_option_name[color]">';
		$html .= '<option value="black"' . selected( $options['color'], 'black', false) . '>black</option>';
		$html .= '<option value="white"' . selected( $options['color'], 'white', false) . '>white</option>';
		$html .= '<option value="blue"' . selected( $options['color'], 'blue', false) . '>blue</option>';
		$html .= '<option value="green"' . selected( $options['color'], 'green', false) . '>green</option>';
		$html .= '<option value="red"' . selected( $options['color'], 'red', false) . '>red</option>';
		$html .= '<option value="yellow"' . selected( $options['color'], 'yellow', false) . '>yellow</option>';
		$html .= '<option value="grey"' . selected( $options['color'], 'grey', false) . '>grey</option>';
		$html .= '<option value="azure"' . selected( $options['color'], 'azure', false) . '>azure</option>';
		$html .= '<option value="pink"' . selected( $options['color'], 'pink', false) . '>pink</option>';
		$html .= '<option value="purple"' . selected( $options['color'], 'purple', false) . '>purple</option>';
		$html .= '<option value="darkblue"' . selected( $options['color'], 'darkblue', false) . '>darkblue</option>';
		$html .= '<option value="brown"' . selected( $options['color'], 'brown', false) . '>brown</option>';

	$html .= '</select>';
     
	echo $html;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function background_color_callback()
    {
        printf(
            '<input type="text" id="background_color_options" name="viewstl_option_name[background_color]" value="%s" />',
            isset( $this->options['background_color'] ) ? esc_attr( $this->options['background_color']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function shading_callback()
    {
	$options = get_option( 'viewstl_option_name' );
     
	$html = '<select id="shading_options" name="viewstl_option_name[shading]">';
		$html .= '<option value="flat"' . selected( $options['shading'], 'flat', false) . '>flat</option>';
		$html .= '<option value="smooth"' . selected( $options['shading'], 'smooth', false) . '>smooth</option>';
		$html .= '<option value="wireframe"' . selected( $options['shading'], 'wireframe', false) . '>wireframe</option>';
	$html .= '</select>';
     
	echo $html;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function rotation_callback()
    {
	$options = get_option( 'viewstl_option_name' );
	$html = '<input type="checkbox" id="rotation_options" name="viewstl_option_name[rotation]" value="1"' . checked( 1, $options['rotation'], false ) . '/>';     
	echo $html;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function clean_callback()
    {
	$options = get_option( 'viewstl_option_name' );
	$html = '<input type="checkbox" id="clean_options" name="viewstl_option_name[clean]" value="1"' . checked( 1, $options['clean'], false ) . '/>';     
	echo $html;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function border_callback()
    {
	$options = get_option( 'viewstl_option_name' );
	$html = '<input type="checkbox" id="border_options" name="viewstl_option_name[border]" value="1"' . checked( 1, $options['border'], false ) . '/>';     
	echo $html;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function show_color_callback()
    {
	$options = get_option( 'viewstl_option_name' );
	$html = '<input type="checkbox" id="show_color_options" name="viewstl_option_name[show_color]" value="1"' . checked( 1, $options['show_color'], false ) . '/>';     
	echo $html;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function show_render_callback()
    {
	$options = get_option( 'viewstl_option_name' );
	$html = '<input type="checkbox" id="show_render_options" name="viewstl_option_name[show_render]" value="1"' . checked( 1, $options['show_render'], false ) . '/>';     
	echo $html;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function show_capture_callback()
    {
	$options = get_option( 'viewstl_option_name' );
	$html = '<input type="checkbox" id="show_capture_options" name="viewstl_option_name[show_capture]" value="1"' . checked( 1, $options['show_capture'], false ) . '/>';     
	echo $html;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function show_download_callback()
    {
	$options = get_option( 'viewstl_option_name' );
	$html = '<input type="checkbox" id="show_download_options" name="viewstl_option_name[show_download]" value="1"' . checked( 1, $options['show_download'], false ) . '/>';     
	echo $html;
    }


}

if( is_admin() )
    $viewstl_settings_page = new wpvstl_ViewSTLSettingsPage();

?>
