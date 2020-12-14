<!DOCTYPE html>
<html lang="en">
<head>
    <title>Example of Looping Over PHP Multidimensional Array</title>
</head>
<body>

<?php
// Multidimensional array
$superheroes = Array ( 
    [0] => Array ( 
        [id] => 7 
        [0] => 7 
        [name] => Services Department 
        [1] => Services Department 
        [parent_location_id] => 12 
        [2] => 12 ) 
    [1] => Array ( 
        [id] => 13 
        [0] => 13 
        [name] => Electronic Engineering Department 
        [1] => Electronic Engineering Department 
        [parent_location_id] => 12 
        [2] => 12 ) 
);
// foreach($superheroes as $n => $val) {
//     echo "$n = $val<br>";
//   }
// Printing all the keys and values one by one
$keys = array_keys($superheroes);

for($i = 0; $i < count($superheroes); $i++) {
    foreach($superheroes[$keys[$i]] as $key => $value) {
        echo  $value . "<br>";
    }
}
?>

</body>
</html>