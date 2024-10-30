<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Mimo_Colors_Display
 * @author    Mimo <mail@mimo.studio>
 * @license   GPL-2.0+
 * @link      http://mimo.studio
 * @copyright 2015 Mimo
 */

 ?>

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <div class="postbox">
			
			<h3 class="hndle"><span><?php _e( 'Mimo Colors', 'mimo-colors' ); ?></span></h3>
			
			<div class="inside">

				<p> <?php _e( 'If you like this plugin please rate it. find support at ', 'mimo-colors' ); ?><a href="http://www.mimo.studio"><?php _e( 'mimo.studio', 'mimo-colors' ); ?></a></p>

			</div>
	</div>
    <div id="tabs" class="settings-tab">
	<ul>
	    <li><a href="#tabs-1"><?php _e( 'Colors' ); ?></a></li>
	    
	    
	    
	</ul>
	<div id="tabs-1" class="wrap">
		<div class="postbox">
			<h3 class="hndle"><span><?php _e( 'General Settings', 'mimo-colors' ); ?></span></h3>
			<div class="inside">
		    
		
		    <?php

		    

		    $cmb = new_cmb2_box( array(

				'id' => $this->plugin_prefix . '_options',
				'hookup' => false,
				'show_on' => array( 'key' => 'options-page', 'value' => array( $this->plugin_prefix  ), ),
				'show_names' => true,
				    ) 
		    );

		    $group_field_id =  $cmb->add_field( array(

			'id'          => $this->plugin_prefix . '_all_colors',
			'type'        => 'group',
			'description' => __( 'Choose a color', 'mimo-colors' ),
			'options'     => array(
				'group_title'   => __( 'Color {#}', 'mimo-colors' ), // {#} gets replaced by row number
				'add_button'    => __( 'Add Another Color', 'mimo-colors' ),
				'remove_button' => __( 'Remove Color', 'mimo-colors' ),
			),
		) );
	    

	    $cmb->add_group_field( $group_field_id, array(
			'name' => __( 'Background Color', 'mimo-colors' ),
			'desc' => __( 'Choose a color to apply to background', 'mimo-colors' ),
			'id' => $this->plugin_prefix . '_bg_color',
			'type' => 'colorpicker',
			'default' => '#000000',
		    ) 
	    );

	    $cmb->add_group_field( $group_field_id, array(

			'name' => __( 'Text Color', 'mimo-colors' ),
			'desc' => __( 'Choose a color to apply to text', 'mimo-colors' ),
			'id' => $this->plugin_prefix . '_text_color',
			'type' => 'colorpicker',
			'default' => '#ffffff',
		    ) 
	    );

	    

		    

	    


	 	$cmb->add_group_field( $group_field_id, array(

			'name'             => __( 'Elements', 'mimo-colors' ),
			'desc'             => __( 'Class or id of element to apply the color, you can write several classes or ids separated by commas', 'mimo-colors' ),
			'id'               => $this->plugin_prefix . '_class',
			'type'             => 'text',
			'default' => '.myclass,#myid'
		    ) 
	    );
	    
	    
		    
		    cmb2_metabox_form( $this->plugin_prefix . '_options', $this->plugin_prefix . '_settings' ); ?>

	   		</div>
	    </div>
	</div>

	
	
	

    
</div>
