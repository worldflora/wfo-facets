<?php


/**
 * 
 * Represents a data source
 * 
 */
class Source extends Facet{

    /**
     * Returns a facet value based on the db row
     * 
     */
    public static function getSource($id){

        global $mysqli;
        $sql = "SELECT * FROM sources WHERE id = $id;";
        $response = $mysqli->query($sql);
        $rows = $response->fetch_all(MYSQLI_ASSOC);
        $response->close();

        if(count($rows) == 0){
            return null;
        }else{
            return new Source($rows[0]['title'],$rows[0]['description'], $rows[0]['uri'], $rows[0]['id']);
        }

    }

    public function save(){

        global $mysqli;

        $title_safe = $mysqli->real_escape_string($this->title);
        $description_safe = $mysqli->real_escape_string($this->description);
        $uri_safe = $mysqli->real_escape_string($this->uri);

        if($this->id){
            // we already exist so update
            $sql = "UPDATE sources 
                    SET 
                    `title` = '$title_safe',
                    `description` = '$description_safe',
                    `uri` = '$uri_safe'
                    WHERE
                    `id` = {$this->id} ";
        }else{
            // we are creating
            $sql = "INSERT INTO `sources` 
                    (`title`,`description`,`uri`)
                    VALUES
                    ('$title_safe','$description_safe','$uri_safe');";
        }
        $mysqli->query($sql);
        if(!$this->id){
             $this->id = $mysqli->insert_id;
        }
        if($mysqli->error){
            echo $mysqli->error;
            exit;
        }
        
        return $this->id;
        
    }

}