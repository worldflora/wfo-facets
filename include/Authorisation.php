<?php

 /**
 * A wrapper around the name cache table that
 */
class Authorisation{

    public static function canEditSourceData($source_id){
        global $mysqli;

        $user = @$_SESSION['user'] ? $_SESSION['user'] : null;

        // is the user logged in?
        if($user){

            if($user['role'] == 'god'){
                return true;
            }else{

                // do they have permission to edit this one 
                $response = $mysqli->query("SELECT * FROM user_sources WHERE user_id = {$user['id']} AND source_id = $source_id");
                $rows = $response->fetch_all(MYSQLI_ASSOC);
                $response->close();

                if(count($rows) > 0){
                    return true;
                }

            }
        }

        return false;

    }

    public static function isGod(){
         $user = @$_SESSION['user'] ? $_SESSION['user'] : null;
         if($user && $user['role'] == 'god'){
                return true;
         }else{
            return false;
         }
    }

}