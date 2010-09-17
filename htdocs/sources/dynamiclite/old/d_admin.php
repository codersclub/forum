<?php

  //----------------------------------------------------------------------------
  // D-Site administration functions
  // Copyright (c) 2005 Anton
  //----------------------------------------------------------------------------

  class d_admin {

          function create_category($data = null) {
          global $ibforums, $DB, $NAV, $DSITE;

                  $DB->query(" SELECT * FROM ibf_cms_uploads_cat

                               WHERE

                               name = '{$data['name']}'

                             ");

                  if ($DB->fetch_row()) {

                          return 0;
                  }

                  $data['parent_cat'] = intval($data['parent_cat']);

                  $DB->query(" INSERT INTO ibf_cms_uploads_cat

                                      (
                                           parent_id,
                                           name,
                                           category_id,
                                           description
                                      )

                               VALUES

                                      (
                                           '{$data['parent_cat']}',
                                           '{$data['name']}',
                                           '{$data['category_id']}',
                                           '{$data['description']}'

                                      )
                             ");

                  $inserted_id = $DB->get_insert_id();

                  $path = $NAV->build_path(array('current_cat_id' => $inserted_id));

                  $this->create_directory($path);

                  return true;
          }

          function edit_category($data = array()) {
          global $DB;

                  $DB->query(" SELECT * FROM ibf_cms_uploads_cat

                               WHERE

                               id = '{$data['id']}'

                             ");

                  if ($DB->fetch_row()) {

                          return -1;
                  }

                  $data['parent_cat'] = ($data['parent_cat']) ? $data['parent_cat'] : 0;

                  $DB->query(" UPDATE ibf_cms_uploads_cat

                               SET
                                      parent_id='{$data['parent_cat']}',
                                      name='{$data['name']}',
                                      description='{$data['description']}'

                               WHERE

                                      id='{$data['cat_id']}'
                             ");

                  return true;
          }

          function create_directory($directory_name) {
          global $ibforums;

                  umask(002);

                  return mkdir($directory_name);
          }

          function delete_file($file_path) {

                  return unlink($file_path);
          }

          function remove_directory($path) {

                  return rmdir($path);
          }

          function upload_files($params) {
          global $ibforums, $HTTP_POST_FILES, $std, $DB;

                  $file = $HTTP_POST_FILES['thefile'];

                  umask(002);

                  $path = $params['name'];

                  $error = "";
                  $copied = array();

                  $file['path'] = array();

                  for($i = 0; $i <= $ibforums->vars['csite_max_upload_files']; $i++) {

                          if(!empty($file['name'][$i])) {

                                  if($ibforums->vars['cms_uploads_size_limit'] >= $file['size'][$i]) {

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

                  if("" != $error) {

                          foreach($copied as $file) {

                                  unlink($file['path']);
                          }

                          $copied = array();

                          //$std->Error( array( 'LEVEL' => 1, 'MSG' => $error ) );
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

          function upload_icon($params) {
          global $ibforums, $HTTP_POST_FILES, $std, $DB;

                $file = $HTTP_POST_FILES['icon'];

                $result = "";
                $error = "";

                $path = $ibforums->vars['csite_cms_path'] . $params['name'];

                if($file['size'] > 0) {

                        if($ibforums->vars['cms_uploads_icon_limit'] >= $file['size']) {

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

                if("" != $error) {

                        $click_site->error = $error;
                        return false;
                }

                if(!$result) {

                        $result = array('id' => 0);
                }

                return $result;
          }

          function get_file() {
          global $ibforums, $DB;


                  $DB->query(" UPDATE ibf_cms_uploads_files

                               SET
                                      hits = hits + 1

                               WHERE
                                      id = {$ibforums->input['id']}

                             ");

                  $DB->query(" SELECT * FROM ibf_cms_uploads_files

                               WHERE
                                        id = {$ibforums->input['id']}
                             ");

                  $file = $DB->fetch_row();

                  header("Content-type: {$file['mime']}");
                  header("Content-Disposition: inline; filename=\"{$file['name']}\"");
                  header("Content-Length: ". (string)(filesize($file['path'])));

                  readfile($file['path']);
          }

          function remove_file() {
          global $ibforums, $DB, $std;

                  $id = intval($ibforums->input['id']);

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
  }

?>