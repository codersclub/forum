<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Admin Framework for IPS Services
|   > Module written by Matt Mecham
|   > Date started: 17 February 2003
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_plugins();

class ad_plugins
{

	var $base_url;

	function ad_plugins()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------

		$tmp_in = array_merge($_GET, $_POST, $_COOKIE);

		foreach ($tmp_in as $k => $v)
		{
			unset($$k);
		}

		//---------------------------------------

		// Make sure we're a root admin, or else!

		if ($MEMBER['mgroup'] != $INFO['admin_group'])
		{
			$ADMIN->error("Sorry, these functions are for the root admin group only");
		}

		switch ($IN['code'])
		{

			case 'ipchat':
				$this->chat_splash();
				break;
			case 'chatframe':
				$this->chat_frame();
				break;
			case 'chatsave':
				$this->chat_save();
				break;
			case 'dochat':
				$this->chat_config_save();
				break;

			//----------------------------

			case 'reg':
				$this->reg_splash();
				break;
			case 'regframe':
				$this->reg_frame();
				break;
			case 'regsave':
				$this->reg_save();
				break;
			case 'doreg':
				$this->reg_config_save();
				break;

			//----------------------------

			case 'copy':
				$this->copy_splash();
				break;
			case 'copyframe':
				$this->copy_frame();
				break;
			case 'copysave':
				$this->copy_save();
				break;
			case 'docopy':
				$this->copy_config_save();
				break;

			default:
				exit();
				break;
		}

	}

	//-------------------------------------------------------------
	// Copyright removal Splash
	//--------------------------------------------------------------

	function copy_splash()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//---------------------------------------
		// Do we have an order number
		//---------------------------------------

		if ($INFO['ipb_copy_number'])
		{
			$this->copy_config();
		} else
		{
			$frames = "<html>
		   			 <head><title>Invision Power Board: Registration Set up</title></head>
					   <frameset rows='*,50' frameborder='yes' border='1' framespacing='0'>
					   	<frame name='chat_top'   scrolling='auto' src='http://customer.invisionpower.com/ipb/copy/redirect_acp.php'>
					   	<frame name='chat_bottom'  scrolling='auto' src='{$SKIN->base_url}&act=pin&code=copyframe'>
					   </frameset>
				   </html>";

			print $frames;
			exit();
		}

	}

	//---------------------------------------------------------------

	function copy_frame()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$css = $SKIN->get_css();

		$html = "<html>
				  <head>
				   <title>Invision Power Board Order Box</title>
				   $css
				  </head>
				  <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#4C77B6'>
				  <table cellpadding=4 cellspacing=0 border=0 align='center'>
				  <form action='{$SKIN->base_url}&act=pin&code=copysave' method='POST' target='body'>
				  <tr>
				   <td valign='middle' align='left'><b style='color:white'>Already paid for copyright removal?</b></td>
				   <td valign='middle' align='left'><input type='text' size=50 name='ipb_copy_number' value='enter your IPB copyright removal key here...' onClick=\"this.value='';\"></td>
				   <td valign='middle' align='left'><input type='submit' value='Continue...'></td>
				  </tr>
				  </table>
				  </form>
				  </body>
				 </html>";

		echo $html;

		exit();

	}

	//---------------------------------------------------------------

	function copy_save()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$acc_number = trim($IN['ipb_copy_number']);

		if (stristr($acc_number, ',pass='))
		{
			list($acc_number, $pass) = explode(',pass=', $acc_number);

			if (md5(strtolower($pass)) == 'b1c4780a00e7d010b0eca0b695398c02')
			{
				$ADMIN->rebuild_config(array(
				                            'ipb_copy_number' => $acc_number,
				                            'ips_cp_purchase' => 1,
				                       ));

				$this->copy_config('new');

				exit();
			} else
			{
				$ADMIN->error("The override password was incorrect. Please <a href='http://www.invisionpower.com/?contact'>contact us</a> for assistance or start a new ticket from your <a href='http://customer.invisionpower.com'>IPS customer account</a>.");
			}
		}

		if ($acc_number == "")
		{
			$ADMIN->error("Sorry, that is not a valid IPB Copyright key, please hit 'back' in your browser and try again.");
		}

		$response = trim(@implode('', @file("http://customer.invisionpower.com/ipb/copy/?k=" . urlencode($acc_number))));

		if ($response == "")
		{
			$ADMIN->error("There was no response back from the Invision Power Services registration server, this might be because of the following:
			               <ul>
			               <li>Your PHP version does not allow remote connections</li>
			               <li>The Invision Power Services registration server is offline</li>
			               <li>You are running this IPB on a server without an internet connection</li>
			               </ul>
			               <br />
			               Please <a href='http://www.invisionpower.com/?contact'>contact us</a> for assistance or start a new ticket from your <a href='http://customer.invisionpower.com'>IPS customer account</a>.
			             ");
		} else
		{
			if ($response == '0')
			{
				$ADMIN->error("The registration key you entered is not valid, this might be because of the following:
			               <ul>
			               <li>You incorrectly entered the registration key</li>
			               <li>You mistakenly used your customer center password instead of the registration key</li>
			               <li>Your registration licence is no longer valid</li>
			               </ul>
			               <br />
			               Please <a href='http://www.invisionpower.com/?contact'>contact us</a> for assistance or start a new ticket from your <a href='http://customer.invisionpower.com'>IPS customer account</a>.
			             ");
			} else
			{
				if ($response == '1')
				{
					$ADMIN->rebuild_config(array(
					                            'ipb_copy_number' => $acc_number,
					                            'ips_cp_purchase' => 1
					                       ));
				}
			}
		}

		$this->copy_config('new');
	}

	//--------------------------------------------------------------

	function copy_config($type = "")
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_detail = "&nbsp;";
		$ADMIN->page_title  = "IPB Copyright Confirmation";

		if ($type == "new")
		{
			$ADMIN->page_detail .= "<br /><br /><b style='color:red'>Thank you for registering your copyrigt removal!</b>";
		}

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "100%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Configuration");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "The copyright should now be removed from the bottom of the IPB pages.<br /><br />If this is not the case, please contact our after sales staff immediately."
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//---------------------------------------------------------------

	function copy_config_save()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$new = array(
			'ipb_reg_show' => $IN['ipb_reg_show'],
			'ipb_reg_name' => $IN['ipb_reg_name'],
		);

		$ADMIN->rebuild_config($new);

		$ADMIN->done_screen("IPB Registration Configuration Updated", "IPB Registration Configuration Updated", "act=pin&code=reg");
	}

	//-------------------------------------------------------------
	// Registration Splash
	//--------------------------------------------------------------

	function reg_splash()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//---------------------------------------
		// Do we have an order number
		//---------------------------------------

		if ($INFO['ipb_reg_number'])
		{
			$this->reg_config();
		} else
		{
			$frames = "<html>
		   			 <head><title>Invision Power Board: Registration Set up</title></head>
					   <frameset rows='*,50' frameborder='yes' border='1' framespacing='0'>
					   	<frame name='chat_top'   scrolling='auto' src='http://www.invisionboard.com/?whyregister++acp'>
					   	<frame name='chat_bottom'  scrolling='auto' src='{$SKIN->base_url}&act=pin&code=regframe'>
					   </frameset>
				   </html>";

			print $frames;
			exit();
		}

	}

	//---------------------------------------------------------------

	function reg_frame()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$css = $SKIN->get_css();

		$html = "<html>
				  <head>
				   <title>Invision Power Board Order Box</title>
				   $css
				  </head>
				  <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#4C77B6'>
				  <table cellpadding=4 cellspacing=0 border=0 align='center'>
				  <form action='{$SKIN->base_url}&act=pin&code=regsave' method='POST' target='body'>
				  <tr>
				   <td valign='middle' align='left'><b style='color:white'>Already Registered?</b></td>
				   <td valign='middle' align='left'><input type='text' size=50 name='ipb_reg_number' value='enter your IPB registration key here...' onClick=\"this.value='';\"></td>
				   <td valign='middle' align='left'><input type='submit' value='Continue...'></td>
				  </tr>
				  </table>
				  </form>
				  </body>
				 </html>";

		echo $html;

		exit();

	}

	//---------------------------------------------------------------

	function reg_save()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$acc_number = trim($IN['ipb_reg_number']);

		if (stristr($acc_number, ',pass='))
		{
			list($acc_number, $pass) = explode(',pass=', $acc_number);

			if (md5(strtolower($pass)) == 'b1c4780a00e7d010b0eca0b695398c02')
			{
				$ADMIN->rebuild_config(array('ipb_reg_number' => $acc_number));
				$this->reg_config('new');

				exit();
			} else
			{
				$ADMIN->error("The override password was incorrect. Please <a href='http://www.invisionpower.com/?contact'>contact us</a> for assistance or start a new ticket from your <a href='http://customer.invisionpower.com'>IPS customer account</a>.");
			}
		}

		if ($acc_number == "")
		{
			$ADMIN->error("Sorry, that is not a valid IPB registration key, please hit 'back' in your browser and try again.");
		}

		$response = trim(implode('', file("http://customer.invisionpower.com/ipb/reg/?k=" . urlencode($acc_number))));

		if ($response == "")
		{
			$ADMIN->error("There was no response back from the Invision Power Services registration server, this might be because of the following:
			               <ul>
			               <li>Your PHP version does not allow remote connections</li>
			               <li>The Invision Power Services registration server is offline</li>
			               <li>You are running this IPB on a server without an internet connection</li>
			               </ul>
			               <br />
			               Please <a href='http://www.invisionpower.com/?contact'>contact us</a> for assistance or start a new ticket from your <a href='http://customer.invisionpower.com'>IPS customer account</a>.
			             ");
		} else
		{
			if ($response == '0')
			{
				$ADMIN->error("The registration key you entered is not valid, this might be because of the following:
			               <ul>
			               <li>You incorrectly entered the registration key</li>
			               <li>You mistakenly used your customer center password instead of the registration key</li>
			               <li>Your registration licence is no longer valid</li>
			               </ul>
			               <br />
			               Please <a href='http://www.invisionpower.com/?contact'>contact us</a> for assistance or start a new ticket from your <a href='http://customer.invisionpower.com'>IPS customer account</a>.
			             ");
			} else
			{
				if ($response == '1')
				{
					$ADMIN->rebuild_config(array('ipb_reg_number' => $acc_number));
				}
			}
		}

		$this->reg_config('new');
	}

	//--------------------------------------------------------------

	function reg_config($type = "")
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_detail = "You may edit the configuration below to suit";
		$ADMIN->page_title  = "IPB Registration Configuration";

		if ($type == "new")
		{
			$ADMIN->page_detail .= "<br /><br /><b style='color:red'>Thank you for registering!</b>";
		}

		//+-------------------------------
		// START THE FORM
		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doreg'),
		                                       2 => array('act', 'pin'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "60%");
		$SKIN->td_header[] = array("&nbsp;", "40%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Configuration");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Display registered to line?</b>",
		                                       $SKIN->form_yes_no("ipb_reg_show", $INFO['ipb_reg_show'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show as registered to...</b><br>Examples: <em>Matthew Mecham</em>, <em>IPS, Inc.</em>",
		                                       $SKIN->form_input("ipb_reg_name", $INFO['ipb_reg_name'])
		                                  ));

		$ADMIN->html .= $SKIN->end_form('Save this configuration');

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//---------------------------------------------------------------

	function reg_config_save()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$new = array(
			'ipb_reg_show' => $IN['ipb_reg_show'],
			'ipb_reg_name' => $IN['ipb_reg_name'],
		);

		$ADMIN->rebuild_config($new);

		$ADMIN->done_screen("IPB Registration Configuration Updated", "IPB Registration Configuration Updated", "act=pin&code=reg");
	}

	//-------------------------------------------------------------
	// CHAT SPLASH
	//--------------------------------------------------------------

	function chat_splash()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//---------------------------------------
		// Do we have an order number
		//---------------------------------------

		if ($INFO['chat_account_no'])
		{
			$this->chat_config();
		} else
		{
			$frames = "<html>
		   			 <head><title>Invision Power Board: Chat Set up</title></head>
					   <frameset rows='*,50' frameborder='yes' border='1' framespacing='0'>
					   	<frame name='chat_top'   scrolling='auto' src='http://www.invisionchat.com/?acp++acp'>
					   	<frame name='chat_bottom'  scrolling='auto' src='{$SKIN->base_url}&act=pin&code=chatframe'>
					   </frameset>
				   </html>";

			print $frames;
			exit();
		}

	}

	//---------------------------------------------------------------

	function chat_frame()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$css = $SKIN->get_css();

		$html = "<html>
				  <head>
				   <title>Invision Power Board Order Box</title>
				   $css
				  </head>
				  <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#4C77B6'>
				  <table cellpadding=4 cellspacing=0 border=0 align='center'>
				  <form action='{$SKIN->base_url}&act=pin&code=chatsave' method='POST' target='body'>
				  <tr>
				   <td valign='middle' align='left'><b style='color:white'>Ordered IP Chat?</b></td>
				   <td valign='middle' align='left'><input type='text' size=35 name='account_no' value='enter your account number here...' onClick=\"this.value='';\"></td>
				   <td valign='middle' align='left'><input type='submit' value='Continue...'></td>
				  </tr>
				  </table>
				  </form>
				  </body>
				 </html>";

		echo $html;

		exit();

	}

	//---------------------------------------------------------------

	function chat_save()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$acc_number = intval($IN['account_no']);

		if ($acc_number == "")
		{
			$ADMIN->error("Sorry, that is not a valid IP Chat account number");
		}

		$ADMIN->rebuild_config(array('chat_account_no' => $acc_number));

		$this->chat_config();
	}

	//---------------------------------------------------------------

	function chat_config_save()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$acc_number = intval($IN['chat_account_no']);

		//if ( $acc_number == "" )
		//{
		//	$ADMIN->error("Sorry, that is not a valid IP Chat account number");
		//}

		$new = array(
			'chat_account_no'  => $acc_number,
			'chat_allow_guest' => $IN['chat_allow_guest'],
			'chat_width'       => $IN['chat_width'],
			'chat_height'      => $IN['chat_height'],
			'chat_language'    => $IN['chat_language'],
			'chat_display'     => $IN['chat_display'],
		);

		//------------------------------------
		// Get the ID's of the groups we're
		// allowing admin accsss chat_access_groups
		//------------------------------------

		$ids = array();

		foreach ($IN as $key => $value)
		{
			if (preg_match("/^sg_(\d+)$/", $key, $match))
			{
				if ($IN[$match[0]])
				{
					$ids[] = $match[1];
				}
			}
		}

		$new['chat_admin_groups'] = implode(",", $ids);

		//------------------------------------
		// Get the ID's of the groups we're
		// allowing chat accsss
		//------------------------------------

		$ids = array();

		foreach ($IN as $key => $value)
		{
			if (preg_match("/^sa_(\d+)$/", $key, $match))
			{
				if ($IN[$match[0]])
				{
					$ids[] = $match[1];
				}
			}
		}

		$new['chat_access_groups'] = implode(",", $ids);

		$ADMIN->rebuild_config($new);

		$ADMIN->done_screen("IP Chat Configurations Updated", "IP Chat Configuration", "act=pin&code=ipchat");
	}

	//--------------------------------------------------------------

	function chat_config()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_detail = "You may edit the configuration below to suit";
		$ADMIN->page_title  = "IP Chat Configuration";

		//+-------------------------------
		// SET UP SOME DEFAULTS
		//+-------------------------------

		$language = $INFO['chat_language'] == ""
			? 'en'
			: $INFO['chat_language'];

		$larray = array(
			0  => array('en', 'English'),
			1  => array('ar', 'Arabic'),
			2  => array('de', 'German'),
			3  => array('es', 'Spanish'),
			4  => array('fr', 'French'),
			5  => array('hr', 'Croation'),
			6  => array('it', 'Italian'),
			7  => array('iw', 'Hebrew'),
			8  => array('nl', 'Dutch'),
			9  => array('pl', 'Polish'),
			10 => array('pt', 'Portuguese'),
		);

		$display = array(
			0 => array('self', 'Normal IPB Page'),
			1 => array('new', 'New Pop Up Window'),
		);

		//+-------------------------------
		// START THE FORM
		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'dochat'),
		                                       2 => array('act', 'pin'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "60%");
		$SKIN->td_header[] = array("&nbsp;", "40%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Basic Configuration");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Chat Room Account Number?</b><br>Removing this number will remove all links / chat functionality within IPB.",
		                                       $SKIN->form_input("chat_account_no", $INFO['chat_account_no'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow guests access to the chat room?</b><br>Choosing 'no' will require all chat users to log into chat.",
		                                       $SKIN->form_yes_no("chat_allow_guest", $INFO['chat_allow_guest'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Chat Room Dimensions (WIDTH)?</b>",
		                                       $SKIN->form_input("chat_width", $INFO['chat_width']
			                                       ? $INFO['chat_width']
			                                       : 600)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Chat Room Dimensions (HEIGHT)?</b>",
		                                       $SKIN->form_input("chat_height", $INFO['chat_height']
			                                       ? $INFO['chat_height']
			                                       : 350)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default Chat Room Interface Language?</b>",
		                                       $SKIN->form_dropdown("chat_language", $larray, $language)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Load the chat room in...?</b>",
		                                       $SKIN->form_dropdown("chat_display", $display, $INFO['chat_display'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "50%");
		$SKIN->td_header[] = array("Access", "25%");
		$SKIN->td_header[] = array("Admin", "25%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Admin Access Permission");

		//-------------------------------
		// Break up our line of admins
		//-------------------------------

		$allowed = array();

		foreach (explode(",", $INFO['chat_admin_groups']) as $i)
		{
			$allowed[$i] = 1;
		}

		//-------------------------------
		// Break up our allowed access
		//-------------------------------

		$access = array();

		foreach (explode(",", $INFO['chat_access_groups']) as $i)
		{
			$access[$i] = 1;
		}

		$stmt = $ibforums->db->query("SELECT g_id, g_title FROM ibf_groups WHERE g_id <> " . $INFO['guest_group'] . " ORDER BY g_title");

		while ($r = $stmt->fetch())
		{

			$mode = $r['g_id'] == $INFO['admin_group']
				? 'green'
				: 'red';

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Group '<span style='color:$mode'>{$r['g_title']}</span>' permissions...</b>",
			                                       "<strong>Can use chat</strong>&nbsp;" . $SKIN->form_checkbox("sa_{$r['g_id']}", $access[$r['g_id']]
				                                       ? 1
				                                       : 0),
			                                       "<strong>Is Chat Admin</strong>&nbsp;" . $SKIN->form_checkbox("sg_{$r['g_id']}", $allowed[$r['g_id']]
				                                       ? 1
				                                       : 0),
			                                  ));
		}

		$ADMIN->html .= $SKIN->end_form('Save this configuration');

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//-------------------------------------------------------------
	//
	// Save config. Does the hard work, so you don't have to.
	//
	//--------------------------------------------------------------

	function save_config($new)
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$master = array();

		if (is_array($new))
		{
			if (count($new) > 0)
			{
				foreach ($new as $field)
				{

					// Handle special..

					if ($field == 'img_ext' or $field == 'avatar_ext')
					{
						$_POST[$field] = preg_replace("/[\.\s]/", "", $_POST[$field]);
						$_POST[$field] = preg_replace("/,/", '|', $_POST[$field]);
					} else
					{
						if ($field == 'coppa_address')
						{
							$_POST[$field] = nl2br($_POST[$field]);
						}
					}

					$_POST[$field] = preg_replace("/'/", "&#39;", stripslashes($_POST[$field]));

					$master[$field] = stripslashes($_POST[$field]);
				}

				$ADMIN->rebuild_config($master);
			}
		}
	}

	//-------------------------------------------------------------
	//
	// Common header: Saves writing the same stuff out over and over
	//
	//--------------------------------------------------------------

	function common_header($formcode = "", $section = "", $extra = "")
	{

		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$extra = $extra
			? $extra . "<br>"
			: $extra;

		$ADMIN->page_detail = $extra . "Please check the data you are entering before submitting the changes";
		$ADMIN->page_title  = "Plug In Configuration [ $section ]";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', $formcode),
		                                       2 => array('act', 'pin'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Settings");

	}

	//-------------------------------------------------------------
	//
	// Common footer: Saves writing the same stuff out over and over
	//
	//--------------------------------------------------------------

	function common_footer($button = "Submit Changes")
	{

		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->html .= $SKIN->end_form($button);

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}
}

?>
