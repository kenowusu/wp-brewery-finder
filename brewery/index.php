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
    
    //get data from api
    $results = wp_remote_retrieve_body(wp_remote_get($open_brewery_url . "?page=". $current_page . "&per_page=50"));
    
    

    /******test insert current page into db */
    $file = get_template_directory() . '/brewery/file.txt';
    file_put_contents($file,"Current Page: ".$current_page . "\n\n",FILE_APPEND);

    
    //decode body(string) of api request  
    $breweries  = json_decode($results);
    
   



    // $breweries  = $results;
    /**insert into database */

    foreach($breweries as $brewery){
      /*insert brewery into table **/

      //check to see if post brewery exist in db 

      $existing_brewery = get_page_by_path( $brewery->id, 'OBJECT', 'brewery');
       
      /** if brewery does not exists, insert post */
      if($existing_brewery === null){

        
      
        $brewery_data = array(
          'post_name'=>$brewery->id,
          'post_title'=>$brewery->id,
          'post_type'=>'brewery',
          'post_status'=>'publish',
        );

        $inserted_id = wp_insert_post($brewery_data);

        $fillable = array(
          'name'=>'field_637e0eefbcc9a',
          'brewery_type'=>'field_637e0f43fc5e8',
          'street'=>'field_637e0f53fc5e9',
          'city'=>'field_637e0f59fc5ea',
          'state'=>'field_637e0f65fc5eb',
          'postal_code'=>'field_637e0f6bfc5ec',
          'country'=>'field_637e0f77fc5ed',
          'longitude'=>'field_637e0f8f1c1e1',
          'latitude'=>'field_637e0f9b1c1e2',
          'phone'=>'field_637e0fb71c1e3',
          'website_url'=>'field_637e0fc31c1e4',
          'updated_at'=>'field_637e0fca1c1e5'
        );
        
        /** insert(update) fields of inserted brewery post type added by acf*/
        foreach($fillable as $name => $field_key){
          update_field($field_key,$brewery->$name,$inserted_id);
        }
    
    }else{
      $existing_brewery_id = $existing_brewery->ID;
      $existing_brewery_timestamp = get_field('updated_at',$existing_brewery_id);

      if($brewery->updated_at >= $existing_brewery_timestamp){
        // update brewery 


        $fillable = array(
          'name'=>'field_637e0eefbcc9a',
          'brewery_type'=>'field_637e0f43fc5e8',
          'street'=>'field_637e0f53fc5e9',
          'city'=>'field_637e0f59fc5ea',
          'state'=>'field_637e0f65fc5eb',
          'postal_code'=>'field_637e0f6bfc5ec',
          'country'=>'field_637e0f77fc5ed',
          'longitude'=>'field_637e0f8f1c1e1',
          'latitude'=>'field_637e0f9b1c1e2',
          'phone'=>'field_637e0fb71c1e3',
          'website_url'=>'field_637e0fc31c1e4',
          'updated_at'=>'field_637e0fca1c1e5'
        );
        
        /** insert(update) fields of inserted brewery post type added by acf*/
        foreach($fillable as $name => $field_key){
          update_field($field_key,$brewery->$name,$existing_brewery_id);
        }

      }

    }

  }
     //increase current_page for next post request 
     $current_page  = $current_page + 1;

   /************disable infinite loop** */
    if(is_array($breweries)){
        $ajax_url = "admin-ajax.php?action=get_breweries_from_api";
        $request_array = array(
          'blocking'=>false,
          'sslverify'=>false,
          'body'=> array(
            'current_page'=>$current_page
          )
        );

        

        //let function call itself until there is no more data from api
        wp_remote_post(admin_url($ajax_url),$request_array);


    }

}

