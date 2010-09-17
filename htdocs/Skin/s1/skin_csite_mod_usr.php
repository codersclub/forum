<?php

/*
+--------------------------------------------------------------------------
|   D-Site User management module skin sets
|   ========================================
|   (c) 2004 - 2005 Anton
|   anton@sources.ru
|   ========================================
+---------------------------------------------------------------------------
*/


class skin_csite_mod_usr {

//------------------------------------------------------------------------------
//  moderators template for showing on the top of the site
//------------------------------------------------------------------------------

function tmpl_moderators($entry) {
global $ibforums;
return <<<EOF
{$ibforums->lang['moderators']}{$entry}
EOF;
}

//------------------------------------------------------------------------------
//  url to the ibforum's user profile
//------------------------------------------------------------------------------

function tmpl_mod_link($id, $name) {
global $ibforums;
return <<<EOF
<a href='{$ibforums->base_url}showuser=$id'>$name</a>
EOF;
}

}
