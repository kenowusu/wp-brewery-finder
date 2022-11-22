<?php 
 
 require  get_template_directory() . '/brewery/post-type.php';
//register get brewery as ajax function 

add_action('wp_ajax_get_breweries_from_api','get_breweries_from_api');
add_action('wp_ajax_nopriv_get_breweries_from_api','get_breweries_from_api');


$open_brewery_url = "https://api.openbrewerydb.org/breweries";


function get_breweries_from_api(){
    global $open_brewery_url;

    /*
     * get brewery from api with limit of 50
     * increase counter
     * if there is still breweries (is_array($results)):
     * call function again with counter (page+=1)
     * insert into database or update
     * if not end

    */
    $current_page = (!empty($_POST['current_page'])) ? $_POST['current_page'] : 1;

    $results = wp_remote_get($open_brewery_url . "?page=". $current_page . "&per_page=10");
    
   
    
    $file = get_template_directory() . '/brewery/file.txt';

    file_put_contents($file,"Current Page: ".$current_page . "\n\n",FILE_APPEND);

    $current_page  = $current_page + 1;
    
    $breweries  = json_decode($results);
    
    /**insert into database */

    foreach($breweries as $brewery){
      /*insert brewery**/
      $brewery_data = array(
        'id'=>$brewery->id,
        'post_name'=>$brewery->id,
        'post_title'=>$brewery->id,
        'post_type'=>'brewery',
        'post_status'=>'published',
      );

      $inserted_id = wp_insert_post($brewery_data);

      $fillable = array(
        'name'=>'field_637d00ef08c10',
        'brewery_type'=>'field_637d014508c11',
        'street'=>'field_637d015808c12',
        'city'=>'field_637d016108c13',
        'state'=>'field_637d017a08c14',
        'postal_code'=>'field_637d018708c15',
        'country'=>'field_637d019408c16',
        'longitude'=>'field_637d019f08c17',
        'latitude'=>'field_637d01a808c18',
        'phone'=>'field_637d01ae08c19',
        'website_url'=>'field_637d01b308c1a',
        'updated_at'=>'field_637d01ba08c1b'
      );
       
      /** insert(update) fields added by custom post types*/
      foreach($fillable as $name => $field_key){
        update_field($field_key,$brewery->$name,$inserted_id);
      }
    
    }


    if(is_array($results)){
        $ajax_url = "admin-ajax.php?action=get_breweries_from_api";
        $request_array = array(
          'blocking'=>false,
          'sslverify'=>false,
          'body'=> array(
            'current_page'=>$current_page
          )
        );

        // wp_send_json_success( $request_array ,200);

        //let function call itself until there is no more data from api
        wp_remote_post(admin_url($ajax_url),$request_array);


    }

}

