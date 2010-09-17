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
|   > Skin -> Templates functions
|   > Module written by Matt Mecham
|   > Date started: 15th April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/




$idx = new ad_settings();


class ad_settings {

	var $base_url;

	function ad_settings() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------
		
		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );
		
		foreach ( $tmp_in as $k => $v )
		{
			unset($$k);
		}
		
		//---------------------------------------

		switch($IN['code'])
		{
			case 'add':
				$this->add_templates();
				break;
				
			case 'find_pop':
				echo "uuups";
				break;
				
			case 'edit':
				$this->show_cats();
				break;
				
			case 'dedit':
				$this->do_form();
				break;
				
			case 'doedit':
				$this->do_edit();
				break;
				
			case 'remove':
				$this->remove();
				break;
				
			case 'tools':
				echo "uuups";
				break;
				
			case 'editinfo':
				$this->edit_info();
				break;
				
			case 'export':
				echo "uuups";
				break;
			
			case 'edit_bit':
				$this->edit_bit();
				break;
				
			case 'download':
				$this->download_group();
				break;
				
			case 'upload':
				$this->upload_form();
				break;
				
			case 'do_upload':
				$this->upload_single();
				break;
				
			
			//-------------------------
			default:
				$this->list_current();
				break;
		}
		
	}
	
	//------------------------------------------------------
	
	//------------------------------------------------------
	
	function upload_single()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$FILE_NAME = $_FILES['FILE_UPLOAD']['name'];
		$FILE_SIZE = $_FILES['FILE_UPLOAD']['size'];
		$FILE_TYPE = $_FILES['FILE_UPLOAD']['type'];
		
		// Naughty Opera adds the filename on the end of the
		// mime type - we don't want this.
		
		$FILE_TYPE = preg_replace( "/^(.+?);.*$/", "\\1", $FILE_TYPE );
		
		// Naughty Mozilla likes to use "none" to indicate an empty upload field.
		// I love universal languages that aren't universal.
		
		if ($_FILES['FILE_UPLOAD']['name'] == "" or !$_FILES['FILE_UPLOAD']['name'] or ($_FILES['FILE_UPLOAD']['name'] == "none") )
		{
			$ADMIN->error("No file was chosen to upload!");
		}
		
		//-------------------------------------------------
		// Copy the upload to the uploads directory
		//-------------------------------------------------
		
		if (! @move_uploaded_file( $_FILES['FILE_UPLOAD']['tmp_name'], ROOT_PATH."Skin/s{$IN['setid']}/{$IN['group']}.php") )
		{
			$ADMIN->error("The upload failed");
		}
		else
		{
			@chmod( ROOT_PATH."Skin/s{$IN['setid']}/{$IN['group']}.php", 0777 );
		}
		
		$ADMIN->done_screen("Template set update complete", "Manage Template Sets", "act=templ" );
		
	}
	
	//------------------------------------------------------
	
	function upload_form()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		require './sources/Admin/skin_info.php';
		
		if ($IN['setid'] == "")
		{
			$ADMIN->error("You must specify an existing template set ID, go back and try again");
		}
		
		if ($IN['group'] == "")
		{
			$ADMIN->error("You must specify an existing template set ID, go back and try again");
		}
		
		//-----------------------------------
		// Get the info from the DB
		//-----------------------------------

		// SergeS - Remove skin from DB ()
		
		$DB->query("SELECT skname FROM ibf_tmpl_names WHERE skid='".$IN['setid']."'");
		
		$row = $DB->fetch_row();
		
		//+-------------------------------
	
		$ADMIN->page_detail = "Please check all the information carefully before continuing.";
		$ADMIN->page_title  = "Upload a template file for template set: {$row['skname']}";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'do_upload' ),
												  2 => array( 'act'   , 'templ'     ),
												  3 => array( 'MAX_FILE_SIZE', '10000000000' ),
												  4 => array( 'setid' , $IN['setid']  ),
												  5 => array( 'group' , $IN['group']  ),
									     ) , "uploadform", " enctype='multipart/form-data'"     );
									     
		$SKIN->td_header[] = array( "&nbsp;"   , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"   , "60%" );

		$ADMIN->html .= $SKIN->start_table("Upload template file to replace: ".$skin_names[ $IN['group'] ][0]);
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Choose a file from your computer to upload</b><br>Note: Uploading this file will replace all data currently held, there is no undo!.",
												  $SKIN->form_upload(),
										 )      );
									     
		$ADMIN->html .= $SKIN->end_form('Upload File');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->nav[] = array( 'act=templ' ,'Template Control Home' );
		$ADMIN->nav[] = array( "act=templ&code=edit&id={$IN['setid']}" ,$row['skname'] );
		
		$ADMIN->output();						
		
	}
	
	//------------------------------------------------------
	
	function download_group($return=0, $setid="", $group="")
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($setid != "")
		{
			$IN['setid'] = $setid;
		}
		
		if ($group != "")
		{
			$IN['group'] = $group;
		}
		
		if ($IN['setid'] == "")
		{
			$ADMIN->error("You must specify an existing template set ID, go back and try again");
		}
		
		if ($IN['group'] == "")
		{
			$ADMIN->error("You must specify an existing template set ID, go back and try again");
		}
		
		//-----------------------------------
		// Get the info from the DB
		//-----------------------------------
		
		// SergeS - RSDB
		
		$output = "<!-- PLEASE LEAVE ALL 'IBF' COMMENTS IN PLACE, DO NOT REMOVE THEM! -->\n<!--IBF_GROUP_START:{$IN['group']}-->\n\n";

		$filename = ROOT_PATH."Skin/s{$IN['setid']}/{$IN['group']}.php";
		$handle = fopen($filename, "r");
		echo fread($handle, filesize($filename));
		fclose($handle);

		$output .= "\n<!--IBF_GROUP_END:{$IN['group']}-->\n";

		
		if ($return == 0)
		{
			@header("Content-type: unknown/unknown");
			@header("Content-Disposition: attachment; filename={$IN['setid']}.{$IN['group']}.html");
			// SergeS - End
			print $output;
			
			exit();
		}
		else
		{
			return $output;
		}
		
	}
	
	//------------------------------------------------------

	function show_cats()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("You must specify an existing template set ID, go back and try again");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_tmpl_names WHERE skid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Could not query the information from the database");
		}
		
		// Get $skin_names stuff
		
		require './sources/Admin/skin_info.php';
		
		
		if ($row['author'] and $row['email'])
		{
			$author = "<br><br>This template set <b>'{$row['skname']}'</b> was created by <a href='mailto:{$row['email']}' target='_blank'>{$row['author']}</a>";
		}
		else if ($row['author'])
		{
			$author = "<br><br>This template set <b>'{$row['skname']}'</b> was created by {$row['author']}";
		}
		
		if ($row['url'])
		{
			$url = " (website: <a href='{$row['url']}' target='_blank'>{$row['url']}</a>)";
		}
		
		//+-------------------------------
	
		$ADMIN->page_detail = "Please choose which section you wish to edit below.<br><br><b>Download</b> this HTML section This option allows you to download all of the HTML for this template section for offline editing.<br><b>Upload</b> HTML for this section This option allows you to upload a saved HTML file to replace this template section.$author $url";
		$ADMIN->page_title  = "Edit Template sets";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->js_checkdelete();
		
//      SergeS - RSDB
		
		$SKIN->td_header[] = array( "Skin Category Title"   , "50%" );
		$SKIN->td_header[] = array( "View Options"          , "20%" );
		$SKIN->td_header[] = array( "Manage"                , "30%" );

		//+-------------------------------
		
		$ADMIN->html .= "<script language='javascript'>
						 function pop_win(theUrl) {
						 	
						 	window.open('{$SKIN->base_url}&'+theUrl,'Preview','width=400,height=450,resizable=yes,scrollbars=yes');
						 }
						 </script>";
		// SergeS - RSDB			 

		$ADMIN->html .= $SKIN->start_table("Template: ".$row['skname'], $quick_click);
		
		if (!($handle = opendir(ROOT_PATH."Skin/s{$IN['id']}/")))
			$ADMIN->error("Could not load skin");
		
		while (false !== ($file = readdir($handle))) 
			if (strpos($file, ".php") !== FALSE) {

				$skin_iname = strtr ( $file, array ( ".php" => "" ));

				$name = "<b>".$group['group_name']."</b>";
				$desc = "";

				$expand = 'Expand to Edit';
				$exp_content = "";

				if ( isset($skin_names[ $skin_iname ]) )
				{
					$name = "<b>".$skin_names[ $skin_iname ][0]."</b>";
					$desc = "<br><span id='description'>".$skin_names[ $skin_iname ][1]."</span>";
				}
				else
				{
					$name .= " (Non-Default Group)";
					$desc = "<br>This group is not part of the standard Invision Power Board installation and no description is available";
				}

				if ($IN['expand'] == $skin_iname ) {
					$expand = 'Collapse';
					$fte = fopen ( ROOT_PATH."Skin/s{$IN['id']}/{$IN['expand']}.php", 'r' );
					$func_l = array ();
					while ( !feof ( $fte ) ) {
						$line = fgets ( $fte, 1024 );
						if (strpos( strtolower($line), "function ") !== FALSE )
							$func_l[] = strtr ( substr($line, 0, strpos ($line, "(")), array ("function " => "", " " => ""));
					}
					fclose ( $fte );
					if ( count($func_l) > 0 ) {
						$exp_content = $SKIN->add_td_basic( "<script type='text/javascript'>
														  function checkall(cb) {
															   var fmobj = document.mutliact;
															   for (var i=0;i<fmobj.elements.length;i++) {
																   var e = fmobj.elements[i];
																   if ((e.name != 'allbox') && (e.type=='checkbox') && (!e.disabled)) {
																	   e.checked = fmobj.allbox.checked;
																   }
															   }
														   }
														   function checkcheckall(cb) {	
															   var fmobj = document.mutliact;
															   var TotalBoxes = 0;
															   var TotalOn = 0;
															   for (var i=0;i<fmobj.elements.length;i++) {
																   var e = fmobj.elements[i];
																   if ((e.name != 'allbox') && (e.type=='checkbox')) {
																	   TotalBoxes++;
																	   if (e.checked) {
																		   TotalOn++;
																	   }
																   }
															   }
															   if (TotalBoxes==TotalOn) {fmobj.allbox.checked=true;}
															   else {fmobj.allbox.checked=false;}
														   }
														   </script>
														  <a name='{$skin_iname}'>
														  <form name='mutliact' action='{$SKIN->base_url}&act=templ&id={$IN['id']}&code=edit_bit&expand={$skin_iname}' method='post'>
														  <table cellspacing='1' cellpadding='5' width='100%' align='center' bgcolor='#333333'>
														  <tr>
														   <td align='left' colspan='2' class='catrow2'><a style='font-weight:bold;font-size:12px;color:#000033' href='{$SKIN->base_url}&act=templ&code=edit&id={$IN['id']}&expand=' title='Collapse' alt='Collapse'>$name</a></td>
														   <td colspan='3' bgcolor='#FFFFFF'>&nbsp;</td>
														  <tr>
														   <td width='5%' class='catrow2' align='center'><input type='checkbox' onclick='checkall()' name='allbox' value='1' /></td>
														   <td width='35%' class='catrow2'>Edit all the sections below</td>
														   <td width='30%' class='catrow2' align='center'>Edit</td>
														   <td width='30%' class='catrow2' align='center'>Preview Options</td>
														  </tr>
														  <!--CONTENT-->
														  <tr>
														   <td colspan='5'  class='catrow2' align='center'><input type='submit' value='Edit Selected' /></td>
														  </tr>
														  </table></form>", "left", "tdrow2" );

						$sec_array = array ();
						$temp = '';

						foreach ( $func_l as $func_nu => $func_n ) {
							$sec_array[$func_nu]['suid'] = $func_nu;
							$sec_array[$func_nu]['fname'] = $func_n;
							$sec_array[$func_nu]['sec_length'] = 1;
							if ($bit_names[ $skin_iname ][ $func_n ] != "")
								$sec_array[$func_nu]['easy_name'] = $bit_names[ $skin_iname ][ $func_n ];
							else
								$sec_array[$func_nu]['easy_name'] = $func_n;
						}

						usort($sec_array, array( 'ad_settings', 'perly_word_sort' ) );

						foreach( $sec_array as $sec ) {
						$sec['easy_name'] = preg_replace( "/^(\d+)\:\s+?/", "", $sec['easy_name'] );
						
						$temp .= "
									<tr>
									 <td width='5%'  class='tdrow1' align='center'><input type='checkbox' onclick='checkcheckall()' name='cb_{$sec['fname']}' value='1' /></td>
									 <td width='40%' class='tdrow1'><b>{$sec['easy_name']}</b></td>
									 <td width='20%' class='tdrow1' align='center'><a href='{$SKIN->base_url}&act=templ&id={$IN['id']}&code=edit_bit&suid={$sec['fname']}&expand={$skin_iname}&type=single'>Edit Single</a></td>
									 <td width='40%' class='tdrow1' align='center' nowrap>(<a href='javascript:pop_win(\"act=rtempl&code=preview&suid={$sec['fname']}&type=html\")'>HTML</a> | <a href='javascript:pop_win(\"act=rtempl&code=preview&suid={$sec['fname']}&type=text\")'>Text</a> | <a href='javascript:pop_win(\"act=rtempl&code=preview&suid={$sec['fname']}&type=css\")'>With CSS</a>)</td>
									</tr>
								";
						}

					$exp_content = str_replace( "<!--CONTENT-->", $temp, $exp_content );

					$desc = "";

					$ADMIN->html .= $exp_content;

					}
				} else {
					$ADMIN->html .= $SKIN->add_td_row( array( 
						"<span style='font-weight:bold;font-size:12px;color:#000033'><a href='{$SKIN->base_url}&act=templ&code=edit&id={$IN['id']}&expand=$skin_iname#$skin_iname'>".$name."</a></span>".$desc,
						"<center><a href='{$SKIN->base_url}&act=templ&code=edit&id={$IN['id']}&expand=$skin_iname'>$expand</a></center>",
						"<center><a href='{$SKIN->base_url}&act=templ&code=download&setid={$IN['id']}&group=$skin_iname' title='Download a HTML file of this section for offline editing'>Download</a> | <a href='{$SKIN->base_url}&act=templ&code=upload&setid={$IN['id']}&group=$skin_iname' title='Upload a saved HTML file to replace this section'>Upload</a></center>",
					));
				}
			}

		// SergeS - END
		
		$ADMIN->html .= $SKIN->end_table();
									     
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->nav[] = array( 'act=templ' ,'Template Control Home' );
		$ADMIN->nav[] = array( '' ,'Managing Template Set "'.$row['skname'].'"' );
		
		$ADMIN->output();
		
		
	}
	
	// Sneaky sorting.
	// We use the format "1: name". without this hack
	// 1: name, 2: other name, 11: other name
	// will sort as 1: name, 11: other name, 2: other name
	// There is natsort and such in PHP, but it causes some
	// problems on older PHP installs, this is hackish but works
	// by simply adding '0' to a number less than 2 characters long.
	// of course, this won't work with three numerics in the hundreds
	// but we don't have to worry about more that 99 bits in a template
	// at this stage.
	
	function perly_word_sort($a, $b)
	{
		$nat_a = intval( $a['easy_name'] );
		$nat_b = intval( $b['easy_name'] );
		
		if (strlen($nat_a) < 2)
		{
			$nat_a = '0'.$nat_a;
		}
		if (strlen($nat_b) < 2)
		{
			$nat_b = '0'.$nat_b;
		}
		
		return strcmp($nat_a, $nat_b);
	}
	
	
	//+--------------------------------------------------------------------------------
	//+--------------------------------------------------------------------------------
	
	
	function edit_info()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("You must specify an existing template set ID, go back and try again");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_tmpl_names WHERE skid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Could not query the information from the database");
		}
		
		$final['skname'] = stripslashes($_POST['skname']);
		
		if (isset($_POST['author']))
		{
			$final['author'] = str_replace( ",", "", stripslashes($_POST['author']) );
			$final['email']  = str_replace( ",", "", stripslashes($_POST['email']) );
			$final['url']    = str_replace( ",", "", stripslashes($_POST['url']) );
		}
		
		$db_string = $DB->compile_db_update_string( $final );
		
		$DB->query("UPDATE ibf_tmpl_names SET $db_string WHERE skid='".$IN['id']."'");
		
		$ADMIN->done_screen("Template information updated", "Manage Template sets", "act=templ" );
		
	}
	
	
	//-------------------------------------------------------------
	// Add templates
	//-------------------------------------------------------------
	
	// SergeS - RSDB;
	function add_templates()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("You must specify an existing template set ID, go back and try again");
		}
		
		//-------------------------------------
		
		if ( ! is_writeable(ROOT_PATH.'Skin') )
			$ADMIN->error("The directory 'Skin' is not writeable by this script. Please check the permissions on that directory. CHMOD to 0777 if in doubt and try again");
			
			//-------------------------------------
		if ( ! is_dir(ROOT_PATH.'Skin/s'.$IN['id']) )
			$ADMIN->error("Could not locate the original template set to copy, please check and try again");
		
		//-------------------------------------
		
		$DB->query("SELECT * FROM ibf_tmpl_names WHERE skid='".$IN['id']."'");
		
		//-------------------------------------
		
		if ( ! $row = $DB->fetch_row() )
			$ADMIN->error("Could not query that template set from the DB, so there");
		
		//-------------------------------------
		
		$row['skname'] = $row['skname'].".NEW";
		
		// Insert a new row into the DB...
		
		$final = array();
		
		foreach($row as $k => $v)
		{
			if ($k == 'skid')
			{
				continue;
			}
			else
			{
				$final[ $k ] = $v;
			}
		}
		
		$db_string = $DB->compile_db_insert_string( $final );
		
		$DB->query("INSERT INTO ibf_tmpl_names (".$db_string['FIELD_NAMES'].") VALUES(".$db_string['FIELD_VALUES'].")");
		
		$new_id = $DB->get_insert_id();
		
		//-------------------------------------
		
		if ( ! $ADMIN->copy_dir( $INFO['base_dir'].'Skin/s'.$IN['id'] , $INFO['base_dir'].'Skin/s'.$new_id ) )
		{
			$DB->query("DELETE FROM ibf_tmpl_names WHERE skid='$new_id'");
			$ADMIN->error( $ADMIN->errors );
		}
		
		//-------------------------------------
		// All done, yay!
		//-------------------------------------
		
		$ADMIN->done_screen("New Template Set", "Manage Template sets", "act=templ" );
	
	}
	
	//-------------------------------------------------------------
	// REMOVE WRAPPERS
	//-------------------------------------------------------------

	function remove()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------
		
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("You must specify an existing template set ID, go back and try again");
		}
		
		
		if ( ! $ADMIN->rm_dir( $INFO['base_dir']."Skin/s".$IN['id'] ) )
				$ADMIN->error("Could not remove the template files, please check the CHMOD permissions to ensure that this script has the correct permissions to allow this");
		
		$DB->query("DELETE FROM ibf_tmpl_names WHERE skid='".$IN['id']."'");
		
		$std->boink_it($SKIN->base_url."&act=templ");
		exit();
		
		
	}
	
	//-------------------------------------------------------------
	// EDIT TEMPLATES, STEP TWO
	//-------------------------------------------------------------
	
	function edit_bit()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//-----------------------------------
		// Check for valid input...
		//-----------------------------------
		
		// Get $skin_names stuff
		
		require './sources/Admin/skin_info.php';
		
		$ADMIN->page_detail = "You may edit the HTML of this template.";
		$ADMIN->page_title  = "Template Editing";
		
		$ids = array();
		
		if ( $IN['type'] == 'single' )
		{
			if ($IN['suid'] == "")
			{
				$ADMIN->error("You must specify an existing template set ID, go back and try again");
			}
			$ids[] = $IN['suid'];
		}
		else
		{
			foreach ($IN as $key => $value)
			{
				// SergeS - RSDB
				if ( (strpos($key, "cb_") !== FALSE) && (strpos($key, "cb_") === 0) )
					$ids[] = substr ( $key, 3 );
				// SergeS - End
			}
 		}

 		if ( count($ids) < 1 )
 		{
 			$ADMIN->error("No ids selected, please go back and select some before submitting the form");
 		}
		
		
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->js_template_tools();
		
		// SergeS - RSDB
		$group_name = $IN['expand'];
		$set_id = $IN['id'];
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doedit'    ),
												  2 => array( 'act'   , 'templ'     ),
												  3 => array( 'expand', $group_name ),
												  4 => array( 'id'    , $set_id ),
									     )  , "theform"    );
									     
		
		//+-------------------------------
		$file = fopen ( ROOT_PATH."Skin/s{$set_id}/{$group_name}.php", 'r' );
		$data = fread ( $file, filesize ( ROOT_PATH."Skin/s{$set_id}/{$group_name}.php" ));
		fclose ( $file );

		$sec_arry = array ();
		$data = strtr ( $data, array ( "\n" => "\1", "EOF" => "\2" ));
		foreach ( $ids as $f_n ) {
			$fnc = preg_replace ( "/.*(function {$f_n}[^}]+<<<\2[^\2]*[^}]*}).*/", "\$1", $data );
			$fnc = strtr ( $fnc, array ( "\1" => "\n", "\2" => "EOF" ));
			$x = array ( 'func_name' => $f_n, 'section_content' => $fnc );
			if ($bit_names[ $group_name ][ $f_n ] != "")
				$x['easy_name'] = $bit_names[ $group_name ][ $f_n ];
			else
				$x['easy_name'] = $x['func_name'];
			$sec_arry[] = $x;
		}
		// SergeS - End

		// Sort by easy_name
		
		usort($sec_arry, array( 'ad_settings', 'perly_word_sort' ) );
		
		// Loop and print
		
		foreach( $sec_arry as $id => $template )
		{
			$template['easy_name'] = preg_replace( "/^(\d+)\:\s+?/", "", $template['easy_name'] );


			//+-------------------------------
			// Swop < and > into ascii entities
			// to prevent textarea breaking html
			//+-------------------------------
			
			$setid     = $set_id;
			$groupname = $group_name;
			
			$templ = $this->convert_tags($template['section_content']);
			
			$templ = preg_replace("/&/", "&#38;", $templ );
			$templ = preg_replace("/</", "&#60;", $templ );
			$templ = preg_replace("/>/", "&#62;", $templ );
			
			//+-------------------------------
			
			$SKIN->td_header[] = array( "{none}"   , "100%" );
			
			$ADMIN->html .= $SKIN->start_table( "Template: ". $template['easy_name'] );
			
			$ADMIN->html .= $SKIN->add_td_basic( "<input type='button' value='Macro Look-up' id='editbutton' title='View a macro definition' onClick='pop_win(\"code=macro_one&suid={$template['suid']}\", \"MacroWindow\", 400, 200)'>".
												 "&nbsp;<input type='button' value='Compare' id='editbutton' title='Compare the edited version to the original' onClick='pop_win(\"act=rtempl&code=compare&suid={$template['suid']}\", \"CompareWindow\", 500,400)'>".
												 "&nbsp;<input type='button' value='Restore' id='editbutton' title='Restore the original, unedited template bit' onClick='restore(\"{$template['suid']}\",\"{$IN['expand']}\")'>".
												 "&nbsp;<input type='button' value='View Original' id='editbutton' title='View the HTML for the unedited template bit' onClick='pop_win(\"act=rtempl&code=preview&suid={$template['suid']}&type=html\", \"OriginalPreview\", 400,400)'>".
												 "&nbsp;<input type='button' value='Search' id='editbutton' title='Search the templates for a string' onClick='pop_win(\"act=rtempl&code=search&suid={$template['suid']}&type=html\", \"Search\", 500,400)'>".
												 "&nbsp;<input type='button' value='Edit Box Size' id='editbutton' title='Change the size of the edit box below' onClick=\"pop_win('&act=prefs', 'prefs', '300', '100')\">",
												 "center", "catrow");
												 
			$ADMIN->html .= $SKIN->add_td_basic( "<b>Show me the HTML code for:&nbsp;".
												 "<select name='htmlcode' onChange=\"document.theform.res.value='&'+document.theform.htmlcode.options[document.theform.htmlcode.selectedIndex].value+';'\" id='multitext'><option value='copy'>&copy;</option>
												 <option value='raquo'>&raquo;</option>
												 <option value='laquo'>&laquo;</option>
												 <option value='#149'>&#149;</option>
												 <option value='reg'>&reg;</option>
												 </select>&nbsp;&nbsp;<input type='text' name='res' size=20 id='multitext'>&nbsp;&nbsp;<input type='button' value='select' id='editbutton' onClick='document.theform.res.focus();document.theform.res.select();'>"
				
												, "center", "tdrow1");
			
			$ADMIN->html .= $SKIN->add_td_row( array( 
														"<center>".$SKIN->form_textarea("txt_{$template['func_name']}", $templ, $INFO['tx'], $INFO['ty'], $wrap)."</center>",
											 )      );
									     
			$ADMIN->html .= $SKIN->end_table();
		}
		
		$ADMIN->html .= "<div class='tableborder'><div class='catrow2' align='center'><input type='submit' value='Update templates' /></div></div></form>";
									     
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_tmpl_names WHERE skid='".$set_id."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Could not query the information from the database");
		}
		
		$ADMIN->nav[] = array( 'act=templ' ,'Template Control Home' );
		$ADMIN->nav[] = array( "act=templ&code=edit&id={$setid}" ,$row['skname'] );
		$ADMIN->nav[] = array( "act=templ&code=edit&id={$setid}&expand={$IN['expand']}", $group_name );
		//$ADMIN->nav[] = array( "", $template['func_name'] );
		
		$ADMIN->output();
		
		
	}
	
	//-------------------------------------------------------------
	// COMPLETE EDIT
	//-------------------------------------------------------------
	
	function do_edit()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------
		// Check incoming
		//+-------------------------------
		
		// SergeS - RSDB
		foreach ($IN as $key => $value)
			if ( (strpos($key, "txt_") !== FALSE) && (strpos($key, "txt_") === 0) ) 
				$ids[] = substr ( $key, 4 );
		// SergeS - End
 		
 		if ( count($ids) < 1 )
 		{
 			$ADMIN->error("No ids selected, please go back and select some before submitting the form");
 		}
 		
 		//+-------------------------------
		// Get the group name, etc
		//+-------------------------------
		
		// SergeS - RSDB 
		$group_name = $IN['expand'];
		$set_id = $IN['id'];
		
		// SergeS - End
		
		//+-------------------------------
		// Get the template set info
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_tmpl_names WHERE skid='".$set_id."'");
		
		if ( !$row = $DB->fetch_row() )
		{
			$ADMIN->error("Could not query the information from the database");
		}
		
		//+-------------------------------
		
		$phpskin  = ROOT_PATH."Skin/s".$set_id."/".$group_name.".php";
		
		//+-------------------------------
		
		// SergeS - RSDB
		if ( ! is_writeable($phpskin) )
			$ADMIN->error("Cannot write into '$phpskin', please check the CHMOD value, and if needed, CHMOD to 0777 via FTP. IBF cannot do this for you.");

		$file = fopen ( ROOT_PATH."Skin/s{$set_id}/{$group_name}.php", 'r' );
		$data = fread ( $file, filesize ( ROOT_PATH."Skin/s{$set_id}/{$group_name}.php" ));
		fclose ( $file );

		$sec_arry = array ();
		$data = strtr ( $data, array ( "\n" => "\1", "EOF" => "\2" ));
		foreach ( $ids as $f_n ) {
			$text = stripslashes($_POST['txt_'.$f_n]);
			$text = preg_replace("/&#60;/", "<", $text);
			$text = preg_replace("/&#62;/", ">", $text);
			$text = preg_replace("/&#38;/", "&", $text);
			$text = str_replace( '\\n' , '\\\\\\n', $text );
			$text = preg_replace("/\r/", "", $text);
			$text = $this->unconvert_tags($text);
			$data = preg_replace ( "/(.*)(function {$f_n}[^}]+<<<\2[^\2]*[^}]*})(.*)/", "\$1\n{$text}\n\$3", $data );
			$data = strtr ( $data, array ( "\1" => "\n", "\2" => "EOF" ));
		}

		$file_write = fopen ( $phpskin, 'w' );
		fwrite ( $file_write, $data );
		fclose ( $file_write );
/*		
		$php_data = file ( $phpskin );
		
		//+-------------------------------
		// Process my bits :o
		//+-------------------------------
		$file_write = fopen ( $phpskin, 'w' );
		$writing_new = FALSE;
		foreach ( $php_data as $line ) {
			if (strpos(strtolower($line), 'function ') !== FALSE) 
				$writing_new = FALSE;

			if (!$writing_new)
				if (strpos(strtolower($line), 'function ') !== FALSE)
					foreach ( $ids as $id )
						if (strpos(strtolower($line), strtolower($id)) !== FALSE) {
							$text = stripslashes($_POST['txt_'.$id]);
							$text = preg_replace("/&#60;/", "<", $text);
							$text = preg_replace("/&#62;/", ">", $text);
							$text = preg_replace("/&#38;/", "&", $text);
							$text = str_replace( '\\n' , '\\\\\\n', $text );
							$text = preg_replace("/\r/", "", $text);
							$text = $this->unconvert_tags($text);
							fwrite($file_write, $text);
							$writing_new = TRUE;
						}

			if (!$writing_new)
				fwrite ( $file_write, $line );
		}
		fclose ( $file_write );
*/		
		$ADMIN->done_screen("Template file(s) updated", "Manage Templates in template set: {$row['skname']}", "act=templ&code=edit&id={$set_id}" );
		
		// SergeS - End
		
	}
	
	//-------------------------------------------------------------
	// EDIT TEMPLATES, STEP ONE
	//-------------------------------------------------------------
	
	function do_form()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("You must specify an existing template set ID, go back and try again");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_tmpl_names WHERE skid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Could not query the information from the database");
		}
		
		
		$form_array = array();
	
		//+-------------------------------
	
		$ADMIN->page_detail = "Please choose which section you wish to edit below.";
		$ADMIN->page_title  = "Edit Template Set Data";
		
		//+-------------------------------
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->js_no_specialchars();
		
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'editinfo'    ),
												  2 => array( 'act'   , 'templ'       ),
												  3 => array( 'id'    , $IN['id']     ),
									     ), "theAdminForm", "onSubmit=\"return no_specialchars('templates')\""      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"   , "60%" );
		$SKIN->td_header[] = array( "&nbsp;"   , "40%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Edit template information" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"<b>Template Set Name</b>",
													$SKIN->form_input('skname', $row['skname']),
									     )      );
									     
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"<b>Template set author name:</b>",
													$SKIN->form_input('author', $row['author']),
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"<b>Template set author email:</b>",
													$SKIN->form_input('email', $row['email']),
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"<b>Template set author webpage:</b>",
													$SKIN->form_input('url', $row['url']),
										 )      );
									     
		
									     
		$ADMIN->html .= $SKIN->end_form("Edit template set details");
									     
		$ADMIN->html .= $SKIN->end_table();
									     
		
		$ADMIN->nav[] = array( 'act=templ' ,'Template Control Home' );
		
		$ADMIN->output();
		
		
	}
	
	//-------------------------------------------------------------
	// SHOW CURRENT TEMPLATE PACKS
	//-------------------------------------------------------------
	
	function list_current()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$form_array = array();
	
		$ADMIN->page_detail = "The skin templates contain the all the HTML that is used by the board.
							   <br>
							   This section will allow you to create new template sets or edit HTML in your current template sets.";
		$ADMIN->page_title  = "Manage Template Sets";
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "Title"        , "30%" );
		$SKIN->td_header[] = array( "Allocation"   , "20%" );
		$SKIN->td_header[] = array( "Edit&nbsp;Properties"    , "20%" );
		$SKIN->td_header[] = array( "Manage HTML"  , "20%" );
		$SKIN->td_header[] = array( "Remove"       , "10%" );
		
		//+-------------------------------
		
		$DB->query("SELECT DISTINCT(s.set_id), s.sname, t.skid, t.skname from ibf_tmpl_names t, ibf_skins s WHERE s.set_id=t.skid ORDER BY t.skname ASC");
		
		$used_ids = array();
		$show_array = array();
		
		if ( $DB->get_num_rows() )
		{
		
			$ADMIN->html .= $SKIN->start_table( "Current Template sets In Use" );
			
			while ( $r = $DB->fetch_row() )
			{
			
				$show_array[ $r['skid'] ] .= stripslashes($r['sname'])."<br>";
			
				if ( in_array( $r['skid'], $used_ids ) )
				{
					continue;
				}
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>".stripslashes($r['skname'])."</b>",
														  "<#X-{$r['skid']}#>",
														  "<center><a href='".$SKIN->base_url."&act=templ&code=dedit&id={$r['skid']}' title='Edit Template Set Name'>Edit Properties</a></center>",
														  "<center><a href='".$SKIN->base_url."&act=templ&code=edit&id={$r['skid']}' title='Edit, upload and download'>Manage HTML</a></center>",
														  "<i>Deallocate before removing</i>",
												 )      );
												   
				$used_ids[] = $r['skid'];
				
				$form_array[] = array( $r['skid'], $r['skname'] );
				
			}
			
			foreach( $show_array as $idx => $string )
			{
				$string = preg_replace( "/<br>$/", "", $string );
				
				$ADMIN->html = preg_replace( "/<#X-$idx#>/", "$string", $ADMIN->html );
			}
			
			$ADMIN->html .= $SKIN->end_table();
		}
		
		if ( count($used_ids) > 0 )
		{
		
			$DB->query("SELECT skid, skname FROM ibf_tmpl_names WHERE skid NOT IN(".implode(",",$used_ids).")");
		
			if ( $DB->get_num_rows() )
			{
			
				$SKIN->td_header[] = array( "Title"          , "50%" );
				$SKIN->td_header[] = array( "Edit&nbsp;Properties"    , "20%" );
				$SKIN->td_header[] = array( "Manage HTML"    , "20%" );
				$SKIN->td_header[] = array( "Remove"         , "10%" );
			
				$ADMIN->html .= $SKIN->start_table( "Current Unallocated Template sets" );
				
				$ADMIN->html .= $SKIN->js_checkdelete();
				
				while ( $r = $DB->fetch_row() )
				{
					
					$ADMIN->html .= $SKIN->add_td_row( array( "<b>".stripslashes($r['skname'])."</b>",
															  "<center><a href='".$SKIN->base_url."&act=templ&code=dedit&id={$r['skid']}'>Edit Properties</a></center>",
															  "<center><a href='".$SKIN->base_url."&act=templ&code=edit&id={$r['skid']}' title='Edit, upload and download'>Manage HTML</a></center>",
															  "<center><a href='javascript:checkdelete(\"act=templ&code=remove&id={$r['skid']}\")'>Remove</a></center>",
													 )      );
													 
					$form_array[] = array( $r['skid'], $r['skname'] );
													   
				}
				
				$ADMIN->html .= $SKIN->end_table();
			}
		}
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add'     ),
												  2 => array( 'act'   , 'templ'    ),
									     ) , "uploadform"  );
												  
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Create New Template Set" );
			
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Base new Template set on...</b>" ,
										  		  $SKIN->form_dropdown( "id", $form_array)
								 )      );
								 
		$ADMIN->html .= $SKIN->end_form("Create new Template set");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$extra = "";
		
		if ( SAFE_MODE_ON == 1)
		{
			$extra = "<br><span id='detail'>WARNING: Safe mode restrictions detected, some of these tools will not work</span>";
		}
		
		//+-------------------------------
		
		$ADMIN->output();
	
	}
	
	function convert_tags($t="")
	{
		if ($t == "")
		{
			return "";
		}
		
		$t = preg_replace( "/{?\\\$ibforums->base_url}?/"            , "{ibf.script_url}"   , $t );
		$t = preg_replace( "/{?\\\$ibforums->session_id}?/"          , "{ibf.session_id}"   , $t );
		$t = preg_replace( "/{?\\\$ibforums->skin\['?(\w+)'?\]}?/"   , "{ibf.skin.\\1}"      , $t );
		$t = preg_replace( "/{?\\\$ibforums->lang\['?(\w+)'?\]}?/"   , "{ibf.lang.\\1}"      , $t );
		$t = preg_replace( "/{?\\\$ibforums->vars\['?(\w+)'?\]}?/"   , "{ibf.vars.\\1}"      , $t );
		$t = preg_replace( "/{?\\\$ibforums->member\['?(\w+)'?\]}?/" , "{ibf.member.\\1}"    , $t );
		
		// Make some tags safe..
		
		$t = preg_replace( "/\{ibf\.vars\.(sql_driver|sql_host|sql_database|sql_pass|sql_user|sql_port|sql_tbl_prefix|smtp_host|smtp_port|smtp_user|smtp_pass|html_dir|base_dir|upload_dir)\}/", "" , $t );
				
		return $t;
		
	}
	
	function unconvert_tags($t="")
	{
		if ($t == "")
		{
			return "";
		}
		
		// Make some tags safe..
		
		$t = preg_replace( "/\{ibf\.vars\.(sql_driver|sql_host|sql_database|sql_pass|sql_user|sql_port|sql_tbl_prefix|smtp_host|smtp_port|smtp_user|smtp_pass|html_dir|base_dir|upload_dir)\}/", "" , $t );
		
		$t = preg_replace( "/{ibf\.script_url}/i"   , '{$ibforums->base_url}'         , $t);
		$t = preg_replace( "/{ibf\.session_id}/i"   , '{$ibforums->session_id}'       , $t);
		$t = preg_replace( "/{ibf\.skin\.(\w+)}/"   , '{$ibforums->skin[\''."\\1".'\']}'   , $t);
		$t = preg_replace( "/{ibf\.lang\.(\w+)}/"   , '{$ibforums->lang[\''."\\1".'\']}'   , $t);
		$t = preg_replace( "/{ibf\.vars\.(\w+)}/"   , '{$ibforums->vars[\''."\\1".'\']}'   , $t);
		$t = preg_replace( "/{ibf\.member\.(\w+)}/" , '{$ibforums->member[\''."\\1".'\']}' , $t);
		
		return $t;
		
	}
	
}


?>