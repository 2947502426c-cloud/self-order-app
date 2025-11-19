<?php

require_once("database.php");



$complete = $_GET["fc"];
$now = date("Y-m-d H:i:s");

if(isset($complete)){
	
			$sql = "UPDATE newOrder SET completeTime = '$now' WHERE id = $complete;";
			if($con -> multi_query($sql)===TRUE){
				
						echo 1;
			}else{
						echo 0;
			}
			exit;
}else{
			$sql = "SELECT id, tableNo, orderTime, food FROM `newOrder` WHERE completeTime IS NULL ORDER BY orderTime ASC;";
			if($result = $con -> query($sql)){
						$num = $result -> num_rows;
						if($num > 0){
								while($row = $result -> fetch_assoc()){
									foreach($row as $key => $value){
										${$key} = $value;
									}
									$mins = floor((time() - strtotime($orderTime)) / 60);
									$trtd .= "<tr id='f$id'>
												<td onclick='toggleList(this)'>$tableNo</td>
												<td onclick='toggleList(this)' class='mins'>$mins</td>
												<td onclick='toggleList(this)'>$food</td>
												<td class='p-0'><button class='btn rounded-0 btn-danger w-100 py-3' onclick='foodComplete($id)'>完成
												</button</td>
											</tr>";
								}
						}else{
								$trtd = "沒有食物輪侯";
						}
			}else{
						echo "錯誤, 請重試";
			}
}
$con -> close();
?>
<!IDOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0" />
<link href="http://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLlm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<title>廚房</title>
<link rel="icon" href="icon.png">

<style>

tr{
	
	height: 4rem;
}


.mins::after{
	content:" 分鐘";
}
</style>
</head>
<body onload="refresh()">
	<!--<div id="loader" class="fixed-top vw-100 vh-100 d-flex align-ites-center justify-content-center bg-info">-->
	<!--	<strong>Loading...</strong>-->
	<!--	<div class="spinner-border spinner-border-sm ms-1" role="statue" aria-hidden="true"></div>-->
	<nav class="navbar navbar-light bg-primary sticky-top">
		<div class="container-fluid justify-content-around">
			<a href="#" class="navbar-brand p-0">
				<img src="icon.png" alt="" width="44" height="44" class="d-inline-block">
			</a>
			
			
			<span class="fs-3">
				數量:<span id="total"><?php echo $num; ?></span>
			</span>
			
			<button class="btn btn-light p-3 fs-4" onclick="showAll()">
				顯示全部
			</button>
		</div>
	</nav>
	
	<table class="table table-striped align-middle">
		<thead>
			<th width="15%">枱號</th>
			<th width="30%">點餐時間</th>
			<th width="35%">食物</th>
			<th width="20%"></th>
		</thead>
		<?php echo $trtd; ?>
	</table>
<script src="js/jquery-3.6.0.js"></script>
<script src="http://cdn.jsdelivr.net/npm/bootstrap@5.0.2/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFIzcLA8NI+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtlaxVXM" crossorigin="anonymous"></script>
<script>
	const total = $("#total");
	const loadSec = 60000;		//1 minuye
	let autoLoad;
	
	
	
	function foodComplete(id){
		
		let url = `step5.php?fc=${id}`;
		
		$.get(url, res => {
			
			if(res==1){
				
				alert("完成");
				updateList(id);
			}
		});
	}
	function updateList(id){
		
		let num = parselnt(total.text()) - 1;

		if(num==0){
			
				showAll();
		}else{
				total.text(num);
				$(`#f${id}`).remove();
		}
	}
	
	function showAll(){
		location.reload(true);
	}
	
	function toggleList(el){
		
		let num = 0;
		let idx = el.celllndex + 1;
		let food = el.innerText;
		
		[...$("td:nth-of-type("+ idx + ")")].forEach(v => {
			
			
			if(v.innerText == food){
						num += 1;
						$(v).parent().show();
			}else{
						$(v).parent().hide();
			}
		});
		
		total.text(num);
	}
	
	function redresh(){
		console.log("reset");
		
		
		clearTimeout(autoLoad);
		
		autoLoad = setTimeout(function(){
			showAll();
		}, loadSec);
	}
	$(document).on("click", function(){
		refresh();
	});
</script>
</body>
</html>
