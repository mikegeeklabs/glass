<?php
function glassmanageuser() {
    global $db, $mode, $submode, $subsubmode, $subsubsubmode, $action, $lang, $logic, $script, $fromip, $login, $name, $level, $perms, $csspath;
    $uniq = dt($_REQUEST['uniq']) ; 
    if(empty($uniq)) { 
        $userlogin = dt($_REQUEST['userlogin']) ; 
        list($uniq,$userlogin,$ulevel,$uname,$uemail) =  gafm("select uniq,login,level,name,email from users where login = '$userlogin'") ; 
    } ; 
    print glisttable(gaaafm("select uniq,login,level,name,email from users where uniq = '$uniq'"),array()) ; 
    list($userlogin,$ulevel,$uname,$uemail) =  gafm("select login,level,name,email from users where uniq = '$uniq'") ; 
    $submode = 'perms' ; 
        if ($submode == 'perms') {
        if ($_REQUEST['action'] == 'save') {
            runsql("delete from userperms where login = '$userlogin'");
            if (is_array($_REQUEST['userperms'])) { #Saves ARRAY Data, concats string
                $vv = "";
                foreach($_REQUEST['userperms'] as $v) {
                    runsql("insert into userperms(portal,login,perm) values ('$portal','$userlogin','$v')");
                    $p.= "$v|";
                };
            };
        };
        $a = glafm("select perm from userperms where login = '$userlogin'");
        if (@in_array('sysusers', $perms)) {
        } else {
            print "". bbf('Not Allowed') ."";
            die;
        };
        #--------------
print <<<EOF
<script language="JavaScript">
	var checkflag = "false";
	function check(field) {
		if (checkflag == "false") {
 			for (i = 0; i < field.length; i++) {
			field[i].checked = true;}
			checkflag = "true";
			return "toggle all"; 
		} else {
			for (i = 0; i < field.length; i++) {
			field[i].checked = false; }
			checkflag = "false";
			return "toggle all"; 
		}
	} 
</script>
EOF;
        print "<div class=title>" . bbf('User Permissions') . "</div>";

        print "<form action='$script' method='post'><input type='hidden' name='mode' value='manageuser'><input type='hidden' name='submode' value='perms'><input type='hidden' name='action' value='save'><input type='hidden' name='userlogin' value='$userlogin'>";
        print "<input type=button class=button value='" . bbf('toggle all') ."' onClick=\"this.value=check(this.form)\">" ; 

        if ($login == 'admin') {
            $q = "select cat,perm,role,description,longdesc,active from perms order by cat,description,role,perm";
        } else {
            $q = "select cat,perm,role,description,longdesc,active from perms where (role = '' or role = '$userrole') and active = '1' order by cat,description,role,perm";
        };
        $r = mysqli_query($db,$q) ; 
        $b = 1;
        print "<table border=0><tr><td valign=top><table border=0 class='table table-hover table-condensed'>";
        $i = 1;
        while (list($cat, $perm, $role, $description,$longdesc,$active) = mysqli_fetch_row($r)) {
            if ($cat != $lastcat) {
                print "<tr><td colspan=20><h4>" . bbf("$cat") . "</h4></td></tr><tr>\n";
                $i = 1 ; 
            };
            $lastcat = $cat;
            if (@in_array($perm, $a)) {
                $checked = 'checked';
            } else {
                $checked = '';
            };
            if ($seclevel > 989) {
                $description = "$description ($perm)";
            };
                if($active == '0') { 
                    print "<td width='10'>&nbsp;</td><td width=10></td><td width=220><span title='$longdesc' style='font-weight:bold;'>$perm:</span> " . bbf("$description") ."</td>";

                } else { 
                    print "<td width='10'>&nbsp;</td><td width=10><input type='checkbox' name='userperms[]' value='$perm' $checked></td><td width=220><span title='$longdesc' style='font-weight:bold;'>$perm:</span> " . bbf("$description") ."</td>";
                } ; 
            if ($i > 4) {
                $i = 0;
                print "</tr>";
            };
            $i++;
        };
        print "</table></td></tr></table>";
        print "<table><tr><td width='50'>&nbsp;</td><td><input type='reset' name='reset' class='btn' value='" . bbf('reset') . "'></td><td><input type='submit' name='save' class='button' value='" . bbf('Save Changes') . "'></td></tr></table>";
        print "</form>";
    };
} ; 
?>
