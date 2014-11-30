<?php
function gltables() {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    print "<h1>TABLES</h1>";
    print "\n<form class='form-horizontal noprint' ACTION='glass.php' METHOD='get'>
    <INPUT TYPE='hidden' NAME='mode' VALUE='tables'>
    <INPUT TYPE='hidden' NAME='submode' VALUE='runsqlinsanity'>
    <textarea name='query' rows=3 cols=150></textarea>
    <br>
    <input name=action type=submit value='Run SQL' class=button></form>\n";
    #ini_set('display_errors', 1);
    #  ini_set('display_startup_errors',1);
    #error_reporting(-1);
    if ($submode == '' or submode == 'tables') {
        gltablemenu();
    };
    if ($submode == 'browse') {
        gltablebrowse(dt($_REQUEST['table']), dt($_REQUEST['start']), dt($_REQUEST['records']), dt($_REQUEST['sortby']));
    };
    if ($submode == 'add') {
        $table = dt($_REQUEST['table']) ; 
        $temptable = "zadd_$table" ; 
        $q = "create temporary table zadd_$table like $table" ; 
        runsql("$q") ; 
        runsql("insert into $temptable (uniq) values ('1')") ; 
        print gltable(gaaafm("select * from $temptable")) ; 
        gltableedit($temptable,'1','edit', 'vert','zadd' );
    
    };
    if ($submode == 'edit') {
        gltableedit(dt($_REQUEST['table']), dt($_REQUEST['uniq']),'edit', 'vert','select account,name from customers where uniq = 2' );
    };
    if ($submode == 'runsqlinsanity') {
        # this should not be allowed
        #$q = mysqli_escape_string($db,$_REQUEST['query']) ;
        $q = $_REQUEST['query'];
        if (preg_match("/delete/i", $q)) {
            print "<p>delete not allowed</p>";
        } else {
            if (strlen($q) > 4) {
                print "Query: <pre><b>$q</b></pre>";
                print gltable(gaaafm("$q"), array());
                #print gaaafm("$q") ;
                
            };
        };
    };
};


function gltableedit($table, $uniq, $editmode, $horvert,$options) {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    if($subsubsubmode == 'zadd') {
        $strip = array('/zadd\_/') ;
        $table = preg_replace($strip, '', $table) ;
        #print "New table name: $table <br>" ; 
        $query = "insert into $table (uniq) values ('')" ; 
        $result = $db->query($query) or die("Insert failed, probably duplicating an existing record");
        $uniq = mysqli_insert_id($db) ; 
        #print "Table: $table Uniq: $uniq" ; 
        if($uniq < 1) { print "Error inserting record" ; return ; } ; 
        $subsubmode = 'save' ; 
    } ; 

    if($subsubmode == 'save') {
        while (list($key, $val) = each($_REQUEST)) {
            if (substr($key, 0, 3) == "fa_") {
                #print "Saving: $val <BR>" ;
                $itemstring .= "$val|";
                if ($val == 'CLEAR') {
                    $itemstring = '|';
                };
            };

            if (substr($key, 0, 2) == "f_") {
               # $j = split("[\_]", $key);
                $j = preg_split('/\_/',$key) ;                 
                if($j[3] != '' or $j[4] != '') {
                    $j[2] .= '_' . $j[3] ; #Allows field names with space ; 
                } ; 
                if($j[4] != '') {
                    $j[2] .= '_' . $j[4] ; 
                } ; 
                if($j[5] != '') {
                    $j[2] .= '_' . $j[5] ; 
                } ; 
                if (is_array($val)) { #Saves ARRAY Data, concats string
                    $vv = "";
                    foreach($val as $v) {
                        $vv.= "$v";
                    };
                    $q = "update $table set $j[2] = '$vv' where uniq = $j[1]";
                } else { #normal text save
                    $val = stripslashes($val);
                    #$val = ereg_replace("'", "&apos;", $val);
                    $replace = array('/&#39;/','/&apos;/');
                    $val = preg_replace($replace, '\'', $val);
                    if($subsubsubmode == 'zadd') { 
                        if($j[2] == 'uniq') { 
                        $q = "select now() " ; 
                        } else {
                        $q = "UPDATE $table set `$j[2]` = '$val' where uniq = '$uniq'";
                        } ; 
                    } else {
                        $q = "update $table set `$j[2]` = '$val' where uniq = '$j[1]'";
                    } ; 
                };
                #print "<pre>$q</pre>" ; 
                $f = mysqli_query($db,$q) or die("Query failed : <p>$q<p>" . mysqli_error());
                $start = $j[1];
            };
        };
        if ($itemstring != '') {  # used for fa_ items that create a Ghetto Normalized Data Set. 
            $query = "update $_REQUEST[sourcetable] set $_REQUEST[sourcefield] = '$itemstring' where uniq = '$_REQUEST[sourceuniq]' and portal = '$portal'";
            $result = mysqli_query($db,$query) or die("Query failed : " . mysqli_error());
        };

} ; 

    include("settings.inc") ; 

   # $q = "select * from INFORMATION_SCHEMA.KEY_COLUMN_USAGE where TABLE_NAME = '$table' and CONSTRAINT_SCHEMA = '$database'" ; 
   # print gltable(gaaafm("$q")) ; 
   # print_r(gaaafm("$q")) ; 

    $query = "select * from $table where uniq = '$uniq'" ;

    print "<pre>$query</pre>" ;    

    print "<form>" ; 
    print "<input type=hidden name=mode value='tables'>" ; 
    print "<input type=hidden name=submode value='edit'>" ; 
    print "<input type=hidden name=subsubmode value='save'>" ; 
    print "<input type=hidden name=table value='$table'>" ; 
    print "<input type=hidden name=uniq value='$uniq'>" ; 
    
    if($options == 'zadd') { 
        print "<input type=hidden name=subsubsubmode value='zadd'>ZADDMODE" ; 
    } ; 

    print "<table cellpadding=0 cellspacing=0 style='margin:0px;padding:0px;'>" ;     

    if($subsubsubmode == 'zadd') { 
    } else {
    print "<td><A HREF='glass.php?mode=tables&submode=add&table=$table' class=button style='font-size:large;'>New</A></td>" ; 
    } ; 
    print "<td><input type=submit name=action value='Save' class=button style='font-size:large;'></td>" ; 
    print "</table>" ; 

    print "<table>" ; 
    #If "hor" creates a title bar
    if($horvert == 'hor') { 
        $h = 0 ; 
        $result = mysqli_query($db,$query) or die("Query failed : $query <p>" . mysqli_error());
        while ($row = mysqli_fetch_assoc($result) and $h < 1) {
            print "<tr>" ; 
            while (list($key, $val) = each($row)) {
                 print "<td>$key</td>" ;                
            } ; 
            print "</tr>" ; 
            $h++ ; 
        } ; 
    } ; 
    #end title bar
    

    $result = mysqli_query($db,$query) or die("Query failed : $query <p>" . mysqli_error());

#    $finfo = mysqli_fetch_field_direct($result,1) ; 
    $finfo = mysqli_fetch_fields($result) ; 

#    glprintr($finfo) ; 
    
    $c = 0 ; 
    while ($row = mysqli_fetch_assoc($result)) {

        if($horvert == 'hor') { print "<tr>" ; } ; 

        while (list($key, $val) = each($row)) {
#-------------------------------------------------------        
        if($key == 'uniq') { $uniq = $val ; } ; 


        if($horvert == 'vert') { print "<tr><td>$key</td>" ; } ; 
            $length = $finfo[$c]->length ; 
            $type = $finfo[$c]->type ; 
            $decimal = $finfo[$c]->decimal ; 

   # $q = "select * from INFORMATION_SCHEMA.KEY_COLUMN_USAGE where TABLE_NAME = '$table' and CONSTRAINT_SCHEMA = '$database'" ; 
   # print gltable(gaaafm("$q")) ; 
   # print_r(gaaafm("$q")) ; 
   
   list($column,$constraint,$reftable,$refcolumn) = gafm("select COLUMN_NAME,CONSTRAINT_NAME,REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME from INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
   where TABLE_NAME = '$table' and CONSTRAINT_SCHEMA = '$database' and COLUMN_NAME = '$key'") ; 
   

            if($key == 'uniq') { #The master key, not editable. 
                print "<td><input name=\"f.$uniq.$key\" value=\"" . $val . "\" READONLY></td>" ;
                         
            } elseif (!empty($reftable) and !empty($refcolumn)) { 
            
            print "<td>" ; 
            print "<select name=\"f.$uniq.$key\">" ; 
            print "<optgroup label='" . bbf('Current') ."'>";
            print "<OPTION VALUE='$val'>$val";
            print "<optgroup label='" . bbf('Available') ."'>";
            $q = "select distinct($refcolumn) from $reftable order by $refcolumn";
            $r = mysqli_query($db,$q) or die("Query failed : " . mysql_error());
                while (list($d) = mysqli_fetch_row($r)) {
                    print "<OPTION VALUE='$d'>$d";
                };
            print "</optgroup></select>\n";
            print "</td>" ; 
            } elseif ( "$column" == "$constraint" and "$column" == "$key") { #Things with constraints
                print "<td><input type=text name=\"f.$uniq.$key\" value=\"" . $val . "\" style='background:#FFFFFF;font-weight:bold;font-size:large;'></td>" ;         


            } elseif ($type == '252' and $horvert == 'vert') { #text
                print "<td><textarea name=\"f.$uniq.$key\" rows=4 cols='80' placeholder=''>$val</textarea></td>" ;         
            } elseif ($type == '252' and $horvert == 'hor') { #text
                print "<td><textarea name=\"f.$uniq.$key\" rows=1 cols='20' placeholder='$key'>$val</textarea></td>" ;         
            } elseif ($type == '7') { #DATE-TIMESTAMP
                print "<td><input type=datetime name=\"f.$uniq.$key\" value=\"" . $val . "\"></td>" ;         
            } elseif ($type == '10') { #DATE
                print "<td><input type=date name=\"f.$uniq.$key\" value=\"" . $val . "\"></td>" ;         
            } elseif ($type == '16') { #BIT
                print "<td><input type=date name=\"f.$uniq.$key\" value=\"" . $val . "\" style='font-family:fixed,courier;'></td>" ;         
            } elseif ($key == 'email' and $type == '253') { #DATE
                print "<td><input type=email name=\"f.$uniq.$key\" value=\"" . $val . "\" placeholder='e-mail'></td>" ;         

            } elseif ($type == '2' or $type == '3' or $type == '4' or $type == '5' or $type == '8' or $type == '9' or $type == '246' ) { #NUMBERS
                print "<td><input type=text name=\"f.$uniq.$key\" value=\"" . $val . "\" style='text-align:right;'></td>" ;         

            } else {
                print "<td><input type=text name=\"f.$uniq.$key\" value=\"" . $val . "\" placeholder='$key $type/$length' size='$length'></td>" ;         
            } ;
            

            $c++ ; 
        if($horvert == 'vert') { print "</tr>" ; } ; 
        
#-------------------------------------------------------    
        } ; 
        if($horvert == 'hor') { print "</tr>" ; } ; 
    } ; 
    print "</table>" ; 
    print "</form>" ; 



} ; 

function gltablebrowse($table, $start, $records, $sortby) {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    $records = '10';
    $q = '';
    $q.= "select * from $table";
    if (!empty($start)) {
        if (empty($sortby)) {
            $start = dtnum($start);
            $q.= " where $sortby >= '$start'";
        } else {
            $start = dtnum($start);
            $q.= " where uniq >= '$start'";
        };
    };
    if (!empty($sortby)) {
        $q.= " order by $sortby";
    };
    if (!empty($records)) {
        $records = dtnum($records);
        $q.= " limit $records";
    };
    print "<P>";
    print "<a href='glass.php?mode=tables&submode=browse&table=$table&sortby=uniq&start=1&records=$records'>Begin</A>";
    print "<a href='glass.php?mode=tables&submode=browse&table=$table&sortby=uniq&start=$start&records=$records'>Here</A>";
    list($next) = gafm("select max(uniq) from $table where uniq >= '$start' order by uniq limit $records");
    print "<a href='glass.php?mode=tables&submode=browse&table=$table&sortby=uniq&start=$next&records=$records'>Next</A>";
    print "</p>";
    print gltablebrowser(gaaafm("$q"), $table, $start, $sortby, $records);
};
function gltablemenu() {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    #*************************** 4. row ***************************
    #  TABLE_CATALOG: def
    #   TABLE_SCHEMA: glass
    #     TABLE_NAME: reports
    #     TABLE_TYPE: BASE TABLE
    #         ENGINE: InnoDB
    #        VERSION: 10
    #     ROW_FORMAT: Compact
    #     TABLE_ROWS: 2
    # AVG_ROW_LENGTH: 8192
    #    DATA_LENGTH: 16384
    #MAX_DATA_LENGTH: 0
    #   INDEX_LENGTH: 32768
    #      DATA_FREE: 10485760
    # AUTO_INCREMENT: 31
    #    CREATE_TIME: 2014-11-16 18:17:40
    #    UPDATE_TIME: NULL
    #     CHECK_TIME: NULL
    #TABLE_COLLATION: utf8_general_ci
    #       CHECKSUM: NULL
    # CREATE_OPTIONS:
    #  TABLE_COMMENT:
    #4 rows in set (0.00 sec)
    #mysql> select * from information_schema.tables where table_schema = 'glass' ;
    print gltablestables(gaaafm("select TABLE_NAME as `table`,  TABLE_ROWS as `rows`,  AUTO_INCREMENT as `increment`,
ENGINE as `engine`, DATA_LENGTH as `length`, INDEX_LENGTH as `index`, DATA_FREE as `free`  from information_schema.tables where table_schema = 'glass' order by `table`"), array());
};
function gltablestables($a, $toton) {
    $header = '<table>';
    $footer = '</table>';
    $th = '';
    $theader = '';
    $tfooter = '';
    $trow = '';
    $totals = array();
    foreach($a as $k => $v) {
        $trow.= '<tr>';
        foreach($v as $key => $val) {
            if (empty($theader)) {
                $th.= "<th>$key</th>";
                if ($key == 'table') {
                    $th.= "<th></th><th></th>";
                };
            }; //builder a table header ;
            if ($key == 'table') {
                $trow.= '<td width=100><b>' . $val . '</b></td>';
                $trow.= '<td>';
                $trow.= '<table><tr><td width=100><A HREF="glass.php?mode=tables&submode=browse&table=' . $val . '">Browse</A></td>
        <td width=100><A HREF="glass.php?mode=tables&submode=search&table=' . $val . '">Search</A></td>
        <td width=100><A HREF="glass.php?mode=tables&submode=add&table=' . $val . '">Add</A></td></tr></table>';
                $trow.= '</td><td>&nbsp;</td>';
                $table = $val;
            } elseif ($key == 'rows') {
                list($val) = gafm("select count(*) from `$table`");
                $trow.= '<td style="text-align:right;color:#000000;"><b>' . $val . '</b></td>';
            } else {
                $trow.= '<td style="text-align:right;color:#666666;">' . $val . '</td>';
            };
            if (@in_array($key, $toton)) {
                $totals["$key"]+= ($val*1);
            };
        };
        $trow.= '</tr>' . "\n";
        if (empty($theader)) {
            $theader = "<tr>$th</tr>\n";
        }; //builder a table header ;
        
    };
    if (!empty($totals)) { //a total line
        foreach($a as $k => $v) {
            $tfooter = '<tr>';
            foreach($v as $key => $val) {
                if (@in_array($key, $toton)) {
                    $tfooter.= "<td>$totals[$key]</td>";
                } else {
                    $tfooter.= '<td></td>';
                }
            };
            $tfooter.= '</tr>';
        };
    };
    return "$header\n$theader\n$trow\n$tfooter\n$footer";
};
function gltablebrowser($a, $table, $start, $sortby, $records) {
    $header = '<table>';
    $footer = '</table>';
    $th = '';
    $theader = '';
    $tfooter = '';
    $trow = '';
    $totals = array();
    foreach($a as $k => $v) {
        $trow.= '<tr>';
        foreach($v as $key => $val) {
            if (empty($theader)) {
                $th.= "<th><A HREF='glass.php?mode=tables&submode=browse&table=$table&start=$start&sortby=$key'>$key</th>";
            }; //builder a table header ;
            if ($key == 'uniq') {
                $trow.= "<td><a href='glass.php?mode=tables&submode=edit&table=$table&uniq=$val'>$val</a></td>";
            } else {
                if (strlen($val) < 21) {
                    $trow.= '<td>' . $val . '</td>';
                } else {
                    $trow.= '<td>' . substr($val, 0, 20) . '...</td>'; #needs a hover over for all the data.
                    
                };
            };
            if (@in_array($key, $toton)) {
                $totals["$key"]+= ($val*1);
            };
        };
        $trow.= '</tr>' . "\n";
        if (empty($theader)) {
            $theader = "<tr>$th</tr>\n";
        }; //builder a table header ;
        
    };
    if (!empty($totals)) { //a total line
        foreach($a as $k => $v) {
            $tfooter = '<tr>';
            foreach($v as $key => $val) {
                if (@in_array($key, $toton)) {
                    $tfooter.= "<td>$totals[$key]</td>";
                } else {
                    $tfooter.= '<td></td>';
                }
            };
            $tfooter.= '</tr>';
        };
    };
    return "$header\n$theader\n$trow\n$tfooter\n$footer";
};
?>
