<?php
header("Content-Type: application/json; charset=utf-8");
foreach($_POST as $key => $value){
 
	${$key} = $value;	// dynamic variable
}
$pass = md5($pass);
echo json_encode($pass);

?>