<?php

class articles {

        function show_upload_form($data = array()) {
        global $ibforums, $navigation, $DB, $std, $DSITE;

               if (!$ibforums->member['id']) {

                       $std->Error(array('MSG' => 'cant_use_feature', 'INIT' => '1'));
                       exit();
               }

               $data['cat'] = $ibforums->input['cat'];

               if (empty($data['cat'])) {

                       $std->Error(array('MSG' => 'cms_category_not_selected', 'INIT' => '1'));
                       exit();
               }

               if ($data['is_edit'] && !$data['name']) {

                       $std->Error(array('MSG' => 'cms_category_not_selected', 'INIT' => '1'));
                       exit();
               }


               if ($data['is_edit']) {


                       if (!$navigation->cats) $navigation->build_main_cats();
                       $cat_name = $navigation->cats[$ibforums->input['cat']];


                       $DB->query(" SELECT * FROM ibf_cms_uploads_file_links

                                    WHERE
                                             base = {$data['id']}
                                  ");


                       while($files = $DB->fetch_row()) {

                               $old_files[] = $files;
                       }

                       if ($old_files) {

                               foreach ($old_files as $old_file) {

                                       $DB->query(" SELECT * FROM ibf_cms_uploads_files

                                                    WHERE
                                                          id = {$old_file['refs']}
                                                  ");

                                       $files[] = $DB->fetch_row();
                               }
                       }

                       if ($data['icon_id'] != 0) {

                               $DB->query(" SELECT * FROM ibf_cms_uploads_files

                                            WHERE id = {$data['icon_id']}

                                          ");

                               $icon = $DB->fetch_row();

                               if ($icon) {

                                       $icon['url'] = $ibforums->vars['csite_cms_url'] . "{$cat_name['category_id']}/{$data['article_id']}/" . $icon['name'];
                                       $icon['del'] = $DSITE->html->tmpl_author_del_file_lnk($file['id'], $ibforums->input['cat'], $ibforums->input['id']);
                                       $uploaded_icon = $DSITE->html->tmpl_show_uploaded_icon($icon);
                               }
                       }
               }

               if ($files) {

                       foreach($files as $file) {

                               $file['url'] = $ibforums->vars['csite_cms_url'] . "{$cat_name['category_id']}/{$data['article_id']}/" . $file['name'];
                               $file['del'] = $DSITE->html->tmpl_author_del_file_lnk($file['id'], $ibforums->input['cat'], $ibforums->input['id']);
                               $uploaded_files[] = $DSITE->html->tmpl_show_uploaded_files($file);
                       }
               }

               //upload files
               for ($i = 0; $i < $ibforums->vars['csite_max_upload_files']; $i++) {

                       if (!$uploaded_files[$i]) {
                               $uploaded_files[] = $DSITE->html->tmpl_show_upload_files($i+1);
                       }
               }

               $data['files'] = implode("\n", $uploaded_files);

               //uploaded icon
               $data['icon'] = ($uploaded_icon) ? $uploaded_icon : $DSITE->html->tmpl_show_upload_icon();


               $DSITE->build_sub_nav($data['cat'], (!$data['is_edit']) ? $ibforums->lang['author_add_article'] : $ibforums->lang['author_edit_article']);

               $data['title'] = (!$data['is_edit']) ? $ibforums->lang['author_add_article'] : $ibforums->lang['author_edit_article'];

               return $DSITE->html->tmpl_show_upload_form($data);
        }


        function delete() {
        global $ibforums, $DB, $d_admin, $navigation, $std, $DSITE;

                if (!$ibforums->input['art']) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }


                $DB->query(" SELECT * FROM ibf_cms_uploads

                             WHERE id = {$ibforums->input['art']}

                           ");

                $data = $DB->fetch_row();

                if (!$data) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                $DB->query(" DELETE FROM ibf_cms_uploads

                             WHERE id = '{$ibforums->input['art']}'

                           ");

                $DB->query(" SELECT * FROM ibf_cms_uploads_cat_links

                             WHERE base = {$ibforums->input['art']}

                           ");

                $result = $DB->fetch_row();

                if (!$navigation->cats) $navigation->build_main_cats();
                $current_dir = $navigation->cats[$result['refs']]['category_id'] . "/";

                if (!$result) {

                        $d_admin->remove_directory($ibforums->vars['csite_cms_path'] . $current_dir . $data['article_id']);
                        return $DSITE->js_redirect($ibforums->vars['dynamiclite']);
                }

                $DB->query(" DELETE FROM ibf_cms_uploads_cat_links

                             WHERE base = {$ibforums->input['art']}

                           ");

                $DB->query(" SELECT * FROM ibf_cms_uploads_file_links

                             WHERE
                                      base = {$ibforums->input['art']}
                           ");

                while ($result = $DB->fetch_row()) {

                        $files[] = $result;
                }

                $DB->query(" DELETE FROM ibf_cms_uploads_file_links

                             WHERE
                                      base = {$ibforums->input['art']}
                           ");

                if ($files) {

                        foreach ($files as $file) {

                                $DB->query(" SELECT * FROM ibf_cms_uploads_files

                                             WHERE
                                                      id = {$file['refs']}
                                           ");

                                $result = $DB->fetch_row();

                                $d_admin->delete_file($result['path']);

                                $DB->query(" DELETE FROM ibf_cms_uploads_files

                                             WHERE
                                                    id = {$file['refs']}
                                           ");
                        }
                }

                                //deleting icon
                if ($data['icon_id'] != 0) {

                        $DB->query(" SELECT * FROM ibf_cms_uploads_files

                                     WHERE id = {$data['icon_id']}

                                   ");

                        $result = $DB->fetch_row();

                        $d_admin->delete_file($result['path']);

                        $DB->query(" DELETE FROM ibf_cms_uploads_files

                                     WHERE id = {$data['icon_id']}

                                   ");
                }


                $d_admin->remove_directory($ibforums->vars['csite_cms_path'] . $current_dir . $data['article_id']);

                return true;
        }


        function edit() {
        global $ibforums, $DB, $articles, $DSITE, $std;

                if (!$ibforums->input['id'] && !$ibforums->input['cat']) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                $article_id = $DSITE->get_digit($ibforums->input['id']);

                $DB->query(" SELECT * FROM ibf_cms_uploads

                             WHERE id = {$article_id}

                           ");

                $data = $DB->fetch_row();

                if (!$data) {

                        $std->Error(array('MSG' => 'article_not_found', 'INIT' => '1'));
                        exit();
                }

                $data['is_edit'] = true;

                return $this->show_upload_form($data);
        }


        function upload() {
        global $ibforums, $DB, $d_admin, $navigation, $std, $DSITE;

                //if we have empty fields...
                if ($DSITE->is_empty($ibforums->input, array('name', 'short_desc', 'Post', 'article_id'))) {

                        $std->Error(array('MSG' => 'data_incorrect', 'INIT' => '1'));
                        exit();
                }

                $current_date = time();

                $current_id = $DSITE->get_digit($ibforums->input['id']);
                $current_cat = $DSITE->get_digit($ibforums->input['cat']);

                if (!$navigation->cats) $navigation->build_main_cats();

                $params['current_dir'] = $navigation->cats[$DSITE->get_digit($ibforums->input['cat'])]['category_id'];
                $params['name'] = $params['current_dir'] . "/" . $ibforums->input['article_id'];

                if (!preg_match("/^[a-z]/i", $ibforums->input['article_id'])) {

                        $std->Error(array('MSG' => 'data_incorrect', 'INIT' => '1'));
                        exit();
                }

               $ibforums->input['Post'] =  $DSITE->parser->unconvert($ibforums->input['Post'], 0, 1);
               $ibforums->input['short_desc'] =  $DSITE->parser->unconvert($ibforums->input['short_desc'], 0, 1);



                //new or existing article?
                if (!$current_id) {

                        $DB->query(" INSERT INTO ibf_cms_uploads

                                     (      name,
                                            article_id,
                                            short_desc,
                                            article,
                                            user_id,
                                            author_name,
                                            submit_date

                                     )

                                     VALUES

                                     (     '{$ibforums->input['name']}',
                                           '{$ibforums->input['article_id']}',
                                           '{$ibforums->input['short_desc']}',
                                           '{$ibforums->input['Post']}',
                                           '{$ibforums->member['id']}',
                                           '{$ibforums->member['name']}',
                                           '{$current_date}'
                                     )

                                   ");

                        $inserted_id = $DB->get_insert_id();

                        $DB->query(" INSERT INTO ibf_cms_uploads_cat_links

                                     (
                                            base,
                                            refs
                                     )

                                     VALUES

                                     (
                                           {$inserted_id},
                                           {$ibforums->input['cat']}
                                     )


                                   ");

                        //create directory


                        $d_admin->create_directory($params['current_dir'] . "/" . $ibforums->input['article_id']);

                        $file_ids = $d_admin->upload_files($params);

                        if ($file_ids) {

                                foreach ($file_ids as $file_id) {

                                        $DB->query( " INSERT INTO ibf_cms_uploads_file_links

                                                      (

                                                             base,
                                                             refs

                                                      )

                                                      VALUES

                                                      (

                                                            {$inserted_id},
                                                            {$file_id['id']}

                                                      )

                                                    " );
                                }
                        }

                        //uploaded icon
                        $icon_id = $d_admin->upload_icon($params);

                        if ($icon_id['id'] != 0) {

                                $DB->query(" UPDATE ibf_cms_uploads SET

                                                    icon_id = '{$icon_id['id']}'

                                             WHERE
                                                    id = {$inserted_id}
                                           ");
                        }

                } else {

                        $DB->query(" SELECT * FROM ibf_cms_uploads

                                     WHERE
                                              id = {$ibforums->input['id']}
                                   ");

                        $article = $DB->fetch_row();


                        $DB->query(" UPDATE ibf_cms_uploads SET

                                            name        = '{$ibforums->input['name']}',
                                            short_desc  = '{$ibforums->input['short_desc']}',
                                            article     = '{$ibforums->input['Post']}',
                                            user_id     = '{$ibforums->member['id']}',
                                            author_name = '{$ibforums->member['name']}',
                                            submit_date = '{$current_date}'

                                     WHERE

                                            id = {$ibforums->input['id']}

                                   ");


                        //заполняем таблицу связей файлов со статьей
                        $file_ids = $d_admin->upload_files($params);

                        if ($file_ids) {

                                foreach ($file_ids as $file_id) {

                                        $DB->query( " INSERT INTO ibf_cms_uploads_file_links

                                                      (

                                                             base,
                                                             refs

                                                      )

                                                      VALUES

                                                      (

                                                             {$ibforums->input['id']},
                                                             {$file_id['id']}

                                                      )

                                                    " );
                                }
                        }


                        $icon_id = $d_admin->upload_icon($params);

                        if ($article['icon_id'] != 0 && $icon_id['id'] == 0) {

                                $DB->query(" UPDATE ibf_cms_uploads SET

                                                    icon_id = '0'

                                             WHERE

                                                    id = {$ibforums->input['id']}

                                           ");
                        }

                        if ($icon_id['id'] != 0) {

                                $DB->query(" UPDATE ibf_cms_uploads SET

                                                    icon_id = '{$icon_id['id']}'

                                             WHERE

                                                    id = {$ibforums->input['id']}

                                           ");
                        }
                }

                return true;
        }

        function get_news_line() {
        global $DB, $ibforums, $DSITE;

                $html = "";

                $DB->query(" SELECT * FROM ibf_cms_uploads_cat_links

                             WHERE refs = 1

                            ");

                 while ($dbres = $DB->fetch_row()) {

                         $got_article_ids[] = $dbres['base'];
                 }

                 if ($got_article_ids) {

                         foreach ($got_article_ids as $article_id) {

                                 $DB->query(" SELECT * FROM ibf_cms_uploads

                                              WHERE id = {$article_id}

                                            ");

                                 $articles[] = $DB->fetch_row();
                         }
                 }


                 if (null != $articles) {

                         foreach ($articles as $article) {

                                  $html .= $DSITE->html->tmpl_articles_main_row($article);
                         }
                 }

                 return $DSITE->html->tmpl_articles_main($html);
        }
}


?>
