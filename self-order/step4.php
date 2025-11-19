<?php

require_once("database.php");
header("Content-Type: application/json; charset=utf-8");
//header("Content-Type: text/plain; charset=utf-8");



/***** receive date *****/
$tableNo = $_POST["tableNo"];
	$food = urldecode($_POST["food"]);			// e.g. 漢堡包,Pizza
	 $now = date("Y-m-d H:i:s");
	 
	 
	 
function multiSQL($str){		//break-down food array into single ones, then insert into database

	global $tableNo, $now;		 //calling outside variables from inside of function, MUST use "global" to refer it back to outside
	
	$arr = explode(",", $str);
		
	foreach($arr as $key => $value){
		
    	$sql .= "INSERT INTO newOrder VALUES('', '$tableNo', '$now', '$value', null);";
	}
		return $sql;
}
if($con -> multi_query(multiSQL($food))===TRUE){
	
			$res = 1;
			//$res = json_encode($_POST);
}else{
			$res = "錯誤: " .$sql . "<br>" . $con->error;
}
$con -> close();

echo $res;
		