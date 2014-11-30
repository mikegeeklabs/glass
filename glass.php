<?php
//An attempt at clear easy to use PHP framework. 
function main() {
 // main program control loop when called from web
 global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath ; 

#  ini_set('display_errors',1);
#  ini_set('display_startup_errors',1);
#  error_reporting(-1);


 include_once("glass-core.php") ; 
 $db = glconnect() ; 
 $fromip = $_SERVER['REMOTE_ADDR'];
 $script = $_SERVER['PHP_SELF'];
 $mode = dt($_REQUEST['mode']) ; 
 $submode = dt($_REQUEST['submode']) ; 
 $subsubmode = dt($_REQUEST['subsubmode']) ; 
 $subsubsubmode = dt($_REQUEST['subsubsubmode']) ; 
 $action = dt($_REQUEST['action']) ; # usually disposable but used sometimes

 if (isset($_SERVER['PHP_AUTH_USER']) or $mode == 'login') {
   list($login,$name,$level,$perms) = glauth() ;  
 } ; 
 if (isset($_SERVER['PHP_AUTH_USER']) and $mode == 'logout') {
   setcookie("a", "");
   setcookie("b", "");
   setcookie("c", "");
   setcookie("d", "");
   $_COOKIE['a'] = '' ; 
   $_COOKIE['b'] = '' ; 
   $_COOKIE['c'] = '' ; 
   $_COOKIE['d'] = '' ; 
   list($login,$name,$level,$perms) = glauth() ;  
 } ; 
  
print <<<EOF
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
  <meta charset="utf-8">
  <title>GLASS</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="favicon.png">
  <link rel="apple-touch-icon" sizes="57x57" href="/images/apple-touch-icon.png">
  <link rel="apple-touch-icon" sizes="72x72" href="/images/apple-touch-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="114x114" href="/images/apple-touch-icon-114x114.png">
  <link rel="stylesheet" href="minimal.css">
</head>
<body>
EOF;



 
#PUBLIC MENU 
 if (!isset($_SERVER['PHP_AUTH_USER'])) {
   print "<ul>" ; 
   print "<li><a href='$script'>Home</A>" ; 
   print "<li><a href='$script?mode=login'>Login</A>" ; 
   print "</ul>" ;  
 } ; 

#INTERNAL MENU - BASICS PLUS PERM TABLE BASED!
#This is completely bogus security for now. 
 if (isset($_SERVER['PHP_AUTH_USER'])) {
   print "<ul>" ; 
   print "<li><a href='$script'>Home</A>" ; 
   print "<li><a href='$script?mode=tables'>Data</A>" ; 
   print "<li><a href='$script?mode=reports'>Reports</A>" ; 
   print "<li><a href='$script?mode=logout'>Logout</A>" ; 
   print "</ul>" ;  
 } ; 
 
 # list($login,$name,$level,$perms) = glauth() ;  
 # print gltable(gaaafm("select uniq,login,name,passwd from people"),array('uniq','passwd')) ; 

 if($mode == 'tables') {
  include_once("glass-tables.php") ; 
  gltables() ;   
 } ;


 #This is a little awkward, but makes it easier to to run the report engine isolated. 
 if($mode == 'reports' or $mode == 'item' or $mode == 'run' or $mode == 'export') {
  include_once("glass-reportengine.php") ; 
  reports() ;   
 } ;
} ;



 
main() ; 
?>
