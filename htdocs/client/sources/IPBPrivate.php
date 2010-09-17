<?

require "./sources/Private.php";
require "../sources/lib/post_parser.php";

class IPBPrivate extends PrivateProtocol {

        function IPBPrivate() {

        global $Log, $DB, $ibforums, $OnlyNew;

 		$sql = "SELECT *
 					  FROM ibf_messages
 					WHERE  " . ($OnlyNew ? "read_state=0 AND" : "") . "
 					   member_id=".$ibforums->member['id']." AND
					   vid='in'
 					ORDER BY msg_date DESC";
 		$query = $DB->query($sql);

                while(true)
                {
			$msg = $DB->fetch_row($query);
			if(empty($msg))
				break;
                      $this->ProcessOneMessage($msg);
                }

                // And do logging
        }

       function ProcessOneMessage($msg){
                 global $ibforums, $DB, $std, $MarkReadOnServer, $DelFromServer;

                 if (!$msg['msg_id'])
                 {
                  return;
                 }

                 //--------------------------------------
                 // Did we read this in the pop up?
                 // If so, reduce new count by 1 (this msg)
                 // 'cos if we went via inbox, we'd have
                 // no new msg
                 //--------------------------------------

                 if ($MarkReadOnServer && $ibforums->member['new_msg'] >= 1)
                 {
                         $DB->query("UPDATE ibf_members SET new_msg=new_msg-1 WHERE id='".$this->member['id']."'");
                 }

                //--------------------------------------
                 // Is this an unread message?
                 //--------------------------------------

                if($DelFromServer)
		{
                         $DB->query("DELETE FROM ibf_messages WHERE msg_id='".$msg['msg_id']."'");
		}
		else if ($MarkReadOnServer && $msg['read_state'] < 1)
                 {
                         $DB->query("UPDATE ibf_messages SET read_state=1, read_date='".time()."' WHERE msg_id='".$msg['msg_id']."'");
                 }


                 //--------------------------------------
                 // Start formatting the member and msg
                 //--------------------------------------
                
		$this->parser = new post_parser();


//                 $msg['msg_date'] = $std->get_date( $msg['msg_date'], 'LONG' );


                 $DB->query("SELECT name ".
                                    "FROM ibf_members WHERE id='".$msg['from_id']."'");

                 $member = $DB->fetch_row();

                 $member = $this->parse_member( $member, $msg );

                 $msg['message'] = $this->parser->prepare( array( 'TEXT'    => $msg['message'],
                                                                                                                 'SMILIES' => 1,
                                                                                                                 'CODE'    => $ibforums->vars['msg_allow_code'],
                                                                                                                 'HTML'    => $ibforums->vars['msg_allow_html']
                                                                                                           )
                                                                                                );

/*                        $member['signature'] = $this->parser->prepare( array( 'TEXT'    => $member['signature'],
                                                                                                                                  'SMILIES' => 0,
                                                                                                                                  'CODE'    => $ibforums->vars['sig_allow_ibc'],
                                                                                                                                  'HTML'    => $ibforums->vars['sig_allow_html'],
                                                                                                                                  'SIGNATURE'=> 1,
                                                                                                                 )      );

                        if ( $ibforums->vars['sig_allow_html'] == 1 )
                        {
                                $member['signature'] = $this->parser->parse_html($member['signature'], 0);
                        }

                        $member['signature'] = $skin_universal->signature_separator($member['signature']);

                $member['VID'] = $this->msg_stats['current_id'];

*/
                $message['privmsgs_text'] = $msg['message'];/*$this->html->Render_msg( array(
                                                                                                                 'msg'    => $msg,
                                                                                                                 'member' => $member,
                                                                                                                 'jump'   => $this->jump_html
                                                                                            )      );*/
                $message['privmsgs_id'] = $msg['msg_id'];
                $message['privmsgs_subject'] = $msg['title'];
                $message['privmsgs_date'] = $msg['msg_date'];
                $message['username'] = $member['name'];
                $message['from_id'] = $msg['from_id'];
                $this->messages[] = $message;
        }

	function parse_member($member=array(), $row=array()) {
		global $ibforums, $std, $DB;
		
		$member['avatar'] = $std->get_avatar( $member['avatar'], $ibforums->member['view_avs'], $member['avatar_size'] );
		
		if ($member['g_icon'])
		{
			$member['member_rank_img'] = "<img src='{$ibforums->vars[TEAM_ICON_URL]}/{$member['g_icon']}' border='0' />";
		}
		
		$member['member_joined'] = $ibforums->lang['m_joined'].' '.$std->get_date( $member['joined'], 'JOINED' );
		
		$member['member_group'] = $ibforums->lang['m_group'].' '.$member['g_title'];
		
		$member['member_posts'] = $ibforums->lang['m_posts'].' '.$std->do_number_format($member['posts']);
// Song

		//Reputation
		if (empty ($member['rep'])) $member['rep'] = 0;
		if ($ibforums->vars['rep_goodnum'] and $member['rep'] >= $ibforums->vars['rep_goodnum']) $member['title'] = $ibforums->vars['rep_goodtitle'].' '.$member['title'];
		if ($ibforums->vars['rep_badnum']  and $member['rep'] <= $ibforums->vars['rep_badnum'])  $member['title'] = $ibforums->vars['rep_badtitle']. ' '.$member['title'];
		//Reputation
// Song
		
		$member['member_number'] = $ibforums->lang['member_no'].' '.$std->do_number_format($member['id']);
		
		$member['profile_icon'] = "<a href='{$this->base_url}showuser={$member['id']}'><{P_PROFILE}></a>";
		
		$member['message_icon'] = "<a href='{$this->base_url}act=Msg&amp;CODE=04&amp;MID={$member['id']}'><{P_MSG}></a>";
		
		if (!$member['hide_email'])
		{
			$member['email_icon'] = "<a href='{$this->base_url}act=Mail&amp;CODE=00&amp;MID={$member['id']}'><{P_EMAIL}></a>";
		}
		
		if ( $member['website'] and preg_match( "/^http:\/\/\S+$/", $member['website'] ) )
		{
			$member['website_icon'] = "<a href='{$member['website']}' target='_blank'><{P_WEBSITE}></a>";
		}
		
		if ($member['icq_number'])
		{
			$member['icq_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}act=ICQ&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_ICQ}></a>";
		}
		
		if ($member['aim_name'])
		{
			$member['aol_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}act=AOL&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_AOL}></a>";
		}
		
		if ($member['yahoo'])
		{
			$member['yahoo_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}act=YAHOO&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_YIM}></a>";
		}
		
		if ($member['msnname'])
		{
			$member['msn_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}act=MSN&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_MSN}></a>";
		}
		
		if ($member['integ_msg'])
		{
			$member['integ_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}act=integ&amp;MID={$member['id']}','Pager','750','450','0','1','1','1')\"><{INTEGRITY_MSGR}></a>";
		}
		
		if ($ibforums->member['id'])
		{
			$member['addresscard'] = "<a href=\"javascript:PopUp('{$this->base_url}act=Profile&amp;CODE=showcard&amp;MID={$member['id']}','AddressCard','470','300','0','1','1','1')\" title='{$ibforums->lang['ac_title']}'><{ADDRESS_CARD}></a>";
		}
		
		//-----------------------------------------------------
		
		return $member;
	
	}

}

?>