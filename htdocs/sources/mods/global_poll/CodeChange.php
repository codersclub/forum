[COMMENT]
/*
+--------------------------------------------------------------------------
|   Invision Board v1.2
|   ========================================
|   > Mod_Installer 
|   > Module written by Peter(Pit)
|        > Peter member at ibforen.de, ibplanet.de, Pit member at ibplanet.com
|        > email: Peter@ibforen.de
|
|   > Mod_Installer Version Number: 0.9 (2003-06-20)
|   > <c> 2003 by Peter
|
|   > This is the CodeChange.php for Global Poll 1.2
+--------------------------------------------------------------------------
*/
[COMMENT_END]

[INTERFACE]
'title' => 'Global Poll',
'sub_title' => 'Puts a global poll on the forum´s main page',
'version' => '1.2 a',
'category' => 'Minor Mod',
'compatible' => 'Invision Power Board  1.2',
'author' => 'Koksi',
'email' => 'Koksi@ibforen.de',
'mod_token' => 'mod_global_poll',
[INTERFACE_END]



[MOD_TOKEN]
mod_global_poll
[FNAME]
conf_global.php

[STEP]
[SEARCH]
$ibforums->vars['post_titlechange']
[INSERT]
//-- mod_global_poll begin
$ibforums->vars['global_poll']			=	'0';
//-- mod_global_poll end
[MODE]
insert_above

[FNAME_END]


[MOD_TOKEN]
mod_global_poll
[FNAME]
sources/Boards.php

[STEP]
[SEARCH]
$this->process_all_cats();
[INSERT]
//-- mod_global_poll begin
if (file_exists(ROOT_PATH."sources/mods/global_poll/mod_global_poll_func.php")) 
{
       require ROOT_PATH."sources/mods/global_poll/mod_global_poll_func.php";
}else{
       die("Could not call required function from file 'sources/mods/global_poll/mod_global_poll_func.php'<br>Does it exist?");
}     
$global_poll = new global_poll;
$this->output .= $global_poll->getOutput();    
//-- mod_global_poll end
[MODE]
insert_below

[FNAME_END]



[MOD_TOKEN]
mod_global_poll
[FNAME]
sources/Admin/ad_settings.php

[STEP]
[SEARCH]
$ADMIN->html .= $SKIN->add_td_basic( 'Polls', 'left', 'catrow2' );
[INSERT]
//-- mod_global_poll begin
if (file_exists(ROOT_PATH."sources/mods/global_poll/mod_global_poll_adm.php")) {
       require ROOT_PATH."sources/mods/global_poll/mod_global_poll_adm.php";
   } else {
       die("Could not call required function from file 'sources/mods/global_poll/mod_global_poll_adm.php'<br>Does it exist?");
   }
   $adminGlobalPoll = new AdminGlobalPoll;
   $adminGlobalPoll->ad_settings();
//-- mod_global_poll end
[MODE]
insert_above


[STEP]
[SEARCH]
case 'dopost':
[INSERT]
//-- mod_global_poll begin
if (file_exists(ROOT_PATH."sources/mods/global_poll/mod_global_poll_adm.php")) {
       require ROOT_PATH."sources/mods/global_poll/mod_global_poll_adm.php";
   } else {
       die("Could not call required function from file 'sources/mods/global_poll/mod_global_poll_adm.php'<br>Does it exist?");
   }    
    
   $adminGlobalPoll = new AdminGlobalPoll;
   $adminGlobalPoll->save_config();
//-- mod_global_poll end
[MODE]
insert_below



[FNAME_END]

[COPY]

[COPY_END]

[CUSTOMIZE]

[CUSTOMIZE_END]

[COMMENT_MOD]


[COMMENT_MOD_END]

[HISTORY_OLD]

[HISTORY_OLD_END]
								     )      );