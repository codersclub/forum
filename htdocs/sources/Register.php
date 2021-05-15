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
  |   > Registration functions
  |   > Module written by Matt Mecham
  |   > Date started: 16th February 2002
  |
  |	> Module Version Number: 1.0.0
  +--------------------------------------------------------------------------
 */

$idx = new Register;
use Views\View;

class Register
{

	var $output = "";
	var $page_title = "";
	var $nav = array();
	var $email = "";
	var $modules = "";

	function __construct()
	{
		global $ibforums, $std, $print;

		//--------------------------------------------
		// Require the HTML and language modules
		//--------------------------------------------

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_register', $ibforums->lang_id);

		$this->base_url        = $ibforums->base_url;
		$this->base_url_nosess = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}";

		//--------------------------------------------
		// Get the emailer module
		//--------------------------------------------

		require_once ROOT_PATH . "sources/lib/emailer.php";

		$this->email = new emailer();

		if (USE_MODULES == 1)
		{
			require ROOT_PATH . "modules/ipb_member_sync.php";

			$this->modules = new ipb_member_sync();
		}

		// don't check for flood for second steps, for example
		$floodcheck_off = in_array($ibforums->input['CODE'], array('11', 'image'));

		if (!$floodcheck_off)
		{
			$std->flood_begin();
		}

		//--------------------------------------------
		// What to do?
		//--------------------------------------------

		switch ($ibforums->input['CODE'])
		{
			case '02':
				$this->create_account();
				break;

			case '03':
				$this->validate_user();
				break;

			case '05':
				$this->show_manual_form();
				break;

			case '06':
				$this->show_manual_form('lostpass');
				break;

			case 'lostpassform':
				$this->show_manual_form('lostpass');
				break;

			case '07':
				$this->show_manual_form('newemail');
				break;

			case '10':
				$this->lost_password_start();
				break;
			case '11':
				$this->lost_password_end();
				break;

			case '12':
				$this->coppa_perms_form();
				break;

			case 'coppa_two':
				$this->coppa_two();
				break;

			case 'image':
				$this->show_image();
				break;

			case 'reval':
				$this->revalidate_one();
				break;

			case 'reval2':
				$this->revalidate_two();
				break;

			default:
				if ($ibforums->vars['use_coppa'] == 1 and $ibforums->input['coppa_pass'] != 1)
				{
					$this->coppa_start();
				} else
				{
					$this->show_reg_form();
				}

				break;
		}

		if (!$floodcheck_off)
		{
			$std->flood_end();
		}
		// If we have any HTML to print, do so...
		$print->exportJSLang([
				'js_blanks',
		        'js_no_check',
		        'js_err_email_address_match',
		        'err_pass_match',
			]);
		$print->js->addLocal('register.js');
		$print->add_output("$this->output");

		$print->do_output(array('TITLE' => $this->page_title, 'JS' => 0, 'NAV' => $this->nav));
	}

	/*	 * ************************************************** */

	// Show "check revalidate form" er.. form. thing.
	// ------------------
	//
	/*	 * ************************************************** */

	function revalidate_one($errors = "")
	{
		global $ibforums;

		if ($errors != "")
		{
			$this->output .= View::make("register.errors", ['data' => $ibforums->lang[$errors]]);
		}

		$name = $ibforums->member['id'] == ""
			? ''
			: $ibforums->member['name'];

		$this->output .= View::make("register.show_revalidate_form", ['name' => $name]);
		$this->page_title = $ibforums->lang['rv_title'];
		$this->nav        = array($ibforums->lang['rv_title']);
	}

	function revalidate_two()
	{
		global $ibforums, $std;

		//------------------------------------------
		// Check in the DB for entered member name
		//------------------------------------------

		if ($_POST['username'] == "")
		{
			$this->revalidate_one('err_no_username');
			return;
		}

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_members
			    WHERE LOWER(name)='" . mb_strtolower($ibforums->input['username']) . "'");

		if (!$member = $stmt->fetch())
		{
			$this->revalidate_one('err_no_username');
			return;
		}

		//------------------------------------------
		// Check in the DB for any validations
		//------------------------------------------

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_validating
			    WHERE member_id='" . intval($member['id']) . "'");

		if (!$val = $stmt->fetch())
		{
			$this->revalidate_one('err_no_validations');
			return;
		}

		//------------------------------------------
		// Which type is it then?
		//------------------------------------------

		if ($val['lost_pass'] == 1)
		{
			$this->email->get_template("lost_pass");

			$this->email->build_message(array(
			                                 'NAME'       => $member['name'],
			                                 'THE_LINK'   => $this->base_url_nosess . "?act=Reg&CODE=lostpassform&uid=" . $member['id'] . "&aid=" . $val['vid'],
			                                 'MAN_LINK'   => $this->base_url_nosess . "?act=Reg&CODE=lostpassform",
			                                 'EMAIL'      => $member['email'],
			                                 'ID'         => $member['id'],
			                                 'CODE'       => $val['vid'],
			                                 'IP_ADDRESS' => $ibforums->input['IP_ADDRESS'],
			                            ));

			$this->email->subject = $ibforums->lang['lp_subject'] . ' ' . $ibforums->vars['board_name'];
			$this->email->to      = $member['email'];

			$this->email->send_mail();
		} else {
			if ($val['new_reg'] == 1)
			{
				$this->email->get_template("reg_validate");

				$this->email->build_message(array(
				                                 'THE_LINK' => $this->base_url_nosess . "?act=Reg&CODE=03&uid=" . $member['id'] . "&aid=" . $val['vid'],
				                                 'NAME'     => $member['name'],
				                                 'MAN_LINK' => $this->base_url_nosess . "?act=Reg&CODE=05",
				                                 'EMAIL'    => $member['email'],
				                                 'ID'       => $member['id'],
				                                 'CODE'     => $val['vid'],
				                            ));

				$this->email->subject = $ibforums->lang['email_reg_subj'] . " " . $ibforums->vars['board_name'];
				$this->email->to      = $member['email'];

				$this->email->send_mail();
			} else
			{
				if ($val['email_chg'] == 1)
				{
					$this->email->get_template("newemail");

					$this->email->build_message(array(
					                                 'NAME'     => $member['name'],
					                                 'THE_LINK' => $this->base_url_nosess . "?act=Reg&CODE=03&type=newemail&uid=" . $member['id'] . "&aid=" . $val['vid'],
					                                 'ID'       => $member['id'],
					                                 'MAN_LINK' => $this->base_url_nosess . "?act=Reg&CODE=07",
					                                 'CODE'     => $val['vid'],
					                            ));

					$this->email->subject = $ibforums->lang['ne_subject'] . ' ' . $ibforums->vars['board_name'];
					$this->email->to      = $member['email'];
					$this->email->send_mail();
				} else
				{
					$this->revalidate_one('err_no_validations');
					return;
				}
			}
		}

		$this->output .= View::make("register.show_revalidated");

		$this->page_title = $ibforums->lang['rv_title'];
		$this->nav        = array($ibforums->lang['rv_title']);
	}

	/*	 * ************************************************** */

	// Coppa Start
	// ------------------
	// Asks the registree if they are an old git or not
	/*	 * ************************************************** */

	function coppa_perms_form()
	{
		echo(View::make("register.coppa_form"));
		exit();
	}

	function coppa_start()
	{
		global $ibforums;

		$coppa_date = date('j-F y', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 13));

		$ibforums->lang['coppa_form_text'] = str_replace("<#FORM_LINK#>", "<a href='{$ibforums->base_url}act=Reg&amp;CODE=12'>{$ibforums->lang['coppa_link_form']}</a>", $ibforums->lang['coppa_form_text']);

		$this->output .= View::make("register.coppa_start", ['coppadate' => $coppa_date]);

		$this->page_title = $ibforums->lang['coppa_title'];

		$this->nav = array($ibforums->lang['coppa_title']);
	}

	function coppa_two()
	{
		global $ibforums, $std;

		$ibforums->lang['coppa_form_text'] = str_replace("<#FORM_LINK#>", "<a href='{$ibforums->base_url}act=Reg&amp;CODE=12'>{$ibforums->lang['coppa_link_form']}</a>", $ibforums->lang['coppa_form_text']);

		$this->output .= View::make("register.coppa_two");

		$this->page_title = $ibforums->lang['coppa_title'];

		$this->nav = array($ibforums->lang['coppa_title']);
	}

	/*	 * ************************************************** */

	// lost_password_start
	// ------------------
	// Simply shows the lostpassword form
	// What do you want? Blood?
	/*	 * ************************************************** */

	function lost_password_start($errors = "")
	{
		global $ibforums, $std;

		if ($ibforums->vars['bot_antispam'])
		{
			// Sort out the security code

			$r_date = time() - (60 * 60 * 6);

			// Remove old reg requests from the DB

			$ibforums->db->exec("DELETE FROM ibf_reg_antispam
				    WHERE ctime < '$r_date'");

			// Set a new ID for this reg request...

			$regid = md5(uniqid(microtime()));

			// Set a new 6 character numerical string

			mt_srand((double)microtime() * 1000000);

			$reg_code = mt_rand(100000, 999999);

			// Insert into the DB

			$data = [
				'regid'      => $regid,
				'regcode'    => $reg_code,
				'ip_address' => $ibforums->input['IP_ADDRESS'],
				'ctime'      => time(),
			];
			$ibforums->db->insertRow("ibf_reg_antispam", $data);
		}

		$this->page_title = $ibforums->lang['lost_pass_form'];

		$this->nav = array($ibforums->lang['lost_pass_form']);

		if ($errors != "")
		{
			$this->output .= View::make("register.errors", ['data' => $ibforums->lang[$errors]]);
		}

		$this->output .= View::make("register.lost_pass_form", ['lasid' => $regid]);

		if ($ibforums->vars['bot_antispam'] == 'gd')
		{
			$this->output = str_replace("<!--{REG.ANTISPAM}-->",
				View::make("register.bot_antispam_gd", ['regid' => $regid]), $this->output);
		} elseif ($ibforums->vars['bot_antispam'] == 'gif')	{
			$this->output = str_replace("<!--{REG.ANTISPAM}-->",
				View::make("register.bot_antispam", ['regid' => $regid]), $this->output);
		}
		// TODO: add recaptcha
		
	}

	function lost_password_end()
	{
		global $ibforums, $std, $print;

		if ($ibforums->vars['bot_antispam'])
		{
			//--------------------------------------
			// Security code stuff
			//--------------------------------------

			if ($ibforums->input['regid'] == "")
			{
				$this->lost_password_start('err_reg_code');
				return;
			}

			$stmt = $ibforums->db->query("SELECT *
				    FROM ibf_reg_antispam
				    WHERE regid='" . trim(addslashes($ibforums->input['regid'])) . "'");

			if (!$row = $stmt->fetch())
			{
				$this->show_reg_form('err_reg_code');
				return;
			}

			if (trim(intval($ibforums->input['reg_code'])) != $row['regcode'])
			{
				$this->lost_password_start('err_reg_code');
				return;
			}
		}

		//--------------------------------------
		// Back to the usual programming! :o
		//--------------------------------------

		if ($_POST['member_name'] == "")
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_username'));
		}

		//------------------------------------------------------------
		// Check for input and it's in a valid format.
		//------------------------------------------------------------

		$member_name = trim(mb_strtolower($ibforums->input['member_name']));

		if ($member_name == "")
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_username'));
		}

		//------------------------------------------------------------
		// Attempt to get the user details from the DB
		//------------------------------------------------------------

		$stmt = $ibforums->db->query("SELECT name, id, email, mgroup
		    FROM ibf_members
		    WHERE LOWER(name)='" . addslashes($member_name) . "'");

		if (!$stmt->rowCount())
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_such_user'));
		} else
		{
			$member = $stmt->fetch();

			//------------------------------------------------------------
			// Is there a validation key? If so, we'd better not touch it
			//------------------------------------------------------------

			if ($member['id'] == "")
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'no_such_user'));
			}

			$validate_key = md5($std->make_password() . time());

			//------------------------------------------------------------
			// Update the DB for this member.
			//------------------------------------------------------------

			$data = [
				'vid'        => $validate_key,
				'member_id'  => $member['id'],
				'real_group' => $member['mgroup'],
				'temp_group' => $member['mgroup'],
				'entry_date' => time(),
				'validate_type' => 'lost_pass',
				'ip_address' => $ibforums->input['IP_ADDRESS']
			];

			$ibforums->db->insertRow("ibf_validating", $data);

			//------------------------------------------------------------
			// Send out the email.
			//------------------------------------------------------------

			$this->email->get_template("lost_pass");

			$this->email->build_message(array(
			                                 'NAME'       => $member['name'],
			                                 'PASSWORD'   => $new_pass,
			                                 'THE_LINK'   => $this->base_url_nosess . "?act=Reg&CODE=lostpassform&uid=" . $member['id'] . "&aid=" . $validate_key,
			                                 'MAN_LINK'   => $this->base_url_nosess . "?act=Reg&CODE=lostpassform",
			                                 'EMAIL'      => $member['email'],
			                                 'ID'         => $member['id'],
			                                 'CODE'       => $validate_key,
			                                 'IP_ADDRESS' => $ibforums->input['IP_ADDRESS'],
			                            ));

			$this->email->subject = $ibforums->lang['lp_subject'] . ' ' . $ibforums->vars['board_name'];
			$this->email->to      = $member['email'];

			$this->email->send_mail();

			$this->output = View::make("register.show_lostpasswait", ['member' => $member]);
		}

		$this->page_title = $ibforums->lang['lost_pass_form'];
	}

	/*	 * ************************************************** */

	// show_reg_form
	// ------------------
	// Simply shows the registration form, no - really! Thats
	// all it does. It doesn't make the tea or anything.
	// Just the registration form, no more - no less.
	// Unless your server went down, then it's just useless.
	/*	 * ************************************************** */

	function show_reg_form($errors = "")
	{
		global $ibforums, $std;

		if ($ibforums->vars['no_reg'] == 1)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'reg_off'));
		}

		if ($ibforums->vars['reg_auth_type'])
		{
			$ibforums->lang['std_text'] .= "<br>" . $ibforums->lang['email_validate_text'];
		}

		$this->bash_dead_validations();

		//-----------------------------------------------
		// Clean out anti-spam stuffy
		//-----------------------------------------------

		if ($ibforums->vars['bot_antispam'])
		{

			// Get a time roughly 6 hours ago...

			$r_date = time() - (60 * 60 * 6);

			// Remove old reg requests from the DB

			$ibforums->db->query("DELETE FROM ibf_reg_antispam
				    WHERE ctime < '$r_date'");

			// Set a new ID for this reg request...

			$regid = md5(uniqid(microtime()));

			// Set a new 6 character numerical string

			mt_srand((double)microtime() * 1000000);

			$reg_code = mt_rand(100000, 999999);

			// Insert into the DB

			$data = [
				'regid'      => $regid,
				'regcode'    => $reg_code,
				'ip_address' => $ibforums->input['IP_ADDRESS'],
				'ctime'      => time(),
			];

			$ibforums->db->insertRow("ibf_reg_antispam", $data);
		}

		//-----------------------------------------------
		// Custom profile fields stuff
		//-----------------------------------------------

		$required_output = "";
		$optional_output = "";
		$field_data      = array();

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_pfields_data
			    WHERE fshowreg=1
			    ORDER BY forder");

		while ($row = $stmt->fetch())
		{
			$form_element = "";

			if ($row['freq'] == 1)
			{
				$ftype = 'required_output';
			} else
			{
				$ftype = 'optional_output';
			}

			if ($row['ftype'] == 'drop')
			{
				$carray = explode('|', trim($row['fcontent']));

				$d_content = "";

				foreach ($carray as $entry)
				{
					$value = explode('=', $entry);

					$ov = trim($value[0]);
					$td = trim($value[1]);

					if ($ov != "" and $td != "")
					{
						$d_content .= "<option value='$ov'>$td</option>\n";
					}
				}

				if ($d_content != "")
				{
					$form_element = View::make(
						"register.field_dropdown",
						['name' => 'field_' . $row['fid'], 'options' => $d_content]
					);
				}
			} else {
				if ($row['ftype'] == 'area')
				{
					$form_element = View::make(
						"register.field_textarea",
						['name' => 'field_' . $row['fid'], 'value' => $ibforums->input['field_' . $row['fid']]]
					);
				} else
				{
					$form_element = View::make(
						"register.field_textinput",
						['name' => 'field_' . $row['fid'], 'value' => $ibforums->input['field_' . $row['fid']]]
					);
				}
			}

			$row['fdesc'] = str_replace(array("&lt;", "&gt;", "&quot;"), array("<", ">", "\""), $row['fdesc']);

			${$ftype} .= View::make(
				"register.field_entry",
				['title' => $row['ftitle'], 'desc' => $row['fdesc'], 'content' => $form_element]
			);
		}

		$this->page_title = $ibforums->lang['registration_form'];
		$this->nav        = array($ibforums->lang['registration_form']);

		$coppa = ($ibforums->input['coppa_user'] == 1)
			? 1
			: 0;

		if ($errors != "")
		{
			$this->output .= View::make("register.errors", ['data' => $ibforums->lang[$errors]]);
		}

		$this->output .= View::make(
			"register.ShowForm",
			[
				'data' => array(
					'TEXT'       => $ibforums->lang['std_text'],
					'RULES'      => $ibforums->lang['click_wrap'],
					'coppa_user' => $coppa,
				)
			]
		);

		if ($ibforums->vars['bot_antispam'] == 'gd')
		{
			$this->output = str_replace("<!--{REG.ANTISPAM}-->",
				View::make("register.bot_antispam_gd", ['regid' => $regid]), $this->output);
		} elseif ($ibforums->vars['bot_antispam'] == 'gif') {
			$this->output = str_replace("<!--{REG.ANTISPAM}-->",
				View::make("register.bot_antispam", ['regid' => $regid]), $this->output);
		} elseif ($ibforums->vars['bot_antispam'] == 'recaptcha') {
			$this->output = str_replace("<!--{REG.ANTISPAM}-->",
					View::make("register.bot_antispam_recapthca", ['regid' => $regid]), $this->output);
		}
	

		if ($required_output != "")
		{
			$this->output = str_replace("<!--{REQUIRED.FIELDS}-->", "\n" . $required_output, $this->output);
		}

		if ($optional_output != "")
		{
			$this->output = str_replace("<!--{OPTIONAL.FIELDS}-->", View::make("register.optional_title") . "\n" . $optional_output, $this->output);
		}

		if (USE_MODULES == 1)
		{
			$this->modules->register_class($this);
			$this->modules->on_register_form();
		}
	}

	/*	 * ************************************************** */

	// create_account
	// ------------------
	// Now this is a really good subroutine. It adds the member
	// to the members table in the database. Yes, really fancy
	// this one. It also finds the time to see if we need to
	// check any email verification type malarky before we
	// can use this brand new account. It's like buying a new
	// car and getting it towed home and being told the keys
	// will be posted later. Although you can't polish this
	// routine while you're waiting.
	/*	 * ************************************************** */

	function create_account()
	{
		global $ibforums, $std, $print;

		echo '<!-- xxxx';
		var_dump($ibforums->vars['bot_antispam'] );
	echo '-->';	ob_flush();
		if ($_POST['act'] == "")
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'complete_form'));
		}

		if ($ibforums->vars['no_reg'] == 1)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'reg_off'));
		}

		$coppa = ($ibforums->input['coppa_user'] == 1)
			? 1
			: 0;

		//----------------------------------
		// Custom profile field stuff
		//----------------------------------

		$custom_fields = array();

		$stmt = $ibforums->db->query("SELECT *
		    FROM ibf_pfields_data
                    WHERE fshowreg=1");

		while ($row = $stmt->fetch())
		{
			if ($row['freq'] == 1)
			{
				if ($_POST['field_' . $row['fid']] == "")
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'complete_form'));
				}
			}

			if ($row['fmaxinput'] > 0)
			{
				if (mb_strlen($_POST['field_' . $row['fid']]) > $row['fmaxinput'])
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'cf_to_long', 'EXTRA' => $row['ftitle']));
				}
			}

			$custom_fields['field_' . $row['fid']] = str_replace('<br>', "\n", $ibforums->input['field_' . $row['fid']]);
		}

		//---------------------------------------
		// Trim off the username and password

		$in_username = trim(str_replace('|', '&#124;', $ibforums->input['UserName']));
		$in_password = trim($ibforums->input['PassWord']);
		$in_email    = mb_strtolower(trim($ibforums->input['EmailAddress']));

		$ibforums->input['EmailAddress_two'] = mb_strtolower(trim($ibforums->input['EmailAddress_two']));

		if ($ibforums->input['EmailAddress_two'] != $in_email)
		{
			$this->show_reg_form('err_email_address_match');
			return;
		}

		// Remove multiple spaces in the username

		$in_username = preg_replace("/\s{2,}/", " ", $in_username);

		$in_username = str_replace(array("\'", "'", "&#39;"), array("", "", ""), $in_username);

		//-------------------------------------------------
		// More unicode..
		//-------------------------------------------------

		$len_u = $in_username;
		$len_u = preg_replace("/&#([0-9]+);/", "-", $len_u);

		$len_p = $in_password;
		$len_p = preg_replace("/&#([0-9]+);/", "-", $len_p);

		//+--------------------------------------------
		//| Check for errors in the input.
		//+--------------------------------------------

		if (empty($len_u))
		{
			$this->show_reg_form('err_no_username');
			return;
		}
		if (mb_strlen($len_u) < 3)
		{
			$this->show_reg_form('err_no_username');
			return;
		}
		if (mb_strlen($len_u) > 30)
		{
			$this->show_reg_form('err_no_username');
			return;
		}

		if (preg_match("#[a-z]+#i", $in_username) && preg_match("#[а-я]+#i", $in_username))
		{
			$this->show_reg_form('err_rus_en_user_name');
			return;
		}

		if (empty($len_p))
		{
			$this->show_reg_form('err_no_password');
			return;
		}
		if (mb_strlen($len_p) < 3)
		{
			$this->show_reg_form('err_no_password');
			return;
		}
		if (mb_strlen($len_p) > 30)
		{
			$this->show_reg_form('err_no_password');
			return;
		}
		if ($ibforums->input['PassWord_Check'] != $in_password)
		{
			$this->show_reg_form('err_pass_match');
			return;
		}
		if (mb_strlen($in_email) < 6)
		{
			$this->show_reg_form('err_invalid_email');
			return;
		}

		//+--------------------------------------------
		//| Check the email address
		//+--------------------------------------------

		$in_email = $std->clean_email($in_email);

		if (!$in_email)
		{
			$this->show_reg_form('err_invalid_email');
			return;
		}

		//+--------------------------------------------
		//| Is this name already taken?
		//+--------------------------------------------

		$stmt = $ibforums->db->query("SELECT id
		    FROM ibf_members
		    WHERE LOWER(name)='" . addslashes(mb_strtolower($in_username)) . "'
		    LIMIT 1");

		$name_check = $stmt->fetch();

		if ($name_check['id'])
		{
			$this->show_reg_form('err_user_exists');
			return;
		}

		if (mb_strtolower($in_username) == 'guest')
		{
			$this->show_reg_form('err_user_exists');
			return;
		}

		//+--------------------------------------------
		//| Is this email addy taken?
		//+--------------------------------------------

		if (!$ibforums->vars['allow_dup_email'])
		{
			$stmt = $ibforums->db->query("SELECT id
			    FROM ibf_members
			    WHERE email='" . addslashes($in_email) . "'");

			$email_check = $stmt->fetch();

			if ($email_check['id'])
			{
				$this->show_reg_form('err_email_exists');
				return;
			}
		}

		//+--------------------------------------------
		//| Are they in the reserved names list?
		//+--------------------------------------------

		if ($ibforums->vars['ban_names'])
		{
			$names = explode("|", $ibforums->vars['ban_names']);

			foreach ($names as $n)
			{
				if (!$n)
				{
					continue;
				}

				if (preg_match("/" . preg_quote($n, '/') . "/i", $in_username))
				{
					$this->show_reg_form('err_user_exists');
					return;
				}
			}
		}

		//+--------------------------------------------
		//| Are they banned?
		//+--------------------------------------------

		if ($std->is_ip_banned($ibforums->input['IP_ADDRESS']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'you_are_banned',
			                 'EXTRA' => '[' . $ibforums->input['IP_ADDRESS'] . ']',
			                 'INIT'  => 1
			            ));
		}

		if ($ibforums->vars['ban_email'])
		{
			$ips = explode("|", $ibforums->vars['ban_email']);

			foreach ($ips as $ip)
			{
				$ip = preg_replace("/\*/", '.*', $ip);

				if (preg_match("/$ip/", $in_email))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'you_are_banned',
					                 'EXTRA' => '[' . $in_email . ']'
					            ));
				}
			}
		}

		//+--------------------------------------------
		//| Check the reg_code
		//+--------------------------------------------

		if ($ibforums->vars['bot_antispam'])
		{
			if ($ibforums->input['regid'] == "")
			{
				$this->show_reg_form('err_reg_code');
				return;
			}

			$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_reg_antispam
			    WHERE regid='" . trim(addslashes($ibforums->input['regid'])) . "'");

			if (!$row = $stmt->fetch())
			{
				$this->show_reg_form('err_reg_code');
				return;
			}

			if ($ibforums->vars['bot_antispam'] === "recaptcha") {
				if (!$this->validate_recaptcha()) {
					$this->show_reg_form('err_reg_code');
					return;
				}
			} elseif (trim(intval($ibforums->input['reg_code'])) != $row['regcode']) {
				$this->show_reg_form('err_reg_code');
				return;
			}

			$ibforums->db->query("DELETE FROM ibf_reg_antispam
			    WHERE regid='" . trim(addslashes($ibforums->input['regid'])) . "'");
		}

		//+--------------------------------------------
		//| Build up the hashes
		//+--------------------------------------------
		$mem_group = $ibforums->vars['newbie_group'];

		//+--------------------------------------------
		//| Are we asking the member or admin to preview?
		//+--------------------------------------------

		if ($ibforums->vars['reg_auth_type'])
		{
			$mem_group = $ibforums->vars['auth_group'];
		} elseif ($coppa == 1)
		{
			$mem_group = $ibforums->vars['auth_group'];
		}

		//+--------------------------------------------
		//| Find the highest member id, and increment it
		//| auto_increment not used for guest id 0 val.
		//+--------------------------------------------
		$member = array(
			'name'          => $in_username,
			'password'      => $in_password,
			'email'         => $in_email,
			'mgroup'        => $mem_group,
			'old_group'     => $mem_group,
			'posts'         => 0,
			'avatar'        => 'noavatar',
			'joined'        => time(),
			'ip_address'    => $ibforums->input['IP_ADDRESS'],
			'time_offset'   => $ibforums->vars['time_offset'],
			'view_sigs'     => 1,
			'email_pm'      => 1,
			'hide_email'    => 1,
			'view_img'      => 1,
			'view_avs'      => 1,
			'restrict_post' => 0,
			'view_pop'      => 1,
			'vdirs'         => "in:Inbox|sent:Sent Items",
			'msg_total'     => 0,
			'new_msg'       => 0,
			'coppa_user'    => $coppa,
			'language'      => $ibforums->vars['default_language'],
			'quick_reply'   => 1,
			'forums_read'   => '',
		);

		$member['password'] = md5($member['password']);

		if ($_POST['field_3'] == 'e')
		{
			$member['show_status']  = 0;
			$member['show_icons']   = 0;
			$member['show_ratting'] = 0;
			$member['show_wp']      = 0;
		}

		//+--------------------------------------------
		//| Insert into the DB
		//+--------------------------------------------

		$ibforums->db->insertRow("ibf_members", $member);

		// id of new user
		$member_id    = $ibforums->db->lastInsertId();
		$member['id'] = $member_id;

		//------------------------------
		// Отсылка ПМ новым пользователям
		//------------------------------
		//	if ( $mem_group == $ibforums->vars['member_group'] and $ibforums->vars['auto_pm_on'] == 1 )
		if ($mem_group == $ibforums->vars['newbie_group'] and $ibforums->vars['auto_pm_on'] == 1)
		{
			$pm_subject = str_replace("*username*", $in_username, $ibforums->vars['auto_pm_subject']);

			$pm_message = str_replace("*username*", $in_username, $ibforums->vars['auto_pm_message']);

			$std->sendpm($member_id, $pm_message, $pm_subject, $ibforums->vars['auto_pm_from']);
		}

		$ibforums->db->exec("INSERT INTO ibf_member_extra
			(id) VALUES ($member_id)");

		//+--------------------------------------------
		//| Insert into the custom profile fields DB
		//+--------------------------------------------
		// Ensure deleted members profile fields are removed.

		$ibforums->db->query("DELETE FROM ibf_pfields_content
                    WHERE member_id='" . $member['id'] . "'");

		$custom_fields['member_id'] = $member['id'];

		$ibforums->db->insertRow("ibf_pfields_content", $custom_fields);

		//+--------------------------------------------

		if (USE_MODULES == 1)
		{
			$this->modules->register_class($this);
			$this->modules->on_create_account($member);

			if ($this->modules->error == 1)
			{
				return;
			}
		}

		//+--------------------------------------------

		$validate_key = md5($std->make_password() . time());
		$time         = time();

		if ($coppa != 1)
		{
			if (($ibforums->vars['reg_auth_type'] == 'user') or ($ibforums->vars['reg_auth_type'] == 'admin'))
			{

				// We want to validate all reg's via email, after email verificiation has taken place,
				// we restore their previous group and remove the validate_key

				$data = [

					'vid'        => $validate_key,
					'member_id'  => $member['id'],
					'real_group' => $ibforums->vars['newbie_group'],
					'temp_group' => $ibforums->vars['auth_group'],
					'entry_date' => $time,
					'validate_type' => 'new_reg',
					'ip_address' => $member['ip_address'],
					'service'    => ''
				];

				$ibforums->db->insertRow("ibf_validating", $data);

				if ($ibforums->vars['reg_auth_type'] == 'user')
				{

					$this->email->get_template("reg_validate");

					$this->email->build_message(array(
					                                 'THE_LINK' => $this->base_url_nosess . "?act=Reg&CODE=03&uid=" . urlencode($member_id) . "&aid=" . urlencode($validate_key),
					                                 'NAME'     => $member['name'],
					                                 'MAN_LINK' => $this->base_url_nosess . "?act=Reg&CODE=05",
					                                 'EMAIL'    => $member['email'],
					                                 'ID'       => $member_id,
					                                 'CODE'     => $validate_key,
					                            ));

					$this->email->subject = "Registration at " . $ibforums->vars['board_name'];
					$this->email->to      = $member['email'];

					$this->email->send_mail();

					$this->output = View::make("register.show_authorise", ['member' => $member]);
				} else {
					if ($ibforums->vars['reg_auth_type'] == 'admin')
					{
						$this->output = View::make("register.show_preview", ['member' => $member]);
					}
				}

				if ($ibforums->vars['new_reg_notify'])
				{

					$date = $std->get_date(time());

					$this->email->get_template("admin_newuser");

					$this->email->build_message(array(
					                                 'DATE'  => $date,
					                                 'ID'    => $member['id'],
					                                 'NAME'  => $member['name'],
					                                 'EMAIL' => $member['email'],
					                                 'IP_ADDRESS' => $member['ip_address'],
					                                 'WEBSITE'    => $ibforums->input['WebSite'],
					                                 'LOCATION'   => $ibforums->input['Location'],
					                                 'REFERER'    => $_SERVER['HTTP_REFERER'],
					                            ));

					$this->email->subject = "New Registration at " . $ibforums->vars['board_name'];
					$this->email->to      = $ibforums->vars['email_in'];
					$this->email->send_mail();
				}

				$this->page_title = $ibforums->lang['reg_success'];

				$this->nav = array($ibforums->lang['nav_reg']);
			} else
			{

				// We don't want to preview, or get them to validate via email.

				$ibforums->db->exec("UPDATE ibf_stats SET " . "MEM_COUNT=MEM_COUNT+1, " . "LAST_MEM_NAME='" . $member['name'] . "', " . "LAST_MEM_ID='" . $member['id'] . "'");

				if ($ibforums->vars['new_reg_notify'])
				{

					$date = $std->get_date(time());

					$this->email->get_template("admin_newuser");

					$this->email->build_message(array(
					                                 'DATE'        => $date,
					                                 'MEMBER_NAME' => $member['name'],
					                            ));

					$this->email->subject = "New Registration at " . $ibforums->vars['board_name'];
					$this->email->to      = $ibforums->vars['email_in'];
					$this->email->send_mail();
				}

				$std->my_setcookie("member_id", $member['id'], 1);
				$std->my_setcookie("pass_hash", $member['password'], 1);

				$std->boink_it($ibforums->base_url . '&act=Login&CODE=autologin&fromreg=1');
			}
		} else
		{
			// This is a COPPA user, so lets tell them they registered OK and redirect to the form.

			$data = [
				'vid'        => $validate_key,
				'member_id'  => $member['id'],
				//					 'real_group'  => $ibforums->vars['member_group'],
				'real_group' => $ibforums->vars['newbie_group'],
				'temp_group' => $ibforums->vars['auth_group'],
				'entry_date' => $time,
				'validate_type' => 'new_reg',
				'ip_address' => $member['ip_address']
			];

			$ibforums->db->insertRow("ibf_validating", $data);

			$print->redirect_screen($ibforums->lang['cp_success'], 'act=Reg&CODE=12');
		}
	}

	/*	 * ************************************************** */

	// validate_user
	// ------------------
	// Leave a message after the tone, and I'll amuse myself
	// by pulling faces when hearing the message later.
	/*	 * ************************************************** */

	function validate_user()
	{
		global $ibforums, $std;

		//------------------------------------------------------------
		// Check for input and it's in a valid format.
		//------------------------------------------------------------

		$in_user_id      = intval(trim(urldecode($ibforums->input['uid'])));
		$in_validate_key = trim(urldecode($ibforums->input['aid']));
		$in_type         = trim($ibforums->input['type']);

		if (!$in_type)
		{
			$in_type = 'reg';
		}

		//------------------------------------------------------------

		if (!preg_match("/^(?:[\d\w]){32}$/", $in_validate_key))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'data_incorrect'));
		}

		//------------------------------------------------------------

		if (!preg_match("/^(?:\d){1,}$/", $in_user_id))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'data_incorrect'));
		}

		//------------------------------------------------------------
		// Attempt to get the profile of the requesting user
		//------------------------------------------------------------

		$stmt = $ibforums->db->query("SELECT id, name, password, email
			    FROM ibf_members
			    WHERE id=$in_user_id");

		if (!$member = $stmt->fetch())
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'auth_no_mem'));
		}

		//------------------------------------------------------------
		// Get validating info..
		//------------------------------------------------------------

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_validating
			    WHERE member_id=$in_user_id");

		if (!$validate = $stmt->fetch())
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'auth_no_key'));
		}

		if (($validate['validate_type']=='new_reg') && ($ibforums->vars['reg_auth_type'] == "admin"))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'auth_no_key_not_allow'));
		}

		if ($validate['vid'] != $in_validate_key)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'auth_key_wrong'));
		} else
		{
			//------------------------------------------------------------
			// REGISTER VALIDATE
			//------------------------------------------------------------

			if ($in_type == 'reg')
			{
				if ($validate['validate_type'] != 'new_reg')
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'auth_no_key'));
				}

				if (empty($validate['real_group']))
				{
					//					$validate['real_group'] = $ibforums->vars['member_group'];
					$validate['real_group'] = $ibforums->vars['newbie_group'];
				}

				$ibforums->db->exec("UPDATE ibf_members
					    SET mgroup='" . intval($validate['real_group']) . "',
						old_group='" . intval($validate['real_group']) . "'
					    WHERE id='" . intval($member['id']) . "'");

				//------------------------------------------------------------
				// Update the stats...
				//------------------------------------------------------------

				$ibforums->db->exec("UPDATE ibf_stats SET " . "MEM_COUNT=MEM_COUNT+1, " . "LAST_MEM_NAME='" . $member['name'] . "', " . "LAST_MEM_ID='" . $member['id'] . "'");

				$std->my_setcookie("member_id", $member['id'], 1);
				$std->my_setcookie("pass_hash", $member['password'], 1);

				//------------------------------------------------------------
				// Remove "dead" validation
				//------------------------------------------------------------

				$ibforums->db->query("DELETE FROM ibf_validating
					    WHERE vid='" . $validate['vid'] . "'
						OR (member_id={$member['id']}
							AND validate_type='new_reg')");

				$this->bash_dead_validations();
				if ($ibforums->vars['auto_pm_on'] == 1)
				{
					$pm_subject = str_replace("*username*", $member['name'], $ibforums->vars['auto_pm_subject']);

					$pm_message = str_replace("*username*", $member['name'], $ibforums->vars['auto_pm_message']);

					$std->sendpm($member['id'], $pm_message, $pm_subject, $ibforums->vars['auto_pm_from']);
				}
				$std->boink_it($ibforums->base_url . '&act=Login&CODE=autologin&fromreg=1');
			} //------------------------------------------------------------
			// LOST PASS VALIDATE
			//------------------------------------------------------------
			else {
				if ($in_type == 'lostpass')
				{
					if ($validate['validate_type'] != 'lost_pass')
					{
						$std->Error(array('LEVEL' => 1, 'MSG' => 'lp_no_pass'));
					}

					if ($_POST['pass1'] == "")
					{
						$std->Error(array('LEVEL' => 1, 'MSG' => 'pass_blank'));
					}

					if ($_POST['pass2'] == "")
					{
						$std->Error(array('LEVEL' => 1, 'MSG' => 'pass_blank'));
					}

					$pass_a = trim($ibforums->input['pass1']);
					$pass_b = trim($ibforums->input['pass2']);

					if (mb_strlen($pass_a) < 3)
					{
						$std->Error(array('LEVEL' => 1, 'MSG' => 'pass_too_short'));
					}

					if ($pass_a != $pass_b)
					{
						$std->Error(array('LEVEL' => 1, 'MSG' => 'pass_no_match'));
					}

					$new_pass = md5($pass_a);

					$ibforums->db->exec("UPDATE ibf_members SET
						password='$new_pass'
					    WHERE id='" . intval($member['id']) . "'");

					$std->my_setcookie("member_id", $member['id'], 1);
					$std->my_setcookie("pass_hash", $new_pass, 1);

					//------------------------------------------------------------
					// Remove "dead" validation
					//------------------------------------------------------------

					$ibforums->db->query("DELETE FROM ibf_validating
					    WHERE vid='" . $validate['vid'] . "'
						OR (member_id={$member['id']}
							AND validate_type='lost_pass')");

					$this->bash_dead_validations();

					$std->boink_it($ibforums->base_url . '&act=Login&CODE=autologin&frompass=1');
				} //------------------------------------------------------------
				// EMAIL ADDY CHANGE
				//------------------------------------------------------------
				else
				{
					if ($in_type == 'newemail')
					{
						if ($validate['validate_type'] != 'email_chg')
						{
							$std->Error(array('LEVEL' => 1, 'MSG' => 'auth_no_key'));
						}

						if (empty($validate['real_group']))
						{
							//					$validate['real_group'] = $ibforums->vars['member_group'];
							$validate['real_group'] = $ibforums->vars['newbie_group'];
						}

						$ibforums->db->exec("UPDATE ibf_members SET
								mgroup='" . intval($validate['real_group']) . "',
								old_group='" . intval($validate['real_group']) . "'
							    WHERE id='" . intval($member['id']) . "'");

						$std->my_setcookie("member_id", $member['id'], 1);
						$std->my_setcookie("pass_hash", $member['password'], 1);

						//------------------------------------------------------------
						// Remove "dead" validation
						//------------------------------------------------------------

						$ibforums->db->query("DELETE FROM ibf_validating
						    WHERE vid='" . $validate['vid'] . "'
							OR (member_id={$member['id']}
							AND validate_type='email_chg')");

						$this->bash_dead_validations();

						$std->boink_it($ibforums->base_url . '&act=Login&CODE=autologin&fromemail=1');
					}
				}
			}
		}
	}
	
	function validate_recaptcha()
	{
		global $ibforums;
		$recaptcha_response = $ibforums->input['g-recaptcha-response'];
		
		$c = curl_init("https://www.google.com/recaptcha/api/siteverify");
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 100);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_TIMEOUT, 100);
		curl_setopt($c, CURLOPT_POST, 1);
	 curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		
		$request_fields = [
			'secret' => $ibforums->vars['recaptcha_secret'],
			'response' => $recaptcha_response								
		];
		$encoded = '';
		foreach($request_fields as $name => $value) {
			$encoded .= urlencode($name).'='.urlencode($value).'&';
		}
		// chop off last ampersand
		$encoded = substr($encoded, 0, strlen($encoded)-1);
		curl_setopt($c, CURLOPT_POSTFIELDS,  $encoded);
	$res = curl_exec($c);	
		$response = json_decode($res, true);
		echo '<!-- xxxx';
		var_dump($res, $response, curl_error($c));
	echo '-->';	ob_flush();
		curl_close($c);
				
		return $response['success'];
	}

	/*	 * ************************************************** */

	// show_board_rules
	// ------------------
	// o_O  ^^
	/*	 * ************************************************** */

	function show_board_rules()
	{
		global $ibforums;

		$stmt  = $ibforums->db->query("SELECT RULES_TEXT
			    FROM ib_forum_rules
			    WHERE ID='00'");
		$rules = $stmt->fetch();

		$this->output     = View::make("register.show_rules", ['text' => $rules]);//todo no such a template
		$this->page_title = $ibforums->lang['board_rules'];
		$this->nav        = array($ibforums->lang['board_rules']);
	}

	/*	 * ************************************************** */

	// show_manual_form
	// ------------------
	// This feature is not available in an auto option
	/*	 * ************************************************** */

	function show_manual_form($type = 'reg')
	{
		global $ibforums, $std;

		if ($type == 'lostpass')
		{

			$this->output = View::make("register.show_lostpass_form");

			//------------------------------------------------------------
			// Check for input and it's in a valid format.
			//------------------------------------------------------------

			if ($ibforums->input['uid'] AND $ibforums->input['aid'])
			{

				$in_user_id      = intval(trim(urldecode($ibforums->input['uid'])));
				$in_validate_key = trim(urldecode($ibforums->input['aid']));
				$in_type         = trim($ibforums->input['type']);

				if ($in_type == "")
				{
					$in_type = 'reg';
				}

				//------------------------------------------------------------

				if (!preg_match("/^(?:[\d\w]){32}$/", $in_validate_key))
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'data_incorrect'));
				}

				//------------------------------------------------------------

				if (!preg_match("/^(?:\d){1,}$/", $in_user_id))
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'data_incorrect'));
				}

				//------------------------------------------------------------
				// Attempt to get the profile of the requesting user
				//------------------------------------------------------------

				$stmt = $ibforums->db->query("SELECT id, name, password, email
					    FROM ibf_members
					    WHERE id=$in_user_id");

				if (!$member = $stmt->fetch())
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'auth_no_mem'));
				}

				//------------------------------------------------------------
				// Get validating info..
				//------------------------------------------------------------

				$stmt = $ibforums->db->query("SELECT *
					    FROM ibf_validating
					    WHERE member_id=$in_user_id
						AND vid='$in_validate_key'
						AND validate_type='lost_pass'");

				if (!$validate = $stmt->fetch())
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'auth_no_key'));
				}

				$this->output = str_replace("<!--IBF.INPUT_TYPE-->",
					View::make("register.show_lostpass_form_auto", ['aid' => $in_validate_key, 'uid' => $in_user_id]), $this->output);
			} else
			{
				$this->output = str_replace("<!--IBF.INPUT_TYPE-->",
					View::make("register.show_lostpass_form_manual"), $this->output);
			}
		} else
		{
			$this->output = View::make("register.show_dumb_form", ['type' => $type]);
		}

		$this->page_title = $ibforums->lang['activation_form'];
		$this->nav        = array($ibforums->lang['activation_form']);
	}

	function show_image()
	{
		global $ibforums, $std;

		//echo "show_image started<br>";
		//echo "input[rc]=".$ibforums->input['rc']."<br>";

		if ($ibforums->input['rc'] == "")
		{
			return false;
		}

		// Get the info from the db

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_reg_antispam
			    WHERE regid='" . trim(addslashes($ibforums->input['rc'])) . "'");

		if (!$row = $stmt->fetch())
		{
			//echo "regid not found!<br>";
			return false;
		}

		//--------------------------------------------
		// Using GD?
		//--------------------------------------------
		//echo "vars['bot_antispam']=".$ibforums->vars['bot_antispam']."<br>";

		if ($ibforums->vars['bot_antispam'] == 'gd')
		{
			$std->show_gd_img($row['regcode']);
		} else
		{

			//--------------------------------------------
			// Using normal then, check for "p"
			//--------------------------------------------
			//echo "input[p]=".$ibforums->input['p']."<br>";
			if ($ibforums->input['p'] == "")
			{
				return false;
			}

			$p = intval($ibforums->input['p']) - 1; //mb_substr starts from 0, not 1 :p
			//echo "this_number=".$this_number."<br>";

			$this_number = mb_substr($row['regcode'], $p, 1);

			$std->show_gif_img($this_number);
		}
	}

	function bash_dead_validations()
	{
		global $ibforums, $std;

		$mids = array();
		$vids = array();

		// If enabled, remove validating new_reg members & entries from members table

		if (intval($ibforums->vars['validate_day_prune']) > 0)
		{
			$less_than = time() - $ibforums->vars['validate_day_prune'] * 86400;

			$stmt = $ibforums->db->query("SELECT v.vid, v.member_id,
					m.posts
			            FROM ibf_validating v
			            LEFT JOIN ibf_members m ON (v.member_id=m.id)
				    WHERE v.validate_type='new_reg'
					AND v.entry_date < $less_than
					");

			while ($i = $stmt->fetch())
			{
				if (intval($i['posts']) < 1)
				{
					$mids[] = $i['member_id'];
					$vids[] = "'" . $i['vid'] . "'";
				}
			}

			// Remove non-posted validating members

			if (count($mids) > 0)
			{
				$ibforums->db->query("DELETE FROM ibf_members
                                            WHERE id IN(" . implode(",", $mids) . ")");
				$ibforums->db->query("DELETE FROM ibf_member_extra
                                            WHERE id IN(" . implode(",", $mids) . ")");
				$ibforums->db->query("DELETE FROM ibf_pfields_content
                                            WHERE member_id IN(" . implode(",", $mids) . ")");
				$ibforums->db->query("DELETE FROM ibf_validating
                                            WHERE vid IN(" . implode(",", $vids) . ")");

				if (USE_MODULES == 1)
				{
					$this->modules->register_class($this);
					$this->modules->on_delete($mids);
				}
			}
		}
	}

}
