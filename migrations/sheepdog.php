<?php
//run this script from the migrations directory

list($db, $dblogin, $dbpasswd, $database) = glconnect();
migrate($db, $dblogin, $dbpasswd, $database);

function migrate($db, $dblogin, $dbpasswd, $database) {

#desc migrations ;
#+-----------+-------------+------+-----+-------------------+----------------+
#| Field     | Type        | Null | Key | Default           | Extra          |
#+-----------+-------------+------+-----+-------------------+----------------+
#| uniq      | int(10)     | NO   | PRI | NULL              | auto_increment |
#| migration | date        | NO   |     | NULL              |                |
#| filename  | varchar(40) | NO   |     |                   |                |
#| lastmod   | timestamp   | NO   |     | CURRENT_TIMESTAMP |                |
#+-----------+-------------+------+-----+-------------------+----------------+

	$lastdate = gsfm($db,"select date_format(max(migration),'%Y%m%d') from migrations") ; 
	$maxdate = gsfm($db,"select max(substring(filename,1,8)) from migrations") ; 
	

	if ($h = opendir('.')) {
		print "Herding migration on $database database, using user $dblogin looking for maxdate: $maxdate \n";
		sleep(2) ; 
		$files = array();
		while(false !== ($file = readdir($h))) {
			$files[] = $file;
		}
		sort($files);
		foreach($files as $file) {
			if (preg_match("/sql$/", $file, $m)) {
			$f = preg_replace("/\-/", "", $file);
			# print "$f = $file\n" ; 
			list($date,$name) = preg_split("/\./", $f) ; 
			  if($date >= $maxdate) { 
			  print "Woof Woof, $file may need to join the herd\n" ; 
			  $c = gsfm($db,"select count(uniq) from migrations where filename = '$file'") ; 
				if($c > 0) {
				  print "$file is already part of the herd: $c times\n" ; 
				} else { 
				  print "Adding $file to the herd\n" ; 
				  runsql($db,"insert into migrations (migration,filename) values (now(),'$file')") ; 
				  system("mysql -u$dblogin -p$dbpasswd $database < $file");
				} ;  
			
			  } ; 
			
			}
		}
		closedir($h);
		print "Migration has finished!\n";
	}
}

function glconnect() {
	require_once('../settings.inc');
	$db = mysql_connect($dbhost, $dblogin, $dbpasswd);
	if (!$db) {
		print "Systm unavailable: please contact technical support\n";
		die;
	}
	mysql_select_db($database, $db);
	return array ($db, $dblogin, $dbpasswd, $database);
}

function gsfm($db,$query){
	$answer = '';
	$result = mysql_query($query, $db) or die("Query gsfm failed $query: " . mysql_error() . "\n");
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		while (list($key, $val) = each($row)) {
			$answer = $val;
		}
	}
	return $answer;
}

function gafm($db, $query) {
	$a = array();
	$result = mysql_query($query, $db) or die("Query gafm failed $query: " . mysql_error() . "\n");
	$i = 0;
	while ( $row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		while (list($key, $val) = each($row)) {
			$a[$i] = $val;
			$i++;
		}
	}
	return $a;
}

function runsql($db, $query) {
	#$query = mysql_real_escape_string($query);
	$result = mysql_query($query, $db) or die("Query failed: $query" . mysql_error() . "\n");
	return $query;
}
	
?>	
