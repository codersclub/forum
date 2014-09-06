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
|   > Import functions
|   > Module written by Matt Mecham
|   > Date started: 22nd April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_langs();

class ad_langs
{

	var $base_url;

	function ad_langs()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------

		$tmp_in = array_merge($_GET, $_POST, $_COOKIE);

		foreach ($tmp_in as $k => $v)
		{
			unset($$k);
		}

		//---------------------------------------

		switch ($IN['code'])
		{

			case 'doimport':
				$this->doimport();
				break;

			case 'import':
				$this->import();
				break;

			case 'remove':
				$this->remove();
				break;

			//-------------------------
			default:
				$this->list_current();
				break;
		}

	}

	//---------------------------------------------
	// Remove archived files
	//---------------------------------------------

	function remove()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['id'] == "")
		{
			$ADMIN->error("You did not select a tar-chive to import!");
		}

		$this->tar_with_path = $INFO['base_dir'] . "archive_in/" . $IN['id'];

		if (!unlink($this->tar_with_path))
		{
			$ADMIN->error("Could not remove that file, please check the CHMOD permissions");
		}

		$std->boink_it($ADMIN->base_url . "&act=import");
		exit();

	}

	//---------------------------------------------
	// Import switcheroo
	//---------------------------------------------

	function import()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['id'] == "")
		{
			$ADMIN->error("You did not select a tar-chive to import!");
		}

		$this->tar_with_path = $INFO['base_dir'] . "archive_in/" . $IN['id'];

		$this->work_path = $INFO['base_dir'] . "archive_in";

		if (!file_exists($this->tar_with_path))
		{
			$ADMIN->error("That archive is not found on the server, it may have been deleted by another admin");
		}

		$this->tar_file = $IN['id'];

		$this->name_translated = preg_replace("/^(css|image|set|tmpl)-(.+?)\.(\S+)$/", "\\2", $this->tar_file);
		$this->name_translated = preg_replace("/_/", " ", $this->name_translated);

		require ROOT_PATH . "sources/lib/tar.php";

		$this->tar = new tar();

		switch ($IN['type'])
		{
			case 'css':
				$this->css_import();
				break;

			case 'image':
				$this->image_import();
				break;

			case 'tmpl':
				$this->template_import();
				break;
			case 'set':
				$this->set_import();
				break;

			//---------
			default:
				$ADMIN->error("Unrecognised archive type");
				break;
		}

	}

	function set_import()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums;

		$images_dir = $INFO['base_dir'] . "style_images";
		$skins_dir  = $INFO['base_dir'] . "Skin";

		if (!is_writeable($images_dir))
		{
			$ADMIN->error("Cannot write to the $images_dir directory, please set sufficient CHMOD permissions to allow this. IBF cannot do this for you.");
		}

		if (!is_dir($skins_dir))
		{
			$ADMIN->error("Cannot write to the $skins_dir directory, It is not there.");
		}

		if (!is_writeable($skins_dir))
		{
			$ADMIN->error("Cannot write to the $skins_dir directory, please set sufficient CHMOD permissions to allow this. IBF cannot do this for you.");
		}

		//------------------------------------------------------

		$this->tar->new_tar($this->work_path, $this->tar_file);

		$files = $this->tar->list_files();

		if (!$this->check_archive($files))
		{
			$ADMIN->error("That is not a valid tar-achive, please re-upload in binary and try again");
		}

		//------------------------------------------------------
		// Attempt to create a new work directory
		//------------------------------------------------------

		$new_dir = preg_replace("/^(.+?)\.tar$/", "\\1", $this->tar_file);

		$this->new_dir = $new_dir;

		if (!mkdir($this->work_path . "/" . $new_dir, 0777))
		{
			$ADMIN->error("Directory creation failed, cannot import skin set. Please check the permission in 'archive_in'");
		}

		@chmod($this->work_path . "/" . $new_dir, 0777);

		$next_id = array('css' => 0, 'wrap' => 0, 'templates' => 0, 'macro' => 0);

		//------------------------------------------------------
		// Get the new macro set_id
		//------------------------------------------------------

		$stmt = $ibforums->db->query("SELECT MAX(set_id) as max FROM ibf_macro_name");

		$max = $stmt->fetch();

		$next_id['macro'] = $max['max'] + 1;

		//------------------------------------------------------
		// Attempt to create the new directories
		//------------------------------------------------------

		$next_id['images'] = str_replace(" ", "_", mb_substr($this->name_translated, 0, 8)) . '-' . mb_substr(time(), 7, 10);

		if (!mkdir($images_dir . "/" . $next_id['images'], 0777))
		{
			$this->import_error("Could not create a new directory in style_images", $next_id);
		}

		@chmod($images_dir . "/" . $next_id['images'], 0777);

		//------------------------------------------------------

		$this->tar->extract_files($this->work_path . "/" . $new_dir);

		if ($this->tar->error != "")
		{
			$this->import_error($this->tar->error, $next_id);
		}

		//------------------------------------------------------
		// Import the CSS
		//------------------------------------------------------

		if ($FH = fopen($this->work_path . "/" . $new_dir . "/stylesheet.css", 'r'))
		{
			$css = fread($FH, filesize($this->work_path . "/" . $new_dir . "/stylesheet.css"));
			fclose($FH);

			//-------------------------
			// Swop Binary to Ascii
			//-------------------------

			$css = preg_replace("/\r/", "\n", stripslashes($css));

			file_put_contents(app_path("/assets/stylesheets/skins/css_{$next_id['css']}.scss"), $css);

		} else
		{
			$this->import_error("Could not read the uploaded CSS archive file, please check the permissions on that file and try again", $next_id);
		}

		//------------------------------------------------------
		// Attempt to copy over the image files
		//------------------------------------------------------

		if (!$ADMIN->copy_dir($this->work_path . "/" . $new_dir . "/images", $images_dir . "/" . $next_id['images']))
		{
			$this->import_error("Could not import images, terminating the import", $next_id);
		}

		//------------------------------------------------------
		// Import the Macro's
		//------------------------------------------------------

		if ($FH = fopen($this->work_path . "/" . $new_dir . "/macro.txt", 'r'))
		{
			$data = fread($FH, filesize($this->work_path . "/" . $new_dir . "/macro.txt"));
			fclose($FH);

			$init_array = array();
			$final_keys = array();

			$init_array = explode("\n", $data);

			foreach ($init_array as $l)
			{
				if (preg_match("~=~", $l))
				{
					// is valid line

					list($k, $v) = explode("~=~", $l);

					$k = trim($k);
					$v = trim($v);

					$final_keys[$k] = $v;
				}
			}

			foreach ($final_keys as $k => $v)
			{
				if ($v == '*UNASSIGNED*')
				{
					$v = "";
				}

				$data = [
					'macro_value'   => stripslashes($k),
					'macro_replace' => stripslashes($v),
					'macro_set'     => $next_id['macro'],
					'can_remove'    => 1,
				];

				$ibforums->db->insertRow("ibf_macro", $data);
			}

			// Add the macro name

			$ibforums->db->exec("INSERT INTO ibf_macro_name SET set_id='" . $next_id['macro'] . "', set_name='" . $this->name_translated . "'");

		} else
		{
			$this->import_error("Could not read the macro.txt file contained in the skin you're importing.", $next_id);
		}

		//------------------------------------------------------
		// Add a new row to the skins table.
		//------------------------------------------------------

		$stmt = $ibforums->db->query("SELECT MAX(sid) as new_id FROM ibf_skins");

		$set = $stmt->fetch();

		$set['new_id']++;

		$new_name = stripslashes($this->name_translated) . " (Import)" . $set['new_id'];

		$data = [
			'sname'       => $new_name,
			'sid'         => $set['new_id'],
			'set_id'      => $next_id['templates'],
			'template_class' => html_entity_decode($set['template_class']),
			'img_dir'     => $next_id['images'],
			'macro_id'    => $next_id['macro'],
			'hidden'      => 0,
		];

		\Models\Skins::add($data);

		$ADMIN->rm_dir($this->work_path . "/" . $new_dir);

		$ADMIN->done_screen("Skin set Imported", "Manage Skin sets", "act=sets");

	}

	//------------------------------------------------------
	//process the template group
	//------------------------------------------------------

	function process_template_group($raw, $setid, $group, $isnew = 0)
	{

		return TRUE;

	}

	//-------------------------------------------------------------------

	function image_import()
	{
		// Depreciated

	}

	//-------------------------------------------------------------------

	function check_archive($files)
	{
		if (count($files) > 0)
		{
			foreach ($files as $giles)
			{
				if (!preg_match("/^(?:[\(\)\:\;\~\.\w\d\+\-\_\/]+)$/", $giles))
				{
					return FALSE;
				}
			}
		} else
		{
			return FALSE;
		}

		return TRUE;
	}

	//-------------------------------------------------------------
	// SHOW ALL LANGUAGE PACKS
	//-------------------------------------------------------------

	function list_current()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$form_array = array();

		$ADMIN->page_detail = "You can select which archives to import onto your board in this section. All archives must be uploaded into the 'archive_in' directory";
		$ADMIN->page_title  = "Import Skin Archive Manager";

		//+-------------------------------

		$files = array();

		$dir = $INFO['base_dir'] . "/archive_in";

		if (is_dir($dir))
		{
			$handle = opendir($dir);

			while (($filename = readdir($handle)) !== false)
			{
				if (($filename != ".") && ($filename != ".."))
				{
					if (preg_match("/^(css|image|set|tmpl).+?\.(tar|html|css)$/", $filename))
					{
						$files[] = $filename;
					}
				}
			}

			closedir($handle);

		}

		//+-------------------------------

		$SKIN->td_header[] = array("Name", "30%");
		$SKIN->td_header[] = array("Type", "20%");
		$SKIN->td_header[] = array("File Name", "30%");
		$SKIN->td_header[] = array("Import", "10%");
		$SKIN->td_header[] = array("Remove", "10%");

		$ADMIN->html .= $SKIN->start_table("Current Archives Uploaded");

		if (count($files) > 0)
		{
			foreach ($files as $file)
			{

				$type = array(
					'css'   => 'Style Sheet',
					'image' => 'Image & Macro set',
					'set'   => 'Skin Set Collection',
					'tmpl'  => 'Template set'
				);

				$rtype = preg_replace("/^(css|image|set|tmpl).+?\.(\S+)$/", "\\1", $file);

				$rname = preg_replace("/^(css|image|set|tmpl)-(.+?)\.(\S+)$/", "\\2", $file);

				$rname = preg_replace("/_/", " ", $rname);

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<b>$rname</b>",
				                                       "<center>{$type[$rtype]}</center>",
				                                       "<center>$file</center>",
				                                       "<center><a href='" . $SKIN->base_url . "&act=import&code=import&type=$rtype&id=$file'>Import</a></center>",
				                                       "<center><a href='" . $SKIN->base_url . "&act=import&code=remove&id=$file'>Remove</a></center>",
				                                  ));
			}

		}

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

	function unconvert_tags($t = "")
	{
		if ($t == "")
		{
			return "";
		}

		// Make some tags safe..

		$t = preg_replace("/\{ibf\.vars\.(sql_driver|sql_host|sql_database|sql_pass|sql_user|sql_port|sql_tbl_prefix|smtp_host|smtp_port|smtp_user|smtp_pass|html_dir|base_dir|upload_dir)\}/", "", $t);

		$t = preg_replace("/{ibf\.script_url}/i", '{$ibforums->base_url}', $t);
		$t = preg_replace("/{ibf\.session_id}/i", '{$ibforums->session_id}', $t);
		$t = preg_replace("/{ibf\.skin\.(\w+)}/", '{$ibforums->skin[\'' . "\\1" . '\']}', $t);
		$t = preg_replace("/{ibf\.lang\.(\w+)}/", '{$ibforums->lang[\'' . "\\1" . '\']}', $t);
		$t = preg_replace("/{ibf\.vars\.(\w+)}/", '{$ibforums->vars[\'' . "\\1" . '\']}', $t);
		$t = preg_replace("/{ibf\.member\.(\w+)}/", '{$ibforums->member[\'' . "\\1" . '\']}', $t);

		return $t;

	}

	function rebuild_phpskin($templates_dir, $skins_dir)
	{

		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$errors = array();

		require $templates_dir . "/config.php";

		if ($handle = opendir($templates_dir))
		{

			while (($filename = readdir($handle)) !== false)
			{
				if (($filename != ".") && ($filename != ".."))
				{
					if (preg_match("/^index\./", $filename))
					{
						continue;
					}

					//-------------------------------------------

					if (preg_match("/\.html$/", $filename))
					{

						$name = preg_replace("/\.html$/", "", $filename);

						if ($FHD = fopen($templates_dir . "/" . $filename, 'r'))
						{
							$text = fread($FHD, filesize($templates_dir . "/" . $filename));
							fclose($FHD);
						} else
						{
							$errors[] = "Could not open $filename, skipping file...";
							continue;
						}

						//----------------------------------------------------

						$need  = count($skin[$name]);
						$start = 0;
						$end   = 0;

						if ($need < 1)
						{
							$errors[] = "Error recalling function data for $filename, skipping...";
							continue;
						}

						// check to make sure the splitter tags are intact

						foreach ($skin[$name] as $func_name => $data)
						{
							if (preg_match("/<!--\|IBF\|$func_name\|START\|-->/", $text))
							{
								$start++;
							}

							//+-------------------------------

							if (preg_match("/<!--\|IBF\|$func_name\|END\|-->/", $text))
							{
								$end++;
							}
						}

						if ($start != $end)
						{
							$errors[] = "Some start or end template splitter comments are missing in $filename, skipping file....";
							continue;
						}

						if ($start != $need)
						{
							$errors[] = "Some template splitter comments are missing in $filename, skipping file...";
							continue;
						}

						//+-------------------------------
						// Convert the tags back to php native
						//+-------------------------------

						$text = $this->unconvert_tags($text);

						//+-------------------------------
						// Start parsing the php skin file
						//+-------------------------------

						$final = "<" . "?php\n\n" . "class $name {\n\n";

						foreach ($skin[$name] as $func_name => $data)
						{

							$top = "\n\nfunction $func_name($data) {\n" . "global \$ibforums;\n" . "return <<<EOF\n";

							$bum = "\nEOF;\n}\n";

							$text = preg_replace("/\s*<!--\|IBF\|$func_name\|START\|-->\s*\n/", "$top", $text);

							//+-------------------------------

							$text = preg_replace("/\s*<!--\|IBF\|$func_name\|END\|-->\s*\n/", "$bum", $text);
						}

						$end = "\n\n}\n?" . ">";

						$final .= $text . $end;

						if ($fh = fopen($skins_dir . "/" . $name . ".php", 'w'))
						{
							fwrite($fh, $final);
							fclose($fh);
							@chmod($skins_dir . "/" . $name . ".php", 0777);
						} else
						{
							$errors[] = "Could not save information to $phpskin, please ensure that the CHMOD permissions are correct.";
						}

						$end   = "";
						$final = "";
						$top   = "";

					} // if *.php

				} // if not dir

			} // while loop

			closedir($handle);

		} else
		{
			$errors[] = "Could not open the templates directory!";
		}

		return $errors;

	}

	function import_error($error, $next_id)
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ibforums->db->exec("DELETE FROM ibf_macro_name WHERE set_id='{$next_id['macro']}'");
		$ibforums->db->exec("DELETE FROM ibf_macro WHERE macro_id='{$next_id['macro']}'");

		@rmdir($INFO['base_dir'] . "/style_images/" . $next_id['images']);
		@rmdir($INFO['base_dir'] . "/Skin/s" . $next_id['templates']);

		$ADMIN->rm_dir($this->work_path . "/" . $this->new_dir);

		$ADMIN->error($error);

	}

}

?>
