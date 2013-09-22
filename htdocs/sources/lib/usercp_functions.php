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
|   > UserCP functions library
|   > Module written by Matt Mecham
|   > Date started: 20th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

class usercp_functions
{

	var $class;

	function usercp_functions($class)
	{

		$this->class = $class;
	}

	//----------------------------------------------------------------------
	//
	// HANDLE PHOTO OP'S
	//
	//----------------------------------------------------------------------

	function do_photo()
	{
		global $ibforums, $std, $print;

		if (!$_POST['act'])
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'complete_form'
			            ));
		}

		//-------------------------------------
		// Nawty, Nawty!
		//-------------------------------------

		if ($ibforums->input['auth_key'] != $this->class->md5_check)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'del_post'
			            ));
		}

		//------------------------------------
		// Did we press "remove"?
		//------------------------------------

		if ($ibforums->input['remove'])
		{
			$this->bash_uploaded_photos($ibforums->member['id']);

			$stmt = $ibforums->db->query("SELECT id
				    FROM ibf_member_extra
				    WHERE id={$ibforums->member['id']}");

			if ($stmt->rowCount())
			{
				$ibforums->db->exec("UPDATE ibf_member_extra
					    SET
						photo_location='',
						photo_type='',
						photo_dimensions=''
					    WHERE id={$ibforums->member['id']}");
			} else
			{
				$ibforums->db->exec("INSERT INTO ibf_member_extra
					    SET
						photo_location='',
						photo_type='',
						photo_dimensions='',
						id={$ibforums->member['id']}");
			}

			$print->redirect_screen($ibforums->lang['photo_c_up'], "act=UserCP&CODE=photo");
		}

		//------------------------------------
		// NO? CARRY ON!!
		//------------------------------------

		list($p_max, $p_width, $p_height) = explode(":", $ibforums->member['g_photo_max_vars']);

		//-----------------------------------
		// Check to make sure we don't just have
		// http:// in the URL box..
		//------------------------------------

		if (preg_match("/^http:\/\/$/i", $ibforums->input['url_photo']))
		{
			$ibforums->input['url_photo'] = "";
		}

		if (empty($ibforums->input['url_photo']))
		{
			//------------------------------------
			// Lets check for an uploaded photo..
			//------------------------------------

			if ($_FILES['upload_photo']['name'] != "" and ($_FILES['upload_photo']['name'] != "none"))
			{
				$FILE_NAME = $_FILES['upload_photo']['name'];
				$FILE_SIZE = $_FILES['upload_photo']['size'];
				$FILE_TYPE = $_FILES['upload_photo']['type'];

				if ($_FILES['upload_photo']['name'] == "")
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'no_av_name'));
				}

				// Naughty Opera adds the filename on the end
				// of the mime type - we don't want this.

				$FILE_TYPE = preg_replace("/^(.+?);.*$/", "\\1", $FILE_TYPE);

				//------------------------------------
				// Are we allowed to upload a photo?
				//------------------------------------

				if ($p_max < 0)
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'no_av_upload'
					            ));
				}

				//-------------------------------------------------
				// Check the file size
				//-------------------------------------------------

				if ($FILE_SIZE > ($p_max * 1024))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'upload_to_big'
					            ));
				}

				$ext = '.gif';

				switch ($FILE_TYPE)
				{
					case 'image/gif':
						$ext = '.gif';
						break;
					case 'image/jpeg':
						$ext = '.jpg';
						break;
					case 'image/pjpeg':
						$ext = '.jpg';
						break;
					case 'image/x-png':
						$ext = '.png';
						break;
					default:
						$ext = '.gif';
						break;
				}

				$real_name = 'photo-' . $ibforums->member['id'] . $ext;

				//-------------------------------------------------
				// Remove any uploaded images..
				//-------------------------------------------------

				$this->bash_uploaded_photos($ibforums->member['id']);

				//-------------------------------------------------
				// Copy the upload to the uploads directory
				//-------------------------------------------------

				if (!@move_uploaded_file($_FILES['upload_photo']['tmp_name'], $ibforums->vars['upload_dir'] . "/" . $real_name))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'upload_failed'
					            ));
				} else
				{
					@chmod($ibforums->vars['upload_dir'] . "/" . $real_name, 0777);
				}

				//-------------------------------------------------
				// Check image size...
				//-------------------------------------------------

				$im = array();

				if (!$ibforums->vars['disable_ipbsize'])
				{
					$img_size = GetImageSize($ibforums->vars['upload_dir'] . "/" . $real_name);

					$im = $std->scale_image(array(
					                             'max_width'  => $p_width,
					                             'max_height' => $p_height,
					                             'cur_width'  => $img_size[0],
					                             'cur_height' => $img_size[1]
					                        ));
				} else
				{
					$w                = intval($ibforums->input['man_width'])
						? intval($ibforums->input['man_width'])
						: $p_width;
					$h                = intval($ibforums->input['man_height'])
						? intval($ibforums->input['man_height'])
						: $p_height;
					$im['img_width']  = $w > $p_width
						? $p_width
						: $w;
					$im['img_height'] = $h > $p_height
						? $p_height
						: $h;
				}

				$final_location  = $real_name;
				$final_type      = 'upload';
				$final_dimension = $im['img_width'] . ',' . $im['img_height'];
			} else
			{
				// URL field and upload field left blank.

				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'no_photo_selected'
				            ));

			}
		} else
		{
			//-------------------------------------------------
			// It's an entered URL 'ting man
			//-------------------------------------------------

			if (empty($ibforums->vars['allow_dynamic_img']))
			{
				if (preg_match("/[?&;]/", $ibforums->input['url_photo']))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'not_url_photo'
					            ));
				}
			}

			//-------------------------------------------------
			// Check extension
			//-------------------------------------------------

			$ext     = explode("|", $ibforums->vars['photo_ext']);
			$checked = 0;
			$av_ext  = preg_replace("/^.*\.(\S+)$/", "\\1", $ibforums->input['url_photo']);

			foreach ($ext as $v)
			{
				if (mb_strtolower($v) == mb_strtolower($av_ext))
				{
					$checked = 1;
				}
			}

			if ($checked != 1)
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'photo_invalid_ext'
				            ));
			}

			//-------------------------------------------------
			// Check image size...
			//-------------------------------------------------

			$im = array();

			if (!$ibforums->vars['disable_ipbsize'])
			{
				if (!$img_size = @GetImageSize($ibforums->input['url_photo']))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'not_url_photo'
					            ));
				}

				$im = $std->scale_image(array(
				                             'max_width'  => $p_width,
				                             'max_height' => $p_height,
				                             'cur_width'  => $img_size[0],
				                             'cur_height' => $img_size[1]
				                        ));
			} else
			{
				$w                = intval($ibforums->input['man_width'])
					? intval($ibforums->input['man_width'])
					: $p_width;
				$h                = intval($ibforums->input['man_height'])
					? intval($ibforums->input['man_height'])
					: $p_height;
				$im['img_width']  = $w > $p_width
					? $p_width
					: $w;
				$im['img_height'] = $h > $p_height
					? $p_height
					: $h;
			}

			//-------------------------------------------------
			// Remove any uploaded images..
			//-------------------------------------------------

			$this->bash_uploaded_photos($ibforums->member['id']);

			$final_location  = $ibforums->input['url_photo'];
			$final_type      = 'url';
			$final_dimension = $im['img_width'] . ',' . $im['img_height'];
		}

		// Do we have an entry?

		$stmt = $ibforums->db->query("SELECT id
			    FROM ibf_member_extra
			    WHERE id={$ibforums->member['id']}");

		if ($stmt->rowCount())
		{
			$ibforums->db->exec("UPDATE ibf_member_extra
				    SET
					photo_location='$final_location',
					photo_type='$final_type',
					photo_dimensions='$final_dimension'
				    WHERE id={$ibforums->member['id']}");
		} else
		{
			$ibforums->db->exec("INSERT INTO ibf_member_extra
				    SET
					photo_location='$final_location',
					photo_type='$final_type',
					photo_dimensions='$final_dimension',
					id={$ibforums->member['id']}");
		}

		$print->redirect_screen($ibforums->lang['photo_c_up'], "act=UserCP&CODE=photo");

	}

	//----------------------------------------------------------------------
	//
	// REMOVE UPLOADED PICCIES
	//
	//----------------------------------------------------------------------

	function bash_uploaded_photos($id)
	{
		global $ibforums, $std, $print;

		foreach (array('swf', 'jpg', 'jpeg', 'gif', 'png') as $ext)
		{
			if (@file_exists($ibforums->vars['upload_dir'] . "/photo-" . $id . "." . $ext))
			{
				@unlink($ibforums->vars['upload_dir'] . "/photo-" . $id . "." . $ext);
			}
		}
	}

	function bash_uploaded_avatars($id)
	{
		global $ibforums, $std, $print;

		foreach (array('swf', 'jpg', 'jpeg', 'gif', 'png') as $ext)
		{
			if (@file_exists($ibforums->vars['upload_dir'] . "/av-" . $id . "." . $ext))
			{
				@unlink($ibforums->vars['upload_dir'] . "/av-" . $id . "." . $ext);
			}
		}
	}

	//----------------------------------------------------------------------
	//
	// SAVE SKIN/LANG PREFS
	//
	//----------------------------------------------------------------------

	function do_skin_langs()
	{
		global $ibforums, $std, $print;

		// Check input for 1337 h/\x0r nonsense

		if (!$_POST['act'])
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'complete_form'
			            ));
		}

		//-------------------------------------
		// Nawty, Nawty!
		//-------------------------------------

		if ($ibforums->input['auth_key'] != $this->class->md5_check)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'del_post'
			            ));
		}

		//+----------------------------------------

		if (preg_match("/\.\./", $ibforums->input['u_skin']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'poss_hack_attempt'
			            ));
		}
		//+----------------------------------------
		if (preg_match("/\.\./", $ibforums->input['u_language']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'poss_hack_attempt'
			            ));
		}

		//+----------------------------------------

		if ($ibforums->vars['allow_skins'])
		{

			$stmt = $ibforums->db->query("SELECT sid FROM ibf_skins WHERE hidden <> 1 AND sid='" . $ibforums->input['u_skin'] . "'");

			if (!$stmt->rowCount())
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'skin_not_found'
				            ));
			}

			$data = [
				'language' => $ibforums->input['u_language'],
				'skin'     => $ibforums->input['u_skin'],
				'sskin_id' => $ibforums->input['u_sskin'],
			];
		} else
		{
			$data = [
				'language' => $ibforums->input['u_language'],
				'sskin_id' => $ibforums->input['u_sskin'],
			];
		}

		//+----------------------------------------

		$ibforums->db->updateRow("ibf_members", array_map([
		                                                  $ibforums->db,
		                                                  'quote'
		                                                  ], $data), "id = '" . $this->class->member['id'] . "'");

		$print->redirect_screen($ibforums->lang['set_updated'], "act=UserCP&CODE=06");

	}

	function do_board_prefs()
	{
		global $ibforums, $std, $print;

		// Check the input for naughties :D

		//-------------------------------------
		// Nawty, Nawty!
		//-------------------------------------

		if ($ibforums->input['auth_key'] != $this->class->md5_check)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'del_post'
			            ));
		}

		if (!$_POST['act'])
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'complete_form'
			            ));
		}
		//+----------------------------------------
		/* if ( !preg_match( "/^[\-\d\.]+$/", $ibforums->input['u_timezone'] ) )
		{
			$std->Error( array( 'LEVEL' => 1,
					    'MSG' => 'poss_hack_attempt' ) );
		} */

		//+----------------------------------------
		if (!preg_match("/^\d+$/", $ibforums->input['VIEW_IMG']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'poss_hack_attempt'
			            ));
		}

		//+----------------------------------------
		if (!preg_match("/^\d+$/", $ibforums->input['VIEW_SIGS']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'poss_hack_attempt'
			            ));
		}

		//+----------------------------------------
		if (!preg_match("/^\d+$/", $ibforums->input['VIEW_AVS']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'poss_hack_attempt'
			            ));
		}

		//+----------------------------------------
		if (!preg_match("/^\d+$/", $ibforums->input['DO_POPUP']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'poss_hack_attempt'
			            ));
		}

		if (!preg_match("/^\d+$/", $ibforums->input['OPEN_QR']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'poss_hack_attempt'
			            ));
		}

		//+----------------------------------------

		if (!$ibforums->vars['postpage_contents'])
		{
			$ibforums->vars['postpage_contents'] = '5,10,15,20,25,30,35,40';
		}

		if (!$ibforums->vars['topicpage_contents'])
		{
			$ibforums->vars['topicpage_contents'] = '5,10,15,20,25,30,35,40';
		}

		$ibforums->vars['postpage_contents'] .= ",-1,";
		$ibforums->vars['topicpage_contents'] .= ",-1,";

		if (!preg_match("/(^|,)" . $ibforums->input['postpage'] . ",/", $ibforums->vars['postpage_contents']))
		{
			$ibforums->input['postpage'] = '-1';
		}

		//+----------------------------------------

		if (!preg_match("/(^|,)" . $ibforums->input['topicpage'] . ",/", $ibforums->vars['topicpage_contents']))
		{
			$ibforums->input['topicpage'] = '-1';
		}
		if (!in_array($ibforums->input['syntax_show_controls'], array('yes', 'no', 'auto')))
		{
			$ibforums->input['syntax_show_controls'] = 'yes';
		}
		if ($ibforums->input['u_tz_region'] == 'UTC')
		{
			$timezone = 'UTC';
		} else
		{
			$timezone = $ibforums->input['u_tz_region'] . '/' . $ibforums->input['u_tz_zone'];
			if (!timezone_open($timezone))
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'unknown_timezone_selected'
				            ));
			}
		}

		//+----------------------------------------
		if (intval($ibforums->input['VIEW_POST_WRAP_SIZE']) < 0 || intval($ibforums->input['VIEW_POST_WRAP_SIZE']) > 2147483647)
		{
			$post_wrap_size = 0;
		} else
		{
			$post_wrap_size = intval($ibforums->input['VIEW_POST_WRAP_SIZE']);
		}
		$data = array_map([$ibforums->db, 'quote'], [
		                                            'time_offset'               => $timezone,
		                                            //$ibforums->input['u_timezone'],
		                                            'view_avs'                  => $ibforums->input['VIEW_AVS'],
		                                            'view_sigs'                 => $ibforums->input['VIEW_SIGS'],
		                                            'view_img'                  => $ibforums->input['VIEW_IMG'],
		                                            'view_pop'                  => $ibforums->input['DO_POPUP'],
		                                            'dst_in_use'                => $ibforums->input['DST'],
		                                            'show_wp'                   => $ibforums->input['WP_LINK'],
		                                            'cb_forumlist'              => $ibforums->input['CB_FORUMLIST'],
		                                            'close_category'            => $ibforums->input['CATEGORY'],
		                                            'quick_search'              => $ibforums->input['QUICK_SEARCH'],
		                                            'highlight_topic'           => $ibforums->input['HIGHLIGHT'],
		                                            'css_method'                => $ibforums->input['CSS'],
		                                            'hotclocks'                 => $ibforums->input['HOTCLOCKS'],
		                                            'forum_icon'                => $ibforums->input['FORUM_ICON'],
		                                            'show_history'              => $ibforums->input['HISTORY'],
		                                            'show_status'               => $ibforums->input['STATUS'],
		                                            'show_icons'                => $ibforums->input['ICONS'],
		                                            'show_ratting'              => $ibforums->input['RATTING'],
		                                            'show_filter'               => $ibforums->input['FILTER'],
		                                            'syntax'                    => $ibforums->input['SYNTAX'],
		                                            'syntax_lines_count'        => $ibforums->input['SYNTAX_LINES_COUNT'],
		                                            'syntax_show_controls'      => $ibforums->input['SYNTAX_SHOW_CONTROLS'],
		                                            'syntax_use_wrap'           => $ibforums->input['SYNTAX_USE_WRAP'],
		                                            'syntax_use_line_numbering' => $ibforums->input['SYNTAX_USE_LINE_NUMBERING'],
		                                            'syntax_use_line_colouring' => $ibforums->input['SYNTAX_USE_LINE_COLOURING'],
		                                            'show_new'                  => $ibforums->input['SHOW_NEW'],
		                                            'quick_reply'               => $ibforums->input['OPEN_QR'],
		                                            'view_prefs'                => $ibforums->input['postpage'] . "&" . $ibforums->input['topicpage'],
		                                            'post_wrap_size'            => $post_wrap_size,
		                                            ]);

		$ibforums->db->updateRow("ibf_members", $data, "id='" . $this->class->member['id'] . "'");

		$print->redirect_screen($ibforums->lang['set_updated'], "act=UserCP&CODE=04");

	}

	function do_email_settings()
	{
		global $ibforums, $std, $print;

		if (!$_POST['act'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'complete_form'));
		}

		//-------------------------------------
		// Nawty, Nawty!
		//-------------------------------------

		if ($ibforums->input['auth_key'] != $this->class->md5_check)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'del_post'));
		}

		//+----------------------------------------

		//check and set the rest of the info

		foreach (array('hide_email', 'admin_send', 'send_full_msg', 'pm_reminder', 'auto_track') as $v)
		{
			$ibforums->input[$v] = $std->is_number($ibforums->input[$v]);

			if ($ibforums->input[$v] < 1)
			{
				$ibforums->input[$v] = 0;
			}
		}

		$data = [
			'hide_email'        => $ibforums->input['hide_email'],
			'email_full'        => $ibforums->input['send_full_msg'],
			'email_pm'          => $ibforums->input['pm_reminder'],
			'allow_admin_mails' => $ibforums->input['admin_send'],
			'auto_track'        => $ibforums->input['auto_track'],
		];

		$ibforums->db->updateRow("ibf_members", array_map([
		                                                  $ibforums->db,
		                                                  'quote'
		                                                  ], $data), "id='" . $this->class->member['id'] . "'");

		$print->redirect_screen($ibforums->lang['email_c_up'], "act=UserCP&CODE=02");

	}

	//-----------------------------------------------------------------------------

	function set_internal_avatar()
	{
		global $ibforums, $std, $print;

		if (!$_POST['act'])
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'complete_form'
			            ));
		}

		//-------------------------------------
		// Nawty, Nawty!
		//-------------------------------------

		if ($ibforums->input['auth_key'] != $this->class->md5_check)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'del_post'
			            ));
		}

		//+----------------------------------------

		$real_choice = 'noavatar';
		$real_dims   = '';
		$real_dir    = "";
		$save_dir    = "";

		//+----------------------------------------
		// Check incoming..
		//+----------------------------------------

		$current_folder  = preg_replace("/[^\s\w_-]/", "", urldecode($ibforums->input['current_folder']));
		$selected_avatar = preg_replace("/[^\s\w\._\-\[\]\(\)]/", "", urldecode($ibforums->input['avatar']));

		//+----------------------------------------
		// Are we in a folder?
		//+----------------------------------------

		if ($current_folder == 'root')
		{
			$current_folder = "";
		}

		if ($current_folder != "")
		{
			$real_dir = "/" . $current_folder;
			$save_dir = $current_folder . "/";
		}

		//+----------------------------------------
		// Check it out!
		//+----------------------------------------

		$avatar_gallery = array();

		$dh = opendir($ibforums->vars['html_dir'] . 'avatars' . $real_dir);

		while ($file = readdir($dh))
		{
			if (!preg_match("/^..?$|^index/i", $file))
			{
				$avatar_gallery[] = $file;
			}
		}
		closedir($dh);

		if (!in_array($selected_avatar, $avatar_gallery))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_avatar_selected'
			            ));
		}

		$final_string = $save_dir . $selected_avatar;

		// Update the DB

		$ibforums->db->exec("UPDATE ibf_members
			    SET avatar='$final_string'
			    WHERE id='" . $ibforums->member['id'] . "'");

		$print->redirect_screen($ibforums->lang['av_c_up'], "act=UserCP&CODE=24");

	}

	//-----------------------------------------------------------------------------

	function do_avatar()
	{
		global $ibforums, $std, $print;

		if (!$_POST['act'])
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'complete_form'
			            ));
		}

		//-------------------------------------
		// Nawty, Nawty!
		//-------------------------------------

		if ($ibforums->input['auth_key'] != $this->class->md5_check)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'del_post'
			            ));
		}

		//------------------------------------
		// Did we press "remove"?
		//------------------------------------

		if ($ibforums->input['remove'])
		{
			$this->bash_uploaded_avatars($ibforums->member['id']);

			$ibforums->db->exec("UPDATE ibf_members
				    SET
					avatar='noavatar',
					avatar_size=''
				    WHERE id='" . $this->class->member['id'] . "'");

			$print->redirect_screen($ibforums->lang['av_c_up'], "act=UserCP&CODE=24");

		}

		//------------------------------------
		// NO? CARRY ON!!
		//------------------------------------

		list($p_width, $p_height) = explode("x", $ibforums->vars['avatar_dims']);

		//-----------------------------------
		// Check to make sure we don't just have
		// http:// in the URL box..
		//------------------------------------

		if (preg_match("/^http:\/\/$/i", $ibforums->input['url_avatar']))
		{
			$ibforums->input['url_avatar'] = "";
		}

		if (preg_match("#javascript:#is", $ibforums->input['url_avatar']))
		{
			$ibforums->input['url_avatar'] = "";
		}

		if (empty($ibforums->input['url_avatar']))
		{
			//------------------------------------
			// Lets check for an uploaded photo..
			//------------------------------------

			if ($_FILES['upload_avatar']['name'] != "" and ($_FILES['upload_avatar']['name'] != "none"))
			{
				$FILE_NAME = $_FILES['upload_avatar']['name'];
				$FILE_SIZE = $_FILES['upload_avatar']['size'];
				$FILE_TYPE = $_FILES['upload_avatar']['type'];

				if ($_FILES['upload_avatar']['name'] == "")
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'no_av_name'
					            ));
				}

				//-------------------------------------------------
				// Naughty Opera adds the filename on the end of the
				// mime type - we don't want this.
				//-------------------------------------------------

				$FILE_TYPE = preg_replace("/^(.+?);.*$/", "\\1", $FILE_TYPE);

				//-------------------------------------------------
				// Are we allowed to upload this avatar?
				//-------------------------------------------------

				if (($ibforums->member['g_avatar_upload'] != 1) or ($ibforums->vars['avup_size_max'] < 1))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'no_av_upload'
					            ));
				}

				// Check to make sure it's the correct content type.
				// Naughty Nominell won't be able to use PNG :P

				require "./conf_mime_types.php";

				if ($mime_types[$FILE_TYPE][3] != 1)
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'no_av_type'
					            ));
				}

				//-------------------------------------------------
				// Check the file size
				//-------------------------------------------------

				if ($FILE_SIZE > ($ibforums->vars['avup_size_max'] * 1024))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'upload_to_big'
					            ));
				}

				$ext = '.gif';

				switch ($FILE_TYPE)
				{
					case 'image/gif':
						$ext = '.gif';
						break;
					case 'image/jpeg':
						$ext = '.jpg';
						break;
					case 'image/pjpeg':
						$ext = '.jpg';
						break;
					case 'image/x-png':
						$ext = '.png';
						break;
					case 'image/png':
						$ext = '.png';
						break;
					case "application/x-shockwave-flash":
						if ($ibforums->vars['allow_flash'] != 1)
						{
							$std->Error(array(
							                 'LEVEL' => 1,
							                 'MSG'   => 'no_flash_av'
							            ));
						}
						$ext = '.swf';
						break;
					default:
						$ext = '.gif';
						break;
				}

				$real_name = 'av-' . $ibforums->member['id'] . $ext;

				//-------------------------------------------------
				// Remove any uploaded avatars..
				//-------------------------------------------------

				$this->bash_uploaded_avatars($ibforums->member['id']);

				//-------------------------------------------------
				// Copy the upload to the uploads directory
				//-------------------------------------------------

				if (!@move_uploaded_file($_FILES['upload_avatar']['tmp_name'], $ibforums->vars['upload_dir'] . "/" . $real_name))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'upload_failed'
					            ));
				} else
				{
					@chmod($ibforums->vars['upload_dir'] . "/" . $real_name, 0777);
				}

				//-------------------------------------------------
				// Check image size...
				//-------------------------------------------------

				$im = array();

				if (!$ibforums->vars['disable_ipbsize'])
				{
					$img_size = GetImageSize($ibforums->vars['upload_dir'] . "/" . $real_name);

					$im = $std->scale_image(array(
					                             'max_width'  => $p_width,
					                             'max_height' => $p_height,
					                             'cur_width'  => $img_size[0],
					                             'cur_height' => $img_size[1]
					                        ));
				} else
				{
					$w                = intval($ibforums->input['man_width'])
						? intval($ibforums->input['man_width'])
						: $p_width;
					$h                = intval($ibforums->input['man_height'])
						? intval($ibforums->input['man_height'])
						: $p_height;
					$im['img_width']  = $w > $p_width
						? $p_width
						: $w;
					$im['img_height'] = $h > $p_height
						? $p_height
						: $h;
				}

				// Set the "real" avatar..

				$real_choice = 'upload:' . $real_name;
				$real_dims   = $im['img_width'] . 'x' . $im['img_height'];
			} else
			{
				// URL field and upload field left blank.

				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'no_avatar_selected'
				            ));

			}
		} else
		{
			//-------------------------------------------------
			// It's an entered URL 'ting man
			//-------------------------------------------------

			$ibforums->input['url_avatar'] = trim($ibforums->input['url_avatar']);

			if (empty($ibforums->vars['allow_dynamic_img']))
			{
				if (preg_match("/[?&;]/", $ibforums->input['url_avatar']))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'avatar_invalid_url'
					            ));
				}
			}

			//-------------------------------------------------
			// Check extension
			//-------------------------------------------------

			$ext     = explode("|", $ibforums->vars['avatar_ext']);
			$checked = 0;
			$av_ext  = preg_replace("/^.*\.(\S+)$/", "\\1", $ibforums->input['url_avatar']);

			foreach ($ext as $v)
			{
				if (mb_strtolower($v) == mb_strtolower($av_ext))
				{
					if (($v == 'swf') AND ($ibforums->vars['allow_flash'] != 1))
					{
						$std->Error(array(
						                 'LEVEL' => 1,
						                 'MSG'   => 'no_flash_av'
						            ));
					}

					$checked = 1;
				}
			}

			if ($checked != 1)
			{
				$std->Error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => 'avatar_invalid_ext'
				            ));
			}

			//-------------------------------------------------
			// Check image size...
			//-------------------------------------------------

			$im = array();

			if (!$ibforums->vars['disable_ipbsize'])
			{
				if (!$img_size = @GetImageSize($ibforums->input['url_avatar']))
				{
					//$std->Error( array( 'LEVEL' => 1, 'MSG' => 'avatar_invalid_url' ) );

					$img_size[0] = $p_width;
					$img_size[1] = $p_height;
				}

				$im = $std->scale_image(array(
				                             'max_width'  => $p_width,
				                             'max_height' => $p_height,
				                             'cur_width'  => $img_size[0],
				                             'cur_height' => $img_size[1]
				                        ));
			} else
			{
				$w                = intval($ibforums->input['man_width'])
					? intval($ibforums->input['man_width'])
					: $p_width;
				$h                = intval($ibforums->input['man_height'])
					? intval($ibforums->input['man_height'])
					: $p_height;
				$im['img_width']  = $w > $p_width
					? $p_width
					: $w;
				$im['img_height'] = $h > $p_height
					? $p_height
					: $h;
			}

			//-------------------------------------------------
			// Remove any uploaded images..
			//-------------------------------------------------

			$this->bash_uploaded_avatars($ibforums->member['id']);

			$real_choice = $ibforums->input['url_avatar'];
			$real_dims   = $im['img_width'] . 'x' . $im['img_height'];
		}

		//-------------------------------------------------
		// Update the DB
		//-------------------------------------------------

		$ibforums->db->exec("UPDATE ibf_members
			    SET
				avatar='$real_choice',
				avatar_size='$real_dims'
			    WHERE id='" . $this->class->member['id'] . "'");

		$print->redirect_screen($ibforums->lang['av_c_up'], "act=UserCP&CODE=24");

	}

	function do_profile()
	{
		global $ibforums, $std, $print;

		//----------------------------------
		// Check for bad entry
		//----------------------------------

		if (!$_POST['act'])
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'complete_form'
			            ));
		}

		//-------------------------------------
		// Nawty, Nawty!
		//-------------------------------------

		if ($ibforums->input['auth_key'] != $this->class->md5_check)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'del_post'
			            ));
		}

		//----------------------------------
		// Custom profile field stuff
		//----------------------------------

		$custom_fields = array();

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_pfields_data
			    WHERE fedit=1");

		while ($row = $stmt->fetch())
		{
			if ($row['freq'] == 1)
			{
				if ($_POST['field_' . $row['fid']] == "")
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'complete_form'
					            ));
				}
			}

			if ($row['fmaxinput'] > 0)
			{
				if (mb_strlen($_POST['field_' . $row['fid']]) > $row['fmaxinput'])
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'cf_to_long',
					                 'EXTRA' => $row['ftitle']
					            ));
				}
			}

			$custom_fields['field_' . $row['fid']] = str_replace('<br>', "\n", $ibforums->input['field_' . $row['fid']]);
		}

		//+--------------------

		if ((mb_strlen($_POST['Interests']) > $ibforums->vars['max_interest_length']) and ($ibforums->vars['max_interest_length']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'int_too_long'
			            ));
		}
		//+--------------------
		if ((mb_strlen($_POST['Location']) > $ibforums->vars['max_location_length']) and ($ibforums->vars['max_location_length']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'loc_too_long'
			            ));
		}
		//+--------------------
		if (mb_strlen($_POST['WebSite']) > 150)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'web_too_long'
			            ));
		}
		//+--------------------
		if (mb_strlen($_POST['Photo']) > 150)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'photo_too_long'
			            ));
		}
		//+--------------------
		if (($_POST['ICQNumber']) && (!preg_match("/^(?:\d+)$/", $_POST['ICQNumber'])))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'not_icq_number'
			            ));
		}

		//----------------------------------
		// make sure that either we entered
		// all calendar fields, or we left them
		// all blank
		//----------------------------------

		$c_cnt = 0;

		foreach (array('day', 'month', 'year') as $v)
		{
			if (!empty($ibforums->input[$v]))
			{
				$c_cnt++;
			}
		}

		if (($c_cnt > 0) and ($c_cnt != 3))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'calendar_not_all'
			            ));
		}

		if (!preg_match("#^http://#", $ibforums->input['WebSite']))
		{
			$ibforums->input['WebSite'] = 'http://' . $ibforums->input['WebSite'];
		}

		if (!preg_match("#^[mf]$#", $ibforums->input['gender']))
		{
			$ibforums->input['gender'] = '';
		}

		//----------------------------------
		// Start off our array
		//----------------------------------

		$set = array(
			'website'    => $ibforums->input['WebSite'],
			'icq_number' => $ibforums->input['ICQNumber'],
			'aim_name'   => $ibforums->input['AOLName'],
			'yahoo'      => $ibforums->input['YahooName'],
			'msnname'    => $ibforums->input['MSNName'],
			'integ_msg'  => $ibforums->input['integ_msg'],
			'location'   => $ibforums->input['Location'],
			'gender'     => $ibforums->input['gender'],
			'interests'  => $ibforums->input['Interests'],
			'bday_day'   => $ibforums->input['day'],
			'bday_month' => $ibforums->input['month'],
			'bday_year'  => $ibforums->input['year'],
		);

		// vot * Check if Rename enabled
		if ($ibforums->vars['allow_user_rename'])
		{

			// Song * change member name, 4.01.05

			if ($_POST['MemberName'])
			{
				$username = $_POST['MemberName'];

				$username = str_replace(array("\'", "'", "&#39;"), array("", "", ""), $username);
				$username = trim(str_replace('|', '&#124;', $username));
				$username = preg_replace("/\s{2,}/", " ", $username);

				$stmt = $ibforums->db->query("SELECT name
				    FROM ibf_members
				    WHERE
				    	LOWER(name)='" . addslashes(mb_strtolower($username)) . "'
					AND
					id!='" . $ibforums->member['id'] . "'
				    LIMIT 1");

				if ($stmt->rowCount() > 0)
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'user_exists'
					            ));
				}

				if ($ibforums->vars['ban_names'])
				{
					$names = explode("|", $ibforums->vars['ban_names']);

					foreach ($names as $name)
					{
						if (!$name)
						{
							continue;
						}

						if (preg_match("/" . preg_quote($name, '/') . "/i", $username))
						{
							$std->Error(array(
							                 'LEVEL' => 1,
							                 'MSG'   => 'wrong_member_name'
							            ));
						}
					}
				}

				$len_u = $username;
				$len_u = preg_replace("/&#([0-9]+);/", "-", $len_u);

				if (empty($len_u))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'wrong_member_name'
					            ));
				}

				if (mb_strlen($len_u) < 3)
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'username_short'
					            ));
				}

				if (mb_strlen($len_u) > 30)
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'username_long'
					            ));
				}

				if (preg_match("#[a-z]+#i", $len_u) && preg_match("#[а-я]+#i", $len_u))
				{
					$std->Error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => 'rus_en_user_name'
					            ));
				}

				$set['name'] = $len_u;
			}

		}

		//----------------------------------
		// check to see if we can enter a member title
		// and if one is entered, update it.
		//----------------------------------

		if (isset($ibforums->input['member_title']) and isset($ibforums->vars['post_titlechange']) and
		                                                $this->class->member['posts'] >= $ibforums->vars['post_titlechange']
		)
		{
			$set['title'] = $ibforums->input['member_title'];
		}

		//----------------------------------
		// Update the DB
		//----------------------------------

		$ibforums->db->updateRow("ibf_members", array_map([$ibforums->db, 'quote'], $set), "id='" . $this->class->member['id'] . "'");

		//----------------------------------
		// Save the profile stuffy wuffy
		//----------------------------------

		if (count($custom_fields) > 0)
		{
			// Do we already have an entry in the content table?

			$stmt = $ibforums->db->query("SELECT member_id
				    FROM ibf_pfields_content
				    WHERE member_id='" . $ibforums->member['id'] . "'");

			$test = $stmt->fetch();

			if ($test['member_id'])
			{
				// We have it, so simply update

				$ibforums->db->updateRow("ibf_pfields_content", array_map([$ibforums->db, 'quote'], $custom_fields), "member_id='" . $ibforums->member['id'] . "'");
			} else
			{
				$custom_fields['member_id'] = $ibforums->member['id'];

				$ibforums->db->insertRow("ibf_pfields_content", $custom_fields);
			}

		}

		//--------------------------------------------
		// Use sync module?
		//--------------------------------------------

		if (USE_MODULES == 1)
		{
			$set['id'] = $ibforums->member['id'];
			$this->class->modules->register_class($this);
			$this->class->modules->on_profile_update($set, $custom_fields);
		}

		// Return us!
		$print->redirect_screen($ibforums->lang['profile_edited'], "act=UserCP&CODE=01");

	}

	function do_signature()
	{
		global $ibforums, $std, $print;

		if (!$_POST['act'])
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'complete_form'
			            ));
		}
		//+----------------------------------------

		//----------------------------------
		// Check for bad entry
		//----------------------------------

		if ((mb_strlen($_POST['Post']) > $ibforums->vars['max_sig_length']) and ($ibforums->vars['max_sig_length']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'sig_too_long'
			            ));
		}

		if ($_POST['key'] != $std->return_md5_check())
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'del_post'
			            ));
		}

		//----------------------------------
		// Check for valid IB CODE
		//----------------------------------
		//
		// For efficiency, we convert the IBF code into HTML and store it in the DB
		// Otherwise we'll have to parse the siggies each time we view a post - that
		// gets boring after a while.
		//
		// We will adjust raw HTML on the fly, as some admins may allow it until it's abused
		// then switch it off. If we pre-compile HTML in siggies, we'd have to edit everyones
		// siggies to remove it. We don't want that.
		//
		// I'm going to stick my neck out again and say that most admins will allow IBF Code
		// in siggies, so it's not much of a bother.

		$ibforums->input['Post'] = $this->class->parser->convert(array(
		                                                              'TEXT'      => $ibforums->input['Post'],
		                                                              'SMILIES'   => 0,
		                                                              'CODE'      => $ibforums->vars['sig_allow_ibc'],
		                                                              'HTML'      => $ibforums->vars['sig_allow_html'],
		                                                              'SIGNATURE' => 1
		                                                         ));

		if ($this->class->parser->error != "")
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => $this->class->parser->error
			            ));
		}

		//Write it to the DB.

		$ibforums->input['Post'] = preg_replace("/'/", "\\'", $ibforums->input['Post']);

		$ibforums->db->exec("UPDATE ibf_members
			    SET signature='" . $ibforums->input['Post'] . "'
			    WHERE id ='" . $this->class->member['id'] . "'");

		// Buh BYE:

		$std->boink_it($this->class->base_url . "act=UserCP&CODE=22");

		exit;
	}

}
