<?php
/*

The script I shouldn't have to write

Takes a CSV file and generates a SQL Schema for it
so mysqlimport can be used to pull it in

https://dev.mysql.com/doc/refman/8.4/en/mysqlimport.html

tell it to ignore the first lines

mysqlimport -u root --ignore-lines=1 messing assessments.csv

*/

require_once('../config.php');

$file_path = @$argv[1];

if(!$file_path){
    echo "You need to pass a file path for me to work on. \n";
    exit;
}

$in = fopen($file_path, 'r');

$header = fgetcsv($in);

$fields = array();

$used_names = array();
foreach($header as $head){
    $name = preg_replace('/[^a-zA-Z0-9]/', '_', trim($head));

    // make sure the names are unique
    if(in_array($name, $used_names)) $name = $name . '_' . count($used_names);
    $used_names[] = $name;

    $fields[] = array(
        'name' => $name,
        'header' => $head,
        'length' => 0,
        'only_ints' => true,
        'only_floats' => true
    );
}

// lets work through the file and see what the values are like
$line_count = 0;
while($line = fgetcsv($in)){

    if(count($line) < count($fields)){
        echo "\n Line too short\n";
        print_r($fields);
        print_r($line);
        exit;
    }

    for($i = 0; $i < count($fields); $i++){
        $val = $line[$i];
        if(strlen(trim($val)) > $fields[$i]['length']) $fields[$i]['length'] = strlen(trim($val));
        if(!valid_int($val)) $fields[$i]['only_ints'] = false;
        if(!valid_float($val)) $fields[$i]['only_floats'] = false;
    }

    $line_count++;
}

// OK let's build some SQL

$table_name = basename($file_path, ".csv");

$table_name = preg_replace('/[^a-zA-Z0-9]/', '_', trim($table_name));


$sql = "DROP TABLE IF EXISTS `$table_name`;\n";
$mysqli->query($sql);

$sql = "CREATE TABLE `$table_name` (\n";

$sep ="\t";
$types = "";
foreach($fields as $field){
    $sql .=  "$sep`{$field['name']}` ";

    if($field['only_floats'] && !$field['only_ints']){
        $sql .=  "DOUBLE";
        $types .= "d";
    }elseif($field['only_ints']){
        $sql .=  'INT';
        $types .= "i";
    }elseif($field['length'] > 255 && $field['length'] < 65000){
        $sql .=  "TEXT";
        $types .= "s";
    }elseif($field['length'] > 65000){
        $sql .=  "BLOB";
        $types .= "b";
    }else{
        $sql .=  "VARCHAR({$field['length']})";
        $types .= "s";
    }

    $sep = ",\n\t";

}

$sql .=  "\n);\n";
$mysqli->query($sql);

echo $sql;

// now lets import the data
rewind($in);
$header = fgetcsv($in); // just to dump the header


// prepare the statement
$question_marks = implode(',', array_fill(0, count($fields), '?'));
$sql = "INSERT INTO `{$table_name}` VALUES ({$question_marks })";

$stmt = $mysqli->prepare($sql);

$insert_count =0;
echo "\nProgress :      ";  // 5 characters of padding at the end
while($line = fgetcsv($in)){

    $line = array_slice($line, 0, count($fields));

    $stmt->bind_param($types, ...$line);
    $stmt->execute();

    $insert_count++;

    echo "\033[5D";      // Move 5 characters backward
    $percent = round($insert_count/$line_count * 100, 0);
    echo str_pad($percent, 3, ' ', STR_PAD_LEFT) . " %"; 

}

echo "\nAll Done!\n";

function valid_int($number){
    $number = filter_var($number, FILTER_VALIDATE_INT);
    return ($number !== FALSE);
}

function valid_float($number){
    $number = filter_var($number, FILTER_VALIDATE_FLOAT);
    return ($number !== FALSE);
}


