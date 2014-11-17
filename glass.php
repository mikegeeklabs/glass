<?php
//An attempt at clear easy to use PHP framework. 
function main() {
 // main program control loop when called from web
 global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms ; 
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
   print "<li><a href='$script?mode=reports'>Reports</A>" ; 
   print "<li><a href='$script?mode=logout'>Logout</A>" ; 
   print "</ul>" ;  
 } ; 
 
 # list($login,$name,$level,$perms) = glauth() ;  
 # print gltable(gaaafm("select uniq,login,name,passwd from people"),array('uniq','passwd')) ; 
 if($mode == 'reports') {
  include_once("glass-reportengine.php") ; 
  reports() ;   
 } ;
} ;



 
main() ; 
?>
