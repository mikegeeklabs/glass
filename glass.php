<?php
//An attempt at clear easy to use PHP framework.
function main() {
    // main program control loop when called from web
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    #  ini_set('display_errors',1);
    #  ini_set('display_startup_errors',1);
    #  error_reporting(-1);

    if($_SERVER['HTTPS'] != 'on') { 
	$servername = $_SERVER['SERVER_NAME'] ; 
	header("Location: https://$servername/") ; 
	die ; #trying to make sure this is only used via HTTPS
    } ; 

    
    include_once ("glass-core.php");
    $db = glconnect();
    $script = $_SERVER['PHP_SELF'];        
    $fromip = $_SERVER['REMOTE_ADDR'];
    $script = $_SERVER['PHP_SELF'];
    $mode = dt($_REQUEST['mode']);
    $submode = dt($_REQUEST['submode']);
    $subsubmode = dt($_REQUEST['subsubmode']);
    $subsubsubmode = dt($_REQUEST['subsubsubmode']);
    $action = dt($_REQUEST['action']); # usually disposable but used sometimes
    if (isset($_SERVER['PHP_AUTH_USER']) or $mode == 'login') {
        list($login, $name, $level, $perms) = glauth();
    };
    if (isset($_SERVER['PHP_AUTH_USER']) and $mode == 'logout') {
        setcookie("a", "");
        setcookie("b", "");
        setcookie("c", "");
        setcookie("d", "");
        $_COOKIE['a'] = '';
        $_COOKIE['b'] = '';
        $_COOKIE['c'] = '';
        $_COOKIE['d'] = '';
        list($login, $name, $level, $perms) = glauth();
    };
print "<!DOCTYPE html>\n" ; 
print <<<EOF
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>GLASS</title>
  <meta name="description" content="A simple clear application framework">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="favicon.ico">
  <link rel="stylesheet" href="minimal.css">

</head>
<body>
EOF;
    //PUBLIC MENU - Note: I am not using a JS/CSS menu on purpose. This is light, fast and simple. Please add the CSS and Javascript bloat of your choice. 
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        print "<table><tr>"; 
        print "<td><a href='$script' class='button'>Home</A></td>";
        print "<td><a href='$script?mode=login' class='button'>Login</A></td>";
        $permcount = count($perms);
        $cookiecount = count($_COOKIE);
        print "<td>$login <span title='security level'>$level</span>/<span title='perms'>$permcount</span>/<span title='cookies'>$cookiecount</span> $fromip</td>";
        print "</tr></table>";
        include('welcome.html') ; 
    };
    //A menu structure. 
    if (isset($_SERVER['PHP_AUTH_USER']) and $level > 4 ) {
        print "<table><tr>";
        print "<td><a href='$script' class='button'>Home</A></td>";
        if (in_array('data', $perms)) {        
        print "<td><a href='$script?mode=tables' class='button'>Data</A></td>";
        } ; 
        if (in_array('reports', $perms)) {        
        print "<td><a href='$script?mode=reports' class='button'>Reports</A></td>";
        } ; 
        print "<td><a href='$script?mode=logout' class='button'>Logout</A></td>";
        $permcount = count($perms);
        $cookiecount = count($_COOKIE);
        print "<td>$login <span title='security level'>$level</span>/<span title='perms'>$permcount</span>/<span title='cookies'>$cookiecount</span> $fromip</td>";
        print "</td></table>";
    };
    //This is the real 'glass' application, a simple, database table editor. 
    //It needs the first column of every table to have a 'uniq' field. 
    //These all need perm control added. 
    if ($mode == 'tables' and $level > 4) {
        include_once("glass-tables.php");
        gltables();
    };
    if ($mode == 'manageuser' and $level > 5) { //reference application module 
        include_once("glass-manageuser.php");
        glassmanageuser();
    };
    //This is a little awkward, but makes it easier to to run the report engine isolated.
    //the report engine is usually embedded within a larger program or stands alone. 
    //This file (glass.php) can be modified to run it alone by simply removing the menu and 
    //header (html output) once logged in. 
    if (($mode == 'reports' or $mode == 'item' or $mode == 'run' or $mode == 'export' or $mode == 'search' ) and $level > 5 ) {
        include_once ("glass-reportengine.php");
        reports();
    };
};
main();
?>
