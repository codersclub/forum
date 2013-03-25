<?
//---------------------------------------------------
// IBStore Upload Otheres Avatar
//---------------------------------------------------
class item
{
	var $name = "Upload Otheres Avatar";
	var $desc = "Upload a new Avatar for another user";
	var $extra_one = "";
	var $extra_two = "";
	var $extra_three = "jpg,gif";

	function on_add($EXTRA)
	{
		global $IN, $SKIN, $ADMIN;
		$ibforums = Ibf::app();
		$ADMIN->HTML .= $SKIN->add_td_row(array(
		                                       "<b>Allowed File Types?</b><br>The files types the image is allowed to have. Seperator with a comma ",
		                                       $SKIN->form_input("extra_three", $EXTRA['extra_three'])
		                                  ));

		return $ADMIN->HTML;
	}

	function on_add_edits($admin)
	{
	}

	function on_add_extra()
	{
	}

	function on_buy()
	{
	}

	function on_use($itemid = "")
	{
		global $ibforums;
		return <<<EOF
			<form action='{$ibforums->base_url}act=store&code=useitem&itemid={$itemid}' name='item' method='post'  enctype='multipart/form-data'>
			  <tr>
				<td class='pformstrip' width='100%' colspan='4'>Upload Avatar</td>
			</tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>Usernames Avatar To Change:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='text' name='username'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='50%' colspan='2'><strong>File To Upload:</strong></td>
				<td class='pformleft' width='50%' colspan='1'><input type='file' name='name'></td>
			 </tr>
			  <tr>
				<td class='pformleft' width='100%' align='center' colspan='4'><input type='submit' name='change' value='Go!'></td>
			   </tr>
			</form>
EOF;
	}

	function run_job()
	{
	}

	function do_on_use($blank = "", $blank = "", $allowed)
	{
		global $ibforums, $print, $lib;
		$stmt      = $ibforums->db->query("SELECT id,name FROM ibf_members WHERE LOWER(name)='" . strtolower($ibforums->input['username']) . "' LIMIT 1");
		$user      = $stmt->fetch();
		$allowed   = explode(",", $allowed);
		$file_type = $_FILES['name']['type'];
		$tmp_name  = $_FILES['name']['tmp_name'];
		$file_type = preg_replace("/^(.+?);.*$/", "\\1", $file_type);

		require "./conf_mime_types.php";

		if ($file_size > ($ibforums->vars['avup_size_max'] * 1024))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'upload_to_big'));
		}
		$ext = '.gif';
		switch ($file_type)
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
				$ext = '.swf';
				break;
			default:
				$ext = "unknown";
				break;
		}
		if (is_array($allowed))
		{
			foreach ($allowed as $usable)
			{
				if (str_replace(".", "", $usable) == str_replace(".", "", $ext))
				{
					$found_type = true;
				}
				$allowed_types[] = $usable;
			}
		} else
		{
			if (str_replace(".", "", $allowed) == str_replace(".", "", $ext))
			{
				$found_type = true;
			} else
			{
				$allowed_types = $allowed;
			}
		}
		if (is_array($allowed_types))
		{
			$allowed_types = implode(", ", $allowed_types);
		}
		if ($ext == 'unknown' || !$found_type)
		{
			$lib->itemerror("You seem to be uploading a file type that is not aloud, the file types that can be used are, " . $allowed_types);
		}
		$name = 'av-' . $user['id'] . $ext;
		@chmod($ibforums->vars["upload_dir"] . '/', 0777);
		if (!@move_uploaded_file($tmp_name, $ibforums->vars["upload_dir"] . '/' . $name))
		{
			$lib->itemerror("We could not upload your avatar and move it to the folder uploads/ this is probly due to the folder not being chmoded to 777 permissions.");
		}
		$ibforums->db->exec("UPDATE ibf_members SET avatar='upload:{$name}' WHERE id='{$user['id']}' LIMIT 1");
		$lib->delete_item($ibforums->input['itemid']);
		$lib->redirect("Uploaded a new avatar for " . $user['name'] . "!", "act=store", "1");
		return "";
	}
}

?>



