<?php

require_once('../config.php');
require_once('../includes/WfoFacets.php');

/**
 * Functions for importing files in 
 * facets package format
 * 
 */
class WfoFacetsImporter extends WfoFacets{


    private $meta;
    private $csv_handle;

    /**
     * @param import_path  path to file or directory we will be using
     * 
     */
     public function init($import_path){
        
        if(is_dir($import_path)){
            // we have a directory so get handles on the files in the directory

        }elseif(is_file($import_path)){
            // we have a zip file so get handles on the files within the zip

        }else{
            // neither file nor directory so throw a wobbly
            //throw Exception
        }

        // load the json into the meta object
        

    }




   


}// end class