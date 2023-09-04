<?php


/**
 * 
 * Represents facets (tags) we use
 * 
 */
class Facet extends WfoFacets{

    private int $id;
    private string $name;
    private string $value;

    function __construct($id, $name, $value) {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
    }

    public static function getFacetById($id){

    }
    
    public static function getFacetByNameValue($name, $value, $value_description = null, $create = false){

        global $mysqli;

        $name_safe = $mysqli->real_escape_string($name);
        $value_safe = $mysqli->real_escape_string($value);
        $response = $mysqli->query("SELECT v.id, n.title as 'name', v.title as 'value' FROM facet_values as v JOIN facet_names as n on n.id = v.name_id WHERE n.title = '$name_safe' AND v.title = '$value_safe';");

        if($response->num_rows == 0){
            $response->close();
            if($create){
                
                // we should make one as it doesn't exist
                // does the facet name exist?
                $response = $mysqli->query("SELECT id FROM facet_names as n WHERE n.title = '$name_safe';");
                if($response->num_rows == 0){
                    $mysqli->query("INSERT INTO facet_names (title) VALUES ('$name_safe');");
                    $name_id = $mysqli->insert_id;
                }else{
                    $rows = $response->fetch_all(MYSQLI_ASSOC);
                    $name_id = $rows[0]['id'];
                }
                $response->close();

                // we have a name id let us create a value
                $description_safe = $mysqli->real_escape_string($value_description);
                $mysqli->query("INSERT INTO facet_values (`name_id`, `title`, `description`) VALUES ($name_id, '$value_safe', '$description_safe');");
                if($mysqli->error){
                    echo $mysqli->error;
                    exit;
                }
                $value_id = $mysqli->insert_id;

                return new Facet($value_id, $name, $value);

            }else{
                return null;
            }
        }else{
            $rows = $response->fetch_all(MYSQLI_ASSOC);
            return new Facet($rows[0]['id'],$rows[0]['name'],$rows[0]['value']);
        }

    }




    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of value
     */ 
    public function getValue()
    {
        return $this->value;
    }
}