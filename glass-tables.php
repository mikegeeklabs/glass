<?php
function gltables() {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    print "<h1>TABLES</h1>";
    print "\n<form class='form-horizontal noprint' ACTION='glass.php' METHOD='get'>
    <INPUT TYPE='hidden' NAME='mode' VALUE='tables'>
    <INPUT TYPE='hidden' NAME='submode' VALUE='runsqlinsanity'>
    <textarea name='query' rows=3 cols=150>$_REQUEST[query]</textarea>
    <br>
    <input name=action type=submit value='Run SQL' class=button></form>\n";
    #ini_set('display_errors', 1);
    #  ini_set('display_startup_errors',1);
    #error_reporting(-1);
    if ($submode == '' or submode == 'tables') {
        gltablemenu();
    };
    if ($submode == 'browse') {
          #  gltablebrowse(dt($_REQUEST['table']), dt($_REQUEST['start']), dt($_REQUEST['records']), dt($_REQUEST['sortby']));
            $uniq = dt($_REQUEST['uniq']);
            $table = dt($_REQUEST['table']);
            gltableedit(dt($_REQUEST['table']), dt($_REQUEST['uniq']), 'view', 'hor', "select * from `$table` limit 100");

    };
    if ($submode == 'add') {
            $uniq = dt($_REQUEST['uniq']);
            $table = dt($_REQUEST['table']);
    
        $temptable = "zadd_$table";
        $q = "create temporary table zadd_$table like $table";
        runsql("$q");
        runsql("insert into $temptable (uniq) values ('1')");
        #print gltable(gaaafm("select * from $temptable"));
        gltableedit($temptable, '1', 'edit', 'vert', 'zadd');
    };
    if ($submode == 'search') {
        gltablesearch();
    };
    if ($submode == 'delete') {
        $uniq = dtnum($_REQUEST['uniq']) ; 
        $table = dt($_REQUEST['table']) ; 
        $d = gaafm("select * from `$table` where uniq = '$uniq'") ; 
        gllog('DELETE',"deleting from $table\n" . print_r($d,1)) ; 
        runsql("delete from `$table` where uniq = '$uniq'") ; 
        gltableedit(dt($_REQUEST['table']), dt($_REQUEST['uniq']), 'edit', 'hor', "select * from `$table` where uniq > ($uniq - 5) and uniq < ($uniq + 5)");
    };
    if ($submode == 'edit') {
        gltableedit(dt($_REQUEST['table']), dt($_REQUEST['uniq']), 'edit', 'vert', '');
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
                #print gltable(gaaafm("$q"), array());
                #print gaaafm("$q") ;
                gltableedit('','', 'view', 'hor', "$q");
            };
        };
    };
};
function gltablesearch() {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    $table = dt($_REQUEST['table']);
    if ($subsubmode == '' or $subsubmode == 'form') {
    include ("settings.inc");
    # $q = "select * from INFORMATION_SCHEMA.KEY_COLUMN_USAGE where TABLE_NAME = '$table' and CONSTRAINT_SCHEMA = '$database'" ;
    # print gltable(gaaafm("$q")) ;
    # print_r(gaaafm("$q")) ;
    $query = "select * from $table limit 1";
    print "<pre>$query</pre>";
    print "<form>";
    print "<input type=hidden name=mode value='tables'>";
    print "<input type=hidden name=submode value='search'>";
    print "<input type=hidden name=subsubmode value='search'>";
    print "<input type=hidden name=table value='$table'>";
    print "<table cellpadding=0 cellspacing=0 style='margin:0px;padding:0px;'>";
    print "<td><A HREF='glass.php?mode=tables&submode=browse&table=$table' class=button style='font-size:large;'>Browse</A></td>";
    print "<td><input type=submit name=action value='Search' class=button style='font-size:large;'></td>";
    print "</table>";
    print "<table>";
    #If "hor" creates a title bar
    $horvert = 'vert' ; #might enable both modes later. 
    if ($horvert == 'hor') {
        $h = 0;
        $result = mysqli_query($db, $query) or die("Query failed : $query <p>" . mysqli_error());
        while ($row = mysqli_fetch_assoc($result) and $h < 1) {
            print "<tr>";
            while (list($key, $val) = each($row)) {
                list($ztable,$field,$display,$description,$specialformat,$placeholder) = 
                gafm("select `table`,`field`,`display`,`description`,`specialformat`,`placeholder` from fielddesc where `field` = '$key' and (`table` = '$table' or `table` ='*') order by `table` DESC") ; 
                if(!empty($display)) {
                    print "<td style='text-align:right;'><label for='$key'><span title='$key = $description'>" . bbf("$display") . "</span></label></td>";
                } else {
                    print "<td style='text-align:right;'>$key</td>";
                } ; 
            };
            print "</tr>";
            $h++;
        };
    };
    #end title bar
    $result = mysqli_query($db, $query) or die("Query failed : $query <p>" . mysqli_error());
    #    $finfo = mysqli_fetch_field_direct($result,1) ;
    $finfo = mysqli_fetch_fields($result);
    #    glprintr($finfo) ;
    $c = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        if ($horvert == 'hor') {
            print "<tr>";
        };
        while (list($key, $val) = each($row)) {
            #-------------------------------------------------------
            if ($key == 'uniq') {
                $uniq = $val;
            };
            if ($horvert == 'vert') {
           #     print "<tr><td>$key</td>";
            list($ztable,$field,$display,$description,$specialformat,$placeholder) = gafm("select `table`,`field`,`display`,`description`,`specialformat`,`placeholder` from fielddesc where `field` = '$key' and (`table` = '$table' or `table` ='*') order by `table` DESC") ; 

                if(!empty($display)) {
                    print "<tr><td style='text-align:right;'><label for='$key'><span title='$key = $description'>" . bbf("$display") . "</span></label></td>";
                } else {
                    print "<tr><td style='text-align:right;'>$key</td>";
                } ; 


            };
            $length = $finfo[$c]->length;
            $type = $finfo[$c]->type;
            $decimal = $finfo[$c]->decimal;
            # $q = "select * from INFORMATION_SCHEMA.KEY_COLUMN_USAGE where TABLE_NAME = '$table' and CONSTRAINT_SCHEMA = '$database'" ;
            # print gltable(gaaafm("$q")) ;
            # print_r(gaaafm("$q")) ;
            $strip = array('/zadd\_/');
            $realtable = preg_replace($strip, '', $table);
            list($column, $constraint, $reftable, $refcolumn) = gafm("select COLUMN_NAME,CONSTRAINT_NAME,REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME from INFORMATION_SCHEMA.KEY_COLUMN_USAGE where TABLE_NAME = '$realtable' and CONSTRAINT_SCHEMA = '$database' and COLUMN_NAME = '$key'");
                
                
            print "<td><input type=text name=\"f.$key\" value=\"\" placeholder='$key $type/$length' size='30'></td>";
            
            
            $c++;
            if ($horvert == 'vert') {
                print "</tr>";
            };
            #-------------------------------------------------------
            
        };
        if ($horvert == 'hor') {
            print "</tr>";
        };
    };
    print "</table>";
    print "</form>";
    }
    if ($subsubmode == 'search') {
    print "Searching" ; 
#    glprintr($_REQUEST) ;     
        while (list($key, $val) = each($_REQUEST)) {
            if (substr($key, 0, 2) == "f_" and !empty($val)) {
                        $strip = array('/f\_/');
                        $key = preg_replace($strip, '', $key);

                $select .= "`$key` like '%$val%' " ;             
            } ; 
        } ; 
        if(empty($select)) { 
            $select = "uniq like '%' limit 10" ; 
            print "Nothing was selected to search for" ; 
        } ;     
        
        $query = "select * from `$table` where $select" ; 
        print "<p>$query</p>" ; 
        gltableedit(dt($table),'', 'edit', 'hor', "$query");
        
    }
};
function gltableedit($table, $uniq, $editmode, $horvert, $options) {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    if ($subsubsubmode == 'zadd') {
        $strip = array('/zadd\_/');
        $table = preg_replace($strip, '', $table);
        #print "New table name: $table <br>" ;
        $query = "insert into $table (uniq) values ('')";
        $result = $db->query($query) or die("Insert failed, probably duplicating an existing record");
        $uniq = mysqli_insert_id($db);
        #print "Table: $table Uniq: $uniq" ;
        if ($uniq < 1) {
            print "Error inserting record";
            return;
        };
        $subsubmode = 'save';
    };
    if ($subsubmode == 'save') {
        #glprintr($_REQUEST) ; 
        while (list($key, $val) = each($_REQUEST)) {
            if (substr($key, 0, 3) == "fa_") {
                #print "Saving: $val <BR>" ;
                $itemstring.= "$val|";
                if ($val == 'CLEAR') {
                    $itemstring = '|';
                };
            };
            if (substr($key, 0, 3) == "fx_") {
                $newval = '' ; 
                while(list($k, $v) = each($val)) {
                    $newval .= "$v " ;
                } ; 
                $j = preg_split('/\_/', $key);
                $q = "update $table set `$j[2]` = '$newval' where uniq = '$j[1]'";
                runsql($q) ; 

            };
            
            if (substr($key, 0, 2) == "f_") {
                # $j = split("[\_]", $key);
                $j = preg_split('/\_/', $key);
                if ($j[3] != '' or $j[4] != '') {
                    $j[2].= '_' . $j[3]; #Allows field names with space ;
                };
                if ($j[4] != '') {
                    $j[2].= '_' . $j[4];
                };
                if ($j[5] != '') {
                    $j[2].= '_' . $j[5];
                };
                if (is_array($val)) { #Saves ARRAY Data, concats string
                    $vv = "";
                    foreach($val as $v) {
                        $vv.= "$v";
                    };
                    $q = "update $table set $j[2] = '$vv' where uniq = $j[1]";
                } else { #normal text save
                    $val = stripslashes($val);
                    #$val = ereg_replace("'", "&apos;", $val);
                    #$replace = array('/&#39;/', '/&apos;/');
                    #$val = preg_replace($replace, '\'', $val);
                    $replace = array('/\'/');
                    $val = preg_replace($replace, '\&apos;', $val);
                    
                    if ($subsubsubmode == 'zadd') {
                        if ($j[2] == 'uniq') {
                            $q = "select now() ";
                        } else {
                            $q = "UPDATE $table set `$j[2]` = '$val' where uniq = '$uniq'";
                        };
                    } else {
                    
                        if($j[2] == 'passwd' and strlen($val) < 40) { 
                            #$val = password_hash($val, PASSWORD_DEFAULT) ; #mysql 5.5+
                            $val = glpasswdhash($val) ; #mysql < 5.5
                        } ; 
                    
                    
                        $q = "update $table set `$j[2]` = '$val' where uniq = '$j[1]'";
                        
                        
                        
                    };
                };
                #print "<pre>$q</pre>" ;
                $f = mysqli_query($db, $q) or die("Query failed : <p>$q<p>" . mysqli_error());
                $start = $j[1];
            };
        };
        if ($itemstring != '') { # used for fa_ items that create a Ghetto Normalized Data Set or 2 parts like datetime
           # $query = "update $_REQUEST[sourcetable] set $_REQUEST[sourcefield] = '$itemstring' where uniq = '$_REQUEST[sourceuniq]' and portal = '$portal'";
           # $result = mysqli_query($db, $query) or die("Query failed : " . mysqli_error());
        };
    };
    include ("settings.inc");
    #QUERY LOGIC
    if(strlen($options) > 10) { 
        $query = $options ; 
    } else {
        $query = "SELECT * from $table where uniq = '$uniq'";
    } ;     
    if(empty($table)) { #try to guess the table
        if(preg_match_all('/((from|join|update) `(.*)`)/', $query, $matches)) {
            $tables = array_unique($matches[3]);
            list($table) = preg_split("/[\s,]+/",$tables[0]);
        }
    } ; 
    if(empty($table)) { #try to guess the table
        if(preg_match_all('/((from|join|update) (.*))/', $query, $matches)) {
            $tables = array_unique($matches[3]);
            list($table) = preg_split("/[\s,]+/",$tables[0]);
        }
    } ; 


    print "<pre>$query</pre>";
    
    $tabledesc = gaafm("select * from tabledesc where `table` = '$table'") ; 
    #glprintr($tabledesc) ; 
    print "<form>";
    print "<input type=hidden name=mode value='tables'>";
    print "<input type=hidden name=submode value='edit'>";
    print "<input type=hidden name=subsubmode value='save'>";
    print "<input type=hidden name=table value='$table'>";
    print "<input type=hidden name=uniq value='$uniq'>";
    if ($options == 'zadd') {
        print "<input type=hidden name=subsubsubmode value='zadd'>";
    };
    print "<table cellpadding=0 cellspacing=0 style='margin:0px;padding:0px;'>";

    if(!empty($tabledesc[table]) ) {
        print "<caption><span title='$tabledesc[description]'>$table / $tabledesc[display]</span></caption>" ; 
        print "<tr><td>&nbsp;</td></tr>" ; //should be removed with better CSS. 
    } ; 


    if ($options == 'zadd') {
    } else {
        if($horvert != 'hor') {
        print "<td><A HREF='glass.php?mode=tables&submode=delete&table=$table&uniq=$uniq' class=button style='font-size:large;background:#ffdddd;'>Delete</A></td>";
        } ; 
        print "<td><A HREF='glass.php?mode=tables&submode=add&table=$table' class=button style='font-size:large;'>New</A></td>";
    };
    print "<td><A HREF='glass.php?mode=tables&submode=browse&table=$table' class=button style='font-size:large;'>Browse</A></td>";
    print "<td><A HREF='glass.php?mode=tables&submode=search&table=$table' class=button style='font-size:large;'>Search</A></td>";
    if(!empty($tabledesc[specialformat]) and $horvert == 'vert') {
        $link = $tabledesc[specialformat] ; 
        $strip = array('/\$uniq/');
        $link = preg_replace($strip, "$uniq", $link);
        print "<td><A HREF='$script?$link' class='button' style='font-size:large;'>Manage</A></td>" ;     
    } ;     

    if($editmode == 'edit') {
        print "<td><input type=submit name=action value='Save' class=button style='font-size:large;'></td>";
    } else { 
        $readonly = 'READONLY' ; 
    } ; 
    print "</tr></table>";
    print "<table>";
    #If "hor" creates a title bar
    
    if ($horvert == 'hor') {
        $h = 0;
        $result = mysqli_query($db, $query) or die("Query failed : $query <p>" . mysqli_error());
        while ($row = mysqli_fetch_assoc($result) and $h < 1) {
            print "<tr>";
            while (list($key, $val) = each($row)) {

            list($ztable,$field,$display,$description,$specialformat,$placeholder) = gafm("select `table`,`field`,`display`,`description`,`specialformat`,`placeholder` from fielddesc where `field` = '$key' and (`table` = '$table' or `table` ='*') order by `table` DESC") ; 

                if(!empty($display)) {
                    print "<td style='text-align:right;'><label for='$key'><span title='$key = $description'>" . bbf("$display") . "</span></label></td>";
                } else {
                    print "<td style='text-align:right;'>$key</td>";
                } ; 

            };
            print "</tr>";
            $h++;
        };
    };
    #end title bar
    $result = mysqli_query($db, $query) or die("Query failed : $query <p>" . mysqli_error());
    #    $finfo = mysqli_fetch_field_direct($result,1) ;
    $finfo = mysqli_fetch_fields($result);
    #    glprintr($finfo) ;
    while ($row = mysqli_fetch_assoc($result)) {
        if ($horvert == 'hor') {
            print "<tr>";
        };
        $c = 0 ; 
        while (list($key, $val) = each($row)) {
            #-------------------------------------------------------
            list($ztable,$field,$display,$description,$specialformat,$placeholder) = gafm("select `table`,`field`,`display`,`description`,`specialformat`,`placeholder` from fielddesc where `field` = '$key' and (`table` = '$table' or `table` ='*') order by `table` DESC") ; 

            if ($key == 'uniq') {
                $uniq = $val;
            };
            if ($horvert == 'vert') {
                if(!empty($display)) {
                    print "<tr><td style='text-align:right;'><label for='$key'><span title='$key = $description'>" . bbf("$display") . "</span></label></td>";
                } else {
                    print "<tr><td style='text-align:right;'>$key</td>";
                } ; 
            };

            if ($horvert == 'hor') {
             $length = 20 ; 
            } else { 
             $length = $finfo[$c]->length;
            } ; 
            
            $type = $finfo[$c]->type;
            $decimal = $finfo[$c]->decimal;
            
            $strip = array('/zadd\_/');
            $realtable = preg_replace($strip, '', $table);
            list($column, $constraint, $reftable, $refcolumn) = gafm("select COLUMN_NAME,CONSTRAINT_NAME,REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME from INFORMATION_SCHEMA.KEY_COLUMN_USAGE where TABLE_NAME = '$realtable' and CONSTRAINT_SCHEMA = '$database' and COLUMN_NAME = '$key'");
            if ($key == 'uniq' and $options == 'zadd') { #The master key, not editable.
                print "<td><input TYPE=HIDDEN name=\"f.$uniq.$key\" value=\"" . $val . "\" READONLY>---</td>";
            } elseif ($key == 'uniq' and $options != 'zadd') {
                print "<td><A id='$key' HREF='glass.php?mode=tables&submode=edit&table=$table&uniq=$val'>$val</A><input TYPE=HIDDEN name=\"f.$uniq.$key\" value=\"" . $val . "\" READONLY></td>";
            } elseif ($key == 'passwd' and $options != 'zadd') {
                print "<td><input id='$key' placeholder='$placeholder' type=password name=\"f.$uniq.$key\" value=\"" . $val . "\" $readonly></td>";
            } elseif (!empty($reftable) and !empty($refcolumn)) {
                print "<td>";
                print "<select id='$key' name=\"f.$uniq.$key\" REQUIRED $readonly>";
                if(!empty($val)) {
                print "<optgroup label='" . bbf('Current') . "'>";
                print "<OPTION VALUE='$val'>$val";
                } ; 
                print "<optgroup label='" . bbf('Available') . "'>";
                $q = "select distinct($refcolumn) from $reftable order by $refcolumn";
                $r = mysqli_query($db, $q) or die("Query failed : " . mysql_error());
                while (list($d) = mysqli_fetch_row($r)) {
                    print "<OPTION VALUE='$d'>$d";
                };
                print "</optgroup></select>\n";
                print "</td>";
            } elseif ("$column" == "$constraint" and "$column" == "$key") { #Things with constraints
                print "<td><input id='$key' placeholder='$placeholder' type=text name=\"f.$uniq.$key\" value=\"" . $val . "\" style='background:#FFFFFF;font-weight:bold;font-size:large;' $readonly></td>";
            } elseif ($type == '252' and $horvert == 'vert') { #text
                if($key == 'query' or $key == 'query2') { 
                print "<td><textarea id='$key' placeholder='$placeholder' name=\"f.$uniq.$key\" rows=10 cols='120' placeholder='' $readonly>$val</textarea></td>";
                } else {
                print "<td><textarea id='$key' placeholder='$placeholder' name=\"f.$uniq.$key\" rows=4 cols='80' placeholder='' $readonly>$val</textarea></td>";
                } ; 
            } elseif ($type == '252' and $horvert == 'hor') { #text
                print "<td><textarea id='$key' placeholder='$placeholder' name=\"f.$uniq.$key\" rows=1 cols='20' placeholder='$key' $readonly>$val</textarea></td>";
            } elseif ($type == '7') { #DATE-TIMESTAMP
               #if only 'datetime' was a working type. 
               # print "<td><input id='$key' placeholder='$placeholder' type=\"datetime\" name=\"f.$uniq.$key\" value=\"" . $val . "\" $readonly></td>";
               #instead we break it into 2 pieces and hopefull they come back together. 
               $date = substr($val,0,10) ; $time = substr($val,11,8) ; 
               #Laughing at:  "Web authors have no way to change the date format because there currently is no standards to specify the format. " ; 
               #I hate that I can't force this to the 'wire' format of YYYY-MM-DD
               if($horvert == 'vert') {
                   print "<td><input id='$key' placeholder='$placeholder' type=\"date\" name=\"fx.$uniq.$key" . "[]\" value=\"" . $date . "\" $readonly>";
                   print "<input id='$key' placeholder='$placeholder' type=\"time\" name=\"fx.$uniq.$key" . "[]\" value=\"" . $time . "\" $readonly></td><td>" ; 
                   print "<span style='color:#888888;font-size:x-small;'>$val</span>" ; 
                   print "</td>";
               } else { 
                   #works better in horizontal displays
                   print "<td><input id='$key' placeholder='$placeholder' type=\"datetime\" name=\"f.$uniq.$key\" value=\"" . $val . "\" $readonly></td>";
               } ; 
            } elseif ($type == '10') { #DATE
                print "<td><input id='$key'  placeholder='$placeholder' type=date name=\"f.$uniq.$key\" value=\"" . $val . "\" $readonly></td>";
            } elseif ($type == '16') { #BIT
                print "<td><input id='$key' placeholder='$placeholder' type=date name=\"f.$uniq.$key\" value=\"" . $val . "\" style='font-family:fixed,courier;' $readonly></td>";
            } elseif ($key == 'email' and $type == '253') { #EMAIL
                print "<td><input id='$key' placeholder='$placeholder' type=email name=\"f.$uniq.$key\" value=\"" . $val . "\" placeholder='e-mail' $readonly></td>";
            } elseif ($type == '2' or $type == '3' or $type == '4' or $type == '5' or $type == '8' or $type == '9' or $type == '246') { #NUMBERS
                print "<td><input id='$key' placeholder='$placeholder' type=text name=\"f.$uniq.$key\" value=\"" . $val . "\" style='text-align:right;' $readonly></td>";
            } else {
                print "<td><input id='$key' type=text name=\"f.$uniq.$key\" value=\"" . $val . "\" placeholder='$placeholder' size='$length' $readonly></td>";
            };
            $c++;
            if ($horvert == 'vert') {
                print "</tr>";
            };
            #-------------------------------------------------------
            
        };
        if ($horvert == 'hor') {
            print "</tr>";
        };
    };
    print "</table>";
    print "</form>";
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
                $th.= "<th style='font-weight:bold;font-size:small;border-bottom:1px solid #000000;'>$key</th>";
                if ($key == 'table') {
                    $th.= "<th style='font-weight:bold;font-size:small;border-bottom:1px solid #000000;'></th><th style='font-weight:bold;font-size:small;border-bottom:1px solid #000000;'></th>";
                };
            }; //builder a table header ;
            if ($key == 'table') {
                $trow.= '<td width=100 style="font-weight:bold;font-size:large;">' . $val . '</td>';
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
                $trow.= '<td style="text-align:right;color:#666666;font-size:x-small;">' . $val . '</td>';
            };
            if (@in_array($key, $toton)) {
                $totals["$key"]+= ($val*1);
            };
        };
        $trow .= "<td style='color:#666666;font-size:x-small;line-height:90%;'>" ; 
        list($display,$description) =  gafm("select display,description from tabledesc where `table` = '$table'") ; 
        if(!empty($description)) { $trow .= "<b>$table/$display</b> $description" ; } ; 
        $trow .= "</td>" ; 
        $trow.= '</tr>' . "\n";
        if (empty($theader)) {
            $theader = "<tr>$th<th style='font-weight:bold;font-size:small;border-bottom:1px solid #000000;'></th><th style='font-weight:bold;font-size:small;border-bottom:1px solid #000000;'></th></tr>\n";
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
