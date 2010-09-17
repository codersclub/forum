<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Matthew Mecham
|	Modified to work with Download System
|	by bfarber
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Custom download field functions
|   > Module written by Matt Mecham
|   > Extended by bfarber (http://bfarber.com | bfarber@bfarber.com)
|   > Date started: 24th June 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/




$idx = new ad_dfields();


class ad_dfields {

	var $base_url;

	function ad_dfields() {
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
				$this->main_form('add');
				break;
				
			case 'doadd':
				$this->main_save('add');
				break;
				
			case 'edit':
				$this->main_form('edit');
				break;
				
			case 'doedit':
				$this->main_save('edit');
				break;
				
			case 'delete':
				$this->delete_form();
				break;
				
			case 'dodelete':
				$this->do_delete();
				break;
						
			default:
				$this->main_screen();
				break;
		}
		
	}
	
	
	
	//+---------------------------------------------------------------------------------
	//
	// Delete a group
	//
	//+---------------------------------------------------------------------------------
	
	function delete_form()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Could not resolve the group ID, please try again");
		}
		
		$ADMIN->page_title = "Deleting a Custom Profile Field";
		
		$ADMIN->page_detail = "Please check to ensure that you are attempting to remove the correct custom profile field as <b>all data will be lost!</b>.";
		
		
		//+-------------------------------
		
		$DB->query("SELECT ftitle, fid FROM ibf_files_custfields WHERE fid='".$IN['id']."'");
		
		if ( ! $field = $DB->fetch_row() )
		{
			$ADMIN->error("Could not fetch the row from the database");
		}
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dodelete'  ),
												  2 => array( 'act'   , 'dfield'     ),
												  3 => array( 'id'    , $IN['id']   ),
									     )      );
									     
		
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Removal Confirmation" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Custom Profile field to remove</b>" ,
												  "<b>".$field['ftitle']."</b>",
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("Delete this custom field");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}
	
	function do_delete()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Could not resolve the field ID, please try again");
		}
		
		
		// Check to make sure that the relevant groups exist.
		
		$DB->query("SELECT ftitle, fid FROM ibf_files_custfields WHERE fid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Could not resolve the ID's passed to deletion");
		}
		
		$DB->query("ALTER TABLE ibf_files_custentered DROP field_{$row['fid']}");
		
		$DB->query("DELETE FROM ibf_files_custfields WHERE fid='".$IN['id']."'");
		
		$ADMIN->done_screen("Custom Download Field Removed", "Custom Download Field Control", "act=dfield" );
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// Save changes to DB
	//
	//+---------------------------------------------------------------------------------
	
	function main_save($type='edit')
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ($IN['ftitle'] == "")
		{
			$ADMIN->error("You must enter a field title.");
		}
		
		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("Could not resolve the field id");
			}
			
		}
		
		$content = "";
		
		if ($HTTP_POST_VARS['fcontent'] != "")
		{
			$content = str_replace( "\n", '|', str_replace( "\n\n", "\n", trim($HTTP_POST_VARS['fcontent']) ) );
		}
		
		$db_string = array( 'ftitle'    => $IN['ftitle'],
						    'fcontent'  => stripslashes($content),
						    'ftype'     => $IN['ftype'],
						    'freq'      => $IN['freq'],
						    'fmaxinput' => $IN['fmaxinput'],
						    'fshow'     => $IN['fshow'],
						    'ftopic'    => $IN['ftopic'],
						  );
		
						  
		if ($type == 'edit')
		{
			$rstring = $DB->compile_db_update_string( $db_string );
			
			$DB->query("UPDATE ibf_files_custfields SET $rstring WHERE fid='".$IN['id']."'");
			
			$ADMIN->done_screen("Custom Download Field Edited", "Custom Download Field Control", "act=dfield" );
			
		}
		else
		{
			$rstring = $DB->compile_db_insert_string( $db_string );
			
			$DB->query("INSERT INTO ibf_files_custfields (" .$rstring['FIELD_NAMES']. ") VALUES (". $rstring['FIELD_VALUES'] .")");
			
			$new_id = $DB->get_insert_id();
			
			$DB->query("ALTER TABLE ibf_files_custentered ADD field_$new_id text default ''");
			
			$DB->query("OPTIMIZE TABLE ibf_files_custfields");
			
			$ADMIN->done_screen("Profile Field Added", "Custom Profile Field Control", "act=dfield" );
		}
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// Add / edit group
	//
	//+---------------------------------------------------------------------------------
	
	function main_form($type='edit')
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("No group id to select from the database, please try again.");
			}
			
			$form_code = 'doedit';
			$button    = 'Complete Edit';
				
		}
		else
		{
			$form_code = 'doadd';
			$button    = 'Add Field';
		}
		
		if ($IN['id'] != "")
		{
			$DB->query("SELECT * FROM ibf_files_custfields WHERE fid='".$IN['id']."'");
			$fields = $DB->fetch_row();
		}
		else
		{
			$fields = array();
		}
		
		if ($type == 'edit')
		{
			$ADMIN->page_title = "Editing Custom Download Field ".$fields['ftitle'];
		}
		else
		{
			$ADMIN->page_title = 'Adding a New Custom Download Field';
			$fields['ftitle'] = '';
		}
		
		$ADMIN->page_detail = "Please double check the information before submitting the form.";
		
		
		
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $form_code  ),
												  2 => array( 'act'   , 'dfield'     ),
												  3 => array( 'id'    , $IN['id']   ),
									     )  );
									     
		$fields['fcontent'] = str_replace( '|', "\n", $fields['fcontent'] );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Field Settings" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Field Title</b><br>Max characters: 200" ,
												  $SKIN->form_input("ftitle", $fields['ftitle'] )
									     )      );
									     
	
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Field Type</b>" ,
												  $SKIN->form_dropdown("ftype",
												  					   array(
												  					   			0 => array( 'text' , 'Text Input' ),
												  					   			1 => array( 'drop' , 'Drop Down Box' ),
												  					   			2 => array( 'area' , 'Text Area' ),
												  					   		),
												  					   $fields['ftype'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Max Input (for text input and text areas) in characters</b>" ,
												  $SKIN->form_input("fmaxinput", $fields['fmaxinput'] )
									     )      );
									     
								     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Option Content (for drop downs)</b><br>In sets, one set per line<br>Example for 'Resolution' field:<br>64=640x480<br>80=800x640<br>u=Unspecified<br>Will produce:<br><select name='pants'><option value='64'>640x480</option><option value='80'>800x640</option><option value='u'>Unspecified</option></select><br>64, 80, or u will be stored in database. When showing field on download page or add/edit forms, will use value from pair (64=640x480, shows '640x480')" ,
												  $SKIN->form_textarea("fcontent", $fields['fcontent'] )
									     )      );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Field should be required?</b>" ,
												  $SKIN->form_yes_no("freq", $fields['freq'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Do you want this field to be displayed for editing?</b><br />Please be smart and don't require a field but not show it...all submissions will receive an error if this happens" ,
												  $SKIN->form_yes_no("fshow", $fields['fshow'] )
									     )      );						     							     
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>If you choose to automautically create topics on new submissions, do you want this field to be included in the topic?</b>" ,
												  $SKIN->form_yes_no("ftopic", $fields['ftopic'] )
									     )      );						     							     
		
									     
		$ADMIN->html .= $SKIN->end_form($button);
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}

	//+---------------------------------------------------------------------------------
	//
	// Show "Management Screen
	//
	//+---------------------------------------------------------------------------------
	
	function main_screen()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "Custom Download Fields";
		
		$ADMIN->page_detail = "Custom download fields can be used to add optional or required fields to be completed when adding or editing a file. You may also choose to have these fields be added in your automautically-created topics if you use this feature.";
		
		$SKIN->td_header[] = array( "Field Title"    , "20%" );
		$SKIN->td_header[] = array( "Type"           , "10%" );
		$SKIN->td_header[] = array( "Var Name" , "20%" );
		$SKIN->td_header[] = array( "REQUIRED"       , "10%" );
		$SKIN->td_header[] = array( "VISIBLE"         , "10%" );
		$SKIN->td_header[] = array( "SHOW TOPIC"       , "10%" );
		$SKIN->td_header[] = array( "Edit"           , "10%" );
		$SKIN->td_header[] = array( "Delete"         , "10%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Custom Download Field Management" );
		
		$real_types = array( 'drop' => 'Drop Down Box',
							 'area' => 'Text Area',
							 'text' => 'Text Input',
						   );
		
		$DB->query("SELECT * FROM ibf_files_custfields");
		
		if ( $DB->get_num_rows() )
		{
			while ( $r = $DB->fetch_row() )
			{
			
				$hide   = '&nbsp;';
				$req    = '&nbsp;';
				$regi   = '&nbsp;';
				
				"<center><a href='{$ADMIN->base_url}&act=group&code=delete&id=".$r['g_id']."'>Delete</a></center>";
				
				//-----------------------------------
				if ($r['fshow'] == 1)
				{
					$hide = '<center><span style="color:red">Y</span></center>';
				}
				//-----------------------------------
				if ($r['freq'] == 1)
				{
					$req = '<center><span style="color:red">Y</span></center>';
				}
				
				if ($r['ftopic'] == 1)
				{
					$regi = '<center><span style="color:red">Y</span></center>';
				}
				
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$r['ftitle']}</b>" ,
														  "<center>{$real_types[$r['ftype']]}</center>",
														  "<center>field_".$r['fid']."</center>",
														  $req,
														  $hide,
														  $regi,
														  "<center><a href='{$ADMIN->base_url}&act=dfield&code=edit&id=".$r['fid']."'>Edit</a></center>",
														  "<center><a href='{$ADMIN->base_url}&act=dfield&code=delete&id=".$r['fid']."'>Delete</a></center>",
											 )      );
											 
			}
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic("None found", "center", "pformstrip");
		}
		
		$ADMIN->html .= $SKIN->add_td_basic("<a href='{$ADMIN->base_url}&act=dfield&code=add' class='fauxbutton'>ADD NEW FIELD</a></center>", "center", "pformstrip");

		$ADMIN->html .= $SKIN->end_table();
		
		
		$ADMIN->output();
		
		
	}
	
		
}


?>