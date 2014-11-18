<?php
function glassdocsdownload() {
    global $portal, $theme, $login, $account, $mode, $submode, $subsubmode, $subsubsubmode, $action, $script, $db, $fromip, $seclevel, $browser, $lang;
    #  ini_set('display_errors',1);
    #  ini_set('display_startup_errors',1);
    #  error_reporting(-1);
    include_once ("glass.php");
    include ("settings.inc");
    $filename = detaintstripfilename2($_REQUEST['filename']);
    $invoice = detaintstripfilename2($_REQUEST['invoice']);
    $dir = detaintstripdir2($_REQUEST['dir']);
    if ($invoice != '') {
        if (is_dir($dir)) {
        } else {
            print "$dir/$invoice $filename not found";
            return;
        };
        if (empty($filename)) {
            print "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"1; URL=glass.php?mode=login\">";
            return;
        };
    };
    $fname = $filename;
    $filename = realpath("$dir/$filename");
    $resescaped = preg_replace('/\//', '\\\/', $reservoir);
    if (preg_match("/^$resescaped/", $filename)) { #In right directory tree. Basic sanity check.
        #    } elseif (preg_match("/printqueue/", $filename, $m)) {   #This can be hard set for other structures. Be careful. Please like to attack file downlaoders.
        
    } else {
        #print "<br>File: $dir/$fname  f: $filename = not allowed";
        print "Error: $filename  not found or not allowed" ; 
        return;
    };
    $file_extension = strtolower(substr(strrchr($filename, "."), 1));
    switch ($file_extension) {
        case "pdf":
            $ctype = "application/pdf";
        break;
        case "exe":
            $ctype = "application/octet-stream";
        break;
        case "zip":
            $ctype = "application/zip";
        break;
        case "doc":
            $ctype = "application/msword";
        break;
        case "xls":
            $ctype = "application/vnd.ms-excel";
        break;
        case "ppt":
            $ctype = "application/vnd.ms-powerpoint";
        break;
        case "gif":
            $ctype = "image/gif";
        break;
        case "png":
            $ctype = "image/png";
        break;
        case "jpe":
        case "jpeg":
        case "jpg":
            $ctype = "image/jpg";
        break;
        default:
            $ctype = "application/force-download";
    }
    if (!file_exists($filename)) {
        gllog('docs-dl', "$filename not found");
        die("Error - File not found");
    }
    gllog('docs-dl', "$filename download start");
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Content-Type: $ctype");
    header("Content-Disposition: attachment; filename=\"" . basename($fname) . "\";");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . @filesize($filename));
    #set_time_limit(20);
    ob_clean();
    flush();
    @readfile("$filename") or die("File not found.");
    exit;
};
function detaintstripfilename2($string) {
    $newstring = $string;
    $newstring = str_replace("'", "", $newstring);
    $newstring = str_replace("\\n", "", $newstring);
    $newstring = str_replace("\r", "", $newstring);
    $newstring = str_replace("\"", "", $newstring);
    $newstring = str_replace("\\", "-", $newstring);
    $newstring = str_replace("\$", "", $newstring);
    $newstring = str_replace("\/", "-", $newstring);
    $newstring = str_replace(" ", "-", $newstring);
    $newstring = str_replace("+", "", $newstring);
    $newstring = str_replace("%", "", $newstring);
    $newstring = str_replace("<", "", $newstring);
    $newstring = str_replace(">", "", $newstring);
    $newstring = str_replace("..", "", $newstring);
    return $newstring;
};
function detaintstripdir2($string) {
    $newstring = $string;
    $newstring = str_replace("'", "", $newstring);
    $newstring = str_replace("\\n", "", $newstring);
    $newstring = str_replace("\r", "", $newstring);
    $newstring = str_replace("\"", "", $newstring);
    $newstring = str_replace("\\", "-", $newstring);
    $newstring = str_replace("\$", "", $newstring);
    $newstring = str_replace(" ", "-", $newstring);
    $newstring = str_replace("+", "", $newstring);
    $newstring = str_replace("%", "", $newstring);
    $newstring = str_replace("<", "", $newstring);
    $newstring = str_replace(">", "", $newstring);
    $newstring = str_replace("..", "", $newstring);
    return $newstring;
};
glassdocsdownload();
?>
