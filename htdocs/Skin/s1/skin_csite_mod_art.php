<?php

/*
+--------------------------------------------------------------------------
|   D-Site Articles module skin sets
|   ========================================
|   (c) 2004 - 2006 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
*/


class skin_csite_mod_art {

//------------------------------------------------------------------------------
//  list of articles on the main page or in the category - main template
//------------------------------------------------------------------------------

function tmpl_articles($entry) {
global $ibforums;
return <<<EOF
 <div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$entry['title']}</div>
  <table cellspacing="0" width="100%">
    <td>
   {$entry['sub_cats']}
   </td>
   </tr>
</table>
</div>
</div>
<br>
  <br>
  {$entry['articles']}
</div>
EOF;
}

//------------------------------------------------------------------------------
//  list of articles on the main page or in the category - row templates
//------------------------------------------------------------------------------

function tmpl_articles_row($entry) {
global $ibforums;
return <<<EOF
<table cellspacing="0" width="100%" class='tableborder'>
<tr>
 <td class='maintitle' colspan="2"><img src="{$ibforums->vars['img_url']}/cs_page.gif" alt="" border="0">&nbsp;{$entry['article_link']}</td>
</tr>
<tr>
 <td class='row3' colspan="2" align='right'>{$entry['move_link']}{$entry['approve']}{$entry['edit_link']}{$entry['del_link']}</td>
</tr>
<tr>
 <td class='row2' colspan="3" style='padding:5px'>{$entry['top_string']}</td>
</tr>
<tr>
 <td class="post1" width="5%" valign="top" style="padding:5px">{$entry['article_icon_url']}</td>
 <td class="post1" width="95%" valign="top" style="padding:5px">{$entry['short_desc']}</td>
</tr>
<tr>
 <td class='row2' colspan="3" style='padding:5px' align='right'>
   {$entry['bottom_string']}
   &nbsp;&nbsp;
 </td>
</tr>
</table>
<br>
EOF;
}

//------------------------------------------------------------------------------
//  urls to insert into articles list rows
//------------------------------------------------------------------------------

function tmpl_cat_link($cat) {
global $ibforums;
return <<<EOF
<a href='{$ibforums->vars['dynamiclite']}cat={$cat['id']}'>{$cat['name']}</a>
EOF;
}

function tmpl_cat_link_rw($url, $name) {
global $ibforums;
return <<<EOF
<a href='{$url}'>{$name}</a>
EOF;
}

function tmpl_cat_link_redir($entry) {
global $ibforums;
return <<<EOF
<a href='{$entry['redirect_url']}'>{$entry['name']}</a>
EOF;
}

function tmpl_article_link($entry) {
global $ibforums;
return <<<EOF
<a href="{$ibforums->vars['dynamiclite']}cat={$entry['refs']}&id={$entry['id']}&version={$entry['version']}">{$entry['name']}</a>
EOF;
}

function tmpl_article_link_rw($url, $name) {
global $ibforums;
return <<<EOF
<a href="{$url}">{$name}</a>
EOF;
}

function tmpl_static_article_link($entry) {
global $ibforums;
return <<<EOF
<a href="{$entry['static_url']}">{$entry['name']}</a>
EOF;
}

function tmpl_approve_link($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$ibforums->vars['dynamiclite']}act=upload&cat={$entry['refs']}&id={$entry['id']}&version={$entry['version']}&ACTION=6'>{$ibforums->lang['approve']}</a>
EOF;
}

function tmpl_approve_link_rw($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$entry['url']}/approve.html?version={$entry['version']}'>{$ibforums->lang['approve']}</a>
EOF;
}

function tmpl_disable_link($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$ibforums->vars['dynamiclite']}act=upload&cat={$entry['refs']}&id={$entry['id']}&version={$entry['version']}&ACTION=7'>{$ibforums->lang['disable']}</a>
EOF;
}

function tmpl_disable_link_rw($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$entry['url']}/disable.html?version={$entry['version']}'>{$ibforums->lang['disable']}</a>
EOF;
}

function tmpl_edit_link($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$ibforums->vars['dynamiclite']}act=upload&cat={$entry['refs']}&id={$entry['id']}&version={$entry['version']}&ACTION=2'>{$ibforums->lang['cskin_go']}</a>
EOF;
}

function tmpl_edit_link_rw($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$entry['url']}/edit.html?version={$entry['version']}'>{$ibforums->lang['cskin_go']}</a>
EOF;
}

function tmpl_delete_link($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$ibforums->vars['dynamiclite']}act=upload&cat={$entry['refs']}&id={$entry['id']}&version={$entry['version']}&ACTION=4'><strong>{$ibforums->lang['del_article']}</strong></a>
EOF;
}

function tmpl_delete_link_rw($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$entry['url']}/delete_screen.html?version={$entry['version']}'><strong>{$ibforums->lang['del_article']}</strong></a>
EOF;
}

function tmpl_move_link($entry) {
global $ibforums;
return <<<EOF
<a href='{$ibforums->vars['dynamiclite']}act=upload&cat={$entry['refs']}&id={$entry['id']}&ACTION=8'>{$ibforums->lang['move_article']}</a>
EOF;
}

function tmpl_move_link_rw($entry) {
global $ibforums;
return <<<EOF
<a href='{$entry['url']}/move.html'>{$ibforums->lang['move_article']}</a>
EOF;
}

function tmpl_subscribe_link($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$entry['url']}/subscribe.html'><b>{$ibforums->lang['subscribe']}</b></a>
EOF;
}

function tmpl_favorites_link($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$entry['url']}/add_to_favorite.html'><b>{$ibforums->lang['favorites']}</b></a>
EOF;
}

function tmpl_article_icon_img($path) {
global $ibforums;
return <<<EOF
<img src='{$path}' border='0' alt='' width='80' height='80'>
EOF;
}

function tmpl_rep_member_link($entry) {
global $ibforums;
return <<<EOF
&nbsp;&middot;&nbsp;<a href='{$ibforums->vars['dynamiclite']}REP=1&cat_id={$entry['refs']}&art_id={$entry['id']}&ver_id={$entry['version']}&mem_id={$entry['user_id']}&CODE=1'>{$ibforums->lang['rep_member']}</a>&nbsp;&middot;&nbsp;
EOF;
}

function tmpl_rep_article_link($entry) {
global $ibforums;
return <<<EOF
<a href='{$ibforums->vars['dynamiclite']}REP=1&cat_id={$entry['refs']}&art_id={$entry['id']}&ver_id={$entry['version']}&mem_id={$entry['refs']}&CODE=2'>{$ibforums->lang['rep_article']}</a>
EOF;
}

function tmpl_sub_cat_row($url) {
global $ibforums;
return <<<EOF
<tr>
<td class='desc' style='padding:5px'>
{$url}
</td>
</tr>
EOF;
}

function tmpl_sub_cat_row_rw($url) {
global $ibforums;
return <<<EOF
<tr>
<td class='desc' style='padding:5px'>
{$url}
</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
//  article upload form templates - standard BB-style upload template
//------------------------------------------------------------------------------

function tmpl_show_upload_form_bb( $entry = array() ) {
global $ibforums;
return <<<EOF
<script language="Javascript1.2"><!-- //

function InsertAsImage(imgUrl) {

        doInsert("[IMG="+imgUrl+"]", "", false);
}


// -->
</script>

{$entry['ibf_post_javascript']}

<div class="tableborder">
<div class=row2>
<form name=REPLIER id="REPLIER" method="post" action='{$entry['url']}' enctype="multipart/form-data">
<input name="cat" value="{$entry['cat']}" type="hidden" id="Hidden1">
<input name="id" value="{$entry['id']}" type="hidden" id="Hidden2">
<input name="version" value="{$entry['version']}" type="hidden">
<input name="ACTION" value="{$entry['ACTION']}" type="hidden" id="Hidden3">
<table cellspacing="0" cellpadding="4" width="100%" border="0" id="Table1">
<tr valign="top">
<td align="left" class="maintitle" colspan='2'>
{$entry['title']}
</td>
</tr>

<tr valign="top">
<td align="left" class="row2" valign="middle">
<span lang="ru">Заголовок</span>:
</td>
<td align="left" valign="middle">
<input name=name class='forminput' value='{$entry['name']}' size="75">
</td>
</tr>
{$entry['article_id_form']}
{$entry['force_article_version']}
{$entry['default_article_version']}
<tr valign="top">
<td align="left" class="row2" valign="top">Описание:</td>
<td align="left" valign="middle">
<textarea name="short_desc" cols="75" rows="5" class='forminput' id="Text1">{$entry['short_desc']}</textarea>
</td>
</tr>
<tr valign="top">
<td align="left" class="row2">&nbsp;
</td>
</tr>
<tr valign="top">
<td align="left" class="row2" colspan=2>
{$entry['ibf_post_body']}
</td>
</tr>

<td align="left" valign="middle" colspan=2 class='row3'>
Иконка к статье:
</td>
</tr>
<td align="center" valign="middle" colspan=2>

<table width=100% align=center>
 {$entry['icon']}
</table>

</td>
</tr>

<tr valign="top">
<td align="left" valign="middle" colspan=2 class='row3'>
Прикрепить файлы: (max {$ibforums->vars['csite_max_upload_files']} файлов)
</td>
</tr>
<td align="center" valign="middle" colspan=2>

<table width=100% align=center>
 {$entry['files']}
</table>

</td>
</tr>

<tr valign="top">
<td align="center" valign="middle" colspan=2>

                                <table width=100% align=center>

                                        {$entry['files_edit']}
                                        {$entry['icon_edit']}
                                </table>
                                <input type="submit" value="Сохранить" id="Submit1" name=subnit> &nbsp; &nbsp; &nbsp; &nbsp;
                                <input type="reset" id="Reset1" value="Сброс">
                </tr>
        </table>
</form>
</div>
</div>

EOF;
}
//------------------------------------------------------------------------------
//  article upload form templates - standard BB-style upload template
//------------------------------------------------------------------------------

function tmpl_show_upload_form_wisiwig( $entry = array() ) {
global $ibforums;
return <<<EOF
<div class="tableborder">
<div class=row2>
<form name=REPLIER id="REPLIER" method="post" action='{$ibforums->vars['dynamiclite']}act=upload' enctype="multipart/form-data">
<input name="cat" value="{$entry['cat']}" type="hidden" id="Hidden1">
<input name="id" value="{$entry['id']}" type="hidden" id="Hidden2">
<input name="version" value="{$entry['version']}" type="hidden">
<input name="ACTION" value="{$entry['ACTION']}" type="hidden" id="Hidden3">
<table cellspacing="0" cellpadding="4" width="100%" border="0" id="Table1">
<tr valign="top">
<td align="left" class="maintitle" colspan='2'>
{$entry['title']}
</td>
</tr>

<tr valign="top">
<td align="left" class="row2" valign="middle">
<span lang="ru">Заголовок</span>:
</td>
<td align="left" valign="middle">
<input name=name class='forminput' value='{$entry['name']}' size="75">
</td>
</tr>
{$entry['article_id_form']}
{$entry['force_article_version']}
{$entry['default_article_version']}
<tr valign="top">
<td align="left" class="row2" valign="top">Описание:</td>
<td align="left" valign="middle">
<textarea name="short_desc" cols="75" rows="5" class='forminput' id="Text1">{$entry['short_desc']}</textarea>
</td>
</tr>
<tr valign="top">
<td align="left" class="row2">&nbsp;
</td>
</tr>
<tr valign="top">
<td align="left" class="row2" colspan=2>
</td>
</tr>
<script language="Javascript1.2"><!-- // load htmlarea
_editor_url = "{$ibforums->vars['html_url']}/htmlarea/";                     // URL to htmlarea files
var win_ie_ver = parseFloat(navigator.appVersion.split("MSIE")[1]);
if (navigator.userAgent.indexOf('Mac')        >= 0) { win_ie_ver = 0; }
if (navigator.userAgent.indexOf('Windows CE') >= 0) { win_ie_ver = 0; }
if (navigator.userAgent.indexOf('Opera')      >= 0) { win_ie_ver = 0; }
if (win_ie_ver >= 5.5) {
  document.write('<scr' + 'ipt src="' +_editor_url+ 'editor.js"');
  document.write(' language="Javascript1.2"></scr' + 'ipt>');
} else { document.write('<scr'+'ipt>function editor_generate() { return false; }</scr'+'ipt>'); }

function InsertAsImage(imgUrl) {

        editor_action("_Post_InsertImage", imgUrl);
}

function InsertAsHyperlink(txtUrl) {

        editor_action("_Post_CreateLink", txtUrl);
}
// -->

</script>
  <TR>
   <TD class="pformstrip" colSpan="2">Введите текст:</TD>
  </TR>
   <TD class="pformright" vAlign="top" colspan="2">
    <textarea cols='100' rows='40' name='Post' tabindex='3' style='width:99%' class='textinput' ID="Textarea2">{$entry['article']}{$entry['Post']}</textarea></td>
    <script language="javascript1.2">editor_generate('Post');</script>
   </TD>
  </TR>


<td align="left" valign="middle" colspan=2 class='row3'>
Иконка к статье:
</td>
</tr>
<td align="center" valign="middle" colspan=2>

<table width=100% align=center>
 {$entry['icon']}
</table>

</td>
</tr>

<tr valign="top">
<td align="left" valign="middle" colspan=2 class='row3'>
Прикрепить файлы: (max {$ibforums->vars['csite_max_upload_files']} файлов)
</td>
</tr>
<td align="center" valign="middle" colspan=2>

<table width=100% align=center>
 {$entry['files']}
</table>

</td>
</tr>

<tr valign="top">
<td align="center" valign="middle" colspan=2>

                                <table width=100% align=center>

                                        {$entry['files_edit']}
                                        {$entry['icon_edit']}
                                </table>
                                <input type="submit" value="Сохранить" id="Submit1" name=subnit> &nbsp; &nbsp; &nbsp; &nbsp;
                                <input type="reset" id="Reset1" value="Сброс">
                </tr>
        </table>
</form>
</div>
</div>
EOF;
}

//------------------------------------------------------------------------------
//  article upload form templates - upload file form/uploaded file list
//------------------------------------------------------------------------------

function tmpl_upload_file($number) {
global $ibforums;
return <<<EOF
<tr valign="top">
<td align="left" class="row3" valign="center">
Добавить файл:
</td>
<td align="left" colspan=2>
<input class="forminput" name="thefile[]" type="file" size="50" id="File1">
<input class="forminput" name="attach_file" type="submit" size="50" value="Прикрепить">
</td>
</tr>
<tr class="row2">
</tr>
EOF;
}

function tmpl_uploaded_file($entry) {
global $ibforums;
return <<<EOF
<tr valign="top">
<td align="left" class="row2">
Файл #{$entry['num']}:
</td>
<td align="left">
<a href='{$entry['url']}' target='blank'>{$entry['name']}</a>&nbsp;&middot;&nbsp;<a href='javascript:InsertAsImage("{$entry['url']}");'>Вставить как картинку</a>&nbsp;&middot;&nbsp;<a href='javascript:InsertAsHyperlink("{$entry['url']}");'>Вставить как ссылку</a>&nbsp;&middot;&nbsp;{$entry['del']}
<br><br>
<td align="left">
</td>
</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
//  delete article confirmation template
//------------------------------------------------------------------------------

function tmpl_delete_form($entry) {
global $ibforums;
return <<<EOF
<div class="tableborder">
 <div class=row2>
  <form name="delete_form" method="post" action='{$entry['url']}/delete.html'>
   <input name="cat" value="{$entry['cat']}" type="hidden">
   <input name="id" value="{$entry['id']}" type="hidden">
   <input name="version" value="{$entry['version']}" type="hidden">
   <input name="ACTION" value="{$entry['ACTION']}" type="hidden">
   <table cellspacing="0" cellpadding="4" width="100%" border="0">
    <tr valign="top">
     <td align="left" class="maintitle" colspan='2'>
      {$entry['title']}
     </td>
    </tr>
    <tr valign="top" class="row2">
     <td align="left" colspan='2'>
      Вы действительно хотите удалить эту статью? Воссстановить удаленную статью будет невозможно.<br />Для подтверждения удаления нажмите кнопку "Удалить". Больше никаких вопросов не будет.
     </td>
    </tr>
    <tr valign="top" class="row1">
     <td align="left" colspan='2'>
      <input name="delete_version" type="radio" value="1" checked> Удалить текущую версию статьи
      <br>
      <input name="delete_version" type="radio" value=""> Удалить все версии статьи
     </td>
    </tr>
    <tr valign="top" class="row3">
     <td align="center" colspan='2'>
      <input type="submit" value="Удалить" class="forminput">
     </td>
    </tr>
   </table>
  </form>
 </div>
</div>
EOF;
}

//------------------------------------------------------------------------------
//  move article template
//------------------------------------------------------------------------------

function tmpl_move_form($entry) {
global $ibforums;
return <<<EOF
<div class="tableborder">
 <div class=row2>
  <form name="delete_form" method="post" action='{$entry['url']}'>
   <input name="cat" value="{$ibforums->input['cat']}" type="hidden">
   <input name="id" value="{$ibforums->input['id']}" type="hidden">
   <input name="ACTION" value="{$entry['ACTION']}" type="hidden">
   <table cellspacing="0" cellpadding="4" width="100%" border="0">
    <tr valign="top">
     <td align="left" class="maintitle" colspan='2'>
      {$entry['title']}
     </td>
    </tr>
    <tr valign="top" class="row2">
     <td align="left" colspan='2'>
      Выберите раздел сайта для перемещения статьи
     </td>
    </tr>
    <tr valign="top" class="row1">
     <td align="left" colspan='2'>
      <select size="1" name="move_cat_id">
       {$entry['cat_list']}
      </select>
     </td>
    </tr>
    <tr valign="top" class="row3">
     <td align="center" colspan='2'>
      <input type="submit" value="Переместить" class="forminput">
     </td>
    </tr>
   </table>
  </form>
 </div>
</div>
EOF;
}


//------------------------------------------------------------------------------
//  article upload form templates - upload icon form/uploaded icon template
//------------------------------------------------------------------------------

function tmpl_show_upload_icon() {
global $ibforums;
return <<<EOF
<tr valign="top">
<td align="left" class="row3" valign="center">
Выберите иконку:
</td>
<td align="left" colspan=2>
<input class='forminput' name="icon" type="file" size="50" id="File1">
<input class="forminput" name="attach_file" type="submit" size="50" value="Прикрепить">
</td>
</tr>
<tr class="row2">
</tr>
EOF;
}

function tmpl_uploaded_icon($entry) {
global $ibforums;
return <<<EOF
<tr valign="top">
<td align="left" class="row2">
Прикрепленная иконка:
</td>
<td align="left">
<a href='{$entry['url']}' target='blank'><img src='{$entry['url']}' alt='{$entry['name']}' border='0' width='80' height='80'></a>
<br><br>
<td align="left">
{$entry['del']}
</td>
</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
//  article upload form templates - add [IMG] tag of uploaded file
//------------------------------------------------------------------------------

function tmpl_add_image_lnk_bb($params) {
global $ibforums;
return <<<EOF
<a href="javascript:editor_insertHTML('Post', '[IMG=$params]','', 0);">Вставить как картинку</a>
EOF;
}

//------------------------------------------------------------------------------
//  article upload form templates - delete attached file
//------------------------------------------------------------------------------

function tmpl_del_file_lnk($fid, $cat, $art, $is_icon=0) {
global $ibforums;
return <<<EOF
<a href='{$ibforums->vars['dynamiclite']}act=upload&fid={$fid}&cat={$cat}&id={$art}&is_icon={$is_icon}&ACTION=10'>Удалить файл</a>
EOF;
}


//------------------------------------------------------------------------------
//  article upload form templates - article ID input form
//------------------------------------------------------------------------------

function tmpl_show_art_id_form( $entry = "" ) {
global $ibforums;
return <<<EOF
<tr valign="top">
<td align="left" class="row2" valign="middle">
<span lang="ru">ID статьи (только английские буквы):</span>
</td>
<td align="left" valign="middle">
<input name="article_id" value="{$entry}" class="forminput" size="75">
</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
//  article upload form templates - force new versioning
//------------------------------------------------------------------------------

function tmpl_force_article_version() {
global $ibforums;
return <<<EOF
<tr valign="top">
<td align="left" class="row2" valign="middle">
<span lang="ru">Контроль версий</span>
</td>
<td align="left" valign="middle">
<input name="force_new_version" type="checkbox" value="1"> Создать новую версию статьи после редактирования?
</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
//  article upload form templates - set version as default
//------------------------------------------------------------------------------

function tmpl_default_article_version() {
global $ibforums;
return <<<EOF
<tr valign="top">
<td align="left" class="row2" valign="middle">
<span lang="ru"></span>
</td>
<td align="left" valign="middle">
<input name="set_as_default_version" type="checkbox" value="1"> Пометить текущую версию, как версию по-умолчанию?
</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
//  dedicated article template - the main template
//------------------------------------------------------------------------------

function tmpl_article($entry) {
global $ibforums;
return <<<EOF
<table cellspacing="0" width="100%" class='tableborder'>
<tr>
 <td width="100%" class='maintitle'><img src="{$ibforums->vars['img_url']}/cs_page.gif" alt="" border="0">&nbsp;{$entry['name']}</td>
</tr>
<tr>
 <td width="100%" class='row2'><div align='right'>{$entry['rep_article_link']}{$entry['rep_member_link']}{$entry['move_link']}{$entry['approve']}{$entry['edit_link']}{$entry['del_link']}{$entry['favorites_link']}{$entry['subscribe_link']}</div></td>
</tr>
<tr>
 <td class='post1' style='padding:5px'>{$entry['article']}</td>
</tr>
{$entry['article_pages']}
{$entry['article_versions']}
{$entry['files']}
{$entry['bottom']}
</table>
<br />
{$entry['comments']}
EOF;
}

//------------------------------------------------------------------------------
// article pages row
//------------------------------------------------------------------------------

function tmpl_article_pages($entry) {
global $ibforums;
return <<<EOF
<tr>
 <td class='desc' style='padding:5px'>{$ibforums->lang['article_pages']}</td>
</tr>
<tr>
  <td class='row2' style='padding:5px'>{$entry}</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
// article pages links
//------------------------------------------------------------------------------

function tmpl_article_page_link($cat_id, $art_id, $ver_id, $page_num) {
global $ibforums;
return <<<EOF
<a href="{$ibforums->vars['dynamiclite']}cat={$cat_id}&id={$art_id}&version={$ver_id}&p={$page_num}">[{$page_num}]</a>&nbsp;
EOF;
}

function tmpl_article_page_link_rw($url, $page_num, $version = "") {
global $ibforums;
return <<<EOF
<a href="{$url}/p{$page_num}.html{$version}">[{$page_num}]</a>&nbsp;
EOF;
}

//------------------------------------------------------------------------------
// article show all pages link
//------------------------------------------------------------------------------

function tmpl_article_pages_all($cat_id, $art_id, $ver_id) {
global $ibforums;
return <<<EOF
<a href="{$ibforums->vars['dynamiclite']}cat={$cat_id}&id={$art_id}&version={$ver_id}&p=all">{$ibforums->lang['all']}</a>
EOF;
}

function tmpl_article_pages_all_rw($url, $page_num, $version = "") {
global $ibforums;
return <<<EOF
<a href="{$url}/p_all.html{$version}">{$ibforums->lang['all']}</a>
EOF;
}

//------------------------------------------------------------------------------
// article pages - show current page
//------------------------------------------------------------------------------

function tmpl_article_page_current($page_num) {
global $ibforums;
return <<<EOF
<b>[{$page_num}]</b>&nbsp;
EOF;
}

//------------------------------------------------------------------------------
// article versions row
//------------------------------------------------------------------------------

function tmpl_article_versions($entry) {
global $ibforums;
return <<<EOF
<tr>
 <td class='desc' style='padding:5px'>{$ibforums->lang['article_versions']}</td>
</tr>
<tr>
  <td class='row2' style='padding:5px'>{$entry}</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
// article versions links
//------------------------------------------------------------------------------

function tmpl_article_versions_link($art_id, $cat_id, $ver) {
global $ibforums;
return <<<EOF
<a href="{$ibforums->vars['dynamiclite']}cat={$cat_id}&id={$art_id}&version={$ver}">[#{$ver}]</a>&nbsp;
EOF;
}

function tmpl_article_versions_link_rw($url, $ver) {
global $ibforums;
return <<<EOF
<a href="{$url}/index.html?version={$ver}">[#{$ver}]</a>&nbsp;
EOF;
}

//------------------------------------------------------------------------------
// article versions - show current version
//------------------------------------------------------------------------------

function tmpl_article_version_current($ver) {
global $ibforums;
return <<<EOF
<b>[#{$ver}]</b>&nbsp;
EOF;
}

//------------------------------------------------------------------------------
//  dedicated article template - uploaded files list
//------------------------------------------------------------------------------

function tmpl_article_files($files_list) {
global $ibforums;
return <<<EOF
<tr>
 <td class='desc' style='padding:5px'>{$ibforums->lang['files']}</td>
</tr>
<tr>
  <td class='row2' style='padding:5px'>{$files_list}</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
//  dedicated article template - bottom template
//------------------------------------------------------------------------------

function tmpl_article_bottom($entry) {
global $ibforums;
return <<<EOF
<tr>
 <td class='desc' style='padding:5px'>{$entry}</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
//  article comments templates
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
//  article comments main template
//------------------------------------------------------------------------------

function tmpl_comments($entry) {
global $ibforums;
return <<<EOF
<a name="comments">
<table cellspacing="0" width="100%" class='tableborder'>
<tr>
 <td width="100%" class='maintitle'><img src="{$ibforums->vars['img_url']}/cs_page.gif" alt="" border="0">&nbsp;{$ibforums->lang['comments']}</td>
</tr>
  {$entry}
</table>
EOF;
}

//------------------------------------------------------------------------------
// quick comments - only count and link to add new comment
//------------------------------------------------------------------------------

function tmpl_quick_comments($entry) {
global $ibforums;
return <<<EOF
<table cellspacing="0" width="100%" class='tableborder'>
<tr>
 <td width="100%" class='maintitle'><img src="{$ibforums->vars['img_url']}/cs_page.gif" alt="" border="0">&nbsp;{$ibforums->lang['comments']}</td>
</tr>
<tr class='row1'>
 <td>Коментариев: {$entry[comments_count]}  </td>
</tr>
<tr class='row1'>
 <td>&middot; <a href="{$ibforums->vars['dynamiclite']}cat={$ibforums->input['cat']}&id={$ibforums->input['id']}&p={$ibforums->input['p']}&version={$ibforums->input['version']}&comments=1#comments">{$ibforums->lang['show_comments']}</a>  </td>
</tr>
<tr class='row1'>
 <td>&middot; <a href="{$ibforums->vars['dynamiclite']}cat={$ibforums->input['cat']}&id={$ibforums->input['id']}&p={$ibforums->input['p']}&version={$ibforums->input['version']}&comments=1#post_comment">{$ibforums->lang['show_edit_comments']}</a>  </td>
</tr>
</table>
EOF;
}

function tmpl_quick_comments_rw($entry, $url, $version = "") {
global $ibforums;
return <<<EOF
<table cellspacing="0" width="100%" class='tableborder'>
<tr>
 <td width="100%" class='maintitle'><img src="{$ibforums->vars['img_url']}/cs_page.gif" alt="" border="0">&nbsp;{$ibforums->lang['comments']}</td>
</tr>
<tr class='row1'>
 <td>Коментариев: {$entry[comments_count]}  </td>
</tr>
<tr class='row1'>
 <td>&middot; <a href="{$url}?comments=1{$version}#comments">{$ibforums->lang['show_comments']}</a>  </td>
</tr>
<tr class='row1'>
 <td>&middot; <a href="{$url}?comments=1{$version}#post_comment">{$ibforums->lang['show_edit_comments']}</a>  </td>
</tr>
</table>
EOF;
}

//------------------------------------------------------------------------------
//  article comments rows
//------------------------------------------------------------------------------

function tmpl_comments_row($entry) {
global $ibforums;
return <<<EOF
<tr class='row2'>
 <td>{$entry['title']}</td>
</tr>
<tr>
 <td class='post1' style='padding:5px'>{$entry['comment']}</td>
</tr>
<tr class='row3'>
 <td align="right">{$entry['edit_link']}&nbsp;{$entry['dele_link']}</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
//  article comments upload form
//------------------------------------------------------------------------------

function tmpl_comment_upload_form($entry) {
global $ibforums;
return <<<EOF
<br />
<div class="tableborder">
<div class=row2>
<a name="post_comment">
<form name=REPLIER id="REPLIER" method="post" action='{$entry['url']}'>
<input name="cat" value="{$entry['cat']}" type="hidden">
<input name="id" value="{$entry['id']}" type="hidden">
<input name="cid" value="{$entry['cid']}" type="hidden">
<input name="version" value="{$ibforums->input['version']}" type="hidden">
<input name="comments" value="1" type="hidden">
<input name="p" value="{$ibforums->input['p']}" type="hidden">
<input name="ACTION" value="{$entry['ACTION']}" type="hidden">
<table cellspacing="0" cellpadding="4" width="100%" border="0">
<tr valign="top">
<td align="left" class="maintitle" colspan='2'>
{$entry['title']}
</td>
</tr>
<tr valign="top">
<td align="left" class="row2" colspan=2>

<SCRIPT language="javascript1.2" type="text/javascript">
<!--
var MessageMax  = "";
var Override    = "";
MessageMax      = parseInt(MessageMax);
if ( MessageMax < 0 )
{
        MessageMax = 0;
}

function hstat(param)
{
}
function keyb_pop()
{

window.open('index.php?act=legends&CODE=keyb&s=','Legends','width=700,height=160,resizable=yes,scrollbars=yes');
}
function emo_pop()
{
  window.open('index.php?act=legends&CODE=emoticons&s=','Legends','width=250,height=500,resizable=yes,scrollbars=yes');
}
function bbc_pop()
{
  window.open('code_help.html','Legends','width=700,height=500,resizable=yes,scrollbars=yes');
}
function CheckLength() {
        MessageLength  = document.REPLIER.Post.value.length;
        message  = "";
                if (MessageMax > 0) {
                        message = "Сообщение: Максимальная длина " + MessageMax + " символов.";
                } else {
                        message = "";
                }
                alert(message + "      Вами использовано " + MessageLength + " символов.");
}

        function ValidateForm(isMsg) {
                MessageLength  = document.REPLIER.Post.value.length;
                errors = "";

                if (isMsg == 1)
                {
                        if (document.REPLIER.msg_title.value.length < 2)
                        {
                                errors = "Необходимо ввести заголовок сообщения";
                        }
                }

                if (MessageLength < 2) {
                         errors = "Вы должны ввести текст сообщения!";
                }
                if (MessageMax !=0) {
                        if (MessageLength > MessageMax) {
                                errors = "Максимальная длина " + MessageMax + " символов. Текущие символы: " + MessageLength;
                        }
                }
                if (errors != "" && Override == "") {
                        alert(errors);
                        return false;
                } else {
                        document.REPLIER.submit.disabled = true;
                        return true;
                }
        }

        // IBC Code stuff
        var text_enter_url      = "Введите полный адрес ссылки";
        var text_enter_url_name = "Введите название сайта";
        var text_enter_image    = "Введите полный адрес картинки";
        var text_enter_email    = "Введите электронный адрес";
        var text_enter_flash    = "Введите полный адрес для Flash.";
        var text_code           = "Использование: [CODE] текст исходника [/CODE]";
        var text_quote          = "Использование: [QUOTE] текст цитаты [/QUOTE]";
        var error_no_url        = "Вы должны ввести адрес";
        var error_no_title      = "Вы должны ввести название";
        var error_no_email      = "Вы должны ввести е-мэйл";
        var error_no_width      = "Вы должны ввести ширину";
        var error_no_height     = "Вы должны ввести высоту";
        var prompt_start        = "Введите текст для форматирования";

        var help_bold           = "Жирный текст (alt + b)";
        var help_italic         = "Наклонный текст (alt + i)";
        var help_under          = "Подчёркнутый текст (alt + u)";
        var help_font           = "Выбор шрифта";
        var help_size           = "Выбор размера текста";
        var help_color          = "Выбор цвета текста";
        var help_close          = "Закрытие всех открытых тэгов";
        var help_url            = "Ввод ссылки (alt+ h)";
        var help_img            = "Картинка (alt + g) [img]http://www.dom.com/img.gif[/img]";
        var help_email          = "Е-мэйл (alt + e)";
        var help_quote          = "Ввод цитаты (alt + q)";
        var help_list           = "Создать список (alt + l)";
        var help_code           = "Ввод кода (alt + p)";
        var help_click_close    = "Нажмите на кнопку для закрытия";
        var list_prompt         = "Введите пункт списка. Для завершения списка нажмите 'отмена' или оставьте очередное поле пустым";
        var help_transit         = "";
        //-->
</SCRIPT>
<SCRIPT language="JavaScript" type="text/javascript">
var rusBig = new Array( "Э", "Ч", "Ш", "Ё", "Ё", "Ж", "Ю", "Ю", "Я", "Я", "А", "Б", "В", "Г", "Д", "Е", "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Щ", "Ъ", "Ы", "Ь");
var rusSmall = new Array("э", "ч", "ш", "ё", "ё","ж", "ю", "ю", "я", "я", "а", "б", "в", "г", "д", "е", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "щ", "ъ", "ы", "ь" );
var engBig = new Array("E'", "CH", "SH", "YO", "JO", "ZH", "YU", "JU", "YA", "JA", "A","B","V","G","D","E", "Z","I","J","K","L","M","N","O","P","R","S","T","U","F","H","C", "W","~","Y");
var engSmall = new Array("e'", "ch", "sh", "yo", "jo", "zh", "yu", "ju", "ya", "ja", "a", "b", "v", "g", "d", "e", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s",  "t", "u", "f", "h", "c", "w", "~", "y");
var rusRegBig = new Array( /Э/g, /Ч/g, /Ш/g, /Ё/g, /Ё/g, /Ж/g, /Ю/g, /Ю/g, /Я/g, /Я/g, /А/g, /Б/g, /В/g, /Г/g, /Д/g, /Е/g, /З/g, /И/g, /Й/g, /К/g, /Л/g, /М/g, /Н/g, /О/g, /П/g, /Р/g, /С/g, /Т/g, /У/g, /Ф/g, /Х/g, /Ц/g, /Щ/g, /Ъ/g, /Ы/g, /Ь/g);
var rusRegSmall = new Array( /э/g, /ч/g, /ш/g, /ё/g, /ё/g, /ж/g, /ю/g, /ю/g, /я/g, /я/g, /а/g, /б/g, /в/g, /г/g, /д/g, /е/g, /з/g, /и/g, /й/g, /к/g, /л/g, /м/g, /н/g, /о/g, /п/g, /р/g, /с/g, /т/g, /у/g, /ф/g, /х/g, /ц/g, /щ/g, /ъ/g, /ы/g, /ь/g);
var engRegBig = new Array( /E'/g, /CH/g, /SH/g, /YO/g, /JO/g, /ZH/g, /YU/g, /JU/g, /YA/g, /JA/g, /A/g, /B/g, /V/g, /G/g, /D/g, /E/g, /Z/g, /I/g, /J/g, /K/g, /L/g, /M/g, /N/g, /O/g, /P/g, /R/g, /S/g, /T/g, /U/g, /F/g, /H/g, /C/g, /W/g, /~/g, /Y/g, /'/g);
var engRegSmall = new Array(/e'/g, /ch/g, /sh/g, /yo/g, /jo/g, /zh/g, /yu/g, /ju/g, /ya/g, /ja/g, /a/g, /b/g, /v/g, /g/g, /d/g, /e/g, /z/g, /i/g, /j/g, /k/g, /l/g, /m/g, /n/g, /o/g, /p/g, /r/g, /s/g, /t/g, /u/g, /f/g, /h/g, /c/g, /w/g, /~/g, /y/g, /'/g);
function rusLang() {
var textar = document.REPLIER.Post.value;
if (textar) {
for (i=0; i<engRegSmall.length; i++) {
textar = textar.replace(engRegSmall[i], rusSmall[i])
    }
for (var i=0; i<engRegBig.length; i++) {
textar = textar.replace(engRegBig[i], rusBig[i])
    }
document.REPLIER.Post.value = textar;
 }
}
        function PopUp(url, name, width,height,center,resize,scroll,posleft,postop) {
                if (posleft != 0) { x = posleft }
                if (postop  != 0) { y = postop  }

                if (!scroll) { scroll = 1 }
                if (!resize) { resize = 1 }

                if ((parseInt (navigator.appVersion) >= 4 ) && (center)) {
                  X = (screen.width  - width ) / 2;
                  Y = (screen.height - height) / 2;
                }
                if (scroll != 0) { scroll = 1 }

                var Win = window.open( url, name, 'width='+width+',height='+height+',top='+Y+',left='+X+',resizable='+resize+',scrollbars='+scroll+',location=no,directories=no,status=no,menubar=no,toolbar=no');
         }
        //-->
</SCRIPT>
<TABLE class="tableborder" cellSpacing="0" cellPadding="0" width="100%" ID="Table1">
        <TBODY>
                <TR>
                        <TD class="pformstrip" colSpan="2">Кнопки кодов</TD>
                </TR>
                <TR>
                        <TD class="pformleft" width="10%" height="86"><INPUT class="radiobutton" onclick="setmode(this.value)" type="radio" value="ezmode" name="bbmode"
                                        ID="Radio1"> <B>Расширенный режим</B><BR>
                                <INPUT class="radiobutton" onclick="setmode(this.value)" type="radio" CHECKED value="normal"
                                        name="bbmode" ID="Radio2"> <B>Нормальный режим</B>
                                <SCRIPT src="{$ibforums->vars['html_url']}/ibfcode.js" type="text/javascript"></SCRIPT>
                        </TD>
                        <TD class="pformright" height="86">
                        <INPUT class="codebuttons" onmouseover="hstat('bold')" style="FONT-WEIGHT: bold" accessKey="b" onclick='simpletag("B")' type="button" value=" B " name="B" ID="Button1">
                        <INPUT class="codebuttons" onmouseover="hstat('italic')" style="FONT-STYLE: italic" accessKey="i" onclick='simpletag("I")' type="button" value=" I " name="I" ID="Button2">
                        <INPUT class="codebuttons" onmouseover="hstat('under')" style="TEXT-DECORATION: underline" accessKey="u" onclick='simpletag("U")' type="button" value=" U " name="U" ID="Button3">

                                <SELECT class="codebuttons" onmouseover="hstat('font')" onchange="alterfont(this.options[this.selectedIndex].value, 'FONT')"
                                        name="ffont" ID="Select1">
                                        <OPTION value="0" selected>ШРИФТ</OPTION>
                                        <OPTION style="FONT-FAMILY: Arial" value="Arial">Arial</OPTION>
                                        <OPTION style="FONT-FAMILY: Times" value="Times">Times</OPTION>
                                        <OPTION style="FONT-FAMILY: Courier" value="Courier">Courier</OPTION>
                                        <OPTION style="FONT-FAMILY: Impact" value="Impact">Impact</OPTION>
                                        <OPTION style="FONT-FAMILY: Geneva" value="Geneva">Geneva</OPTION>
                                        <OPTION style="FONT-FAMILY: Optima" value="Optima">Optima</OPTION>
                                </SELECT><SELECT class="codebuttons" onmouseover="hstat('size')" onchange="alterfont(this.options[this.selectedIndex].value, 'SIZE')"
                                        name="fsize" ID="Select2">
                                        <OPTION value="0" selected>РАЗМЕР</OPTION>
                                        <OPTION value="1">Малый</OPTION>
                                        <OPTION value="7">Большой</OPTION>
                                        <OPTION value="14">Огромный</OPTION>
                                </SELECT><SELECT class="codebuttons" onmouseover="hstat('color')" onchange="alterfont(this.options[this.selectedIndex].value, 'COLOR')"
                                        name="fcolor" ID="Select3">
                                        <OPTION value="0" selected>ЦВЕТ</OPTION>
                                        <OPTION style="COLOR: blue" value="blue">Синий</OPTION>
                                        <OPTION style="COLOR: red" value="red">Красный</OPTION>
                                        <OPTION style="COLOR: purple" value="purple">Фиолетовый</OPTION>
                                        <OPTION style="COLOR: orange" value="orange">Оранжевый</OPTION>
                                        <OPTION style="COLOR: yellow" value="yellow">Жёлтый</OPTION>
                                        <OPTION style="COLOR: gray" value="gray">Серый</OPTION>
                                        <OPTION style="COLOR: green" value="green">Зелёный</OPTION>
                                </SELECT>
                                <A onmouseover="hstat('close')" href="javascript:closeall();">Закрыть все тэги</A>
                                <BR>
                                <INPUT class="codebuttons" onmouseover="hstat('url')" accessKey="h" onclick="tag_url()"
                                        type="button" value=" http:// " name="url" ID="Button4"> <INPUT class="codebuttons" onmouseover="hstat('img')" accessKey="g" onclick="tag_image()"
                                        type="button" value=" IMG " name="img" ID="Button5"> <INPUT class="codebuttons" onmouseover="hstat('email')" accessKey="e" onclick="tag_email()"
                                        type="button" value="  @  " name="email" ID="Button6"> <INPUT class="codebuttons" onmouseover="hstat('quote')" accessKey="q" onclick='simpletag("QUOTE")'
                                        type="hidden" value=" QUOTE " name="QUOTE" ID="Button7"> <INPUT class="codebuttons" onmouseover="hstat('code')" accessKey="p" onclick='simpletag("CODE")'
                                        type="hidden" value=" CODE " name="CODE" ID="Button8"> <INPUT class="codebuttons" onmouseover="hstat('list')" accessKey="l" onclick="tag_list()"
                                        type="button" value=" LIST " name="LIST" ID="Button9"> <INPUT class="codebuttons" onmouseover="hstat('transit')" accessKey="y" onclick="rusLang()"
                                        type="hidden" value=" TRANSLIT " name="TRANSLIT" ID="Button10"><BR>
                                <br>
                                Открытых тэгов: <INPUT class="row1" style="BORDER-TOP-WIDTH: 0px; FONT-WEIGHT: bold; BORDER-LEFT-WIDTH: 0px; FONT-SIZE: 10px; BORDER-BOTTOM-WIDTH: 0px; FONT-FAMILY: verdana,arial; BORDER-RIGHT-WIDTH: 0px"
                                        readOnly maxLength="3" size="3" value="0" name="tagcount" ID="Text1"> <INPUT class="row1" style="BORDER-TOP-WIDTH: 0px; BORDER-LEFT-WIDTH: 0px; FONT-SIZE: 10px; BORDER-BOTTOM-WIDTH: 0px; WIDTH: auto; FONT-FAMILY: verdana,arial; BORDER-RIGHT-WIDTH: 0px"
                                        readOnly maxLength="120" size="50" value="Картинка (alt + g) [img]http://www.dom.com/img.gif[/img]" name="helpbox" ID="Text2">
                        </TD>
                </TR>
<TR>
<TD class="pformstrip" colSpan="2">Введите текст:</TD>
</TR>
<TD class="pformright" vAlign="top" colspan="2">
<textarea cols='20' rows='10' name='Post' tabindex='3' style='width:99%' class='textinput' ID="Textarea2">{$entry['comment']}</textarea></td>
</TD>
</TR>
</TBODY>
</TABLE>
</td>
</tr>
<tr valign="top">
<td align="center" valign="middle" colspan=2>
<input type="submit" value="Добавить">
</tr>
</table>
</form>
</div>
</div>
EOF;
}

//------------------------------------------------------------------------------
//  article comments - edit comment link
//------------------------------------------------------------------------------

function tmpl_comment_edit_link($url, $cid) {
global $ibforums;
return <<<EOF
<a href="{$url}?id={$cid}">{$ibforums->lang['cskin_go']}</a>
EOF;
}

//------------------------------------------------------------------------------
//  article comments - delete comment link
//------------------------------------------------------------------------------

function tmpl_comment_dele_link($url, $cid) {
global $ibforums;
return <<<EOF
<a href="{$url}?id={$cid}">{$ibforums->lang['del_article']}</a>
EOF;
}

//------------------------------------------------------------------------------
//  article comments - where to return after add/edit
//------------------------------------------------------------------------------

function tmpl_comment_return_url( $cat_id, $art_id ) {
global $ibforums;
return <<<EOF
Location: {$ibforums->vars['dynamiclite']}cat={$cat_id}&id={$art_id}&p={$ibforums->input['p']}&version={$ibforums->input['version']}&comments=1#comments
EOF;
}

}