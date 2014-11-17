<?php
#This is a script used for testing some functions. Not exactly a unit test. 
global $db, $lang ; 
include_once('glass-core.php') ; 
$db = glconnect() ; 
print "$DB is: " . print_r($db) . "\n" ; 
print "\n\n" ; 
print "gafm test: \n" ; 
list($login, $name) = gafm("select login,name from people limit 1") ; 
print "Name from People is: $name\n" ; 
print "gaafm: \n" ; 
print_r(gaafm("select login,name from people")) ; 
print "runsql test\n" ; 
#print_r(runsql("insert into people (login,name) values ('test','testy testor')")) ; 
print_r(gaaafm("select uniq,login,name,passwd from people")) ; 
glist(gaaafm("select uniq,login,name,passwd from people")) ; 
print gltable(gaaafm("select uniq,login,name,passwd from people"),array('uniq','passwd')) ; 
print dt(" This is a \"dirty\" string with ' in it and '0x000000' and \'     ") . "end\n" ; 
print dtless(" This is a \"dirty\" string with ' in it and '0x000000' and \'     ") . "end\n" ; 
print "\n" ; 
print dtamt(" 8887.0187 A ") . "\n" ; 
$lang = 'fr' ; 
print dtamt(" 8.887,0187 A ") . "\n" ; 
$lang = 'en' ; 
print dtamt(" 8,887.0187 A ") . "\n" ; 
print "\n" ; 
?>
