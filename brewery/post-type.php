<?php 
    

    add_action('init','register_brewery_custom_post_type');
    function register_brewery_custom_post_type(){
        $post_array = array(
            'label'=>'Breweries',
            'public'=>true,
            
        );
        register_post_type( 'brewery', $post_array);
    }