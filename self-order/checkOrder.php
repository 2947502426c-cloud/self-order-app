<?php
require_once("database.php");
header("Content-Type: application/json; charset=utf-8");
//header("Content-Type: text/plain; charset=utf-8");

/***** receive data *****/
$tableNo = $_GET["tableNo"];

$sql = "SELECT food FROM newOrder WHERE tableNo='$tableNo';";
$res = $con -> query($sql);
$arr = array();

if($res -> num_rows > 0){
	
		while($row = $res -> fetch_assoc()){
			
			$arr[] = $row["food"];
		}
		$res -> free_result();
}
$con -> close();
echo json_encode($arr);		// num_rows=0 will just return an empty array