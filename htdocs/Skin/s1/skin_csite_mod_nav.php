<?php

/*
+--------------------------------------------------------------------------
|   D-Site Navigation module skin sets
|   ========================================
|   (c) 2004 - 2005 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
*/


class skin_csite_mod_nav {

//-------------------------------------
//  top navigation templates
//-------------------------------------

function start_nav($NEW="") {
global $ibforums;
return <<<EOF
<div id='navstrip' align='left'><{F_NAV}>&nbsp;<a href='{$ibforums->vars['dynamiclite']}'>{$ibforums->vars['csite_title']}</a>
EOF;
}

function start_nav_rw($NEW="") {
global $ibforums;
return <<<EOF
<div id='navstrip' align='left'><{F_NAV}>&nbsp;<a href='{$ibforums->vars['csite_cms_url']}/'>{$ibforums->vars['csite_title']}</a>
EOF;
}

function nav_link($entry, $sep = "") {
global $ibforums;
return <<<EOF
{$sep}<a href='{$ibforums->vars['dynamiclite']}cat={$entry['id']}'>{$entry['name']}</a>
EOF;
}

function nav_link_rw($entry, $name, $sep = "") {
global $ibforums;
return <<<EOF
{$sep}<a href='{$entry}'>{$name}</a>
EOF;
}

function end_nav() {
global $ibforums;
return <<<EOF
</div>
EOF;
}


//-------------------------------------
//  main menu templates
//-------------------------------------

function tmpl_main_menu( $entry, $block_caption ) {
global $ibforums;
return <<<EOF
<div class='tableborder'>
<div class='maintitle'><{CAT_IMG}> {$block_caption}</div>
<table cellspacing="0" width="100%">
{$entry}
</table>
</div>
</div>
EOF;
}

function tmpl_main_menu_parent_row($id, $sep, $name) {
global $ibforums;
return <<<EOF
<tr>
<td style='padding:5px'>
<b>&middot; <a href='{$ibforums->vars['dynamiclite']}cat={$id}'>{$name}</a></b>
</td>
</tr>
EOF;
}

function tmpl_main_menu_parent_row_rw($url, $sep, $name) {
global $ibforums;
return <<<EOF
<tr>
<td style='padding:5px'>
<b>&middot; <a href='{$url}'>{$name}</a></b>
</td>
</tr>
EOF;
}

function tmpl_main_menu_parent_row_redir($redir_url, $name) {
global $ibforums;
return <<<EOF
<tr>
<td style='padding:5px'>
<b>&middot; <a href='{$redir_url}'>{$name}</a></b>
</td>
</tr>
EOF;
}

function tmpl_main_menu_row($id, $sep, $name) {
global $ibforums;
return <<<EOF
<tr>
<td class='desc' style='padding:5px'>
{$sep}<a href='{$ibforums->vars['dynamiclite']}cat={$id}'>{$name}</a>
</td>
</tr>
EOF;
}

function tmpl_main_menu_row_rw($id, $sep, $name) {
global $ibforums;
return <<<EOF
<tr>
<td class='desc' style='padding:5px'>
{$sep}<a href='{$id}'>{$name}</a>
</td>
</tr>
EOF;
}

function tmpl_main_menu_row_redir($redir_url, $sep, $name) {
global $ibforums;
return <<<EOF
<tr>
<td class='desc' style='padding:5px'>
{$sep}<a href='{$redir_url}'>{$name}</a>
</td>
</tr>
EOF;
}

}