<?php


$css_names = array(

	'BODY'                                                                                 => "Web Page Body",
	'TABLE, TR, TD'                                                                        => "Default table",
	'a:link, a:visited, a:active'                                                          => "Default link",
	'a:hover'                                                                              => "Default link hover",
	'.googlebottom, .googlebottom a:link, .googlebottom a:visited, .googlebottom a:active' => "Simple Search Results: Dark",
	'.googlish, .googlish a:link, .googlish a:visited, .googlish a:active'                 => "Simple Search Results: Topic Link",
	'.googlesmall, .googlesmall a:link, .googlesmall a:active, .googlesmall a:visited'     => "Simple Search Results: Small Text",
	'option.sub'                                                                           => "Search Form: Select forums box: subcat colour",
	'.caldate'                                                                             => "Calendar: Date",
	'#ucpmenu'                                                                             => "User CP: Menu colour",
	'#ucpcontent'                                                                          => "User CP: Content colour",
	'#logostrip'                                                                           => "Board Header: Logo Strip",
	'#submenu'                                                                             => "Board Header: Icon Strip",
	'#submenu a:link, #submenu a:visited, #submenu a:active'                               => "Board Header: Icon Strip link colours",
	'#userlinks'                                                                           => "Board Header: Member Bar",
	'.activeuserstrip'                                                                     => 'Forums & Topics: Active users bar',
	'.pformstrip'                                                                          => 'Global: Sub heading',
	'.pformleft'                                                                           => 'Global: left side table cell',
	'.pformleftw'                                                                          => 'Global: left side table cell wide',
	'.pformright'                                                                          => 'Global: right side table cell',
	'.post1'                                                                               => 'Post View: Alt colour #1',
	'.post2'                                                                               => 'Post View: Alt colour #2',
	'.postlinksbar'                                                                        => 'Post View: Track topic, etc links',
	'.row1'                                                                                => 'Global: Alt row #1',
	'.row2'                                                                                => 'Global: Alt row #2',
	'.row3'                                                                                => 'Global: Alt row #3',
	'.row4'                                                                                => 'Global: Alt row #4',
	'.darkrow1'                                                                            => 'Global: Alt dark row #1',
	'.darkrow2'                                                                            => 'Global: Alt dark row #2',
	'.darkrow3'                                                                            => 'Global: Alt dark row #3',
	'.hlight'                                                                              => 'Msgr: Inbox row selected',
	'.dlight'                                                                              => 'Msgr: Inbox row not selected',
	'.titlemedium'                                                                         => 'Global: Sub heading (minor)',
	'.titlemedium a:link, .titlemedium a:visited, .titlemedium a:active'                   => 'Global: Sub heading (minor) Links',
	'.maintitle'                                                                           => 'Global: Main table heading',
	'.maintitle a:link, .maintitle a:visited, .maintitle a:active'                         => 'Global: Main table heading Links',
	'.plainborder'                                                                         => 'Global: Table alt #1',
	'.tableborder'                                                                         => 'Global: Table cellspacing colour',
	'.tablefill'                                                                           => 'Global: Table alt #2',
	'.tablepad'                                                                            => 'Global: Table alt #3',
	'.desc'                                                                                => 'Forum View: Last post info',
	'.signature'                                                                           => 'Post View: Signature',
	'.normalname'                                                                          => 'Post View: Member name (reg)',
	'.unreg'                                                                               => 'Post View: Unreg name',
	'.searchlite'                                                                          => 'Global: Search highlighting',
	'.quote'                                                                               => 'Quote Box',
	'.code'                                                                                => 'Code Box',

);

$skin_names = array(

	'skin_boards'    => array(
		'Board Index Sections',
		'Elements for the board index listing (shows you all the forums and is the first page you see when visiting the board) such as
					 						   forum link HTML, active user overview HTML, board statistics overview HTML, today\'s birthdays overview HTML',
	),
	'skin_buddy'     => array(
		'myAssistant Sections',
		'Elements for the myAssistant feature, including links and search boxes',
	),
	'skin_calendar'  => array(
		'Calendar Sections',
		'Elements for the calendar, including calendar view, view events',
	),
	'skin_emails'    => array(
		'Member Contact Sections',
		'Elements from various contact methods, such as ICQ, AIM pager, email form and report post to moderator form',
	),
	'skin_forum'     => array(
		'Forum Index Sections',
		'Elements for forum view (list all topics in forum). Includes forum log in window, forum rules',
	),
	'skin_global'    => array(
		'Global HTML elements',
		'Various HTML elements such as board header, redirect page, error page',
	),
	'skin_help'      => array(
		'Help Sections',
		'Elements for the help screen, including search boxes and view help files',
	),
	'skin_legends'   => array(
		'Board Legends Sections',
		'Elements for the "view all emoticons" windows, and the "Find User" sections',
	),
	'skin_login'     => array(
		'Log In Sections',
		'Elements for the log in form',
	),
	'skin_mlist'     => array(
		'Member List Sections',
		'Elements for the member list views',
	),
	'skin_mod'       => array(
		'Moderator Function Sections',
		'Elements for the moderator tools such as delete topic, edit topic and the moderator CP sections',
	),
	'skin_msg'       => array(
		'Messenger Sections',
		'Elements for the messenger, such as view inbox, view message, archive messages, etc',
	),
	'skin_online'    => array(
		'Online List Sections',
		'Elements for the "Show all online users" link',
	),
	'skin_poll'      => array(
		'View Poll Sections',
		'Elements for the poll view, includes vote form and vote results',
	),
	'skin_post'      => array(
		'Post Screen Sections',
		'Elements for the post screens such as new topic, reply to topic, quote post, edit post, some messenger new PM sections, some calender new event sections',
	),
	'skin_printpage' => array(
		'Printable Topic Sections',
		'Elements for displaying the "printable topic" page',
	),
	'skin_profile'   => array(
		'Profile View Sections',
		'Elements for viewing a members profile',
	),
	'skin_register'  => array(
		'Register Sections',
		'Elements for the register form, validate account form and COPPA forms',
	),
	'skin_search'    => array(
		'Search Sections',
		'Elements for the search forum and the results view',
	),
	'skin_stats'     => array(
		'Statistics Section',
		'Elements for various functions such as "View Moderating Team", "View todays top 10 posts", "View Active Topics"',
	),
	'skin_topic'     => array(
		'Topic View Section',
		'Elements for the topic view screen, such as post view, inline attachment view, etc.',
	),
	'skin_modcp'     => array(
		'Mod CP Section',
		'Elements for the moderators control panel sections.',
	),
	'skin_ucp'       => array(
		'User Control Panel Section',
		'Elements for the user control panel, such as the menu (also used in Messenger Views), edit profile, edit signature, edit avatar, etc.',
	)
);

$bit_names['skin_boards'] = array(
	'PageTop'            => '1: Board Index Page Top',
	'newslink'           => '2: Latest News Link',
	'CatHeader_Expanded' => '3: Start Category Table',
	'ForumRow'           => '4: +-- Board Index Forum Row',
	'subheader'          => '5: +-- Sub Forum Row',
	'end_this_cat'       => '6: +-- End Category Row',
	'end_all_cats'       => '7: End Category Table',
	'BoardInformation'   => '8: Page Bottom Links - Moderating Team,etc',
	'quick_log_in'       => '9: Quick Log In Box',
	'stats_header'       => '10: Start Board Statistics Table',
	'ActiveUsers'        => '11: +-- Online Users Row',
	'birthdays'          => '12: +-- Members Birthdays Row',
	'calendar_events'    => '13: +-- Forthcoming Calendar Events Row',
	'ShowStats'          => '14: +-- Board Stats: Posts, Topics, etc Row',
	'stats_footer'       => '15: End Board Statistics Table',
);

$bit_names['skin_buddy'] = array(
	'buddy_js'       => '1: myAssistant Javascript source',
	'build_away_msg' => '2: New posts and topics info',
	'append_view'    => '3: Javascript redirect link wrapper',
	'main'           => '4: Main myAssistant HTML',
	'login'          => '5: Not logged in HTML',
	'closelink'      => '6: Close window links',
);

$bit_names['skin_calendar'] = array(
	'cal_main_content'      => '1: Main Month Display HTML',
	'cal_birthday_start'    => '2: +-- Start Birthday Entry',
	'cal_birthday_entry'    => '3: +-- Birthday Entry Link',
	'cal_birthday_end'      => '4: End Birthday Entry',
	'cal_edit_button'       => '5: Edit Entry Button',
	'cal_page_events_start' => '6: Start Event Page',
	'cal_show_event'        => '7: +-- Show Event Page',
	'cal_events_end'        => '8: End Day Events',
	'cal_date_cell_today'   => '9: Today\'s Calendar Cell',
	'cal_date_cell'         => '10: Date Cell',
	'cal_blank_cell'        => '11: Blank Cell',
	'cal_events_start'      => '12: Cell Start Event Entry',
	'cal_events_wrap'       => '13: Event cell wrap',
	'cal_day_bit'           => '14: Top Bar Weekday Name',
	'cal_new_row'           => '15: Start New Calendar Row',
);

$bit_names['skin_emails']  = array(
	'pager_header' => '1: Pop-up Header',
	'yahoo_body'   => '2: +-- Yahoo Body',
	'msn_body'     => '3: +-- MSN Body',
	'aol_body'     => '4: +-- AIM Body',
	'icq_body'     => '5: +-- ICQ Body',
	'end_table'    => '6: End Table',
	'report_form'  => '7: Report this post form',
	'send_form'    => '8: Send Email form',
	'show_address' => '9: Show email address',
	'forward_form' => '10: Forward Topic form',
	'forum_jump'   => '11: Forum Jump',
	'sent_screen'  => '12: Email sent screen'
);
$bit_names['skin_forum']   = array(
	'page_title'          => '1: Page Title',
	'show_rules'          => '2: Forum Rules Page',
	'show_rules_full'     => '3: Show Forum Rules Full',
	'show_rules_link'     => '4: Forum Rules Link',
	'PageTop'             => '5: Forum Page Header',
	'render_pinned_start' => '6: +-- Important Topics Title Start',
	'render_pinned_row'   => '7: +-- Pinned Topic Entry',
	'render_pinned_end'   => '8: +-- Important Topics Title End',
	'RenderRow'           => '9: +-- Normal Topic Entry',
	'forum_active_users'  => '10: +-- Users Browsing Forum Box',
	'TableEnd'            => '11: Forum Page End',
	'show_sub_link'       => '12: Subscribe to Forum Link',
	'show_no_matches'     => '13: No Topics to Display Message',
	'Forum_log_in'        => '14: Forum Log In Page',
	'who_link'            => '15: Who Posted? Link',
);
$bit_names['skin_global']  = array(
	'BoardHeader'         => '1: Board Header (logo, links, etc)',
	'ibf_banner'          => '2: +--IPS Hosting Banner (not activated)',
	'Member_bar'          => '3: +--Member Bar WITH Messenger Links',
	'Member_no_usepm_bar' => '4: +--Member Bar NO Messenger Links',
	'Guest_bar'           => '5: +--Guest Bar',
	'admin_link'          => '6: +--Link to Admin CP',
	'mod_link'            => '7: +--Link to Mod CP',
	'start_nav'           => '8: +--Navigation Start',
	'end_nav'             => '9: +--Navigation End',
	'Error'               => '10: Error Page',
	'error_log_in'        => '11: Error Page Log in Box',
	'board_offline'       => '12: Offline Board Message',
	'PM_popup'            => '13: New PM Pop up Javascript',
	'Redirect'            => '14: Redirect Page',
);
$bit_names['skin_help']    = array(
	'start'      => '1: Start Help Page',
	'display'    => '2: Show Help Page',
	'row'        => '3: Show Help Topic Title',
	'no_results' => '4: No Help Search Results',
	'end'        => '5: End Help Page',
);
$bit_names['skin_legends'] = array(
	'page_header'         => '1: Page Header',
	'find_user_one'       => '2: Find User Form',
	'find_user_error'     => '3: Find User Error',
	'find_user_final'     => '4: Find User Result',
	'emoticon_javascript' => '5: Emoticons Javascript',
	'emoticons_row'       => '6: Emoticons Row',
	'page_footer'         => '7: Page Footer'
);
$bit_names['skin_login']   = array(
	'ShowForm'       => '1: Log In Form',
	'errors'         => '2: Log In Errors Box',
	'ShowLogOutForm' => '3: Log Out Form (no longer used)'
);
$bit_names['skin_mlist']   = array(
	'start'       => '1: Start Page',
	'Page_header' => '2: Page Header',
	'show_row'    => '3: +-- Member List Row',
	'no_results'  => '4: +-- No Results',
	'Page_end'    => '5: Page Footer',
	'end'         => '6: End Page',
);
$bit_names['skin_mod']     = array(
	'table_top'         => "1: Start Table",
	'mod_exp'           => "2: Action Explanation",
	'move_form'         => "3: Move Topic Form",
	'merge_body'        => "4: Merge Topics Form",
	'split_body'        => "5: Split Topic Form",
	'split_row'         => "6: +-- Split Topics Post Row",
	'split_end_form'    => "7: Split Topic End Form",
	'poll_select_form'  => "8: Edit Poll: Form",
	'poll_entry'        => "9: Edit Poll: Poll Choice Row",
	'topictitle_fields' => "10: Edit Topic Title",
	'end_form'          => "11: End Form",
	'delete_js'         => "12: Delete Javascript",
	'topic_history'     => "13: Topic History Table",
	'mod_log_start'     => "14: Mod Logs: Start Entries",
	'mod_log_none'      => "15: +-- Mod Logs: No Entries",
	'mod_log_row'       => "16: +-- Mod Logs: Entry",
	'mod_log_end'       => "17: Mod Logs: End Entries",
	'forum_jump'        => "18: Forum Jump",
);
$bit_names['skin_modcp']   = array(
	'splash'                  => "0: Depreciated (Not Used)",
	'mod_cp_start'            => "1: Mod CP Start",
	'modpost_topicentry'      => "2: Mod Q: Topic Entry",
	'modtopics_end'           => "3: Mod Q: Topics: End",
	'mod_topic_title'         => "4: Mod Q: Topic Title",
	'mod_postentry'           => "5: Mod Q: Post Entry",
	'mod_postentry_checkbox'  => "6: Mod Q: Post Entry check box",
	'mod_topic_spacer'        => "7: Mod Q: Topic Spacer",
	'modpost_topicstart'      => "8: Mod Q: Post: Topic Start",
	'modtopicview_start'      => "9: Mod Q: Topic: Start",
	'modtopics_start'         => "10: Mod Q: Topics Overview Start",
	'modpost_topicend'        => "11: Mod Q: Post :Topic End",
	'prune_confirm'           => "12: Prune Posts: Confirm Box",
	'prune_splash'            => "13: Prune Posts: Main Form",
	'ip_start_form'           => "14: IP Search: Start Form",
	'ip_post_results'         => "15: IP Post Entry",
	'ip_member_start'         => "16: IP Member Start",
	'ip_member_row'           => "17: IP Member Row",
	'ip_member_end'           => "18: IP Member End",
	'find_user'               => "19: Find User: I",
	'find_two'                => "20: Find User: II",
	'edit_user_form'          => "21: Edit User Form",
	'results'                 => "22: Result Screen",
	'mod_simple_page'         => "23: Display Error/Result Box",
	'start_topics'            => "24: Topic View: Start",
	'topic_row'               => "25: Topic View: Entry",
	'show_no_topics'          => "26: Topic View: None Found",
	'topics_end'              => "27: Topic View: End",
	'cat_row'                 => "28: Category Row",
	'forum_page_start'        => "29: Forum View Start",
	'forum_row'               => "30: Forum View Entry",
	'forum_page_end'          => "31: Forum View End",
	'move_checked_form_start' => "32: Move Form Start",
	'move_checked_form_entry' => "33: Move Form Entry",
	'move_checked_form_end'   => "34: Move Form End",
	'mod_exp'                 => "35: Action Explanation",
	'end_form'                => "36: End Form",
);

$bit_names['skin_msg']       = array(
	'Address_header'           => "1: Address Book: Header",
	'Address_table_header'     => "2: +-- Address Book: Table Header",
	'address_add'              => "3: +-- Address Book: Add Entry",
	'address_edit'             => "4: +-- Address Book: Edit Entry",
	'render_address_row'       => "5: +-- Address Book: Show Entry",
	'Address_none'             => "6: +-- Address Book: No Entries",
	'end_address_table'        => "7: Address Book: Footer",
	'archive_html_header'      => "8: Archive PM: Header",
	'archive_form'             => "9: +-- Archive PM: Form",
	'archive_complete'         => "10: +-- Archive PM: Success Page",
	'archive_html_entry'       => "11: +-- Archive PM: HTML Entry",
	'archive_html_footer'      => "12: Archive PM: HTML Entry Footer",
	'unsent_table_header'      => "13: Unsent PM: Table Header",
	'unsent_row'               => "14: +-- Unsent PM: Entry",
	'unsent_end'               => "15: Unsent PM: End",
	'trackUNread_table_header' => "16: Track: Unread: Header",
	'trackUNread_row'          => "17: +-- Track: Unread: Entry",
	'trackUNread_end'          => "18: Track: Unread: End",
	'trackread_table_header'   => "19: Track: Read: Header",
	'trackread_row'            => "20: +-- Track: Read: Entry",
	'trackread_end'            => "21: Track: Read: End",
	'inbox_table_header'       => "22: Msg List: Inbox Header",
	'No_msg_inbox'             => "23: +-- Msg List: No Entries",
	'inbox_row'                => "24: +-- Msg List: PM Entry",
	'end_inbox'                => "25: Msg List: End InBox",
	'prefs_header'             => "26: Folders: Header",
	'prefs_row'                => "27: +-- Folders: Entry",
	'prefs_add_dirs'           => "28: +-- Folders: Add Folder Entry",
	'prefs_footer'             => "29: Folders: Footer",
	'send_form_header'         => "30: Send PM: Header",
	'Send_form'                => "31: +-- Send PM: Main Form",
	'preview'                  => "32: +-- Send PM: Preview Message Box",
	'pm_errors'                => "33: +-- Send PM: Error Box",
	'mass_pm_box'              => "34: +-- Send PM: BCC Box",
	'send_form_footer'         => "35: Send PM: Form Footer",
	'Render_msg'               => "36: Show Received PM",
	'pm_popup'                 => "37: New Messages POP UP HTML",
);
$bit_names['skin_online']    = array(
	'end'         => "0: Depreciated (Not Used)",
	'Page_header' => "1: Header",
	'show_row'    => "2: Entry",
	'Page_end'    => "3: Footer",
);
$bit_names['skin_poll']      = array(
	'ShowPoll_header'      => "1: Show Poll: Header",
	'ShowPoll_Form_header' => "2: Show Poll: Form Header",
	'Render_row_results'   => "3: Show Poll: Result Row",
	'Render_row_form'      => "4: Show Poll: Choice Radio Button",
	'ShowPoll_footer'      => "5: Show Poll: Footer",
	'delete_link'          => "6: Delete Link",
	'edit_link'            => "7: Edit Link",
);
$bit_names['skin_post']      = array(

	'calendar_start_edit_form' => "1: Calendar: Start Edit Form",
	'calendar_event_title'     => "2: +-- Calendar: Event Title",
	'calendar_start_form'      => "3: +-- Calendar: New Event Start Form",
	'calendar_delete_box'      => "4: +-- Calendar: Delete Box",
	'calendar_choose_date'     => "5: +-- Calendar: Choose Date",
	'calendar_admin_group_box' => "6: +-- Calendar: Admin Group Box",
	'calendar_event_type'      => "7: +-- Calendar: Event Type",
	'calendar_end_form'        => "8: Calendar: End Form",
	'TopicSummary_top'         => "9: Post: Topic Summary Start",
	'TopicSummary_body'        => "10: +-- Post: Topic Summary Body",
	'TopicSummary_bottom'      => "11: Post: Topic Summary End",
	'table_structure'          => "12: Post: Table Structure",
	'table_top'                => "13: Post: Table Top",
	'preview'                  => "14: +-- Post: Preview Box",
	'errors'                   => "15: +-- Post: Errors Box",
	'nameField_unreg'          => "16: +-- Post: Guest Name Fields",
	'nameField_reg'            => "17: +-- Post: Registered Member Name Fields",
	'postbox_buttons'          => "18: +-- Post: Code Buttons & Post Text area",
	'PostIcons'                => "19: +-- Post: Post Icons Box",
	'topictitle_fields'        => "20: +-- Post: Topic Title Fields",
	'Upload_field'             => "21: +-- Post: Upload Field",
	'edit_upload_field'        => "22: +-- Post: Edit Upload Field",
	'add_edit_box'             => "23: +-- Post: Append Edited By.. checkbox",
	'mod_options'              => "24: +-- Post: Mod Options Drop Down",
	'quote_box'                => "25: +-- Post: Quote Text area",
	'smilie_table'             => "26: +-- Post: Smilie Box",
	'EndForm'                  => "27: Post: End Form",
	'poll_box'                 => "28: Poll: Options Text area",
	'pm_postbox_buttons'       => "29: PM: Code Buttons & msg Text area",
);
$bit_names['skin_printpage'] = array(
	'pp_header'    => "1: Start Page",
	'pp_postentry' => "2: Post Entry",
	'pp_end'       => "3: End Page",
	'choose_form'  => "4: Client Choose Form",
);
$bit_names['skin_profile']   = array(
	'show_profile' => "1: Member Profile Page",
	'user_edit'    => "2: User Edit Links",
	'custom_field' => "3: Custom Field",
);
$bit_names['skin_register']  = array(
	'show_authorise'  => "1: Reg. Complete, Authorise Needed Msg",
	'show_preview'    => "2: Reg. Complete, Admin Authorise Msg",
	'errors'          => "3: Errors Box",
	'ShowForm'        => "4: Main Registration Form",
	'reg_antispam'    => "5: Reg Flood Bot Code Box",
	'optional_title'  => "6: Custom Fields Start",
	'field_entry'     => "7: Custom Fields Entry",
	'field_textarea'  => "8: Custom Fields Textarea",
	'field_textinput' => "9: Custom Fields Input",
	'field_dropdown'  => "10: Custom Fields Drop Down",
	'coppa_start'     => "11: COPPA Start",
	'coppa_two'       => "12: COPPA Middle",
	'coppa_form'      => "13: COPPA Form",
	'lost_pass_form'  => "14: Lost Password Form",
	'show_dumb_form'  => "15: User Validate Account Form",
);
$bit_names['skin_search']    = array(
	'Form'          => "1: Main Search Form",
	'RenderRow'     => "2: Results: Topic Row",
	'RenderPostRow' => "3: Results: Post Row",
	'end'           => "4: Results: End Page",
	'end_as_post'   => "5: Results: End Post Page",
	'start'         => "6: Results: Start Page",
	'start_as_post' => "7: Results: Post Start",
	'active_start'  => "8: Active Topics Start",
	'active_none'   => "9: Active Topics No Results",
);
$bit_names['skin_stats']     = array(
	'page_title'         => "1: Page Title",
	'who_header'         => "2: Who Posted? Header",
	'who_row'            => "3: Who Posted? Entry",
	'who_name_link'      => "4: Who Posted? Name Link",
	'who_end'            => "5: Who Posted? End",
	'leader_row'         => "6: Forum Leaders: Entry",
	'group_strip'        => "7: Forum Leaders: Group Row",
	'close_strip'        => "8: Forum Leaders: Group Close",
	'top_poster_header'  => "9: Top Poster: Header",
	'top_poster_no_info' => "10: Top Poster: No Results",
	'top_poster_row'     => "11: Top Poster: Entry",
	'top_poster_footer'  => "12: Top Poster: Footer",
);
$bit_names['skin_topic']     = array(
	'PageTop'              => "1: Header",
	'golastpost_link'      => "2: Go to Last Post Link",
	'RenderRow'            => "3: Post Entry",
	'Show_attachments_img' => "4: Attachments: Show Image",
	'Show_attachments'     => "5: Attachments: Show Link Box",
	'report_link'          => "6: Report This Post Link",
	'ip_show'              => "7: IP Address Show/Link",
	'topic_active_users'   => "8: Users Browsing This Topic Box",
	'email_options'        => "9: Subscribe/Track Links",
	'Mod_Panel'            => "10: Mod Options Drop Down",
	'mod_wrapper'          => "11: Mod Options Entry",
	'TableFooter'          => "12: Footer",
);
$bit_names['skin_ucp']       = array(
	'personal_splash_av'  => "0: Depreciated (Not Used)",
	'personal_splash'     => "0: Depreciated (Not Used)",
	'Menu_bar'            => "1: Side Menu Links",
	'splash'              => "2: UCP Home",
	'signature'           => "3: Signature Edit / Preview",
	'pass_change'         => "4: Password Change Section",
	'personal_panel'      => "5: Profile Form: Main",
	'field_entry'         => "6: Profile Form: Custom Profile Entry",
	'field_textarea'      => "7: Profile Form: Custom Profile Textarea",
	'field_textinput'     => "8: Profile Form: Custom Profile Text Input",
	'field_dropdown'      => "9: Profile Form: Custom Profile DropDown",
	'required_title'      => "10: Profile Form: Required Fields Title",
	'member_title'        => "11: Profile Form: Member Title Field",
	'birthday'            => "12: Profile Form: Birthday Fields",
	'email_change'        => "13: Email Change Form",
	'personal_avatar'     => "14: Avatar Options Main",
	'personal_avatar_URL' => "15: Avatar Options URL",
	'avatar_upload_field' => "16: Avatar Options Upload",
	'personal_avatar_end' => "17: Avatar Options End",
	'forum_subs_header'   => "18: Forum Subscriptions: Header",
	'forum_subs_row'      => "19: Forum Subscriptions: Entry",
	'forum_subs_none'     => "20: Forum Subscriptions: No Entries",
	'forum_subs_end'      => "21: Forum Subscriptions: End",
	'subs_header'         => "22: Topic Subscriptions: Header",
	'subs_row'            => "23: Topic Subscriptions: Entry",
	'subs_none'           => "24: Topic Subscriptions: No Entries",
	'subs_forum_row'      => "25: Topic Subscriptions: Forum Header",
	'subs_end'            => "26: Topic Subscriptions: End",
	'skin_lang_header'    => "27: Skins & Languages Header",
	'settings_skin'       => "28: Skins & Languages Main",
	'skin_lang_end'       => "29: Skins & Languages End",
	'settings_header'     => "30: Board Settings Header",
	'settings_end'        => "31: Board Settings End",
	'email'               => "32: Email Options",
	'forum_jump'          => "33: Forum Jump",
	'CP_end'              => "34: End CP",
);

