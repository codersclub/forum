<?php

/*
+--------------------------------------------------------------------------
|   D-Site Favorites module skin sets
|   ========================================
|   (c) 2004 - 2006 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
*/


class skin_csite_mod_fav {

//------------------------------------------------------------------------------
// show subscriptions template
//------------------------------------------------------------------------------

function tmpl_show_subscriptions( $entry ) {
global $ibforums;
return <<<EOF
 <div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$entry['title']}</div>
</div>
<br>
  {$entry['subscriptions']}
EOF;
}

//------------------------------------------------------------------------------
// show subscriptions rows
//------------------------------------------------------------------------------

function tmpl_subscriptions_cat_header() {
global $ibforums;
return <<<EOF
<table cellspacing="0" width="100%" class='tableborder'>
<tr>
 <td class='row3' colspan="2" align='center'>{$ibforums->lang['subscriptions_cats_header_name']}</td>
</tr>
<tr>
 <td class='row1' align='center' width="80%">{$ibforums->lang['subscriptions_cat_name']}</td>
 <td class='row1' align='center'>{$ibforums->lang['subscriptions_manage']}</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
// show subscriptions rows
//------------------------------------------------------------------------------

function tmpl_subscriptions_cat_footer() {
global $ibforums;
return <<<EOF
</table>
EOF;
}

//------------------------------------------------------------------------------
// show subscriptions rows
//------------------------------------------------------------------------------

function tmpl_subscriptions_art_header() {
global $ibforums;
return <<<EOF
<br>
<table cellspacing="0" width="100%" class='tableborder'>
<tr>
 <td class='row3' colspan="2" align='center'>{$ibforums->lang['subscriptions_arts_header_name']}</td>
</tr>
<tr>
 <td class='row1' align='center' width="80%">{$ibforums->lang['subscriptions_art_name']}</td>
 <td class='row1' align='center'>{$ibforums->lang['subscriptions_manage']}</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
// show subscriptions rows
//------------------------------------------------------------------------------

function tmpl_subscriptions_art_footer() {
global $ibforums;
return <<<EOF
</table>
EOF;
}

//------------------------------------------------------------------------------
// show subscriptions rows
//------------------------------------------------------------------------------

function tmpl_subscriptions_cats_row( $entry ) {
global $ibforums;
return <<<EOF
<tr>
 <td class='row2' align='center' width="80%">{$entry['cat_link']}</td>
 <td class='row2' align='center' width="80%">{$entry['delete_link']}</td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
// show subscriptions rows
//------------------------------------------------------------------------------

function tmpl_subscriptions_no_subscription() {
global $ibforums;
return <<<EOF
<tr>
 <td class='row2' align='center' colspan="2"><br /><b>{$ibforums->lang['no_subscriptions']}</b><br /><br /></td>
</tr>
EOF;
}

//------------------------------------------------------------------------------
// subscriptions delete link
//------------------------------------------------------------------------------

function tmpl_subscribe_delete_link( $entry ) {
global $ibforums;
return <<<EOF
<a href="{$ibforums->vars['csite_cms_url']}/unsubscribe.html?id={$entry['id']}">{$ibforums->lang['delete']}</a>
EOF;
}

//------------------------------------------------------------------------------
// favorites delete link
//------------------------------------------------------------------------------

function tmpl_favorite_delete_link( $entry ) {
global $ibforums;
return <<<EOF
<a href="{$ibforums->vars['csite_cms_url']}/unsubscribe.html?id={$entry['id']}&t=1">{$ibforums->lang['delete']}</a>
EOF;
}

//------------------------------------------------------------------------------
// subscription finish screen
//------------------------------------------------------------------------------

function tmpl_success_subscription( $title, $text, $ret_url, $ret_name ) {
global $ibforums;
return <<<EOF
 <div class='tableborder'>
 <div class='maintitle'><{CAT_IMG}> {$title}</div>
</div>
<table cellspacing="0" width="100%" class='tableborder'>
<tr>
 <td class='row3' align='center'>{$text}</td>
</tr>
<tr>
 <td class='row1' align='center'><a href="{$ret_url}">{$ret_name}</a></td>
</tr>
</table>
EOF;
}

//------------------------------------------------------------------------------
// just a link
//------------------------------------------------------------------------------

function tmpl_link( $url, $name ) {
global $ibforums;
return <<<EOF
<a href="{$url}">{$name}</a>
EOF;
}
}

?>
