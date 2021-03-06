<?php
function reports() {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath, $item, $itemid, $fromdate, $todate;
    //GLASS-WARNING: This module is undergoing rapid re-write and transformation!
    #   ini_set('display_errors',1);
    #  ini_set('display_startup_errors',1);
    #   error_reporting(-1);
    include_once ('glass-core.php'); #redundant, but this can be initiated by CLI or other means.
    $db = glconnect();
    $seclevel = $level; # The report module/engine uses 'seclevel'
    if ($level < 5) {
        print "Not for you";
        return;
    }
    $portal = 'glass'; #for use with other systems I run. A stub.
    $role = 'dev'; #for use with other systems I run.
    $mode = dt($_REQUEST['mode']);
    $submode = dt($_REQUEST['submode']);
    $subsubmode = dt($_REQUEST['subsubmode']);
    $action = dt($_REQUEST['action']);
    $lang = 'en'; # for now. hard coded
    $item = dt($_REQUEST['item']);
    $itemid = dt($_REQUEST['itemid']);
    $fromdate = dt($_REQUEST['fromdate']);
    $todate = dt($_REQUEST['todate']);
    if ($mode == '' or $mode == 'menu' or $mode == 'search' or $mode == 'reports') {
        reportheader();
        reporttopmenu();
        reportitemmenu();
        reportfooter();
    };
    if ($mode == 'item') {
        reportheader();
        reporttopmenu();
        reportitem();
        reportfooter();
    };
    if ($mode == 'export') {
        reportexport();
    };
    if ($mode == 'run') {
        reportheader();
        reporttopmenu();
        reportitem();
        if (empty($_REQUEST['offline'])) {
            #----------------------============================------------------Danger Will Robinson, Danger!
            $load = sys_getloadavg();
            if ($load[0] > 15) {
                print "<div style='background:#ffFF88;font-size:x-large;'>&nbsp;&nbsp;<b>" . $load[0] . "</b>/<i>" . $load[1] . "</i>&nbsp;&nbsp;" . bbf('Extremely High System Load, please try again in 5 minutes') . "</div>";
                return;
            }
            if ($dirty) { // set  $dirty = true in settings.inc if you want this. Useful.
                runsql("SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
            };
            #----------------------============================-----------------------------------------------
            reportrun();
        } else {
            reportschedule();
        };
        reportfooter();
    };
    if ($mode == 'reservoir') {
        reportheader();
        reporttopmenu();
        reservoir($portal, $login);
        reportfooter();
    };
    if ($mode == 'reportqrun') {
        reportqrun();
    };
};
function reportqrun() {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    include "settings.inc"; # get basic config variables - Note: Server configured to not deliver .inc files
    include_once ('glass-core.php');
    $db = glconnect();
    $level = 90;
    $query = "SELECT uniq,portal,login,itemid,request,completedatetime from reportq where completedatetime < '2001-01-01'";
    $result2 = mysqli_query($db, $query) or die("Query failed : " . mysqli_error($db));
    while (list($funiq, $portal, $login, $itemid, $request) = mysqli_fetch_row($result2)) {
        print "Running: $funiq $portal $login $itemid <br>$request";
        $_REQUEST = json_decode($request, true);
        print "<pre>" . print_r($_REQUEST, 1) . "</pre>";
        $item = dt($_REQUEST['item']);
        $itemid = dt($_REQUEST['itemid']);
        $fromdate = dt($_REQUEST['fromdate']);
        $todate = dt($_REQUEST['todate']);
        $email = dtnumber($_REQUEST['email']);
        $_REQUEST['QRUN'] = 'true';
        $rfile = reportrun();
        runsql("update reportq set completedatetime = now() where uniq = '$funiq'");
        if (!empty($email) and $_REQUEST['notify'] == 'true') {
            if (@fopen("glass-sendemail.php", "r")) { #site specific file
                include_once ("glass-sendemail.php");
                list($itemname) = gafm("select itemname from reports where itemid = '$itemid'");
                if (empty($itemname)) {
                    list($itemname) = gafm("select itemname from localreports where itemid = '$itemid'");
                };
                $message = "$baseurl" . "glass.php?mode=reservoir";
                $message.= "\n\n";
                $message.= "$rfile.csv\n";
                $message.= "$rfile.html\n";
                $message.= "$rfile.xlsx\n";
                $message.= "\n\n";
                $message.= bbf("This report has been created and is waiting for you in your Glass Report Reservoir folder");
                $message.= "\n\n";
                $message.= bbf("Note: This report storage is temporary, 3 days maximum. Please move this report to permanent storage if required.");
                $message.= "\n\n";
                sendemail("$email", '[' . bbf('Report Notice') . ': ' . bbf("$itemname") . "  " . date('Ymd') . "]", "$message ");
            };
        };
        if (!empty($email) and $_REQUEST['sendmail'] == 'true') {
            if (@fopen("glass-sendemail.php", "r")) { #site specific file
                include_once ("glass-sendemail.php");
                list($itemname) = gafm("select itemname from reports where itemid = '$itemid'");
                if (empty($itemname)) {
                    list($itemname) = gafm("select itemname from localreports where itemid = '$itemid'");
                };
                $dir = reservoircheckdirs($portal, $portal, $login);
                if (@fopen("$dir/$rfile.csv", "r")) {
                    $message = "\n\n";
                    $message.= "$rfile.csv\n\n";
                    sendemailfile("$email", bbf("Glass Report") . " $itemname $rfile.csv", "$message", "$dir/$rfile.csv", "$rfile.csv", 'CSV');
                };
                if (@fopen("$dir/$rfile.xlsx", "r")) {
                    $message = "\n\n";
                    $message.= "$rfile.xlsx\n\n";
                    sendemailfile("$email", bbf("Glass Report") . " $itemname $rfile.xlsx", "$message", "$dir/$rfile.xlsx", "$rfile.xlsx", 'XLS');
                };
            };
        };
    };
};
function reportschedule() {
    #    global $portal, $login, $account, $mode, $submode, $subsubmode, $subsubsubmode, $action, $script, $db, $fromip, $level, $lang, $role, $perms, $item, $itemid, $fromdate, $todate, $vendornumber ;
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    print "$portal &rarr; $login <pre>";
    $REQUEST = json_encode($_REQUEST);
    $REQUEST = mysqli_escape_string($REQUEST);
    $_REQUEST['itemid'] = mysqli_escape_string($_REQUEST['itemid']);
    list($pending) = gafm("select count(uniq) from reportq where login = '$login' and completedatetime < '2001-01-01'");
    if ($pending < 5) {
        $q = "insert into reportq (portal,login,itemid,requestdatetime,request) values ('$portal','$login','$_REQUEST[itemid]',now(),'$REQUEST')";
        runsql("$q");
    } else {
        print "<h2>$pending " . bbf('Item Pending Limit') . ": ";
        print bbf('Not Added');
        print "</h2>";
    };
    include_once ("glass-core.php");
    if ($login == 'admin') {
        print "<div>Admin Debug</div>";
        print quickshowr("select itemid,requestdatetime as Requested,completedatetime as Completed,request from reportq where portal = '$portal' and login = '$login' and completedatetime < '2001-01-01'", 'nada', 'nada', bbf('Pending Items'), 9, '500px');
    };
    print quickshowr("select q.itemid as Item,report.itemname as Description ,q.requestdatetime as Requested from reportq q left join reports on (report.itemid = q.itemid) where q.portal = '$portal' and q.login = '$login' and q.completedatetime < '2001-01-01'", 'nada', 'nada', "<a href='glass.php?mode=reservoir'>open folder</i></a>&nbsp;&nbsp;" . bbf('Pending Items'), 9, '500px');
};
function reportrun() {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath, $item, $itemid, $fromdate, $todate, $thousands, $decimals;
    # should recheck access control ;
    #    $db = glconnect2() ; #Will select a slave system first using other credentials
    $db = glconnect(); #Will select a slave system first
    $time_start = microtime(true);
    include "settings.inc"; # get basic config variables - Note: Server should be configured to not deliver .inc files
    $query = "select * from reports where seclevel <= '$level' and (uniq = '$item' or itemid = '$itemid')  order by uniq limit 1";
    $results = gaafm("$query");
    $r = 0;
    foreach($results as $key => $val) {
        $s = "\$$key = \"$val\" ; ";
        eval($s);
        $r++;
    }; #messy way to set all column names to variables containing the data.
    if ($r < 1) {
        $query = "select * from localreports where seclevel <= '$level' and (uniq = '$item' or itemid = '$itemid')  order by uniq limit 1";
        $results = gaafm("$query");
        $r = 0;
        foreach($results as $key => $val) {
            $s = "\$$key = \"$val\" ; ";
            eval($s);
            $r++;
        }; #messy way to set all column names to variables containing the data.
        
    };
    if ($r < 1) {
        print "<div>#$item $itemid $level " . bbf("Report not found") . "\n</div>";
        return;
    };
    #LOOP START HERE!!!!!
    $y = 1;
    $loop = 1;
    $loops = 1;
    if (!empty($query2)) {
        $loops = 2;
    };
    while ($loop <= $loops) {
        if ($loop == 2) {
            $query = $query2;
            unset($tot);
            unset($keys);
            list($asnumbers, $totals) = gafm("select asnumbers,totals from reports where uniq = '$item' or itemid = '$itemid'");
            # $totals = '' ;
            $y = 1;
            $THEAD = '';
            $TFOOT = '';
            $TLINE = '';
            $TBODY = '';
            $CSVLINE.= "\n\n";
        };
        $loop++;
        #An attempt at normallizing the query
        $replace = array('/&#39;/', '/&apos;/');
        $query = preg_replace($replace, '\'', $query);
        if (strlen($todate) < 11) {
            $todate.= ' 23:59:59';
        };
        if (strlen($fromdate) < 11) {
            $fromdate.= ' 00:00:00';
        };
        $query = preg_replace('/fromdate/', "$fromdate", $query);
        $query = preg_replace('/todate/', "$todate", $query);
        #Not elegant, but easy to do/read
        $input1 = reportdetaintstrip($_REQUEST['input1']);
        $input2 = reportdetaintstrip($_REQUEST['input2']);
        $input3 = reportdetaintstrip($_REQUEST['input3']);
        $input4 = reportdetaintstrip($_REQUEST['input4']);
        $input5 = reportdetaintstrip($_REQUEST['input5']);
        $input6 = reportdetaintstrip($_REQUEST['input6']);
        $input6 = reportdetaintstrip($_REQUEST['input7']);
        $input8  = reportdetaintstrip($_REQUEST['input8']);
        $input9 = reportdetaintstrip($_REQUEST['input9']);
        if (preg_match("/\%/", $input1, $matches)) {
            $replace = array('/= \'input1\'/', '/\>= \'input1\'/', '/\<= \'input1\'/');
            $query = preg_replace($replace, "like 'input1'", $query);
        }
        if (preg_match("/\%/", $input2, $matches)) {
            $replace = array('/= \'input2\'/', '/\>= \'input2\'/', '/\<= \'input2\'/');
            $query = preg_replace($replace, "like 'input2'", $query);
        }
        if (preg_match("/\%/", $input3, $matches)) {
            $replace = array('/= \'input3\'/', '/\>= \'input3\'/', '/\<= \'input3\'/');
            $query = preg_replace($replace, "like 'input3'", $query);
        }
        if (preg_match("/\%/", $input4, $matches)) {
            $replace = array('/= \'input4\'/', '/\>= \'input4\'/', '/\<= \'input4\'/');
            $query = preg_replace($replace, "like 'input4'", $query);
        }
        if (preg_match("/\%/", $input5, $matches)) {
            $replace = array('/= \'input5\'/', '/\>= \'input5\'/', '/\<= \'input5\'/');
            $query = preg_replace($replace, "like 'input5'", $query);
        }
        if (preg_match("/\%/", $input6, $matches)) {
            $replace = array('/= \'input6\'/', '/\>= \'input6\'/', '/\<= \'input6\'/');
            $query = preg_replace($replace, "like 'input6'", $query);
        }
        if (preg_match("/\%/", $input7, $matches)) {
            $replace = array('/= \'input7\'/', '/\>= \'input7\'/', '/\<= \'input7\'/');
            $query = preg_replace($replace, "like 'input7'", $query);
        }
        if (preg_match("/\%/", $input8, $matches)) {
            $replace = array('/= \'input8\'/', '/\>= \'input8\'/', '/\<= \'input8\'/');
            $query = preg_replace($replace, "like 'input8'", $query);
        }
        if (preg_match("/\%/", $input9, $matches)) {
            $replace = array('/= \'input9\'/', '/\>= \'input9\'/', '/\<= \'input9\'/');
            $query = preg_replace($replace, "like 'input9'", $query);
        }
        $query = preg_replace('/input1/', "$input1", $query);
        $query = preg_replace('/input2/', "$input2", $query);
        $query = preg_replace('/input3/', "$input3", $query);
        $query = preg_replace('/input4/', "$input4", $query);
        $query = preg_replace('/input5/', "$input5", $query);
        $query = preg_replace('/input6/', "$input6", $query);
        $query = preg_replace('/input7/', "$input7", $query);
        $query = preg_replace('/input8/', "$input8", $query);
        $query = preg_replace('/input9/', "$input9", $query);
        if (preg_match("/\;/", $query, $matches)) {
            $qs = split("[\;]", $query);
            foreach($qs as $qqq) {
                #print "$qqq <p>" ;
                if (strlen($qqq) > 5) {
                    $result1 = mysqli_query($db, $qqq) or die("Query[] failed:  $qqq : <p>" . mysqli_error());
                    $lastquery = $qqq;
                };
            };
        } else {
            $result1 = mysqli_query($db, $query) or die("Query 2 failed : $query <p>" . mysqli_error());
            $lastquery = $query;
        };
        #COMMON FOR EXPORT
        $dir = reservoircheckdirs($portal, $portal, $login);
        $now2 = date('Ymd-Hi');
        $tz = date('T');
        $itemnamestripped = detaintstripfilename2($itemname);
        #XLS
        if (preg_match("/XLS/", $outputformats, $matches)) {
            $XLS = true;
        } else {
            $XLS = false;
        };
        if ($XLS) {
            $now3 = date('Ymd');
            if ($loop == '2') {
                $writer = new XLSXWriter();
                $filename = "$itemnamestripped-$now3-$counter" . ".xlsx";
                $sheet = "$uniq-A";
            };
            if ($loop == '3') {
                $sheet = "$uniq-B";
            };
        };
        if (strlen($fromdate) > 10) {
            $xlssel.= ' ' . bbf('From') . ': ' . $fromdate;
            $xlssel.= ' ' . bbf('To') . ': ' . $todate . ' ' . $tz;
        };
        if ($input1 != '%' and $input1 != '') {
            $xlssel.= "  $input1field: $input1";
        };
        if ($input2 != '%' and $input2 != '') {
            $xlssel.= "  $input2field: $input2";
        };
        if ($input3 != '%' and $input3 != '') {
            $xlssel.= "  $input3field: $input3";
        };
        if ($input4 != '%' and $input4 != '') {
            $xlssel.= "  $input4field: $input4";
        };
        if ($input5 != '%' and $input5 != '') {
            $xlssel.= "  $input5field: $input5";
        };
        if ($input6 != '%' and $input6 != '') {
            $xlssel.= "  $input6field: $input6";
        };
        if ($XLS) {
            $writer->writeSheetRowBold("$sheet", array("$itemname", '', '', '', '', '', '', '', '', '', ''));
            $writer->writeSheetRow("$sheet", array("$itemdesc", '', '', '', '', '', '', '', ''));
            $writer->writeSheetRow("$sheet", array("", '', '', '', '', '', '', '', ''));
            $writer->writeSheetRow("$sheet", array("$filename", '', '', '', '', '', '', '', ''));
            $writer->writeSheetRow("$sheet", array(bbf('Created') . ':', "$now3", bbf('By') . ':', "$login"));
            $writer->writeSheetRow("$sheet", array(bbf('Selection') . ':', "$xlssel"));
            $xlsrow = 4;
        };
        #XLS END
        $asnumbers = preg_split('/[\,|]/', $asnumbers);
        $totalss = preg_split('/[\,|]/', $totals);
        $groupon = preg_split('/[\,|]/', $groupon);
        $groupchart = preg_split('/[\,|]/', $groupchart);
        $totalchart = preg_split('/[\,|]/', $totalchart);
        $mapchart = preg_split('/[\,|]/', $mapchart);
        $c = 1;
        $cc = 0;
        while ($row = mysqli_fetch_assoc($result1)) {
            $x = 1;
            $TLINE = "<tr><td>$y</td>";
            $defwidth = '100';
            $break = false;
            foreach($groupon as $g) {
                if ($row[$g] != $last[$g] and $y > 1) {
                    $break = true;
                    if (sizeof($groupchart) > 0) {
                        $chart = true;
                    };
                    $cc++;
                };
            };
            if ($break) {
                $subtotalline = "\n<tr style='height:50px;'><td></td>";
                $t = 0;
                while ($t < count($keys)) {
                    if (@in_array($keys[$t], $totals)) {
                        $f = $keys[$t];
                        $subtotalline.= "<td style='text-align:right;font-weight:bold;border: 1px solid #666666;border-width:1px 0px 0px 0px;color:#333333;'>" . nff($subtot[$f]) . "</td>";
                        $subtot[$f] = 0;
                    } else {
                        $subtotalline.= "<td style='text-align:right;font-weight:bold;border: 1px solid #666666;border-width:1px 0px 0px 0px;'>&nbsp;</td>";
                    };
                    $t++;
                };
                $subtotalline.= "</tr>";
                $TBODY.= $subtotalline;
                $break = false;
            };
            if ($chart and sizeof($a) > 3) {
                $subtotalline = "\n<tr><td colspan=20></td><td>";
                include_once ("report-flot.php");
                $subtotalline.= "\n\n<div style='position:relative;top:-300px;'><div style='position:absolute;right:100px;display:block;'>";
                $subtotalline.= reportflot(500, 200, $a, "$sn", $groupchart[0], '');
                $subtotalline.= "</div></div>\n\n";
                $subtotalline.= "</td></tr>";
                $TBODY.= $subtotalline;
                $chart = false;
                $a = '';
            };
            if ($chart and sizeof($a) < 3) {
                $a = '';
            };
            $xlscol = 0;
            while (list($key, $val) = each($row)) {
                $last[$key] = $val;
                if (@in_array($key, $totals)) {
                    $tot[$key]+= $val;
                };
                if (in_array($key, $totals)) {
                    $subtot[$key]+= $val;
                };
                #------------------
                if ($key == 'timestamp' or $key == 'day' or $key == 'billingtimestamp' or $key == 'Date') {
                    $timestamp = $val;
                };
                if ($key == 'label') {
                    $label = $val;
                };
                if ($key == 'serialnumber') {
                    $sn = $val;
                };
                if (in_array($key, $groupchart)) {
                    if (!empty($timestamp)) {
                        $a[$c] = array('timestamp' => strtotime($timestamp), "$key" => $val);
                    } else {
                        $a[$c] = array('label' => "$label", 'units' => $val);
                    };
                };
                if (in_array($key, $totalchart)) {
                    if (empty($sn)) {
                        $sn = "$key";
                    };
                    if (!empty($timestamp)) {
                        $aa[$c] = array('timestamp' => strtotime($timestamp), "$sn" => $val);
                    } else {
                        $aa[$c] = array('label' => "$label", "$sn" => $val);
                    };
                };
                if (in_array($key, $mapchart)) {
                    #MAPCHART
                    list($loclat, $loclong) = gafm("select loclat,loclong from locations where locid = '$locid'");
                    if (!empty($loclat)) {
                        $chartme.= "    gldata['$locid'] = { center: new google.maps.LatLng($loclat, $loclong), values: $val , title: '$locid'};\n";
                        if (empty($latc)) {
                            $latc = $loclat;
                            $lonc = $loclong;
                        };
                        if ($val > $maxvalue) {
                            $maxvalue = $val;
                            $chartfactor = 50/$maxvalue;
                        };
                        $cm++;
                    };
                };
                $c++;
                #-------------------
                if (@in_array($key, $asnumbers)) {
                    $dval = nff($val);
                    $align = 'text-align:right;font-weight:bold;';
                } else {
                    $dval = $val;
                    $align = '';
                };
                #TRYING TO KEEP THIS SANE!
                if (strtolower($key) == 'account' and $level > 40) { #keeping this as an example for future customizations
                    $dval = "<a href='glass.php?mode=login&account=$val' target='_NEW'>$dval</a>";
                    $acctz = $val;
                };
                if ($y < 2) {
                    $bbfkey = bbf($key);
                    $THEAD.= "<td id='hd$x' style='width:$defwidth" . "px;$align'> $key </td>";
                    $CSVHEAD.= "\"$key\",";
                    $keys[] = $key; # used outside of the while loop.
                    if ($XLS) {
                        $xlscolheader[$xlscol] = "$bbfkey";
                    };
                };
                if ($y < 2) {
                    $TLINE.= "<td id='col$x' style='width:$defwidth" . "px;$align'>$dval</td>";
                } else {
                    $TLINE.= "<td style='$align'>$dval</td>";
                };
                $CSVLINE.= "\"$val\",";
                if ($key == 'foobunnies') { #generic examples of custom behavior per column type
                    
                } elseif ($key == 'created') {
                    #Assumes YYYY/MM/DD but should work with anything?
                    $valtime = strtotime($val);
                    #note: 14400 is 4 hours.
                    if ($XLS) {
                        $valtime = date("Y-m-d h:i:s", strtotime("$val"));
                        $xlsarray[$xlscol] = "$valtime";
                    };
                } else {
                    if ($XLS) {
                        $xlsarray[$xlscol] = "$val";
                    };
                };
                $x++;
                $xlscol++;
            }
            if ($y < 2) {
                $empty = array('');
                $writer->writeSheetRow("$sheet", $empty);
                #$writer->writeSheetRow("$sheet", $xlscolheader );
                $writer->writeSheetRowBold("$sheet", $xlscolheader);
            };
            $writer->writeSheetRow("$sheet", $xlsarray, $rowdef);
            $TLINE.= '</tr>' . "\n";
            $TBODY.= $TLINE;
            $CSVBODY.= $CSVLINE . "\n";
            $CSVLINE = '';
            $y++;
            $xlsrow++;
        };
        $t = 0;
        $TFOOT = '';
        if ($groupon != '') {
            $break = true;
        };
        if ($break) {
            $TFOOT = "\n<tr style='height:50px;'><td></td>";
            $t = 0;
            while ($t < count($keys)) {
                if (@in_array($keys[$t], $totals)) {
                    $f = $keys[$t];
                    $TFOOT.= "<td style='text-align:right;font-weight:bold;border: 1px solid #666666;border-width:1px 0px 0px 0px;color:#333333;'>" . nff($subtot[$f]) . "</td>";
                    $subtot[$f] = 0;
                } else {
                    $TFOOT.= "<td style='text-align:right;font-weight:bold;border: 1px solid #666666;border-width:1px 0px 0px 0px;'>&nbsp;</td>";
                };
                $t++;
            };
            $TFOOT.= "</tr>";
            $break = false;
            $t = 0;
        };
        $tf = 0;
        $TFOOT.= "<tr><td></td>";
        while ($t < $x-1) {
            #     $TFOOT .= "<td>$keys[$t]</td>" ;
            $f = $keys[$t];
            if (@in_array($f, $totals)) {
                $TFOOT.= "<td style='text-align:right;font-weight:bold;font-size:large;border: 1px solid #000000;border-width:1px 0px 0px 0px;'>" . nff($tot[$f]) . "</td>";
                $tf++;
            } else {
                $TFOOT.= "<td style='text-align:right;font-weight:bold;border: 1px solid #000000;border-width:1px 0px 0px 0px;'>&nbsp;</td>";
            };
            $t++;
        };
        $TFOOT.= '</tr>';
        if ($tf < 1) {
            $TFOOT = '';
        };
        $chart = true;
        if (sizeof($groupchart) > 0 and sizeof($a) > 2) {
            $subtotalline = "\n<tr><td colspan=20></td><td>";
            include_once ("report-flot.php");
            $subtotalline.= "\n\n<div style='position:relative;top:-300px;'><div style='position:absolute;right:100px;display:block;'>";
            $subtotalline.= reportflot(500, 200, $a, "$sn", $groupchart[0], '');
            $subtotalline.= "</div></div>\n\n";
            $subtotalline.= "</td></tr>";
            $TFOOT.= $subtotalline;
            #$chart = false;
            $a = '';
        };
        if (sizeof($totalchart) > 0 and sizeof($aa) > 2) {
            $subtotalline = "\n<tr><td colspan=20>";
            include_once ("glass-flot.php");
            $ch = 200+(25*$cc);
            $subtotalline.= reportflot(800, $ch, $aa, "all", $totalchart[0], '');
            $subtotalline.= "</td></tr>";
            $TFOOT.= $subtotalline;
            $chart = false;
            $a = '';
        };
        if ($chart and sizeof($a) < 2) {
            $a = '';
        };
        if (sizeof($mapchart) > 0 and strlen($chartme) > 0) {
            $mapline = "\n<tr><td colspan=20>";
            #----------------------- Glueing in Google Maps Why? I dunno
            $mapline.= "
    <meta name=\"viewport\" content=\"initial-scale=1.0, user-scalable=yes\">
    <meta charset=\"utf-8\">
    <script src=\"https://maps.googleapis.com/maps/api/js?sensor=false\"></script>
    <script>
var gldata = {};

$chartme

var eachzCircle;
function initialize() {
  var mapOptions = {
    zoom: 16,
    center: new google.maps.LatLng($latc, $lonc),
    panControl: true,
    zoomControl: true,
    scaleControl: true,
             
    mapTypeId: google.maps.MapTypeId.ROADMAP
    
    
  };
  var map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);
  for (var eachz in gldata) {
    // Construct the circle for each value in gldata. We scale values by 20.
    var valuesOptions = {
      strokeColor: '#FF0000',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: '#FF0000',
      fillOpacity: 0.3,
      map: map,
      center: gldata[eachz].center,
      radius: gldata[eachz].values  * $chartfactor
    };
    cityCircle = new google.maps.Circle(valuesOptions);
    var marker = new google.maps.Marker({ position: gldata[eachz].center, map: map, title:gldata[eachz].title });
  }  
}
 google.maps.event.addDomListener(window, 'load', initialize);
    </script>

          <div id=\"map-canvas\" style=\"width:1000px; height:800px;\"></div>

";
            #-----------------------
            $mapline.= "</td></tr>";
            $mapline.= "<tr><td colspan=20>";
            $chartfactor = sprintf("%8.2f", $chartfactor);
            $ym1 = $y-1;
            $mapline.= "<div class='alert alert-info'><b>$mapchart[0]</b>&nbsp;&nbsp;&nbsp;Factor: $chartfactor applied to $cm $mapchart[0] records</div>";
            $mapline.= "</td></tr>";
            $TMAP.= $mapline;
        };
        #$style = 'plain' ; #options; plain | sexy
        $style = 'plain';
        #$_REQUEST['d'] = 't' ;
        if ($_REQUEST['debug'] == 'true' or $_REQUEST['d'] == 't') {
            $style = 'plain'; #options; plain | sexy
            print "<pre>" . print_r($_REQUEST, 1) . "</pre>";
            print "<pre style='z-index:9'>$lastquery</pre>";
        };
        if ($y < 2) {
            print "<h4>" . bbf('No Matching Records Found') . "</h4>\n $vendornumber $xlssel\n";
            return;
        };
        if ($style == 'sexy' and empty($_REQUEST['QRUN'])) {
            $yy = $_REQUEST['yy']*1+30;
            $yy0 = 200+$yy;
            $yyz = 160+$yy;
            $yy1 = (200+$yy) . 'px';;
            $yy2 = (295+$yy) . 'px';
            $ym1 = $y-1;
            print <<<EOF
<script language="javascript">
  $(document).ready(function(){  
    $(window).scroll(function () {  
    var wh = $(window).height();
    var st = $(window).scrollTop();
    var newheight = $yy0 ; 
    if(st > $yyz) {
      var newheight = st + 40 ; 
    } ;   
    document.getElementById("tit").style.top=newheight + "px" ;
    });  
  });  
 function fixwidths() {
  var hd = [] ; 
  var col = [] ; 
  var w = [] ;
  for(var i = 1, len = $x ; i < len; ++i) {
     var h = "hd" + i ;
     var c = "col" + i ;
     hd[i] = document.getElementById(h);   
     col[i] = document.getElementById(c);
     w[i] = 99 ;
     if (hd[i].offsetWidth > col[i].offsetWidth) { w[i] = hd[i].offsetWidth ; } ; 
     if (hd[i].offsetWidth <= col[i].offsetWidth) { w[i] = col[i].offsetWidth ; } ; 
     w[i] = w[i] * 3
     hd[i].style.width = w[i] + "px"; 
     col[i].style.width = w[i] + "px" ;
 } ; 
} ;  
</script>
    <style type="text/css" media="screen">
      thead { position:absolute;top:$yy1;z-index:+1; }
    </style>
    <style type="text/css" media="screen">
      tbody { position:absolute;top:$yy2;z-index:-1; }
    </style>

<table border=0 class='table table-striped table-hover table-condensed'>
<thead id=tit>
<tr style='background:#BBBBBB;color:#000000;font-weight:bold;' id=titz>
<td colspan=8>$itemname</td><td colspan=2>$ym1 records</td></tr>
<tr style='background:#FFFFFF;color:#000000;font-weight:normal;' id=titz><td></td><td colspan=9>$xlssel</td></td></tr>
$TMAP
<tr style='background:#CCCCCC;color:#000000;font-weight:bold;' id=titz><td class=muted id=hd0 style='width:40px;border:1px solid #666666;border-width:0px 1px 0px 0px;'>#</td>
$THEAD
</tr>
</thead>
<tbody id=tbod>
$TBODY 
$TFOOT
</tbody>
</table>
<script>fixwidths()</script>
EOF;
            
        };
        if ($style == 'plain' and empty($_REQUEST['QRUN'])) {
            $ym1 = $y-1;
            print <<<EOF
<table border=0>
<thead">
<tr style='background:#CCCCCC;color:#000000;font-weight:bold;' id=titz><td colspan=8>$itemname</td><td colspan=2>$ym1 records</td></tr>
$TMAP
<tr style='background:#FFFFFF;color:#000000;font-weight:normal;' id=titz><td></td><td colspan=9>$xlssel</td></td></tr>
<tr style='background:#CCCCCC;color:#000000;font-weight:bold;' id=titz><td class=muted id=hd0 style='width:20px;border:1px solid #666666;border-width:0px 1px 0px 0px;'>#</td>
$THEAD
</tr>
</thead>
<tbody id=tbod>
$TBODY 
$TFOOT
</tbody>
</table>
EOF;
            
        };
    }; #END LOOP HERE ;
    #NOW TO WRITE OUT FILES!
    $dir = reservoircheckdirs($portal, $portal, $login);
    #$css = file_get_contents("cogs/css/bootstrap.css");
    $itemname = detaintstripfilename2($itemname);
    $htmlout = fopen("$dir/$itemname-$now3-$counter.htm", "w");
    fputs($htmlout, "<head><style>$css</style></head><table cellpadding=10><tr><td>#</td>$THEAD</tr>$TMAP $TBODY $TFOOT </table> ");
    fclose($htmlout);
    #CONVERT TO PDF
    $options = '--landscape --browserwidth 1500 --continuous --fontsize 6';
    system("htmldoc -t pdf --quiet --jpeg --webpage $options '$dir/$itemname-$now3-$counter.htm' --outfile '$dir/$itemname-$now3-$counter.pdf'");
    $csvout = fopen("$dir/$itemname-$now3-$counter" . ".csv", "w");
    fputs($csvout, "$CSVHEAD\n$CSVBODY");
    fclose($csvout);
    if ($XLS) {
        #$workbook->close();
        $writer->writeToFile("$dir/$itemname-$now3-$counter" . '.xlsx');
    };
    $time_end = microtime(true);
    $time = $time_end-$time_start;
    runsql("update reports set counter = counter+1 where itemid = '$itemid'");
    runsql("update localreports set counter = counter+1 where itemid = '$itemid'");
    runsql("update reports set maxtime = '$time' where itemid = '$itemid' and maxtime < '$time'");
    runsql("update localreports set maxtime = '$time' where itemid = '$itemid' and maxtime < '$time'");
    runsql("delete from reportq where completedatetime < date_sub(now(),interval 3 day)");
    print "<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><div style='text-align:right;color:#CCCCCC;'>$time</div>";
    return "$itemname-$now3-$counter";
};
function cleanrestmp($dir, $minutes) {
    #Mikes Generic Clean Temp File Routine. Call.. Often.
    if (preg_match("/reservoir/", $dir, $matches)) {
    } else {
        print "$dir invalid dir";
        return;
    };
    $tmpdir = "$dir/";
    $filetypes = '*.*';
    if ($minutes < 1) {
        $minutes = 5;
    };
    foreach(glob($tmpdir . $filetypes) as $filename) {
        $filecreationtime = filectime($filename);
        $fileage = time() -$filecreationtime;
        if ($fileage > ($minutes*60)) {
            #    print "Rm: $filename <br>" ;
            unlink($filename);
        };
    };
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
function reportitem() {
    # global $portal, $login, $account, $mode, $submode, $subsubmode, $subsubsubmode, $action, $script, $db, $fromip, $level, $lang, $role, $perms, $item, $itemid, $fromdate, $todate, $vendornumber ;
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath, $item, $itemid, $fromdate, $todate;
    #should recheck access control ;
    $sseclevel = $level;
    $query = "select * from reports where seclevel <= '$level' and (uniq = '$item' or itemid = '$itemid')  order by uniq limit 1";
    $results = gaafm("$query");
    $r = 0;
    foreach($results as $key => $val) {
        $s = "\$$key = \"$val\" ; ";
        eval($s);
        $r++;
    }; #messy way to set all column names to variables containing the data.
    if ($r < 1) {
        #--
        $query = "select * from localreports where seclevel <= '$level' and (uniq = '$item' or itemid = '$itemid')  order by uniq limit 1";
        $results = gaafm("$query");
        $r = 0;
        foreach($results as $key => $val) {
            $s = "\$$key = \"$val\" ; ";
            eval($s);
            $r++;
        }; #messy way to set all column names to variables containing the data.
        
    };
    if (empty($account)) {
        $account = dt($_COOKIE['account']);
    };
    if (!isset($_REQUEST['input1'])) {
        $_REQUEST['input1'] = '%';
    };
    if (!isset($_REQUEST['input2'])) {
        $_REQUEST['input2'] = '%';
    };
    if (!isset($_REQUEST['input3'])) {
        $_REQUEST['input3'] = '%';
    };
    if (!isset($_REQUEST['input4'])) {
        $_REQUEST['input4'] = '%';
    };
    if (!isset($_REQUEST['input5'])) {
        $_REQUEST['input5'] = '%';
    };
    $input1 = reportdetaintstrip($_REQUEST['input1']);
    $input2 = reportdetaintstrip($_REQUEST['input2']);
    $input3 = reportdetaintstrip($_REQUEST['input3']);
    $input4 = reportdetaintstrip($_REQUEST['input4']);
    $input5 = reportdetaintstrip($_REQUEST['input5']);
    $input6 = reportdetaintstrip($_REQUEST['input6']);
    $input7 = reportdetaintstrip($_REQUEST['input7']);
    $input8 = reportdetaintstrip($_REQUEST['input8']);
    $input9 = reportdetaintstrip($_REQUEST['input9']);
    if ($r < 1) {
        print "<div>#$item $itemid $level " . bbf("Report not found") . "\n</div>";
        return;
    };
    print "<div class='noprint title'><a href='glass.php?mode=item&item=$uniq'><span style='color:#888888;'>#$uniq &rarr;</span>&nbsp;" . bbf("$itemname") . "</a></div>";
    print "<div class='noprint'>" . bbf("$itemdesc") . "</div>";
    $yy = 0; #Used for sexy adaptive layout
    #First lets see if there are fromdate/todate in the query.
    print "\n<form class='form-horizontal noprint' ACTION='$script' METHOD='get'><INPUT TYPE='hidden' NAME='mode' VALUE='run'><INPUT TYPE='hidden' NAME='item' VALUE='$uniq'><INPUT TYPE='hidden' NAME='itemid' VALUE='$itemid'> \n";
    if (preg_match("/fromdate/", $query, $matches)) { #Assume if fromdate there is todate.
        $yy+= 30;
        if (empty($fromdate)) {
            if ($defaultdatemode == 'M') {
                list($fromdate) = gafm("select date_format(now(),'%Y-%m-01')");
            } else {
                list($fromdate) = gafm("select date_format(now(),'%Y-%m-%d')");
            };
        };
        if (empty($todate)) {
            list($todate) = gafm("select date_format(now(),'%Y-%m-%d')");
        };
        print <<<EOF
<script  type="text/javascript" charset="utf-8">
    $(function() {
        $('#fromdate').datepicker();
        $('#todate').datepicker();
    });
</script>
EOF;
        if ($level < 70) {
            $r = 'READONLY';
        } else {
            $r = '';
        };
        $ft = bbf('From');
        $tt = bbf('To');
        print <<<EOF
        <label for=fromdate>$ft</label>
        <input name=fromdate size="16" type="date" value="$fromdate" $r>
        <label for=todate>$tt</label>
        <input name=todate size="16" type="date" value="$todate" $r>
EOF;
        
    }; # end match fromdate
    #NOW TO LOOK FOR INPUT1 and format accordingly. Formats:  blank = text input  table.field.fielddesc   A|B|C|D
    #I would kill to be able to effectively do this one time for all inputs1/2/3/4/...
    #But I am not that smart. Cut/Paste/Find/Replace. Works for now.
    #KILLING TO REWITE THIS TOMORROW AM!
    $yyinc = 50;

    $inputs = 8 ; 
    $i = 1 ; 
    while($i <= $inputs) {
     $s =  "\$inputfield = \${\"input\" . $i . \"field\"} ; " ; 
     eval($s) ; 
     $s =  "\$inputsource = \${\"input\" . $i . \"source\"} ; " ; 
     eval($s) ; 
     $s =  "\$input = \${\"input\" . $i} ; " ; 
     eval($s) ; 
    if (!empty($inputfield)) {
        $yy+= $yyinc;
        $selected = bbf('selected');
        $available = bbf('available');
        if (empty($input1source)) {
            print "<label for='input$i'>$inputfield</label>";
            print "<input name='input$i' size=\"20\" type=\"text\" value=\"$input\">";
        };
        if (preg_match("/\./", $inputsource, $matches)) {
            $is = split("[\.]", $inputsource);
            if ($is[0] == 'zippymaster') {
                $qi = "select $is[1],$is[2] from $is[0] where availableto like '%$portal%' order by $is[2]";
            } elseif ($is[0] == 'perms' or $is[0] == 'seclevel' or $is[0] == 'substatus' or $is[0] == 'statustypeid') {
                $qi = "select $is[1],$is[2] from $is[0] order by $is[2]";
            } else {
                $qi = "select distinct($is[1]),$is[2] from $is[0] where portal = '$portal' order by $is[2]";
            };
            $r = mysqli_query($db, $qi) or die("Error : " . mysqli_error($db));
            $inputselector = "\n" . "<select name='input$i'>";
            if (empty($input1)) {
                $inputselector.= "<optgroup label='$selected'><option value=''>blank ''</optgroup>";
            };
            if (!empty($input1)) {
                if ($input1 == '%') {
                    $inputselector.= "<optgroup label='$selected'><option value='%'>all %</optgroup>";
                } else {
                    if ($is[1] == $is[2]) {
                        $inputselector.= "<optgroup label='$selected'><option value='$input'>$input</optgroup>";
                    } else {
                        list($inputdesc) = gafm("select $is[2] from $is[0] where $is[1] = '$input' order by uniq limit 1");
                        $inputselector.= "<optgroup label='$selected'><option value='$input'>$input - $inputdesc</optgroup>";
                    };
                };
            };
            $inputselector.= "<optgroup label='$available'><option value='%'>" . bbf('all') . " %<option value=''>" . bbf('blank') . " ''";
            while (list($issk, $isst) = mysqli_fetch_row($r)) { #not checked yet.
                if ($issk == $isst) {
                    $inputselector.= "<option value='$issk'>" . bbf($isst) . "</option>\n";
                } else {
                    $inputselector.= "<option value='$issk'>" . bbf($issk) . ' - ' . bbf($isst) . "</option>\n";
                };
            };
            $inputselector.= "</optgroup></select>\n";
            print "<label for='input$i'>$inputfield</label>";
            print $inputselector;
            $inputselector = '';
        };
        if (preg_match("/^select/", $inputsource, $matches)) {
            #$is = split("[\|]", $input1source);
            #if ($input1 == '%') {
            #    $input1 = $is[0];
            #};
            if (!empty($input)) {
                $inputselector = "<optgroup label='$selected'><option value='$input'>" . bbf($input) . "</optgroup>";
            };
            $inputselector.= "<optgroup label='$available'>";
            $inputselector.= "<option value='%'>% all</option>";
            $replace = array('/&#39;/', '/&apos;/');
            $query = preg_replace($replace, '\'', $inputsource);
            $result2 = mysqli_query($db, $query) or die("Query failed : " . mysqli_error($db));
            while (list($one, $two, $three) = mysqli_fetch_row($result2)) {
                $inputselector.= "<option value='$one'>$one $two $three</option>";
            };
            $inputselector.= "</optgroup>\n";
            print "<label for='input$i'>$input1field</label>";
            print "<select name='input$i' id='input$i'>";
            print $inputselector;
            $inputselector = '';
            print "</select>";
        };
        if (preg_match("/\|/", $inputsource, $matches)) {
            $is = split("[\|]", $inputsource);
            if ($input1 == '%') {
                $input1 = $is[0];
            };
            if (!empty($input1)) {
                $inputselector = "<optgroup label='$selected'><option value='$input'>" . bbf($input) . "</optgroup>";
            };
            $inputselector.= "<optgroup label='$available'>";
            foreach($is as $s) {
                $inputselector.= "<option value='$s'>" . bbf($s) . "</option>\n";
            };
            $inputselector.= "</optgroup>\n";
            print "<label for='input$i'>$inputfield</label>";
            print "<select name='input$i' id='input$i'>";
            print $inputselector;
            $inputselector = '';
            print "</select>";
        };
        if (preg_match("/datepicker/", $inputsource, $matches)) {
            if (empty($input)) {
                list($input) = gafm("select date_format(now(),'%Y-%m-%d')");
            };
            print "<label for='input$i'>$inputfield</label><input name='input$i' size=\"16\" type=\"text\" value=\"$input\">\n";
        };
        if (preg_match("/accountpicker/", $inputsource, $matches)) {
            if (empty($input)) {
                $input = '%';
            };
            print <<<EOF
<script type="text/javascript">
<!--
var q;
  function sf(){
    q=document.getElementById('livesearch');
    q.focus();
  }
  function tf() {
   if (q.value==""){
     alert("Try a key field");
     q.focus();
     return false;
   }
 return liveSearchSubmit()
 }
// -->
</script>
<script src="glass-lookup.js" type="text/javascript"></script>
</HEAD>
<body onload="sf();liveSearchInit();">
EOF;
            print "<label class=\"control-label\" for='input$i'>$inputfield</label>"; #this one needs some testing.
            print "<table><tr><td colspan=3>";
            print "<input type=text name='input$i' id=\"livesearch\" onKeypress=\"liveSearchStart('wallet')\" size=15 value='$input'></td></tr>";
            print '<tr><td colspan=3><div id="picked" style="display: none;"></tr><tr><td colspan=3><div id="LSResult" style="display: none;"><div id="LSShadow"></div></tr></table>';
            print "";
        };
    };
    $i++ ; 
   } ;  
    #-----------------------------------------------END LOOP FOR 8 INPUTS
    
    
    
    
    
    
    print "<div class='muted'>$displaynotes</div>";
    #Now the big run button.
    $yy+= 50;
    print "<div class='form-actions'>";
    #HERE
    list($email) = gafm("select email from users where login = '$login'");
    if (preg_match("/OFFLINE/", $outputmode, $matches)) {
        if ($_REQUEST['offline'] == 'true') {
            $c = 'CHECKED';
        } else {
            $c = '';
        };
        print "<span style='border:2px solid #AAAAAA;padding:10px;margin:20px;'>" . bbf('Offline') . ": <input type=checkbox name=offline value=true $c>&nbsp;<i class='icon-time' style='font-size: 18px;'></i>&nbsp;&nbsp;";
        if (preg_match("/EMAIL/", $outputmode, $matches)) {
            if ($_REQUEST['notify'] == 'true') {
                $c = 'CHECKED';
            } else {
                $c = '';
            };
            print bbf('Notify') . ":<input type=checkbox name=notify value=true $c>&nbsp;";
        };
        if (preg_match("/SENDMAIL/", $outputmode, $matches)) {
            if ($_REQUEST['sendmail'] == 'true') {
                $c = 'CHECKED';
            } else {
                $c = '';
            };
            print bbf('Send') . ":<input type=checkbox name=sendmail value=true $c>&nbsp;";
        };
        if (preg_match("/SENDMAIL/", $outputmode, $matches) or preg_match("/EMAIL/", $outputmode, $matches)) {
            if (empty($email) and !empty($_REQUEST['email'])) {
                $email = $_REQUEST['email'];
            };
            print "<input id=email type=email name=email value='$email'  placeholder='E-Mail'>";
        };
        print "</span>";
    } else {
        # print "<input type=checkbox name=offline value=true>&nbsp;<i class='icon-time' style='font-size: 18px;'></i>&nbsp;&nbsp;";
        
    };
    print "<button type=submit class='button'>" . bbf('Create') . "</button>";
    if ($mode == 'run' and $_REQUEST['offline'] != 'true') {
        $now3 = date('Ymd');
        print "<button onClick='window.print()' class='button'>" . bbf('Print') . "</button>";
        #        print "<button onClick='' class='btn btn-info'><i class='icon-download' style='font-size: 18px;'></i>&nbsp;&nbsp;" . bbf('XLS') . "</button>";
        if (preg_match("/XLS/", $outputformats, $matches)) {
            $XLS = true;
            $dir = reservoircheckdirs($portal, $portal, $login);
            $itemnamestripped = detaintstripfilename2($itemname);
            $filename = "$itemnamestripped-$now3-$counter" . ".xlsx";
            print "<a href='glass-docsdownload.php?account=$account&dir=$dir&filename=$filename' class='button'>" . bbf('XLS') . "</a>";
        } else {
            $XLS = false;
        };
        if (preg_match("/CSV/", $outputformats, $matches)) {
            $XLS = true;
            $dir = reservoircheckdirs($portal, $portal, $login);
            $itemnamestripped = detaintstripfilename2($itemname);
            $filename = "$itemnamestripped-$now3-$counter" . ".csv";
            #print "$dir/$filename" ;
            print "<a href='glass-docsdownload.php?account=$account&dir=$dir&filename=$filename' class='button'>" . bbf('CSV') . "</a>";
        } else {
            $XLS = false;
        };
        if (preg_match("/PDF/", $outputformats, $matches)) {
            $XLS = true;
            $dir = reservoircheckdirs($portal, $portal, $login);
            $itemnamestripped = detaintstripfilename2($itemname);
            $filename = "$itemnamestripped-$now3-$counter" . ".pdf";
            print "<a href='glass-docsdownload.php?account=$account&dir=$dir&filename=$filename' class='button'>" . bbf('PDF') . "</a>";
        } else {
            $XLS = false;
        };
    };
    if ($sseclevel > 98) {
        print "<span style='border:2px solid #AAAAAA;padding:10px;margin:20px;'><a href='glass.php?mode=edit&table=reports&uniq=$uniq'><i class='icon-wrench' style='font-size: 18px;'></i></a><a href='glass.php?mode=export&uniq=$uniq'><i class='icon-wrench' style='font-size: 18px;color:red;'></i></a>";
        print "&nbsp;&nbsp;<input type=checkbox name=debug value=true><i class='icon-bullhorn' style='font-size: 18px;'></i></span>";
    };
    if ($uniq > 9999 and @in_array('editreport', $perms)) {
        print "<span style='border:2px solid #AAAAAA;padding:10px;margin:20px;'><a href='reporteditor.php?uniq=$uniq'><i class='icon-wrench' style='font-size: 18px;'></i></a>";
        print "&nbsp;&nbsp;<input type=checkbox name=debug value=true><i class='icon-bullhorn' style='font-size: 18px;'></i></span>";
    };
    print "</div>";
    print "</form>";
    $_REQUEST['yy'] = $yy;
};
function reporttopmenu() {
    #global $portal, $login, $account, $mode, $submode, $subsubmode, $subsubsubmode, $action, $script, $db, $fromip, $level, $lang, $role, $perms, $multilang, $vendornumber ;
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    #Currently all of these items are in the sidemenu.
    
};
function reportitemmenu() {
    #    global $portal, $login, $account, $mode, $submode, $subsubmode, $subsubsubmode, $action, $script, $db, $fromip, $level, $lang, $role, $perms, $sseclevel, $vendornumber ;
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    $searchfor = dt($_REQUEST['searchfor']);
    #CREATES A SINGLE TABLE FOR QUERIES!!!!  More columsn than needed, but why not just in case.
    runsql("create temporary table zr select uniq,itemgroup,itemid,itemname,itemdesc,seclevel,availtologins,availtoroles,query,query2,groupon,asnumbers,totals,input1field,input1source,input2field,input2source,input3field,input3source,input4field,input4source,input5field,input5source,input6field,input6source,input7field,input7source,input8field,input8source,input9field,input9source,fieldcharlimit,defaultdatemode,output,outputformats,outputmode,counter,comments,created,lastmod,displaynotes,groupchart,totalchart,mapchart from reports order by itemgroup,itemid,uniq");
    runsql("insert into zr select uniq,itemgroup,itemid,itemname,itemdesc,seclevel,availtologins,availtoroles,query,query2,groupon,asnumbers,totals,input1field,input1source,input2field,input2source,input3field,input3source,input4field,input4source,input5field,input5source,input6field,input6source,input7field,input7source,input8field,input8source,input9field,input9source,fieldcharlimit,defaultdatemode,output,outputformats,outputmode,counter,comments,created,lastmod,displaynotes,groupchart,totalchart,mapchart from localreports order by itemgroup,itemid,uniq");
    if ($mode == 'search') {
        $query = "SELECT uniq,itemgroup,itemid,itemname,itemdesc,seclevel,availtologins,availtoroles from zr where 
        seclevel <= '$level' and (availtoroles like '%$role,%' or availtoroles like '%$role|%' or availtoroles = '') 
        and (uniq = '$searchfor' or itemid like '%$searchfor%' or itemname like '%$searchfor%' or itemdesc like '%$searchfor%') 
        order by itemgroup,itemid,uniq";
        if ($login == 'admin') { #full access dispite role ;
            print "<div>Admin Search: no roles enforced, also search query</div>";
            $query = "SELECT uniq,itemgroup,itemid,itemname,itemdesc,seclevel,availtologins,availtoroles from zr where 
        seclevel <= '$level' 
        and (uniq = '$searchfor' or itemid like '%$searchfor%' or itemname like '%$searchfor%' or itemdesc like '%$searchfor%' or query like '%$searchfor%') 
        order by itemgroup,itemid,uniq";
        };
    } else {
        $query = "SELECT uniq,itemgroup,itemid,itemname,itemdesc,seclevel,availtologins,availtoroles from zr where 
        seclevel <= '$level' and (availtoroles like '%$role,%' or availtoroles like '%$role|%' or availtoroles = '') order by itemgroup,itemid,uniq";
    };
    $result2 = gaaafm($query);
    $sumusage = 0;
    $peakusage = 0;
    $lastgroup = 'zz'; #dummy trigger setting
    $searchmenu = "\n<FORM ACTION='glass.php' METHOD='get' name='reportsearch' class='form-search'><input type='hidden' name='mode' value='search'>";
    $searchmenu.= "<div class='input-append' style='padding:10px 0px 0px 10px;'>";
    $searchmenu.= "<input type='text' class='span2 search-query' name='searchfor' value='$searchfor'>";
    $searchmenu.= "<button class='btn' type='button' onclick=\"document.reportsearch.submit()\">Search</button>";
    $searchmenu.= "</div>";
    $searchmenu.= "</FORM>\n";
    if ($level > 40) {
        $sidemenu.= "<li><a href='glass.php?mode=reservoir'>Report Folder</a></li>";
    };
    foreach($result2 as $row) {
        $uniq = $row['uniq'];
        $itemgroup = $row['itemgroup'];
        $itemid = $row['itemid'];
        $itemname = $row['itemname'];
        $itemdesc = $row['itemdesc'];
        $level = $row['seclevel'];
        $availtologins = $row['availtologins'];
        $availtoroles = $row['availtoroles'];
        if ($itemgroup != "$lastgroup" and $lastgroup != 'Userz') {
            if ($lastgroup != 'zz') {
                $mainmenu.= "\n</ul></section>\n\n";
            };
            if ($itemgroup == '' or $itemgroup == 'User') {
                #$itemgroup = "User";
                
            };
            $sidemenu.= "<li><a href=\"#$itemgroup\">" . bbf("$itemgroup") . "</a></li>\n";
            $mainmenu.= "\n<section id='$itemgroup'><h3>" . bbf("$itemgroup") . "</h3>\n";
            $mainmenu.= "<ul>\n";
        };
        $ss = "$uniq &gt;=$level $availtologins $availtoroles";
        $mainmenu.= "<li>$uniq <a href='$script?mode=item&item=$uniq&itemid=$itemid' title='$ss'>" . bbf("$itemname") . "</A>&nbsp;" . bbf("$itemdesc") . "</li>\n";
        $lastgroup = $itemgroup;
    };
    $mainmenu.= "</ul>\n";
    if (@in_array('editreport', $perms)) { // May need to get added to reports ;
        $sidemenu.= "<li><a href='reporteditor.php?mode=create'>Create Report</a></li>";
    };
    print "<div id=floatingmenu style='border:2px solid #FF00FF;position:fixed;top:5em;left:0em;'><ul>";
    print "$sidemenu $searchmenu\n";
    list($pending) = gafm("select count(uniq) from reportq where login = '$login' and completedatetime < '2001-01-01'");
    if ($pending > 0) {
        print "<li><a href=\"#pending\"><i class=\"icon-chevron-right\"></i>$pending " . bbf('pending') . "</a></li>\n";
    };
    print "\n</ul></div>\n"; //div is end of floatingmenu
    print "\n<div id=listofreports style='border:2px solid #006600;position:relative;left:15em;width:40em;'>\n";
    print "$mainmenu";
    if ($pending > 0) {
        include_once ("glass-core.php");
        $mainmenu = "\n<section id='pending'>\n";
        $mainmenu.= "<li>";
        $mainmenu.= quickshowr("select q.itemid as Item,report.itemname as Description,q.requestdatetime as Requested from reportq q left join reports on (report.itemid = q.itemid) where q.portal = '$portal' and q.login = '$login' and q.completedatetime < '2001-01-01'", 'nada', 'nada', "<a href='reports.php?mode=reservoir'><i class='icon-folder-open' style='font-size: 18px;'></i></a>&nbsp;&nbsp;" . bbf('Pending Items'), 9, '500px');
        $mainmenu.= "</li>";
        $mainmenu.= "</ul>";
        print $mainmenu;
    };
    print "\n</div>\n";
};
function reportexport() {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    $uniq = dt($_REQUEST['uniq']);
    if ($level > 95 and $uniq > 0) {
        print "Exporting report $uniq into /tmp/$uniq.sql";
        include ("settings.inc");
        $string = "/usr/bin/mysqldump -c -n -t -u $dblogin -p$dbpasswd $database reports --where=\"uniq='$uniq'\" >/tmp/$uniq.sql";
        #print "<pre>$string</pre>" ;
        system("$string");
        $file = file_get_contents("/tmp/$uniq.sql");
        print "<pre>\n\n$file\n\n</pre>";
    };
};
function reportheader() {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    $r = bbf('Reports');
    #$brand = "<a class='brand' href='glass.php?mode=login'>$login</a>";
    $brand = '';
    $dplang = 'en';
    if ($lang == 'fr' or $lang == 'es') {
        $dplang = $lang;
    };
    print "<style type=\"text/css\" media=\"print\"> .noprint { display:none; }</style>";
};
function reportfooter() {
    print "\n\n</body></html>\n";
};
function reportdetaintstrip($string) {
    $newstring = $string;
    $newstring = str_replace("'", "", $newstring);
    $newstring = str_replace("\\n", "", $newstring);
    $newstring = str_replace("\r", "", $newstring);
    $newstring = str_replace("\"", "", $newstring);
    $newstring = str_replace("\\", "", $newstring);
    $newstring = str_replace("=", "", $newstring);
    $newstring = str_replace("<", "", $newstring);
    $newstring = str_replace(">", "", $newstring);
    return $newstring;
};
function reservoir($zcabinet, $zfolder) {
    # version of subdocs for report output.
    #cabinet is portal. folder = account
    #    global $portal, $theme, $login, $account, $mode, $submode, $subsubmode, $subsubsubmode, $action, $script, $db, $fromip, $level, $browser, $lang, $role, $folder, $cabinet, $vendornumber ;
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    $folder = $zfolder;
    while (strlen($folder) < 4) {
        $folder = '0' . $folder;
    };
    $cabinet = $zcabinet;
    if ($level < 50) {
        die; # for now ;
        
    };
    #first we do some dir/file checking/creation.
    $dir = reservoircheckdirs($portal, $cabinet, $folder);
    $rootdir = $dir;
    if ($_REQUEST['dir'] != '') {
        $dir = detaintstripdir($_REQUEST['dir']);
        $subdir = detaintstripdir($_REQUEST['subdir']);
    };
    $filename = detaintstripfilename2($_REQUEST['filename']);
    if ($subsubmode == 'view') {
        reservoirview($dir, $filename);
    };
    if ($subsubmode == 'upload') {
        reservoirupload();
    };
    if ($subsubmode == '') {
        $archivedir = @dir("$dir");
        #creates date sorted array
        while (FALSE !== ($filename = $archivedir->read())) {
            $ctime = filemtime("$dir/$filename");
            $dirlist[] = array("$filename", $ctime);
        };
        #usort($dirlist, 'DateCmp');
        rsort($dirlist);
        #print "$rootdir $dir" ;
        $FILES.= "<a class=btn href='reports.php?mode=reservoir&submode=flush'><i class='icon-remove-circle' style='font-size: 18px;'></i></a>";
        $FILES = "\n<TABLE BORDER=0><tr><td colspan=4 style=\"border:1px solid #888888;border-color:#ffffff #ffffff #333333 #ffffff;\">$folder </td>\n";
        $DIRS = "<table>";
        $DIRS.= "<tr><td><a href='reports.php?mode=reservoir&submode=flush'><i class='icon-remove-circle' style='font-size: 18px;'></i></a></tr>";
        $DIRS.= "<tr><td><a href='$script?mode=$mode&submode=docs&folder=$folder'><i class='icon-folder-open' style='font-size: 18px;'></i></a></td><td></td></tr>";
        foreach($dirlist as $filetemparray) {
            $filename = $filetemparray[0];
            if ($filename != ".." and $filename != ".") {
                if (filetype("$dir/$filename") == "dir") {
                    $DIRS.= "<tr><td><a href='$script?mode=$mode&submode=docs&folder=$folder&dir=$dir/$filename'><i class='icon-folder-close' style='font-size: 18px;'></i></a>&nbsp&nbsp;</td><td><a href='$script?mode=$mode&submode=docs&folder=$folder&dir=$dir/$filename'>" . bbf($filename) . "</a></td></tr>";
                } else {
                    if ($_REQUEST['submode'] == 'flush') {
                        #print "Flushing $filename<br>" ;
                        unlink("$dir/$filename");
                    } else {
                        $ctime = filemtime("$dir/$filename");
                        #THUMBNAIL CHECK
                        if (preg_match("/txt$/", $filename, $m)) {
                        } elseif (preg_match("/thumb/", $filename, $m)) {
                        } elseif (preg_match("/jpg$/", $filename, $m) or preg_match("/JPG$/", $filename, $m)) {
                            if (@fopen("$dir/thumb.$filename.png", "r")) {
                            } else {
                                system("/usr/bin/convert -quality 75 -geometry 93x120 $dir/$filename $dir/thumb.$filename");
                            };
                        } elseif (preg_match("/png$/", $filename, $m) or preg_match("/PNG$/", $filename, $m)) {
                            if (@fopen("$dir/thumb.$filename.png", "r")) {
                            } else {
                                system("/usr/bin/convert -quality 75 -geometry 93x120 $dir/$filename $dir/thumb.$filename");
                            };
                        } elseif (preg_match("/pdf$/", $filename, $m) or preg_match("/PDF$/", $filename, $m)) {
                            if (@fopen("$dir/thumb.$filename-0.png", "r")) {
                            } else {
                                #  system("/usr/bin/convert -thumbnail x120 $dir/$filename $dir/thumb.$filename.png");
                                
                            };
                        } else {
                        };
                        if (preg_match("/txt$/", $filename, $m)) {
                            $FILES.= "<TR><td></td><TD ALIGN=RIGHT><A HREF='$script?mode=$mode&folder=$folder&submode=docs&subsubmode=view&filename=$filename&dir=$dir'>$filename</A></TD><TD ALIGN=RIGHT>";
                        } elseif (preg_match("/thumb/", $filename, $m)) {
                        } elseif (preg_match("/jpg$/", $filename, $m) or preg_match("/JPG$/", $filename, $m)) {
                            $sdir = str_replace("/home/foo", "", $dir);
                            $FILES.= "<TR><td valign=top><img src='$sdir/thumb.$filename'></td><TD ALIGN=RIGHT valign=top><a href='glass-docsdownload.php?account=$account&dir=$dir&filename=$filename'>$filename</a></td><td align=right valign=top>";
                        } elseif (preg_match("/png$/", $filename, $m) or preg_match("/PNG$/", $filename, $m)) {
                            $sdir = str_replace("/home/foo", "", $dir);
                            $FILES.= "<TR><td valign=top><img src='$sdir/thumb.$filename'></td><TD ALIGN=RIGHT valign=top><a href='glass-docsdownload.php?account=$account&dir=$dir&filename=$filename'>$filename</a></td><td align=right valign=top>";
                        } elseif (preg_match("/pdf$/", $filename, $m) or preg_match("/PDF$/", $filename, $m)) {
                            $sdir = str_replace("/home/foo", "", $dir);
                            $FILES.= "<TR><td valign=top>";
                            $thumb = @fopen("$dir/thumb.$filename-0.png", "r");
                            if (!($thumb)) {
                            } else {
                                $FILES.= "<img src='$sdir/thumb.$filename-0.png'>";
                            };
                            $FILES.= "</td><TD ALIGN=RIGHT valign=top><a href='glass-docsdownload.php?account=$account&dir=$dir&filename=$filename'><span style='font-family:courier;fixed;font-weight:bold;'>$filename</span></a></td><td align=right valign=top>";
                        } else {
                            $FILES.= "<TR><td></td><TD ALIGN=RIGHT><a href='glass-docsdownload.php?account=$account&dir=$dir&filename=$filename'><span style='font-family:courier;fixed;font-weight:bold;'>$filename</span></a></td><td align=right>";
                        };
                        if (preg_match("/thumb/", $filename, $m)) {
                        } else {
                            $FILES.= "&nbsp;&nbsp;&nbsp;" . date("Y-m-d </TD><TD ALIGN='right'>H:i A", $ctime);
                            $FILES.= "</TD><td>";
                            $FILES.= "</td><td valign=top>";
                            $FILES.= $sn;
                            $FILES.= "</td><td valign=top>";
                            if (preg_match("/jpg$/", $filename, $m) or preg_match("/JPG$/", $filename, $m)) {
                                #EXIF DATA
                                $exif = '';
                                $latitude = '';
                                $longitude = '';
                                $exif = exif_read_data("$dir/$filename", 'IFD0');
                                $exif = exif_read_data("$dir/$filename", 0, true);
                                foreach($exif as $key => $section) {
                                    foreach($section as $name => $val) {
                                        if (strlen($val) < 80) {
                                            if (preg_match("/GPS/", $name, $m)) {
                                                if (is_array($val)) { #Saves ARRAY Data, concats string
                                                    $vv = '';
                                                    foreach($val as $v) {
                                                        $vv.= "$v ";
                                                    };
                                                    if ("$key.$name" == 'GPS.GPSLatitude') {
                                                        $n = preg_split('/\//', $val[0]);
                                                        if (is_array($n)) {
                                                            $h = $n[0]/$n[1];
                                                        } else {
                                                            $h = 0;
                                                        };
                                                        $n = preg_split('/\//', $val[1]);
                                                        if (is_array($n)) {
                                                            $m = $n[0]/$n[1];
                                                        } else {
                                                            $m = 0;
                                                        };
                                                        $n = preg_split('/\//', $val[2]);
                                                        if (is_array($n)) {
                                                            $s = $n[0]/$n[1];
                                                        } else {
                                                            $s = 0;
                                                        };
                                                        $m = sprintf("%2.5f", $m+($s/60/60)) *1;
                                                        $latitude = "N" . $h . '°' . "$m";;
                                                    };
                                                    if ("$key.$name" == 'GPS.GPSLongitude') {
                                                        $n = preg_split('/\//', $val[0]);
                                                        if (is_array($n)) {
                                                            $h = $n[0]/$n[1];
                                                        } else {
                                                            $h = 0;
                                                        };
                                                        $n = preg_split('/\//', $val[1]);
                                                        if (is_array($n)) {
                                                            $m = $n[0]/$n[1];
                                                        } else {
                                                            $m = 0;
                                                        };
                                                        $n = preg_split('/\//', $val[2]);
                                                        if (is_array($n)) {
                                                            $s = $n[0]/$n[1];
                                                        } else {
                                                            $s = 0;
                                                        };
                                                        $m = $m+($s/60/60);
                                                        $m = sprintf("%2.5f", $m+($s/60/60)) *1;
                                                        $longitude = "W" . $h . '°' . "$m";;
                                                    };
                                                    #$FILES .= "$key.$name :(a) $vv<br />\n";
                                                    
                                                } else {
                                                    #  $FILES .= "$key.$name: $val<br />\n";
                                                    
                                                };
                                            };
                                        };
                                    }
                                }
                                if ($latitude != '') {
                                    $FILES.= "Latitude: $latitude<br>";
                                    $FILES.= "Longitude: $longitude<br>";
                                };
                            } #------------------------------
                            $FILES.= "</td></TR>\n";
                        };
                    };
                };
            };
        };
        $FILES.= "</TABLE>";
        $DIRS.= "</table>";
        $archivedir->close();
        print "<table><tr><td valign=top>$DIRS</td><td style=\"border:1px solid #888888;border-color:#ffffff #333333 #ffffff #ffffff;\">&nbsp;</td><td valign=top>$FILES</td></tr></table>";
        cleanrestmp("$dir", 5000); #more than 3 days ;
        
    };
};
function reservoirview($dir, $filename) {
    # global $portal, $theme, $login, $account, $mode, $submode, $subsubmode, $subsubsubmode, $action, $script, $db, $fromip, $level, $browser, $lang, $vendornumber ;
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    if (preg_match("/txt$/", $filename, $m)) {
        #SHOW AS ASCII TEXT
        myinfo();
        $ctime = filectime("$dir/$filename");
        print "<center><table style=\"border:1px solid #888888;\"><tr><td style=\"border:1px solid #000000;border-color:#ffffff #ffffff #000000 #ffffff;\"><b>$filename</b></td><td style=\"border:1px solid #000000;border-color:#ffffff #ffffff #000000 #ffffff;\">";
        print date(" l F d, Y h:i A  ", $ctime);
        print "</td><td><A class=button HREF='#' onClick=\"window.print()\"><img src='images/print.gif' border=0> " . bbf('Print') . "</A></td></tr><tr><td colspan=3><pre>";
        readfile("$dir/$filename");
        print "</pre></td></table></center>";
    } else { #Show as raw file
        #readfile("$dir/$filename");
        #        print "<script type=\"text/javascript\">
        #        window.open('glass-docsdownload.php?account=$account&dir=$dir&filename=$filename','download','toolbars=0,status=0,scrollbars=1,width=600,height=475')
        #        </script>" ;
        #        print "<a href='glass-docsdownload.php?account=$account&dir=$dir&filename=$filename'>glass-docsdownload.php?account=$account&dir=$dir&filename=$filename</a>" ;
        $subsubmode = '';
    };
};
function reservoircheckdirs($portal, $cabinet, $folder) {
    # global $theme, $login, $mode, $submode, $subsubmode, $subsubsubmode, $action, $script, $db, $fromip, $level, $browser, $lang, $vendornumber ;
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    #    print "$portal $cabinet $folder" ;
    while (strlen($folder) < 4) {
        $folder = '0' . $folder;
    };
    include ('settings.inc');
    $dir = @fopen("$reservoir", "r");
    if (!($dir)) {
        mkdir("$reservoir");
    };
    @fclose($dir);
    $dir = @fopen("$reservoir/$portal/", "r");
    if (!($dir)) {
        mkdir("$reservoir/$portal/");
    };
    @fclose($dir);
    $dir = @fopen("$reservoir/$portal/$cabinet/", "r");
    if (!($dir)) {
        mkdir("$reservoir/$portal/$cabinet/");
    };
    @fclose($dir);
    $a1 = substr($folder, 0, 1);
    $a2 = substr($folder, 1, 1);
    $a3 = substr($folder, 2, 1);
    $a4 = substr($folder, 3, 1);
    $dir = @fopen("$reservoir/$portal/$cabinet/$a1", "r");
    if (!($dir)) {
        mkdir("$reservoir/$portal/$cabinet/$a1");
    };
    @fclose($dir);
    $dir = @fopen("$reservoir/$portal/$cabinet/$a1/$a2", "r");
    if (!($dir)) {
        mkdir("$reservoir/$portal/$cabinet/$a1/$a2");
    };
    @fclose($dir);
    $dir = @fopen("$reservoir/$portal/$cabinet/$a1/$a2/$a3", "r");
    if (!($dir)) {
        mkdir("$reservoir/$portal/$cabinet/$a1/$a2/$a3");
    };
    @fclose($dir);
    $dir = @fopen("$reservoir/$portal/$cabinet/$a1/$a2/$a3/$a4", "r");
    if (!($dir)) {
        mkdir("$reservoir/$portal/$cabinet/$a1/$a2/$a3/$a4");
    };
    @fclose($dir);
    $dir = @fopen("$reservoir/$portal/$cabinet/$a1/$a2/$a3/$a4/$folder", "r");
    if (!($dir)) {
        mkdir("$reservoir/$portal/$cabinet/$a1/$a2/$a3/$a4/$folder");
    };
    @fclose($dir);
    $dir = "$reservoir/$portal/$cabinet/$a1/$a2/$a3/$a4/$folder";
    return $dir;
};
function detaintstripfilenamez($string) {
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
function detaintstripdir($string) {
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
function DateCmp($a, $b) {
    return ($a[1] < $b[1]) ? -1 : 0;
}
if (!class_exists('ZipArchive')) {
    throw new Exception('ZipArchive not found');
}
class XLSXWriter {
    //------------------------------------------------------------------
    //http://office.microsoft.com/en-us/excel-help/excel-specifications-and-limits-HP010073849.aspx
    const EXCEL_2007_MAX_ROW = 1048576;
    const EXCEL_2007_MAX_COL = 16384;
    //------------------------------------------------------------------
    protected $author = 'Mike Harrison GeekLabs.com';
    protected $sheets = array();
    protected $shared_strings = array(); //unique set
    protected $shared_string_count = 0; //count of non-unique references to the unique set
    protected $temp_files = array();
    protected $current_sheet = '';
    public function __construct() {
        if (!ini_get('date.timezone')) {
            //using date functions can kick out warning if this isn't set
            date_default_timezone_set('UTC');
        }
    }
    public function setAuthor($author = '') {
        $this->author = $author;
    }
    public function __destruct() {
        if (!empty($this->temp_files)) {
            foreach($this->temp_files as $temp_file) {
                @unlink($temp_file);
            }
        }
    }
    protected function tempFilename() {
        $filename = tempnam(sys_get_temp_dir(), "xlsx_writer_");
        $this->temp_files[] = $filename;
        return $filename;
    }
    public function writeToStdOut() {
        $temp_file = $this->tempFilename();
        self::writeToFile($temp_file);
        readfile($temp_file);
    }
    public function writeToString() {
        $temp_file = $this->tempFilename();
        self::writeToFile($temp_file);
        $string = file_get_contents($temp_file);
        return $string;
    }
    public function writeToFile($filename) {
        foreach($this->sheets as $sheet_name => $sheet) {
            self::finalizeSheet($sheet_name); //making sure all footers have been written
            
        }
        @unlink($filename); //if the zip already exists, overwrite it
        $zip = new ZipArchive();
        if (empty($this->sheets)) {
            self::log("Error in " . __CLASS__ . "::" . __FUNCTION__ . ", no worksheets defined.");
            return;
        }
        if (!$zip->open($filename, ZipArchive::CREATE)) {
            self::log("Error in " . __CLASS__ . "::" . __FUNCTION__ . ", unable to create zip.");
            return;
        }
        $zip->addEmptyDir("docProps/");
        $zip->addFromString("docProps/app.xml", self::buildAppXML());
        $zip->addFromString("docProps/core.xml", self::buildCoreXML());
        $zip->addEmptyDir("_rels/");
        $zip->addFromString("_rels/.rels", self::buildRelationshipsXML());
        $zip->addEmptyDir("xl/worksheets/");
        foreach($this->sheets as $sheet) {
            $zip->addFile($sheet->filename, "xl/worksheets/" . $sheet->xmlname);
        }
        if (!empty($this->shared_strings)) {
            $zip->addFile($this->writeSharedStringsXML(), "xl/sharedStrings.xml"); //$zip->addFromString("xl/sharedStrings.xml",     self::buildSharedStringsXML() );
            
        }
        $zip->addFromString("xl/workbook.xml", self::buildWorkbookXML());
        $zip->addFile($this->writeStylesXML(), "xl/styles.xml"); //$zip->addFromString("xl/styles.xml"           , self::buildStylesXML() );
        $zip->addFromString("[Content_Types].xml", self::buildContentTypesXML());
        $zip->addEmptyDir("xl/_rels/");
        $zip->addFromString("xl/_rels/workbook.xml.rels", self::buildWorkbookRelsXML());
        $zip->close();
    }
    protected function initializeSheet($sheet_name) {
        //if already initialized
        if ($this->current_sheet == $sheet_name || isset($this->sheets[$sheet_name])) return;
        $sheet_filename = $this->tempFilename();
        $sheet_xmlname = 'sheet' . (count($this->sheets) +1) . ".xml";
        $this->sheets[$sheet_name] = (object)array('filename' => $sheet_filename, 'sheetname' => $sheet_name, 'xmlname' => $sheet_xmlname, 'row_count' => 0, 'file_writer' => new XLSXWriter_BuffererWriter($sheet_filename), 'cell_formats' => array(), 'max_cell_tag_start' => 0, 'max_cell_tag_end' => 0, 'finalized' => false,);
        $sheet = &$this->sheets[$sheet_name];
        $tabselected = count($this->sheets) == 1 ? 'true' : 'false'; //only first sheet is selected
        $max_cell = XLSXWriter::xlsCell(self::EXCEL_2007_MAX_ROW, self::EXCEL_2007_MAX_COL); //XFE1048577
        $sheet->file_writer->write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
        $sheet->file_writer->write('<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">');
        $sheet->file_writer->write('<sheetPr filterMode="false">');
        $sheet->file_writer->write('<pageSetUpPr fitToPage="false"/>');
        $sheet->file_writer->write('</sheetPr>');
        $sheet->max_cell_tag_start = $sheet->file_writer->ftell();
        $sheet->file_writer->write('<dimension ref="A1:' . $max_cell . '"/>');
        $sheet->max_cell_tag_end = $sheet->file_writer->ftell();
        $sheet->file_writer->write('<sheetViews>');
        $sheet->file_writer->write('<sheetView colorId="64" defaultGridColor="true" rightToLeft="false" showFormulas="false" showGridLines="true" showOutlineSymbols="true" showRowColHeaders="true" showZeros="true" tabSelected="' . $tabselected . '" topLeftCell="A1" view="normal" windowProtection="false" workbookViewId="0" zoomScale="100" zoomScaleNormal="100" zoomScalePageLayoutView="100">');
        $sheet->file_writer->write('<selection activeCell="A1" activeCellId="0" pane="topLeft" sqref="A1"/>');
        $sheet->file_writer->write('</sheetView>');
        $sheet->file_writer->write('</sheetViews>');
        $sheet->file_writer->write('<cols>');
        $sheet->file_writer->write('<col collapsed="false" hidden="false" max="1025" min="1" style="0" width="11.5"/>');
        $sheet->file_writer->write('</cols>');
        $sheet->file_writer->write('<sheetData>');
    }
    public function writeSheetHeader($sheet_name, array$header_types) {
        if (empty($sheet_name) || empty($header_types) || !empty($this->sheets[$sheet_name])) return;
        self::initializeSheet($sheet_name);
        $sheet = &$this->sheets[$sheet_name];
        $sheet->cell_formats = array_values($header_types);
        $header_row = array_keys($header_types);
        $sheet->file_writer->write('<row collapsed="false" customFormat="false" customHeight="false" hidden="false" ht="12.1" outlineLevel="0" r="' . (1) . '">');
        foreach($header_row as $k => $v) {
            $this->writeCell($sheet->file_writer, 0, $k, $v, $cell_format = 'string');
        }
        $sheet->file_writer->write('</row>');
        $sheet->row_count++;
        $this->current_sheet = $sheet_name;
    }
    public function writeSheetRow($sheet_name, array$row) {
        if (empty($sheet_name) || empty($row)) return;
        self::initializeSheet($sheet_name);
        $sheet = &$this->sheets[$sheet_name];
        if (empty($sheet->cell_formats)) {
            $sheet->cell_formats = array_fill(0, count($row), 'string');
        }
        $sheet->file_writer->write('<row collapsed="false" customFormat="false" customHeight="false" hidden="false" ht="12.1" outlineLevel="0" r="' . ($sheet->row_count+1) . '">');
        foreach($row as $k => $v) {
            $this->writeCell($sheet->file_writer, $sheet->row_count, $k, $v, $sheet->cell_formats[$k]);
        }
        $sheet->file_writer->write('</row>');
        $sheet->row_count++;
        $this->current_sheet = $sheet_name;
    }
    public function writeSheetRowBold($sheet_name, array$row) {
        if (empty($sheet_name) || empty($row)) return;
        self::initializeSheet($sheet_name);
        $sheet = &$this->sheets[$sheet_name];
        if (empty($sheet->cell_formats)) {
            $sheet->cell_formats = array_fill(0, count($row), 'string');
        }
        $sheet->file_writer->write('<row collapsed="false" customFormat="false" customHeight="false" hidden="false" ht="12.1" outlineLevel="0" r="' . ($sheet->row_count+1) . '">');
        foreach($row as $k => $v) {
            $this->writeCellBold($sheet->file_writer, $sheet->row_count, $k, $v, $sheet->cell_formats[$k]);
        }
        $sheet->file_writer->write('</row>');
        $sheet->row_count++;
        $this->current_sheet = $sheet_name;
    }
    protected function finalizeSheet($sheet_name) {
        if (empty($sheet_name) || $this->sheets[$sheet_name]->finalized) return;
        $sheet = &$this->sheets[$sheet_name];
        $sheet->file_writer->write('</sheetData>');
        $sheet->file_writer->write('<printOptions headings="false" gridLines="false" gridLinesSet="true" horizontalCentered="false" verticalCentered="false"/>');
        $sheet->file_writer->write('<pageMargins left="0.5" right="0.5" top="1.0" bottom="1.0" header="0.5" footer="0.5"/>');
        $sheet->file_writer->write('<pageSetup blackAndWhite="false" cellComments="none" copies="1" draft="false" firstPageNumber="1" fitToHeight="1" fitToWidth="1" horizontalDpi="300" orientation="portrait" pageOrder="downThenOver" paperSize="1" scale="100" useFirstPageNumber="true" usePrinterDefaults="false" verticalDpi="300"/>');
        $sheet->file_writer->write('<headerFooter differentFirst="false" differentOddEven="false">');
        $sheet->file_writer->write('<oddHeader>&amp;C&amp;&quot;Times New Roman,Regular&quot;&amp;12&amp;A</oddHeader>');
        $sheet->file_writer->write('<oddFooter>&amp;C&amp;&quot;Times New Roman,Regular&quot;&amp;12Page &amp;P</oddFooter>');
        $sheet->file_writer->write('</headerFooter>');
        $sheet->file_writer->write('</worksheet>');
        $max_cell = self::xlsCell($sheet->row_count-1, count($sheet->cell_formats) -1);
        $max_cell_tag = '<dimension ref="A1:' . $max_cell . '"/>';
        $padding_length = $sheet->max_cell_tag_end-$sheet->max_cell_tag_start-strlen($max_cell_tag);
        $sheet->file_writer->fseek($sheet->max_cell_tag_start);
        $sheet->file_writer->write($max_cell_tag . str_repeat(" ", $padding_length));
        $sheet->file_writer->close();
        $sheet->finalized = true;
    }
    public function writeSheet(array$data, $sheet_name = '', array$header_types = array()) {
        $sheet_name = empty($sheet_name) ? 'Sheet1' : $sheet_name;
        $data = empty($data) ? array(array('')) : $data;
        if (!empty($header_types)) {
            $this->writeSheetHeader($sheet_name, $header_types);
        }
        foreach($data as $i => $row) {
            $this->writeSheetRow($sheet_name, $row);
        }
        $this->finalizeSheet($sheet_name);
    }
    protected function writeCell(XLSXWriter_BuffererWriter&$file, $row_number, $column_number, $value, $cell_format) {
        static $styles = array('money' => 1, 'dollar' => 1, 'datetime' => 2, 'date' => 3, 'string' => 0);
        $cell = self::xlsCell($row_number, $column_number);
        $s = isset($styles[$cell_format]) ? $styles[$cell_format] : '0';
        if (!is_scalar($value) || $value == '') { //objects, array, empty
            $file->write('<c r="' . $cell . '" s="' . $s . '"/>');
            #Excel Datetime is a pain.
            # } elseif ($cell_format=='date' or date("Y-m-d",strtotime("$value")) =="$value") {
            #} elseif ($cell_format=='date') {
            #	$value = date("Y-m-d 00:00:00",strtotime($value)) ;
            #	print "d: $value\n" ;
            #	$file->write('<c r="'.$cell.'" s="'.$s.'" t="n"><v>'.intval(self::convert_date_time($value)).'</v></c>');
            #} elseif ($cell_format=='datetime' or date("Y-m-d",strtotime("$value")) =="$value") {
            #	$value = date("Y-m-d 00:00:00",strtotime($value)) ;
            #	print "d: $value\n" ;
            #	$file->write('<c r="'.$cell.'" s="'.$s.'" t="n"><v>'.self::convert_date_time($value).'</v></c>');
            
        } elseif (!is_string($value)) {
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="n"><v>' . ($value*1) . '</v></c>'); //int,float, etc
            
        } elseif ($value{0} != '0' && is_numeric($value)) { //excel wants to trim leading zeros
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="n"><v>' . ($value*1) . '</v></c>'); //int,float, etc
            
        } elseif ($value == '0.00' or $value == '0.0') { //excel wants to trim leading zeros
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="n"><v>' . ($value*1) . '</v></c>'); //int,float, etc
            
        } elseif ($value{0} == '=') {
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="s"><f>' . self::xmlspecialchars($value) . '</f></c>');
        } elseif ($value !== '') {
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="s"><v>' . self::xmlspecialchars($this->setSharedString($value)) . '</v></c>');
        }
    }
    protected function writeCellBold(XLSXWriter_BuffererWriter&$file, $row_number, $column_number, $value, $cell_format) {
        static $styles = array('money' => 1, 'dollar' => 1, 'datetime' => 2, 'date' => 3, 'string' => 0);
        $cell = self::xlsCell($row_number, $column_number);
        $s = isset($styles[$cell_format]) ? $styles[$cell_format] : '0';
        if (!is_scalar($value) || $value == '') { //objects, array, empty
            $file->write('<c r="' . $cell . '" s="' . $s . '"/>');
        } elseif ($cell_format == 'date') {
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="n"><v>' . intval(self::convert_date_time($value)) . '</v></c>');
        } elseif ($cell_format == 'datetime') {
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="n"><v>' . self::convert_date_time($value) . '</v></c>');
        } elseif (!is_string($value)) {
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="n"><v>' . ($value*1) . '</v></c>'); //int,float, etc
            
        } elseif ($value{0} != '0' && is_numeric($value)) { //excel wants to trim leading zeros
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="n"><v>' . ($value*1) . '</v></c>'); //int,float, etc
            
        } elseif ($value == '0.00' or $value == '0.0') { //excel wants to trim leading zeros
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="n"><v>' . ($value*1) . '</v></c>'); //int,float, etc
            
        } elseif ($value{0} == '=') {
            $file->write('<c r="' . $cell . '" s="' . $s . '" t="s"><f>' . self::xmlspecialchars($value) . '</f></c>');
        } elseif ($value !== '') {
            $file->write('<c r="' . $cell . '" s="' . '4' . '" t="s"><v>' . self::xmlspecialchars($this->setSharedString($value)) . '</v></c>');
        }
    }
    protected function writeStylesXML() {
        $temporary_filename = $this->tempFilename();
        $file = new XLSXWriter_BuffererWriter($temporary_filename);
        $file->write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
        $file->write('<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
        $file->write('<numFmts count="4">');
        $file->write('<numFmt formatCode="GENERAL" numFmtId="164"/>');
        $file->write('<numFmt formatCode="[$$-1009]#,##0.00;[RED]\-[$$-1009]#,##0.00" numFmtId="165"/>');
        $file->write('<numFmt formatCode="YYYY-MM-DD\ HH:MM:SS" numFmtId="166"/>');
        $file->write('<numFmt formatCode="YYYY-MM-DD" numFmtId="167"/>');
        $file->write('</numFmts>');
        $file->write('<fonts count="5">');
        $file->write('<font><b val="0"/><name val="Arial"/><charset val="1"/><family val="2"/><sz val="10"/></font>');
        $file->write('<font><b val="0"/><name val="Arial"/><family val="0"/><sz val="10"/></font>');
        $file->write('<font><b val="0"/><name val="Arial"/><family val="0"/><sz val="10"/></font>');
        $file->write('<font><b val="0"/><name val="Arial"/><family val="0"/><sz val="10"/></font>');
        $file->write('<font><b val="1"/><name val="Arial"/><charset val="1"/><family val="2"/><sz val="10"/></font>');
        $file->write('</fonts>');
        $file->write('<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>');
        $file->write('<borders count="2"><border diagonalDown="false" diagonalUp="false"><left/><right/><top/><bottom/><diagonal/></border><border diagonalDown="false" diagonalUp="false" LineStyle="Continuous" Position="Bottom" Weight="3"><left/><right/><top/><bottom/><diagonal/></border></borders>');
        $file->write('<cellStyleXfs count="20">');
        $file->write('<xf applyAlignment="true" applyBorder="true" applyFont="true" applyProtection="true" borderId="0" fillId="0" fontId="0" numFmtId="164">');
        $file->write('<alignment horizontal="general" indent="0" shrinkToFit="false" textRotation="0" vertical="bottom" wrapText="false"/>');
        $file->write('<protection hidden="false" locked="true"/>');
        $file->write('</xf>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="2" numFmtId="0"/>');
        $file->write('<xf applyAlignment="true" applyBorder="true" applyFont="true" applyProtection="true" borderId="0" fillId="0" fontId="0" numFmtId="168">');
        $file->write('<alignment horizontal="general" indent="0" shrinkToFit="false" textRotation="0" vertical="bottom" wrapText="false"/>');
        $file->write('<protection hidden="false" locked="true"/>');
        $file->write('</xf>');
        #		$file->write(		'<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="2" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="43"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="41"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="44"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="42"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="true" applyProtection="false" borderId="0" fillId="0" fontId="1" numFmtId="9"/>');
        $file->write('</cellStyleXfs>');
        $file->write('<cellXfs count="5">');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="false" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="164" xfId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="false" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="165" xfId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="false" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="166" xfId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="false" applyProtection="false" borderId="0" fillId="0" fontId="0" numFmtId="167" xfId="0"/>');
        $file->write('<xf applyAlignment="false" applyBorder="false" applyFont="false" applyProtection="false" borderId="1" fillId="0" fontId="4" numFmtId="168" xfId="0"/>');
        $file->write('</cellXfs>');
        $file->write('<cellStyles count="6">');
        $file->write('<cellStyle builtinId="0" customBuiltin="false" name="Normal" xfId="0"/>');
        $file->write('<cellStyle builtinId="3" customBuiltin="false" name="Comma" xfId="15"/>');
        $file->write('<cellStyle builtinId="6" customBuiltin="false" name="Comma [0]" xfId="16"/>');
        $file->write('<cellStyle builtinId="4" customBuiltin="false" name="Currency" xfId="17"/>');
        $file->write('<cellStyle builtinId="7" customBuiltin="false" name="Currency [0]" xfId="18"/>');
        $file->write('<cellStyle builtinId="5" customBuiltin="false" name="Percent" xfId="19"/>');
        $file->write('</cellStyles>');
        $file->write('</styleSheet>');
        $file->close();
        return $temporary_filename;
    }
    protected function setSharedString($v) {
        if (isset($this->shared_strings[$v])) {
            $string_value = $this->shared_strings[$v];
        } else {
            $string_value = count($this->shared_strings);
            $this->shared_strings[$v] = $string_value;
        }
        $this->shared_string_count++; //non-unique count
        return $string_value;
    }
    protected function writeSharedStringsXML() {
        $temporary_filename = $this->tempFilename();
        $file = new XLSXWriter_BuffererWriter($temporary_filename, $fd_flags = 'w', $check_utf8 = true);
        $file->write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
        $file->write('<sst count="' . ($this->shared_string_count) . '" uniqueCount="' . count($this->shared_strings) . '" xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
        foreach($this->shared_strings as $s => $c) {
            $file->write('<si><t>' . self::xmlspecialchars($s) . '</t></si>');
        }
        $file->write('</sst>');
        $file->close();
        return $temporary_filename;
    }
    protected function buildAppXML() {
        $app_xml = "";
        $app_xml.= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $app_xml.= '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><TotalTime>0</TotalTime></Properties>';
        return $app_xml;
    }
    protected function buildCoreXML() {
        $core_xml = "";
        $core_xml.= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $core_xml.= '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        $core_xml.= '<dcterms:created xsi:type="dcterms:W3CDTF">' . date("Y-m-d\TH:i:s.00\Z") . '</dcterms:created>'; //$date_time = '2014-10-25T15:54:37.00Z';
        $core_xml.= '<dc:creator>' . self::xmlspecialchars($this->author) . '</dc:creator>';
        $core_xml.= '<cp:revision>0</cp:revision>';
        $core_xml.= '</cp:coreProperties>';
        return $core_xml;
    }
    protected function buildRelationshipsXML() {
        $rels_xml = "";
        $rels_xml.= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rels_xml.= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $rels_xml.= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
        $rels_xml.= '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>';
        $rels_xml.= '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>';
        $rels_xml.= "\n";
        $rels_xml.= '</Relationships>';
        return $rels_xml;
    }
    protected function buildWorkbookXML() {
        $i = 0;
        $workbook_xml = "";
        $workbook_xml.= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $workbook_xml.= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $workbook_xml.= '<fileVersion appName="Calc"/><workbookPr backupFile="false" showObjects="all" date1904="false"/><workbookProtection/>';
        $workbook_xml.= '<bookViews><workbookView activeTab="0" firstSheet="0" showHorizontalScroll="true" showSheetTabs="true" showVerticalScroll="true" tabRatio="212" windowHeight="8192" windowWidth="16384" xWindow="0" yWindow="0"/></bookViews>';
        $workbook_xml.= '<sheets>';
        foreach($this->sheets as $sheet_name => $sheet) {
            $workbook_xml.= '<sheet name="' . self::xmlspecialchars($sheet->sheetname) . '" sheetId="' . ($i+1) . '" state="visible" r:id="rId' . ($i+2) . '"/>';
            $i++;
        }
        $workbook_xml.= '</sheets>';
        $workbook_xml.= '<calcPr iterateCount="100" refMode="A1" iterate="false" iterateDelta="0.001"/></workbook>';
        return $workbook_xml;
    }
    protected function buildWorkbookRelsXML() {
        $i = 0;
        $wkbkrels_xml = "";
        $wkbkrels_xml.= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $wkbkrels_xml.= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $wkbkrels_xml.= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        foreach($this->sheets as $sheet_name => $sheet) {
            $wkbkrels_xml.= '<Relationship Id="rId' . ($i+2) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/' . ($sheet->xmlname) . '"/>';
            $i++;
        }
        if (!empty($this->shared_strings)) {
            $wkbkrels_xml.= '<Relationship Id="rId' . (count($this->sheets) +2) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
        }
        $wkbkrels_xml.= "\n";
        $wkbkrels_xml.= '</Relationships>';
        return $wkbkrels_xml;
    }
    protected function buildContentTypesXML() {
        $content_types_xml = "";
        $content_types_xml.= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $content_types_xml.= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $content_types_xml.= '<Override PartName="/_rels/.rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $content_types_xml.= '<Override PartName="/xl/_rels/workbook.xml.rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        foreach($this->sheets as $sheet_name => $sheet) {
            $content_types_xml.= '<Override PartName="/xl/worksheets/' . ($sheet->xmlname) . '" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        if (!empty($this->shared_strings)) {
            $content_types_xml.= '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
        }
        $content_types_xml.= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $content_types_xml.= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $content_types_xml.= '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>';
        $content_types_xml.= '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>';
        $content_types_xml.= "\n";
        $content_types_xml.= '</Types>';
        return $content_types_xml;
    }
    //------------------------------------------------------------------
    /*
    * @param $row_number int, zero based
    * @param $column_number int, zero based
    * @return Cell label/coordinates, ex: A1, C3, AA42
    * */
    public static function xlsCell($row_number, $column_number) {
        $n = $column_number;
        for ($r = "";$n >= 0;$n = intval($n/26) -1) {
            $r = chr($n%26+0x41) . $r;
        }
        return $r . ($row_number+1);
    }
    //------------------------------------------------------------------
    public static function log($string) {
        file_put_contents("php://stderr", date("Y-m-d H:i:s:") . rtrim(is_array($string) ? json_encode($string) : $string) . "\n");
    }
    //------------------------------------------------------------------
    public static function sanitize_filename($filename) //http://msdn.microsoft.com/en-us/library/aa365247%28VS.85%29.aspx
    {
        $nonprinting = array_map('chr', range(0, 31));
        $invalid_chars = array('<', '>', '?', '"', ':', '|', '\\', '/', '*', '&');
        $all_invalids = array_merge($nonprinting, $invalid_chars);
        return str_replace($all_invalids, "", $filename);
    }
    //------------------------------------------------------------------
    public static function xmlspecialchars($val) {
        return str_replace("'", "&#39;", htmlspecialchars($val));
    }
    //------------------------------------------------------------------
    public static function array_first_key(array$arr) {
        reset($arr);
        $first_key = key($arr);
        return $first_key;
    }
    //------------------------------------------------------------------
    public static function convert_date_time($date_input) //thanks to Excel::Writer::XLSX::Worksheet.pm (perl)
    {
        $days = 0; # Number of days since epoch
        $seconds = 0; # Time expressed as fraction of 24h hours in seconds
        $year = $month = $day = 0;
        $hour = $min = $sec = 0;
        $date_time = $date_input;
        if (preg_match("/(\d{4})\-(\d{2})\-(\d{2})/", $date_time, $matches)) {
            list($junk, $year, $month, $day) = $matches;
        }
        if (preg_match("/(\d{2}):(\d{2}):(\d{2})/", $date_time, $matches)) {
            list($junk, $hour, $min, $sec) = $matches;
            $seconds = ($hour*60*60+$min*60+$sec) /(24*60*60);
        }
        //using 1900 as epoch, not 1904, ignoring 1904 special case
        # Special cases for Excel.
        if ("$year-$month-$day" == '1899-12-31') return $seconds; # Excel 1900 epoch
        if ("$year-$month-$day" == '1900-01-00') return $seconds; # Excel 1900 epoch
        if ("$year-$month-$day" == '1900-02-29') return 60+$seconds; # Excel false leapday
        # We calculate the date by calculating the number of days since the epoch
        # and adjust for the number of leap days. We calculate the number of leap
        # days by normalising the year in relation to the epoch. Thus the year 2000
        # becomes 100 for 4 and 100 year leapdays and 400 for 400 year leapdays.
        $epoch = 1900;
        $offset = 0;
        $norm = 300;
        $range = $year-$epoch;
        # Set month days and check for leap year.
        $leap = (($year%400 == 0) || (($year%4 == 0) && ($year%100))) ? 1 : 0;
        $mdays = array(31, ($leap ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        # Some boundary checks
        if ($year < $epoch || $year > 9999) return 0;
        if ($month < 1 || $month > 12) return 0;
        if ($day < 1 || $day > $mdays[$month-1]) return 0;
        # Accumulate the number of days since the epoch.
        $days = $day; # Add days for current month
        $days+= array_sum(array_slice($mdays, 0, $month-1)); # Add days for past months
        $days+= $range*365; # Add days for past years
        $days+= intval(($range) /4); # Add leapdays
        $days-= intval(($range+$offset) /100); # Subtract 100 year leapdays
        $days+= intval(($range+$offset+$norm) /400); # Add 400 year leapdays
        $days-= $leap; # Already counted above
        # Adjust for Excel erroneously treating 1900 as a leap year.
        if ($days > 59) {
            $days++;
        }
        return $days+$seconds;
    }
    //------------------------------------------------------------------
    
}
class XLSXWriter_BuffererWriter {
    protected $fd = null;
    protected $buffer = '';
    protected $check_utf8 = false;
    public function __construct($filename, $fd_fopen_flags = 'w', $check_utf8 = false) {
        $this->check_utf8 = $check_utf8;
        $this->fd = fopen($filename, $fd_fopen_flags);
        if ($this->fd === false) {
            XLSXWriter::log("Unable to open $filename for writing.");
        }
    }
    public function write($string) {
        $this->buffer.= $string;
        if (isset($this->buffer[8191])) {
            $this->purge();
        }
    }
    protected function purge() {
        if ($this->fd) {
            if ($this->check_utf8 && !self::isValidUTF8($this->buffer)) {
                XLSXWriter::log("Error, invalid UTF8 encoding detected.");
                $this->check_utf8 = false;
            }
            fwrite($this->fd, $this->buffer);
            $this->buffer = '';
        }
    }
    public function close() {
        $this->purge();
        if ($this->fd) {
            fclose($this->fd);
            $this->fd = null;
        }
    }
    public function __destruct() {
        $this->close();
    }
    public function ftell() {
        if ($this->fd) {
            $this->purge();
            return ftell($this->fd);
        }
        return -1;
    }
    public function fseek($pos) {
        if ($this->fd) {
            $this->purge();
            return fseek($this->fd, $pos);
        }
        return -1;
    }
    protected static function isValidUTF8($string) {
        if (function_exists('mb_check_encoding')) {
            return mb_check_encoding($string, 'UTF-8') ? true : false;
        }
        return preg_match("//u", $string) ? true : false;
    }
}
?>
