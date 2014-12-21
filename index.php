<?php
#production level index.php script enforcing SSL. 
#custom applications should probably rename 'glass.php' 
if($_SERVER['HTTPS'] != 'on') { 
	$servername = $_SERVER['SERVER_NAME'] ; 
	$url = $_SERVER['REQUEST_URI'] ; 
	header("Location: https://$servername/$_SERVER[REQUEST_URI]") ; 
	die ; 
} else {
	header("Location: glass.php") ; 
} ; 
?>