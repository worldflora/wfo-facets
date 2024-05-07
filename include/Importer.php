<?php

class Importer{


    public string $filePath;
    public bool   $overwrite;
    public int $sourceId;
    public int $facetValueId;
    public string $offset;
    public ?int $created = null;
    public $file = null;
    

    public function __construct($input_file_path, $overwrite, $source_id, $facet_value_id, $offset = 0){

        global $mysqli;

        $this->created = time();
        
        $this->filePath = $input_file_path;
        $this->overwrite = $overwrite;
        $this->sourceId = $source_id;
        $this->facetValueId = $facet_value_id;
        $this->offset = $offset;

        $this->file = fopen($this->filePath, 'r');

        // if they are choosing to remove then remove
        if($this->overwrite){
            $mysqli->query("DELETE FROM wfo_scores WHERE source_id = $this->sourceId;");
        }

        $this->seek($this->offset);

    }
        
    public function __sleep(){
        fclose($this->file);
        return array('filePath', 'overwrite', 'sourceId', 'facetValueId', 'offset', 'created');
    }
    
    public function __wakeup(){
        $this->file = fopen($this->filePath, 'r');
        $this->seek($this->offset);
    }

    public function seek($line){
        rewind($this->file);
        for ($i=0; $i < $line; $i++) {
            fgetcsv($this->file);
        }
    }

    public function import($page_size){

        global $mysqli;

  
        for ($i=0; $i < $page_size; $i++) { 

            $row = fgetcsv($this->file);
            $this->offset++;

            if(!$row){
                $mysqli->query("UPDATE sources SET harvest_last = now() WHERE id = {$this->sourceId};");
                return $i;
            } 

            // wfo id is in first column
            $wfo_id = $row[0];
            
            // must be correct format
            if(!preg_match('/^wfo-[0-9]{10}$/', $wfo_id)) continue;

            // do nothing if it is already there
            $response = $mysqli->query("SELECT * FROM wfo_scores WHERE wfo_id = '$wfo_id' AND source_id = {$this->sourceId};");
            if($response->num_rows > 0){
                $response->close();
                continue;
            }else{
                $response->close();
            }

            // must exist in the cache - will be added if it isn't there
            if(NameCache::cacheName($wfo_id)){
                $mysqli->query("INSERT INTO wfo_scores (`wfo_id`, `source_id`, `value_id`) VALUES ('$wfo_id', {$this->sourceId}, {$this->facetValueId});");
                if($mysqli->error) error_log($mysqli->error);
            }

        }

        return $page_size;
        
    }
}