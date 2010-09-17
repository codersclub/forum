<?php

/*--------------------------------------------------------------------------

  mod_cat() - working with site categories
  Copyright (c) Anton 2004-2005

-------------------------------------------------------------------------*/

class mod_cat {

        function show_create_category($params = array()) {
        global $ibforums, $NAV, $DSITE, $USR, $std, $DB;

                $cat_id = intval($ibforums->input['id']);
                $is_edit = false;

                if ( $ibforums->input['is_edit'] == '1' ) {

                        $is_edit = true;
                }

                if ($USR->is_admin() == false) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                if ( $is_edit == true && $cat_id == 0 ) {

                        $std->Error(array('MSG' => 'category_not_found', 'INIT' => '1'));
                        exit();
                }


                if ($cat_id != 0 && $is_edit == true) {

                        $DB->query(" SELECT * FROM ibf_cms_uploads_cat

                                     WHERE id = {$cat_id}

                                   ");

                        $params = $DB->fetch_row();

                        if ($params['visible'] == 1) {

                                $params['visible'] = 'CHECKED';
                        } else {
                                $params['visible'] = '';
                        }

                } else {
                        $params['visible'] = 'CHECKED';
                }


                $params['cat_list'] = $NAV->build_cat_list_select();
                $params['title'] = (!$is_edit) ? $ibforums->lang['create_category'] : $ibforums->lang['edit_category'];

                $DSITE->build_sub_nav($ibforums->input['id'], (!$is_edit) ? $ibforums->lang['create_category'] : $ibforums->lang['edit_category']);

                return $DSITE->html->tmpl_show_create_category($params);
        }


        //----------------------------------------------------------------------
        //  checks input information, access rights
        //  and apply edit/change information
        //----------------------------------------------------------------------

        function do_edit_create() {
        global $ibforums, $NAV, $DSITE, $std, $USR;

                if ($USR->is_admin() == false) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }

                $cat_id     = intval($ibforums->input['cat_id']);
                $parent_cat = intval($ibforums->input['parent_cat']);

                if ($DSITE->is_empty($ibforums->input, array('name', 'category_id'))) {

                        $std->Error(array('MSG' => 'data_incorrect', 'INIT' => '1'));
                        exit();
                }

                if (!preg_match("/^[a-z0-9]/i", $ibforums->input['category_id'])) {

                        $std->Error(array('MSG' => 'data_incorrect', 'INIT' => '1'));
                        exit();
                }

                if ($cat_id != 0 && $cat_id == $parent_cat) {

                        $std->Error(array('MSG' => 'category_include_incorrect', 'INIT' => '1'));
                        exit();
                }

                if ($cat_id != 0) {

                        return $this->edit_category($ibforums->input);
                } else {
                        return $this->create_category($ibforums->input);
                }
        }

        //----------------------------------------------------------------------
        //      removing category entries from DB & local FS
        //      TODO: удаление прикрепленных статей? перенос существующих статей в верхний раздел?
        //----------------------------------------------------------------------

        function delete_category() {
        global $ibforums, $DB, $std, $NAV, $USR;

                if ($USR->is_admin() == false) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                }


                $cat_id = intval($ibforums->input['id']);


                if ($cat_id == 0) {

                        $std->Error(array('MSG' => 'category_not_found', 'INIT' => '1'));
                        exit();
                }

                $DB->query(" SELECT * FROM ibf_cms_uploads_cat

                             WHERE id = {$cat_id}

                           ");

                $data = $DB->fetch_row();

                if (!$data) {

                        $std->Error(array('MSG' => 'category_not_found', 'INIT' => '1'));
                        exit();
                }

                // if we have sub cats - deny deleting...

                if ($NAV->get_children($cat_id)) {

                        $std->Error(array('MSG' => 'remove_subcats', 'INIT' => '1'));
                        exit();
                }



                //if articles exists - deny deleting...

                $DB->query(" SELECT * FROM ibf_cms_uploads_cat_links

                             WHERE refs = {$ibforums->input['id']}

                           ");

                $result = $DB->fetch_row();

                if ($result) {

                        $std->Error(array('MSG' => 'remove_articles', 'INIT' => '1'));
                        exit();
                }

                $path = $NAV->build_path(array('current_cat_id' => $cat_id));

                if (!$this->remove_directory($path)) {

                        $std->Error(array('MSG' => 'remove_articles', 'INIT' => '1'));
                        exit();
                }

                $DB->query(" DELETE FROM ibf_cms_uploads_cat

                             WHERE id = '{$cat_id}'

                           ");

                $DB->query(" SELECT * FROM ibf_cms_uploads_cat_links

                             WHERE refs = {$cat_id}

                           ");

                return header("Location: " . $ibforums->vars['dynamiclite']);
        }

          //--------------------------------------------------------------------
          //    applying $this->create() changes
          //--------------------------------------------------------------------

          function create_category($data = null) {
          global $ibforums, $DB, $NAV, $DSITE, $USR, $std;

                  $is_visible = 0;

                  if (isset($data['is_visible'])) {

                          $is_visible = 1;
                  }

                  $data['parent_cat'] = intval($data['parent_cat']);

                  $DB->query(" INSERT INTO ibf_cms_uploads_cat

                                      (
                                           parent_id,
                                           name,
                                           category_id,
                                           description,
                                           visible
                                      )

                               VALUES

                                      (
                                           '{$data['parent_cat']}',
                                           '{$data['name']}',
                                           '{$data['category_id']}',
                                           '{$data['description']}',
                                           '{$is_visible}'

                                      )
                             ");

                  $inserted_id = $DB->get_insert_id();

                  $path = $NAV->build_path(array('current_cat_id' => $inserted_id));

                  if ($this->create_directory($path) == false) {

                          $DB->query(" DELETE FROM ibf_cms_uploads_cat

                                       WHERE id = {$inserted_id}

                                     ");

                          $std->Error(array('MSG' => 'error_create_directory', 'INIT' => '1'));
                          exit();
                  }

                  return header("Location: " . $ibforums->vars['dynamiclite'] . "cat=" . $inserted_id);
          }

          //--------------------------------------------------------------------
          //      applying category changes to DB
          //      TODO[CRITICAL]: перенос файлов и директории на локальной ‘— на новое место
          //--------------------------------------------------------------------

          function edit_category($data = array()) {
          global $DB, $NAV, $ibforums, $USR, $std;


                  $is_visible = 0;

                  if (isset($data['is_visible'])) {

                          $is_visible = 1;
                  }

                  $DB->query(" SELECT * FROM ibf_cms_uploads_cat

                               WHERE

                               id = '{$data['id']}'

                             ");

                  if ($DB->fetch_row()) {

                          $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                          exit();
                  }

                  $data['parent_cat'] = intval($data['parent_cat']);

                  //moving old files to new location

                  $old_path = $NAV->build_path(array('current_cat_id' => $data['id']));

                  $DB->query(" UPDATE ibf_cms_uploads_cat

                               SET
                                      parent_id='{$data['parent_cat']}',
                                      name='{$data['name']}',
                                      description='{$data['description']}',
                                      visible='{$is_visible}'

                               WHERE

                                      id='{$data['cat_id']}'
                             ");

                  $new_path = $NAV->build_path(array('current_cat_id' => $data['cat_id']));
                  //$this->create_directory($new_path);

                  //$this->move_files($old_path, $new_path);

                  return header("Location: " . $ibforums->vars['dynamiclite'] . "cat=" . $data['cat_id']);
          }

          //--------------------------------------------------------------------
          //      mkdir() implementation + setting file access rigths
          //--------------------------------------------------------------------

          function create_directory($directory_name) {
          global $ibforums;

                  umask( 002 );

                  if ( file_exists( $directory_name ) ) {

                          return false;
                  }

                  return @mkdir($directory_name);
          }

          //--------------------------------------------------------------------
          //      unlink() implementation
          //--------------------------------------------------------------------

          function delete_file($file_path) {

                  return @unlink($file_path);
          }

          //--------------------------------------------------------------------
          //    rmdir() implementation
          //--------------------------------------------------------------------

          function remove_directory($path) {

                  return @rmdir($path);
          }

          //--------------------------------------------------------------------
          //  saving uploaded files to server
          //--------------------------------------------------------------------

          function upload_files($path) {
          global $ibforums, $HTTP_POST_FILES, $std, $DB;

                  $file = $HTTP_POST_FILES['thefile'];

                  umask(002);

                  $error = "";
                  $copied = array();

                  $file['path'] = array();

                  for($i = 0; $i <= $ibforums->vars['csite_max_upload_files']; $i++) {

                          if(!empty($file['name'][$i])) {

                                  //--------------------------------------------
                                  // fuck off hackerZ!
                                  //--------------------------------------------

                                  $allow_upload = false;

                                  $allowed_exts = explode("|", $ibforums->vars['csite_allowed_ext']);

                                  preg_match('/.*\.(.+)$/', $file['name'][$i], $matches);
                                  $ext = $matches[1];

                                  foreach ( $allowed_exts as $allowed_ext ) {

                                          if ( preg_match("/{$ext}/i", $allowed_ext) ) {

                                                  $allow_upload = true;
                                          }
                                  }

                                  if ( $allow_upload == false ) {

                                          $error = "no_av_type";
                                          break;
                                  }


                                  if( $ibforums->vars['cms_uploads_size_limit'] >= $file['size'][$i] && $ibforums->member['g_art_attach_max'] >= $file['size'][$i] ) {

                                          $file_path = $path . '/' . $file['name'][$i];

                                          if(copy($file['tmp_name'][$i], $file_path)) {

                                                  $copied[] = array (
                                                                         'path' => $file_path,
                                                                         'name' => $file['name'][$i],
                                                                         'type' => $file['type'][$i]
                                                                     );
                                          } else {

                                                  $error = 'missing_files';
                                                  break;
                                          }
                                  } else {

                                          $error = 'upload_to_big';
                                          break;
                                  }
                          }
                  }

                  if( $error ) {

                          foreach($copied as $file) {

                                  unlink($file['path']);
                          }

                          $copied = array();

                          $std->Error(array('MSG' => $error, 'INIT' => '1'));
                          return false;
                  }

                  $result = array();
                  foreach($copied as $file) {

                          if(empty($file['type'])) {

                                  preg_match('/.*\.(.+)$/', $file['name'], $matches);
                                  $ext = $matches[1];
                                  $mime = $ibforums->vars['cms_mime_map'][$ext];

                                  if(empty($mime)) {

                                          $mime = "text/plain";
                                  }
                          } else {

                                  $mime = $file['type'];

                          }

                          $query_add_file = " INSERT INTO ibf_cms_uploads_files

                                                     (

                                                          name,
                                                          path,
                                                          mime
                                                     )

                                              VALUES

                                                     (
                                                          '{$file['name']}',
                                                          '{$file['path']}',
                                                          '{$mime}'
                                                     )";

                        $DB->query($query_add_file);

                        $file['id'] = $DB->get_insert_id();

                        $result[] = $file;
                  }

                  return $result;
          }

          //--------------------------------------------------------------------
          //      saving uploaded icon to server
          //--------------------------------------------------------------------

          function upload_icon($path) {
          global $ibforums, $HTTP_POST_FILES, $std, $DB;

                $file = $HTTP_POST_FILES['icon'];

                $result = "";
                $error = "";

                if($file['size'] > 0) {

                        if($ibforums->vars['cms_uploads_icon_limit'] >= $file['size'] && $ibforums->member['g_art_attach_max'] >= $file['size'] ) {

                                  //--------------------------------------------
                                  // fuck off hackerZ!
                                  //--------------------------------------------

                                  $allow_upload = false;

                                  $allowed_exts = explode("|", $ibforums->vars['csite_allowed_ext']);

                                  preg_match('/.*\.(.+)$/', $file['name'], $matches);
                                  $ext = $matches[1];

                                  foreach ( $allowed_exts as $allowed_ext ) {

                                          if ( preg_match("/{$ext}/i", $allowed_ext) ) {

                                                  $allow_upload = true;
                                          }
                                  }

                                  if ( $allow_upload == false ) {

                                          $error = "no_av_type";
                                          $std->Error(array('MSG' => $error, 'INIT' => '1'));
                                          exit();
                                  }


                                $file_path = $path . '/' . $file['name'];


                                if(copy($file['tmp_name'], $file_path)) {

                                        if(empty($file['type'])) {

                                                preg_match('/.*\.(.+)$/', $file['name'], $matches);
                                                $ext = $matches[1];
                                                $mime = $ibforums->vars['cms_mime_map'][$ext];

                                                if(empty($mime)) {

                                                        $mime = "text/plain";
                                                }
                                        } else {

                                                $mime = $file['type'];
                                        }

                                        $query_add_file = "
                                                INSERT INTO ibf_cms_uploads_files (
                                                        name,
                                                        path,
                                                        mime
                                                )
                                                VALUES (
                                                        '{$file['name']}',
                                                        '{$file_path}',
                                                        '{$mime}'
                                                )";

                                        $DB->query($query_add_file);
                                        $file['id'] = $DB->get_insert_id();
                                        $result = $file;

                                } else {
                                        $error = 'missing_files';
                                        break;
                                }
                        } else {
                                $error = 'upload_to_big';
                        }
                }

                if( $error ) {

                          $std->Error(array('MSG' => $error, 'INIT' => '1'));
                          return false;
                }

                if(!$result) {

                        $result = array('id' => 0);
                }

                return $result;
          }

          function get_file() {
          global $ibforums, $DB, $NAV, $std;

                  $file_id = intval($ibforums->input['get_file']);
                  $cat_id = intval($ibforums->input['cat']);
                  $art_id = intval($ibforums->input['id']);

                  if ( $file_id == 0 || $cat_id == 0 || $art_id == 0 ) {

                          $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                          exit();
                  }

                  $DB->query(" UPDATE ibf_cms_uploads_files

                               SET
                                      hits = hits + 1

                               WHERE
                                      id = {$file_id}

                             ");

                  $DB->query("SELECT * FROM ibf_cms_uploads_files WHERE  id = {$file_id}");

                  $file = $DB->fetch_row();

                  if ( $file ) {

                          $DB->query("SELECT article_id FROM ibf_cms_uploads WHERE  id = {$art_id} ");

                          $dbres = $DB->fetch_row();

                          $path = $NAV->build_path($cat_id) . $dbres['article_id'] . "/" . $file['name'];

                          if ( !file_exists($path) ) {

                                  $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                                  exit;
                          }
                  }

                  header("Content-type: {$file['mime']}");
                  header("Content-Disposition: inline; filename=\"{$file['name']}\"");
                  header("Content-Length: ". (string)(filesize($path)));

                  readfile($path);

                  exit();
          }

          function remove_file() {
          global $ibforums, $DB, $std, $USR;

                  $id = intval($ibforums->input['id']);

                  //deleting icon

                  if ($ibforums->input['is_icon'] == 1) {

                          $DB->query(" SELECT user_id FROM ibf_cms_uploads

                                       WHERE icon_id = {$id}

                                     ");

                          $article = $DB->fetch_row();

                          if ($USR->is_owner($article['user_id']) == false) {

                                  $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                                  exit();
                          }

                          $DB->query(" SELECT * FROM ibf_cms_uploads_files

                                       WHERE id = {$id}

                                     ");

                          $dbres = $DB->fetch_row();

                          if (!$dbres) {

                                  $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                                  exit();
                          }


                          if ($this->delete_file($dbres['path'])) {

                                  $DB->query(" DELETE FROM ibf_cms_uploads_files

                                               WHERE id = {$id}

                                             ");

                                  $DB->query(" UPDATE ibf_cms_uploads

                                               SET icon_id = '0'

                                               WHERE icon_id = {$id}

                                             ");


                                  header("Location: {$ibforums->vars['dynamiclite']}act=edit_article&cat={$ibforums->input['cat']}&id={$ibforums->input['art']}");
                          } else {

                                  $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                                  exit();
                          }
                  }

                  // deleting usual file

                  $DB->query(" SELECT * FROM ibf_cms_uploads_file_links

                               WHERE refs = {$id}

                             ");

                  $dbres = $DB->fetch_row();

                  $DB->query(" SELECT user_id FROM ibf_cms_uploads

                               WHERE id = {$dbres['base']}

                             ");

                  $article = $DB->fetch_row();

                  if ($USR->is_owner($article['user_id']) == false) {

                        $std->Error(array('MSG' => 'no_permission', 'INIT' => '1'));
                        exit();
                  }

                  $DB->query(" SELECT * FROM ibf_cms_uploads_files

                               WHERE id = {$id}

                             ");

                  $dbres = $DB->fetch_row();

                  if (!$dbres) {

                          $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                          exit();
                  }

                  if ($this->delete_file($dbres['path'])) {

                          $DB->query(" DELETE FROM ibf_cms_uploads_files

                                       WHERE id = {$id}

                                     ");

                          $DB->query(" DELETE FROM ibf_cms_uploads_file_links

                                       WHERE refs = {$id}

                                     ");


                          header("Location: {$ibforums->vars['dynamiclite']}act=edit_article&cat={$ibforums->input['cat']}&id={$ibforums->input['art']}");
                  } else {

                          $std->Error(array('MSG' => 'file_not_found', 'INIT' => '1'));
                          exit();
                  }
          }

          //--------------------------------------------------------------------
          //      moving files from $old_path to $new_path
          //--------------------------------------------------------------------

          function move_files($old_path = null, $new_path = null) {

                  $old_handle = opendir($old_path);

                  while (false !== ($old_file = readdir($old_handle))) {

                          if ($old_file != "." && $old_file != "..") {

                                  copy($old_path . $old_file, $new_path . $old_file);
                                  unlink($old_path . $old_file);
                          }
                  }

                  rmdir($old_path);

                  return true;
          }
}

?>
