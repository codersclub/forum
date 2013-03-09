<?php

// (C) SergeS
// Ver 2

$idx = new ad_sskins();

class ad_sskins
{

	var $base_url;

	function ad_sskins()
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

		$ADMIN->nav[] = array('act=sskins', 'Smiles sets');

		//---------------------------------------

		// Что делать ?
		switch ($IN['code'])
		{

			case 'edit':
				$this->emoticons();
				break;
			case 'emo_add':
				$this->add_emoticons();
				break;
			case 'emo_remove':
				$this->remove_emoticons();
				break;
			case 'emo_edit':
				$this->edit_emoticons();
				break;
			case 'emo_doedit':
				$this->doedit_emoticons();
				break;

			case 'rename':
				$this->rename_s();
				break;

			case 'remove': // Удалить
				$this->remove();
				break;

			case 'add': // Добавить
				$this->add();
				break;

			default: // Показать список
				$this->list_current();
				break;
		}
	}

	function rename_s()
	{
		global $ADMIN, $IN, $std, $SKIN;
		$ibforums = Ibf::app();

		// Проверяемся
		if ($IN['id'] == '')
		{
			$ADMIN->error("Bad request ( no id )");
		}

		// Получаем текущее имя скина
		$stmt = $ibforums->db->query("SELECT `name` FROM ibf_emoticons_skins WHERE id = {$IN['id']}");

		// А есть ли такой скин ?
		if ($stmt->rowCount() == 0)
		{
			$ADMIN->error("Invalid skin");
		}

		$t_array = $stmt->fetch();

		// А меняли ли мы название ?
		if ($IN['name'] == $t_array['name'])
		{
			$std->boink_it($SKIN->base_url . "&act=sskin&code=edit&id={$IN['id']}"); // Нет :)
			exit();
		}

		// написать чтонить нада :)
		if ($IN['name'] == '')
		{
			$ADMIN->error("Name is empty");
		}

		// переименуем и если усё ок то запишем в базу
		if (rename(ROOT_PATH . 'smiles/' . $t_array['name'], ROOT_PATH . 'smiles/' . $IN['name']))
		{
			$ibforums->db->exec("UPDATE ibf_emoticons_skins SET `name` = '{$IN['name']}' WHERE `id` = '{$IN['id']}'");
		}

		$std->boink_it($SKIN->base_url . "&act=sskin&code=edit&id={$IN['id']}");
		exit();
	}

	function remove()
	{
		global $ADMIN, $IN, $std, $SKIN;
		$ibforums = Ibf::app();

		// Проверяемся
		if ($IN['id'] == '')
		{
			$ADMIN->error("Bad request ( no id )");
		}

		// А есть ли такое ?
		$stmt = $ibforums->db->query("SELECT `name` FROM ibf_emoticons_skins WHERE id = {$IN['id']}");
		if ($stmt->rowCount() == 0)
		{
			$ADMIN->error("Invalid ID");
		}

		// Наш скин
		$sskin_td = $stmt->fetch();

		// Один чтоб остался
		$stmt = $ibforums->db->query("SELECT * FROM ibf_emoticons_skins WHERE id <> {$IN['id']}");
		if ($stmt->rowCount() == 0)
		{
			$ADMIN->error("Only one skin left - dont do this");
		}

		// Скин на который заменить %)
		$fallonskin = $stmt->fetch();

		// Меняем смайлоскин юзверов
		$ibforums->db->exec("UPDATE ibf_members SET `sskin_id` = {$fallonskin['id']} WHERE `sskin_id` = {$IN['id']} ");
		// Чистим базу
		$ibforums->db->exec("DELETE FROM ibf_emoticons_skins WHERE id={$IN['id']}");

		// Удаляем диру
		$ADMIN->rm_dir(ROOT_PATH . 'smiles/' . $sskin_td['name']);
		// Усё
		$std->boink_it($SKIN->base_url . "&act=sskin");
		exit();
	}

	function add()
	{
		global $ADMIN, $IN, $INFO, $std, $SKIN;
		$ibforums = Ibf::app();

		// Проверяем то что на входе
		if ($IN['name'] == 'Name of set')
		{
			$std->boink_it($SKIN->base_url . "&act=sskin");
			exit();
		}

		if ($IN['name'] == '')
		{
			$ADMIN->error("Name is empty");
		}

		// Проверяем название
		$stmt = $ibforums->db->query("SELECT id FROM ibf_emoticons_skins WHERE `name` = '{$IN['name']}'");
		if ($stmt->rowCount() > 0)
		{
			$ADMIN->error("Set with this name already exists");
		}

		// Добавляем
		$ibforums->db->exec("INSERT INTO ibf_emoticons_skins (`name`) VALUES('{$IN['name']}')");

		$new_id = $ibforums->db->lastInsertId();

		// Название первого скина
		$stmt = $ibforums->db->query("SELECT name FROM ibf_emoticons_skins WHERE `id` = 1");
		if (!($name_a = $stmt->fetch()))
		{
			$ADMIN->error("No skin with id 1");
		}

		// cоздаём диру
		if (!$ADMIN->copy_dir(ROOT_PATH . 'smiles/' . $name_a['name'], ROOT_PATH . 'smiles/' . $IN['name']))
		{
			$ibforums->db->exec("DELETE FROM ibf_emoticons_skins WHERE name='{$IN['name']}'");
			$ADMIN->error($ADMIN->errors);
		}

		// Последнее - копируем смайлы
		$stmt = $ibforums->db->query("SELECT `typed`, `image`, `clickable` FROM ibf_emoticons WHERE `skid` = 1");
		while ($line = $stmt->fetch())
		{
			$ibforums->db->exec("INSERT INTO ibf_emoticons ( `typed`, `image`, `clickable`, `skid` ) VALUES ( '{$line['typed']}', '{$line['image']}', '{$line['clickable']}', '{$new_id}')");
		}

		// Фсё %)
		$std->boink_it($SKIN->base_url . "&act=sskin");
		exit();
	}

	function list_current()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->page_detail = "";
		$ADMIN->page_title  = "Smiles sets manager";

		$SKIN->td_header[] = array("Smiles ser name", "70%");
		$SKIN->td_header[] = array("Options", "30%");

		$ADMIN->html .= $SKIN->start_table("Smiles sets");

		// Читаем усё
		$stmt = $ibforums->db->query("SELECT * FROM ibf_emoticons_skins ORDER BY `id`");

		// Можно ли удалять ?
		$remove = FALSE;
		if ($stmt->rowCount() > 1)
		{
			$remove = TRUE;
		}

		// генерим таблицу
		while ($line = $stmt->fetch())
		{
			$remove_str = '';
			if ($remove && ($line['id'] != 1))
			{
				$remove_str = " | <a href=\"" . $SKIN->base_url . "&act=sskin&code=remove&id={$line['id']}\">Remove</a>";
			}
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       $line['name'],
			                                       "<center><a href=\"" . $SKIN->base_url . "&act=sskin&code=edit&id={$line['id']}\">Edit</a>{$remove_str}</center>"
			                                  ));
		}

		$ADMIN->html .= $SKIN->end_table();

		// Форма клонирования
		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'add'),
		                                       2 => array('act', 'sskin'),
		                                  ));

		//    $SKIN->td_header[] = array( "&nbsp;"  , "100%" );

		$ADMIN->html .= $SKIN->start_table("Add new smiles set");
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       $SKIN->form_input('name', 'Name of set'),
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Add");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	function doedit_emoticons()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['before'] == "")
		{
			$ADMIN->error("You must enter text to replace, silly!");
		}

		if ($IN['sid'] == "")
		{
			$ADMIN->error("You must pass a valid emoticon id, silly!");
		}

		if (strstr($IN['before'], '&#092;'))
		{
			$ADMIN->error("You cannot use the backslash character in \"{$IN['before']}\". Please use another character");
		}

		$IN['clickable'] = $IN['clickable']
			? 1
			: 0;

		$db_string = [
			'typed'     => $IN['before'],
			'image'     => $IN['after'],
			'clickable' => $IN['click'],
		];

		$ibforums->db->updateRow("ibf_emoticons", array_map([
		                                                    $ibforums->db,
		                                                    'quote'
		                                                    ], $db_string), "id='" . $IN['sid'] . "'");

		$std->boink_it($SKIN->base_url . "&act=sskin&code=edit&id={$IN['id']}");
		exit();

	}

	//=====================================================

	function edit_emoticons()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->page_detail = "You may edit the emoticon filter below";
		$ADMIN->page_title  = "Edit Emoticon";
		$ADMIN->nav[]       = array('act=sskins', 'Smiles sets');
		$ADMIN->nav[]       = array("act=sskins&code=edit&id={$IN['id']}", 'Edit current smile set');

		//+-------------------------------

		if ($IN['sid'] == "")
		{
			$ADMIN->error("You must pass a valid filter id, silly!");
		}

		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT * FROM ibf_emoticons WHERE id='" . $IN['sid'] . "'");

		if (!$r = $stmt->fetch())
		{
			$ADMIN->error("We could not find that emoticon in the database");
		}

		//+-------------------------------
		$stmt    = $ibforums->db->query("SELECT `name` from ibf_emoticons_skins WHERE id = {$IN['id']}");
		$test_ss = $stmt->fetch();
		$emo_url = ROOT_PATH . "/smiles/{$test_ss['name']}/";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'emo_doedit'),
		                                       2 => array('act', 'sskin'),
		                                       3 => array('sid', $IN['sid']),
		                                       4 => array('id', $IN['id']),
		                                  ));

		$SKIN->td_header[] = array("Before", "40%");
		$SKIN->td_header[] = array("After", "40%");
		$SKIN->td_header[] = array("+ Clickable", "20%");

		//+-------------------------------

		$emos = array();

		if (!is_dir($emo_url))
		{
			$ADMIN->error("Could not locate the emoticons directory - make sure the 'html_dir' path is set correctly");
		}

		//+-------------------------------

		$dh = opendir($emo_url) or die("Could not open the emoticons directory for reading, check paths and permissions");
		while ($file = readdir($dh))
		{
			if (!preg_match("/^..?$|^index|htm$|html$|^\./i", $file))
			{
				$emos[] = array($file, $file);
			}
		}
		closedir($dh);

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Edit an Emoticon");

		$ADMIN->html .= "<script language='javascript'>
						 <!--
						 	function show_emo() {

						 		var emo_url = '$emo_url' + document.theAdminForm.after.options[document.theAdminForm.after.selectedIndex].value;

						 		document.images.emopreview.src = emo_url;
							}
						//-->
						</script>
						";

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       $SKIN->form_input('before', stripslashes($r['typed'])),
		                                       $SKIN->form_dropdown('after', $emos, $r['image'], "onChange='show_emo()'") . "&nbsp;&nbsp;<img src='$emo_url{$r['image']}' name='emopreview' border='0'>",
		                                       $SKIN->form_dropdown('click', array(
		                                                                          0 => array(1, 'Yes'),
		                                                                          1 => array(0, 'No')
		                                                                     ), $r['clickable'])
		                                  ));

		$ADMIN->html .= $SKIN->end_form('Edit Emoticon');

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//=====================================================

	function remove_emoticons()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['sid'] == "")
		{
			$ADMIN->error("You must pass a valid emoticon id, silly!");
		}

		$ibforums->db->exec("DELETE FROM ibf_emoticons WHERE id='" . $IN['sid'] . "'");

		$std->boink_it($SKIN->base_url . "&act=sskin&code=edit&id={$IN['id']}");
		exit();

	}

	//=====================================================

	function add_emoticons()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['before'] == "")
		{
			$ADMIN->error("You must enter an emoticon text to replace, silly!");
		}

		if (strstr($IN['before'], '&#092;'))
		{
			$ADMIN->error("You cannot use the backslash character in \"{$IN['before']}\". Please use another character");
		}

		$IN['click'] = $IN['click']
			? 1
			: 0;

		$data = [
			'typed'     => $IN['before'],
			'image'     => $IN['after'],
			'clickable' => $IN['click'],
			'skid'      => $IN['id'],
		];

		$ibforums->db->insertRow("ibf_emoticons", $data);

		$std->boink_it($SKIN->base_url . "&act=sskin&code=edit&id={$IN['id']}");
		exit();

	}

	function perly_length_sort($a, $b)
	{
		if (strlen($a['typed']) == strlen($b['typed']))
		{
			return 0;
		}
		return (strlen($a['typed']) > strlen($b['typed']))
			? -1
			: 1;
	}

	function perly_word_sort($a, $b)
	{
		if (strlen($a['type']) == strlen($b['type']))
		{
			return 0;
		}
		return (strlen($a['type']) > strlen($b['type']))
			? -1
			: 1;
	}

	function emoticons()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->page_detail = "You may add/edit or remove emoticons in this section.<br>You can only choose emoticons that have been uploaded into the 'html/emoticons' directory.<br><br>Clickable refers to emoticons that are in the posting screens 'Clickable Emoticons' table.";
		$ADMIN->page_title  = "Emoticon Control";
		//		$ADMIN->nav[] = array( 'act=sskins', 'Smiles sets' );

		$stmt    = $ibforums->db->query("SELECT `name` from ibf_emoticons_skins WHERE `id` = '{$IN['id']}'");
		$test_ss = $stmt->fetch();
		$emo_url = ROOT_PATH . "/smiles/{$test_ss['name']}/";

		// Переименование
		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'rename'),
		                                       2 => array('act', 'sskin'),
		                                       3 => array('id', $IN['id']),
		                                  ));

		$ADMIN->html .= $SKIN->start_table("Rename");

		$ADMIN->html .= $SKIN->add_td_row(array($SKIN->form_input('name', $test_ss['name'])));

		$ADMIN->html .= $SKIN->end_form('Rename');

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("Before", "30%");
		$SKIN->td_header[] = array("After", "30%");
		$SKIN->td_header[] = array("+ Clickable", "20%");
		$SKIN->td_header[] = array("Edit", "10%");
		$SKIN->td_header[] = array("Remove", "10%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Current Emoticons");

		$stmt = $ibforums->db->query("SELECT * from ibf_emoticons WHERE `skid` = '{$IN['id']}' ORDER BY id DESC");

		$smilies = array();

		if ($stmt->rowCount())
		{
			while ($r = $stmt->fetch())
			{
				$smilies[] = $r;
			}

			//			usort($smilies, array( 'ad_sskin', 'perly_length_sort' ) );

			foreach ($smilies as $array_idx => $r)
			{

				$click = $r['clickable']
					? 'Yes'
					: 'No';

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       stripslashes($r['typed']),
				                                       "<center><img src='$emo_url/{$r['image']}'></center>",
				                                       "<center>$click</center>",
				                                       "<center><a href='" . $SKIN->base_url . "&act=sskin&code=emo_edit&id={$IN['id']}&sid={$r['id']}'>Edit</a></center>",
				                                       "<center><a href='" . $SKIN->base_url . "&act=sskin&code=emo_remove&id={$IN['id']}&sid={$r['id']}'>Remove</a></center>",
				                                  ));

			}
		}

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$emos = array();

		if (!is_dir($emo_url))
		{
			$ADMIN->error("Could not locate the emoticons directory - make sure the 'html_dir' path is set correctly");
		}

		//+-------------------------------

		$cnt   = 0;
		$start = "";

		$dh = opendir($emo_url) or die("Could not open the emoticons directory for reading, check paths and permissions");
		while ($file = readdir($dh))
		{
			if (!preg_match("/^..?$|^index|htm$|html$|^\./i", $file))
			{
				$emos[] = array($file, $file);

				if ($cnt == 0)
				{
					$cnt   = 1;
					$start = $file;
				}
			}
		}
		closedir($dh);

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'emo_add'),
		                                       2 => array('act', 'sskin'),
		                                       3 => array('id', $IN['id']),
		                                  ));

		$SKIN->td_header[] = array("Before", "40%");
		$SKIN->td_header[] = array("After", "40%");
		$SKIN->td_header[] = array("+ Clickable", "20%");

		//+-------------------------------

		$ADMIN->html .= "<script language='javascript'>
						 <!--
						 	function show_emo() {

						 		var emo_url = '$emo_url' + document.theAdminForm.after.options[document.theAdminForm.after.selectedIndex].value;

						 		document.images.emopreview.src = emo_url;
							}
						//-->
						</script>
						";

		$ADMIN->html .= $SKIN->start_table("Add a new Emoticon");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       $SKIN->form_input('before'),
		                                       $SKIN->form_dropdown('after', $emos, "", "onChange='show_emo()'") . "&nbsp;&nbsp;<img src='{$emo_url}$start' name='emopreview' border='0'>",
		                                       $SKIN->form_dropdown('click', array(
		                                                                          0 => array(1, 'Yes'),
		                                                                          1 => array(0, 'No')
		                                                                     ))
		                                  ));

		$ADMIN->html .= $SKIN->end_form('Add Emoticon');

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

}

?>
