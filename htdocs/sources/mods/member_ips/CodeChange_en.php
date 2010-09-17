[COMMENT]
/*
+--------------------------------------------------------------------------
|   Invision Board v1.2
|   ========================================
|   > Mod_Installer 
|   > Module written by Peter(Pit)
|        > Peter member at ibforen.de, ibplanet.de, Pit member at ibplanet.com
|        > email: Peter@ibforen.de
|
|   > Mod_Installer Version Number: 1.1 (2003-10-08)
|   > <c> 2003 by Peter
|
|   > This is the CodeChange.php for Mod Member Ips by Peter
+--------------------------------------------------------------------------
*/
[COMMENT_END]

[INTERFACE]
'title' => 'Member Ips',
'sub_title' => 'Which ip addresses does a member use ? A new function in ModCP',
'category' => 'Minor Mod',
'compatible' => 'Invision Power Board  1.3',
'version' => '0.9',
'author' => 'Peter',
'email' => 'Peter@ibforen.de',
'mod_token' => 'mod_member_ips',
[INTERFACE_END]

[HISTORY]

[HISTORY_END]

[SQL]

[SQL_END]

[CODE]
[MOD_TOKEN]
mod_member_ips

[FNAME]
sources/mod_cp.php

[STEP]
[SEARCH]
		$this->output .= $this->html->edit_user_form($editable);

[INSERT] 
//-- mod_member_ips begin
        if (isset($ibforums->input['show_ips'])) {
            $ip_row .= "\n<tr><td class='pformleft'>\nUsed IP Adresses</td>\n<td class='pformright'>\n";
    		$DB->query("SELECT DISTINCT ip_address FROM ibf_posts WHERE author_id =  '".$ibforums->input['memberid']."' ORDER BY ip_address");
            $num = $DB->get_num_rows();
            if ($num) {
                $ip_row .= "<textarea style='font:Courier' cols='80' rows='".(min(15,3+round($num/4)))."' name='ips' readonly='readonly' wrap='soft'>\n";
                $ip_row .= "Registered with ".$member['ip_address'];
                $ip_row .= "\n\nUsed ip addresses:\n";
                while ($r = $DB->fetch_row()) 
                    $ip_row .= str_pad($r['ip_address'],20);
            }
            else {
                $ip_row .= "<textarea cols='50' rows='3' name='ips' class='forminput' readonly='readonly' wrap='soft'>\n";
                $ip_row .= "Registered with ".$member['ip_address'];
                $ip_row .= "\n<br>No posts found.";
            }
            $ip_row .= "\n</textarea>\n</td>\n</tr>\n";
            $this->output =  preg_replace("`(<\/table>)`is","$ip_row\\1",$this->output);
        }
        $this->output =  preg_replace("`(<input type.*?submit.*?>)`is","\\1&nbsp;&nbsp;<input type='submit' name='show_ips' value='IP Addresses' class='forminput' />",$this->output);
//-- mod_member_ips end

[MODE]
insert_below

[STEP]
[SEARCH]
	function complete_user_edit()
	{
		global $std, $ibforums, $DB, $print;

[INSERT] 
//-- mod_member_ips begin
        if (isset($ibforums->input['show_ips'])) $this->edit_user();
        return;
//-- mod_member_ips end

[MODE]
insert_below

[FNAME_END]

[CODE_END]

[COPY]
Copy all files of archive into the root dir of your board. Take care of the structure of this archive.
[List]
[*]sources [arrow] sources
[/list]
[COPY_END]

[CUSTOMIZE]

[CUSTOMIZE_END]

[COMMENT_MOD]
In Moderator Control Panel (ModCP) [b]Manage Members[/b] you will find a new button [b]IP Addresses[/b]
[COMMENT_MOD_END]

[HISTORY_OLD]

[HISTORY_OLD_END]

