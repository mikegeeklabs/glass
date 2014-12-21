<?php
function glconnect() {
 // database connector
 global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $csspath ; 
 //get credentials from settings.inc
  include('settings.inc') ; 
  $db = mysqli_connect("$dbserver", "$dblogin", "$dbpasswd", "$database");
  if ($db->connect_errno) { print "No SQL (" . $db->connect_errno . ") " . $db->connect_error; }
  #set timezone 
  
  #set language
  return $db ;  
} ;

function bbf($input) { 

return $input ; 
} ; 


function gafm($query) { 
  global $db;
  if (!$db || empty($db)) { $db = glconnect(); } ; 
  $a = array() ; //declare the array
  $result = $db->query($query) or die("gafm failed:<br>\n$query<br>\n" . $db->connect_errno . " : " . $db->connect_error . "<br>\n");
  $result->data_seek(0);
  $a = array_values($result->fetch_assoc()) ; 
  return $a;
};
function gsfm($query) { 
  global $db;
  if (!$db || empty($db)) { $db = glconnect(); } ; 
  $a = array() ; //declare the array
  $result = $db->query($query) or die("gafm failed:<br>\n$query<br>\n" . $db->connect_errno . " : " . $db->connect_error . "<br>\n");
  $result->data_seek(0);
  $a = array_values($result->fetch_assoc()) ; 
  return $a[0] ;
};

function gaafm($query) { 
  //Get Associative Array from MySQL
  global $db;
  if (!$db || empty($db)) { $db = glconnect(); } ; 
  $a = array() ; //declare the array
  $result = $db->query($query) or die("gaafm failed:<br>\n$query<br>\n" . $db->connect_errno . " : " . $db->connect_error . "<br>\n");
  $result->data_seek(0);
  $a = $result->fetch_assoc() ; 
  return $a;
};

function gaaafm($query) { 
  //Get Indexed Associative Array from MySQL
  global $db;
  if (!$db || empty($db)) { $db = glconnect(); } ; 
  $a = array() ; //declare the array
  $result = $db->query($query) or die("gaaafm failed:<br>\n$query<br>\n" . $db->connect_errno . " : " . $db->connect_error . "<br>\n");
  if(is_object($result)) {
  $result->data_seek(0);
  $i = 0 ; 
  while ($row = $result->fetch_assoc()) { $a[$i] = $row ; $i++ ; } ; 
   return $a;
  } else { 
   return array() ;  
  } ; 
};
function glafm($query) { 
  //Get List  Array from MySQL
  global $db;
  if (!$db || empty($db)) { $db = glconnect(); } ; 
  $a = array() ; //declare the array
  $result = $db->query($query) or die("gaaafm failed:<br>\n$query<br>\n" . $db->connect_errno . " : " . $db->connect_error . "<br>\n");
  if(is_object($result)) {
   $result->data_seek(0);
   $i = 0 ; 
   while ($row = mysqli_fetch_array($result,MYSQLI_NUM)) { array_push($a,$row[0]) ; } ; 
   return $a;
  } else { 
   return array() ;  
  } ; 
};

function runsql($query) { 
  global $db;
  if (!$db || empty($db)) { $db = glconnect(); } ; 
  $result = $db->query($query) or die("runsql failed:<br>\n$query<br>\n" . $db->connect_errno . " : " . $db->connect_error . "<br>\n");
  return $result ; 
} ; 
function glist($a) {
  foreach($a as $k=>$v) { 
    foreach($v as $key=>$val) {
      print "$key = $val \n" ; 
    } ; 
  } ;   
} ; 

function glisttable($a) {
  $stuff = "<table>\n" ;   
  foreach($a as $k=>$v) { 
    foreach($v as $key=>$val) {
      $stuff .= "<tr><td>$key</td><td>$val</td></tr>\n" ; 
    } ; 
  } ;   
  $stuff .= "</table>\n" ; 
  return $stuff ; 
} ; 

function glprintr($thing) { 
 print "<pre>" . print_r($thing,1) . "</pre>" ; 
} ; 

function gltable($a,$toton) {
  $header = '<table>' ; 
  $footer = '</table>' ; 
  $th = '' ; 
  $theader = '' ; 
  $tfooter = '' ; 
  $trow = '' ; 
  $totals = array() ; 
  foreach($a as $k=>$v) { 
    $trow .= '<tr>' ; 
    foreach($v as $key=>$val) {
      if(empty($theader)) {  $th .= "<th>$key</th>" ;  } ; //builder a table header ; 
      $trow .= '<td>' . $val . '</td>' ; 
      if (@in_array($key, $toton)) {
        $totals["$key"] += ($val * 1) ; 
      } ; 
      
    } ; 
    $trow .= '</tr>' . "\n" ; 
    if(empty($theader)) {  $theader = "<tr>$th</tr>\n"  ;   } ; //builder a table header ; 
  } ;   
  if(!empty($totals)) { //a total line
    foreach($a as $k=>$v) { 
      $tfooter = '<tr>' ; 
      foreach($v as $key=>$val) {
        if (@in_array($key, $toton)) {
          $tfooter .= "<td>$totals[$key]</td>" ; 
        } else { 
          $tfooter .= '<td></td>' ; 
        }  
      } ; 
      $tfooter .= '</tr>' ; 
    } ;   
  
  } ; 
  return "$header\n$theader\n$trow\n$tfooter\n$footer" ; 
} ; 
function dt($string) {
  global $lang ; 
   if (empty($string)) {
    } else {
        $string = trim($string) ; 
        $strip = array('/^ /','/\s+$/', '/\$/', '/\n/', '/\r/', '/\n/', '/\,/', '/\:/','/\@/','/\%/','/0x/');
        $string = preg_replace($strip, '', $string);
        $strip = array('/\'/');
        $string = preg_replace($strip, '&amp;', $string);
        $string = mysql_escape_string($string) ; 
        return $string ;
    };
} ; 
function dtemail($string) {
  global $lang ; 
   if (empty($string)) {
        return '';
    } else {
        $string = trim($string) ; 
        $strip = array('/^ /','/\s+$/', '/\$/', '/\n/', '/\r/', '/\n/', '/\,/', '/\:/','/\%/','/0x/');
        $string = preg_replace($strip, '', $string);
        $strip = array('/\'/');
        $string = preg_replace($strip, '&amp;', $string);
        $string = mysql_escape_string($string) ; 
        return $string ;
    };
} ; 

function dtless($string) {
  global $lang ; 
   if (empty($string)) {
        return '';
    } else {
        $string = trim($string) ; 
        $strip = array('/\'/');
        $string = preg_replace($strip, '&amp;', $string);
        $string = mysql_escape_string($string) ; 
        return $string ;
    };
} ; 
function dtamt($string) {
  global $lang ; 
    $string = trim($string) ; 
    if($lang == 'pt' or $lang == 'fr') {
      $string = preg_replace("/[.]/", "", $string);
      $string = preg_replace("/[,]/", ".", $string);
    }   
    $string = preg_replace("/[^0-9.]/", "", $string);
    return $string;
};
function dtnum($string) {
  global $lang ; 
    $string = preg_replace('/\D/', '', $string);
    return $string;
};

function glauth() {
    global $db, $lang, $script ; 
    //Enforces HTTP Auth, MUST be absolutely first thing output, BEFORE Header!
    $level = 0;
    $posslogin = dt($_SERVER['PHP_AUTH_USER']); #sets login here, or in fakeauth.
    $posspasswd = dt($_SERVER['PHP_AUTH_PW']);
    $level = 0 ; 
    $name = '' ; 
    $perms = array() ; 
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        header("WWW-Authenticate: Basic realm=\"Credentials Please (1)\"");
        header('HTTP/1.0 401 Unauthorized');
        print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=$script?\">";
        exit ; 
    } elseif (isset($_SERVER['PHP_AUTH_USER']) and empty($_COOKIE['a']) and $_REQUEST['mode'] == 'logout') {         
        header("WWW-Authenticate: Basic realm=\"Re-enter Credentials or click OK then Escape\"");
        header('HTTP/1.0 401 Unauthorized');
        print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0; URL=$script?\">";
        exit ; 
    } elseif (isset($_SERVER['PHP_AUTH_USER']) and empty($_COOKIE['a']) and $_REQUEST['mode'] == 'login') {         
        list($login,$name,$level,$passwd) = gafm("select login,name,level,passwd from users where login = '$posslogin' limit 1") ;  
        if(glpasswdverify($posspasswd,$passwd)) { 
        
        } else { 
           $login = '' ; 
           $name = '' ; 
           $level = 0 ; 
           $passwd = '' ; 
        } ;  
        
        if ($level > 0) {
            if(empty($_COOKIE['a'])) {
                $fromip = $_SERVER['REMOTE_ADDR'];
                $key = glakey();
                setcookie("a", gldesencrypt($fromip, $key)); #could be argued these need a secure and http flag set. 
                setcookie("b", gldesencrypt("$login", $key)); #but the cookies are really just noise and useful as flags. 
                setcookie("c", gldesencrypt("opensaysme", $key));
                setcookie("d", str_rot13($key));
             } ;             
        };
        if ($level < 1) {
            header("WWW-Authenticate: Basic realm=\"Credentials Please (2)\"");
            header('HTTP/1.0 401 Unauthorized');
            print bbf('Error 401') . ' b<hr>' . bbf('You must have a valid login and password to access this system');
            exit;
        };
        #now we have a chicken and egg problem
    } else {
        list($login,$name,$level,$passwd) = gafm("select login,name,level,passwd from users where login = '$posslogin' limit 1") ;  
        if(glpasswdverify($posspasswd,$passwd)) { 
        
        } else { 
           $login = '' ; 
           $name = '' ; 
           $level = 0 ; 
           $passwd = '' ; 
        } ;  

        if (empty($_COOKIE['a'])) {
            header("WWW-Authenticate: Basic realm=\"Credentials or click OK then Escape\"");
            header('HTTP/1.0 401 Unauthorized');
            print bbf('Error 401') . ' c<hr>' . bbf('You must have a valid login and password to access this system');
            print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"2; URL=$script?mode=welcome&submode=logout\">";
            die ;
        };
       # print "RE-AUTH" ; 
    } ; 
        if($level > 0) {
         $perms = glafm("select perm from userperms where login = '$login'") ; 
        } ; 
        return array($login,$name,$level,$perms);
    
}; //end function glsimpleauth

#apt-get install php5-mcrypt
function gldesencrypt($string, $key) {
    if (empty($key)) {
        die;
    };
    srand((double)microtime() *1000);
    $key = md5($key);
    $td = mcrypt_module_open('des', '', 'cfb', '');
    $key = substr($key, 0, mcrypt_enc_get_key_size($td));
    $iv_size = mcrypt_enc_get_iv_size($td);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    if (mcrypt_generic_init($td, $key, $iv) != -1) {
        $c_t = mcrypt_generic($td, $string);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $c_t = $iv . $c_t;
        return base64_encode($c_t);
    }
}
function gldesdecrypt($string, $key) {
    if (empty($key)) {
        die;
    };
    $string = base64_decode($string);
    $key = md5($key);
    $td = mcrypt_module_open('des', '', 'cfb', '');
    $key = substr($key, 0, mcrypt_enc_get_key_size($td));
    $iv_size = mcrypt_enc_get_iv_size($td);
    $iv = substr($string, 0, $iv_size);
    $string = substr($string, $iv_size);
    if (mcrypt_generic_init($td, $key, $iv) != -1) {
        $c_t = mdecrypt_generic($td, $string);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $c_t;
    }
}
function glakey() {
    #Generate a key used elsewhere. It is a password safe string. No ambiguous characters.
    $string = "aeiouBCDFHJKMNPQRTZ23456789QPZQB";
    srand(date("s"));
    $len = 20;
    while (strlen($str) <= $len) {
        $c = rand(1, 28);
        $str.= substr("$string", $c, 1);
    }
    return $str;
};

function nff($number) { 
    global $lang, $thousands, $decimals ; 
    $places = 2 ; 
    if($thousands != ',' or $decimals != '.') { 
     $number =  number_format($number, $places, "$decimals", "$thousands") ; 
    } else { 
     $number =  number_format($number, $places, '.', ',') ; 
    } ;   
    return $number ;
} ; 

function gllog($log, $what) {
    global $portal, $login, $fromip ; 
    $dir = @fopen("logs", "r");
    if (!($dir)) {
        mkdir("logs");
    };
    @fclose($dir);
    if ($log == '') {
        $log = 'log';
    };
    $now1 = date("Ymd");
    $now2 = date("Ymd H:i:s");
    $fileout = fopen("logs/$now1-$portal$login-$log.log", "a");
    fputs($fileout, "$now2    $login    $fromip    $what\n");
    fclose($fileout);
};

function glpasswdhash($input) {
#This should not be used on PHP < 5.5, use PHP's 'password_hash' instead. 
#This is for PHP 5.3
$salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
$salt = base64_encode($salt);
$salt = str_replace('+', '.', $salt);
$hash = crypt("$input", '$2y$10$'.$salt.'$');
return $hash;
} ; 

function glpasswdverify($input,$existinghash) {
  $hash = crypt($input, $existinghash);
  return $hash === $existinghash ; // returns true/1 if matches. 
} ; 

?>
