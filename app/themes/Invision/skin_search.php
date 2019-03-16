<?php

use Views\View;

class skin_search
{

/*
 * Mod functions in searchbox
**/
    function mod_checkbox($class, $tid)
    {
        global $ibforums;
        return <<<EOF

<td class="b-topics-list-column-mod_checkbox $class"><input type="checkbox" name="TID_$tid" value="1" class="forminput" onclick="cca(this,'darkrow2');"></td>

EOF;
    }

    function mod_nocheckbox($class)
    {
        global $ibforums;
        return <<<EOF

<td class="$class b-topics-list-column-mod_checkbox">&nbsp;</td>

EOF;
    }

    function mod_column_head()
    {
        global $ibforums;
        return <<<EOF

<td width="4%" class="titlemedium b-topics-list-column-mod_checkbox">{$ibforums->lang["h_mod_checkbox"]}</th>

EOF;
    }

    function modform_open($data)
    {
        global $ibforums;
        return <<<EOF

<form name="topic" action="{$ibforums->base_url}act=modcp&old_act=search&searchid={$data["search_id"]}&search_in={$data["search_in"]}&result_type={$data["result_type"]}&highlite={$data["hl"]}&new={$ibforums->input["new"]}&CODE=topicchoice" method="post" onsubmit="return checkdelete("{$ibforums->lang["cp_js_delete"]}");">

EOF;
    }

    function modform_close()
    {
        global $ibforums;
        return <<<EOF
<tfoot>
<tr class="topics-mod-actions">
<td class="darkrow3" colspan="3">{$ibforums->lang["t_w_selected"]}
<select name="tact" class="forminput">
 <option value="close">{$ibforums->lang["cpt_close"]}</option>
 <option value="open">{$ibforums->lang["cpt_open"]}</option>
 <option value="pin">{$ibforums->lang["cpt_pin"]}</option>
 <option value="unpin">{$ibforums->lang["cpt_unpin"]}</option>
 <option value="move">{$ibforums->lang["cpt_move"]}</option>
 <option value="delete">{$ibforums->lang["cpt_delete"]}</option>
 <option value="approve">{$ibforums->lang["cpt_approve"]}</option>
 <option value="decline">{$ibforums->lang["cpt_decline"]}</option>
 <option value="hide">{$ibforums->lang["cpt_hide"]}</option>
 <option value="show">{$ibforums->lang["cpt_show"]}</option>
</select> &nbsp;<input type="submit" value="{$ibforums->lang["sort_submit"]}" class="forminput">

</td>
<td class="darkrow3" colspan="6">&nbsp;</td>
</tr>
</tfoot>
</table>
</form>
</div>
EOF;
    }

    function RenderRow($Data)
    {
        global $ibforums;
        return <<<EOF

    <tr class="b-topics-list-row">
      <td class="row4 b-topics-list-column-status">{$Data["folder_img"]}</td>
      <td class="row2 b-topics-list-column-icon">{$Data["topic_icon"]}</td>
      <td class="row4 b-topics-list-column-title">{$Data["go_new_post"]}{$Data["prefix"]} <a class="topic-link" href="{$ibforums->base_url}showtopic={$Data["tid"]}&amp;hl={$Data["keywords"]}">{$Data["title"]}</a><span class="b-topics-list-title-pages">{$Data[PAGES]}</span>
      <div class="desc">{$Data["description"]}</span></td>
      <td class="row4 b-topics-list-column-forum"><a href="{$ibforums->base_url}showforum={$Data["forum_id"]}">{$Data["forum_name"]}</a></td>
      <td class="row2 b-topics-list-column-author">{$Data["starter"]}</td>
      <td class="row4 b-topics-list-column-posts_num">{$Data["posts"]}</td>
      <td class="row2 b-topics-list-column-views_num">{$Data["views"]}</td>
      <td class="row2 b-topics-list-column-last_post"><div class="b-last_post_date-wrapper"><time datetime="{$Data["last_post_std"]}" class="last-post-date">{$Data["last_post"]}</time></div><span class="last-post-text"><a href="{$ibforums->base_url}showtopic={$Data["tid"]}&view=getlastpost">{$Data["last_text"]}</a></span> <span class="last-post-author">{$Data["last_poster"]}</span></td>
      {$Data["mod_checkbox"]}
    </tr>

EOF;
    }


    function start_as_post($Data)
    {
        global $ibforums;
        return <<<EOF

<div class="b-list-pages-wrapper b-found-posts-list-pages b-found-posts-pages-top">{$Data[SHOW_PAGES]}</div>

EOF;
    }


    function RenderPostRow($Data)
    {
        global $ibforums;
        return <<<EOF

<div class="tableborder b-post__wrapper b-found-posts-post-wrapper">
  <div class="maintitle b-found-posts-post_topic-title">{$Data["folder_img"]}&nbsp;{$Data["prefix"]} <a href="{$ibforums->base_url}showtopic={$Data["tid"]}&amp;hl={$Data["keywords"]}" class="linkthru e-found-posts-post_topic-title-link">{$Data["title"]}</a></span></b>  {$Data[PAGES]}</div>
  <table class="tablebasic b-post b-found-posts-post">
  <tr class="b-post__headers-row">
	<td class="b-post__author-name row4"><span class="e-post-author normalname" data-author-id="{$Data["author_id"]}">{$Data["author_name"]}</span></td>
	<td class="row4 b-post__header"><div class="b-post__info row4"><span class="e-post-date-prefix">{$ibforums->lang["rp_postedon"]}</span><time class="e-post-date" datetime="{$Data["std_post_date"]}">{$Data["post_date"]}</time></div></td>
  </tr>
  <tr class="b-post__data-row">
	<td class="post1 b-post__author-info">
	  <div class="postdetails">
		  <div class="b-post__topic-replies"><span class="e-post-topic_replies-title">{$ibforums->lang["rp_replies"]}</span><span class="e-post-topic_replies">{$Data["posts"]}</span></div>
		  <div class="b-post__topic_hits"><span class="b-post__topic_hits-title">{$ibforums->lang["rp_hits"]}</span><span class="e-post-topic_hits">{$Data["views"]}</span></div>
		  <div class="b-post__author-ip"><span class="e-ip-value">{$Data["ip_address"]}</span></div>
	  </div>
	</td>
	<td class="post1 b-post__body">{$Data["post"]}</td>
  </tr>
   <tr class="b-post__footer-row">
	<td class="row4 b-post__footer-left_cell">&nbsp;</td>
	<td class="row4 b-post-links"><span class="b-post-links__forum"><span class="b-post-links__forum-title">{$ibforums->lang["rp_forum"]}</span><a href="{$ibforums->base_url}showforum={$Data["forum_id"]}">{$Data["forum_name"]}</a></span><span class="b-post-links__post"><span class="b-post-links__post-title">{$ibforums->lang["rp_post"]}</span><a href="{$ibforums->base_url}act=ST&amp;f={$Data["forum_id"]}&amp;t={$Data["tid"]}&amp;hl={$Data["keywords"]}&amp;view=findpost&amp;p={$Data["pid"]}" class="linkthru b-post-links__post">#{$Data["pid"]}</a></span></td>
  </tr>
  </table>
</div>

EOF;
    }


    function result_simple_footer($data)
    {
        global $ibforums;
        return <<<EOF

  <div class="pformstrip" align="left">{$ibforums->lang["search_pages"]} &nbsp;  &nbsp; &nbsp;<span class="googlepagelinks">{$data["links"]}</span></div>
</div>

EOF;
    }


    function boolean_explain_link()
    {
        global $ibforums;
        return <<<EOF

&#091; <a href="#" title="{$ibforums->lang["be_ttip"]}" onclick="win_pop()">{$ibforums->lang["be_link"]}</a> &#093;

EOF;
    }


    function end_as_post($Data)
    {
        $legend = View::make('global.topicsListLegend');
        return <<<EOF
<div class="b-list-pages-wrapper b-found-posts-list-pages b-found-posts-pages-bottom">{$Data[SHOW_PAGES]}</div>
<div class="b-legend-row-wrapper clearfix">
{$legend}
</div>
EOF;
    }


    function result_simple_entry($data)
    {
        global $ibforums;
        return <<<EOF

  <div class="{$data["css_class"]}">
  <span class="googlish"><a href="{$ibforums->base_url}act=ST&amp;t={$data["tid"]}&amp;f={$data["id"]}&amp;view=findpost&amp;p={$data["pid"]}">{$data["title"]}</span></a>
  <br>
  {$data["post"]}
  <br>
  <span class="googlesmall">
  {$ibforums->lang["location_g"]}: <a href="{$ibforums->base_url}act=idx">{$ibforums->lang["g_b_home"]}</a>
  &gt; <a href="{$ibforums->base_url}act=SC&amp;c={$data["cat_id"]}">{$data["cat_name"]}</a>
  &gt; <a href="{$ibforums->base_url}act=SF&amp;f={$data["id"]}">{$data["name"]}</a>
  </span>
  <br>
  <span class="googlebottom"><strong>{$ibforums->lang["g_relevance"]}: {$data["relevance"]}% &middot; Author: {$data["author_name"]} &middot; Posted on: {$data["post_date"]}</strong></span>
  <span class="googlesmall"> - <a href="{$ibforums->base_url}act=ST&amp;t={$data["tid"]}&amp;f={$data["id"]}&amp;view=findpost&amp;p={$data["pid"]}" target="_blank">{$ibforums->lang["g_new_window"]}</a></span>
  </div>
  <br>

EOF;
    }


    function result_simple_header($data)
    {
        global $ibforums;
        return <<<EOF

<div class="plainborder">
  <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang["search_results"]}</div>
  <div class="pformstrip">
	<div class="pagelinks">{$ibforums->lang["g_searched_for"]} <strong>{$data["keyword"]}</strong></div>
	<div align="right">
	   {$ibforums->lang["g_results"]} <strong>{$data["start"]} - {$data["end"]}</strong> {$ibforums->lang["g_of_about"]} <strong>{$data["matches"]}</strong>.
	   {$ibforums->lang["g_search_took"]} <strong>{$data["ex_time"]}</strong> {$ibforums->lang["g_seconds"]}
	</div>
  </div>
  <br>

EOF;
    }


    function RenderPinnedRow($Data)
    {
        global $ibforums;
        return <<<EOF

    <tr>
      <td align="center" class="pinned_topic">{$Data["folder_img"]}</td>
      <td align="center" width="3%" class="pinned_topic">{$Data["topic_icon"]}</td>
      <td class="pinned_topic">{$Data["go_new_post"]}{$Data["prefix"]}  <a href="{$ibforums->base_url}showtopic={$Data["tid"]}&amp;hl={$Data["keywords"]}"><b>{$Data["title"]}</b></a> <span>{$Data[PAGES]}</span>
        <span class="desc">{$Data["description"]}</span></td>
      <td class="pinned_topic" width="20%" align="center"><a href="{$ibforums->base_url}showforum={$Data["forum_id"]}">{$Data["forum_name"]}</a></td>
      <td align="center" class="pinned_topic">{$Data["starter"]}</td>
      <td align="center" class="pinned_topic">{$Data["posts"]}</td>
      <td align="center" class="pinned_topic">{$Data["views"]}</td>
      <td class="pinned_topic">{$Data["last_post"]}<br><a href="{$ibforums->base_url}showtopic={$Data["tid"]}&amp;view=getlastpost">{$Data["last_text"]}</a> <b>{$Data["last_poster"]}</b></td>
      {$Data["mod_checkbox"]}
    </tr>

EOF;
    }


    function button()
    {
        global $ibforums;
        $action = "Search";
        if (preg_match("/act\=Select/", $_SERVER['REQUEST_URI'])) {
            $action = "Select";
        }

        return <<<EOF
<a href="{$ibforums->base_url}act={$action}&amp;CODE=02" target="_blank">{$ibforums->lang["select_button"]}</a>

EOF;
    }


    function active_none()
    {
        global $ibforums;
        return <<<EOF

<tr><td colspan="8" class="row1" align="center"><strong>{$ibforums->lang["active_no_topics"]}</strong></td></tr>

EOF;
    }


    function checkbox_where()
    {
        global $ibforums;
        return <<<EOF

<br>
<label><input type="checkbox" name="space_determine" value="1" class="checkbox">{$ibforums->lang["space_determine"]}</label>
<label><br><input type="checkbox" name="space_determine" value="phrase" class="checkbox">{$ibforums->lang["space_determine_phrase"]}</label>

EOF;
    }

    function Form($forums, $search_txt = "", $where = "")
    {
        global $ibforums, $print;
        $print->js->addVariable('current_forum', Ibf::app()->input["f"]);
        $print->js->addLocal('search.js');
        return <<<EOF
<form action="{$ibforums->base_url}" method="get" name="sForm">
<input type="hidden" name="act" value="Search">
<input type="hidden" name="CODE" value="01">
<div class="tableborder">
<table cellpadding="4" cellspacing="0" border="0" width="100%">
<tr>
	<td colspan="2" class="maintitle"  align="center">{$ibforums->lang["keywords_title"]}</td>
</tr>
<tr>
	<td class="pformstrip" width="50%">{$ibforums->lang["key_search"]}</td>
	<td class="pformstrip" width="50%">{$ibforums->lang["mem_search"]}</td>
</tr>
<tr>
	<td class="row1" valign="top">
	  <input type="text" maxlength="100" size="40" name="keywords" id="keywords" class="forminput">
	  <br><label><input type="checkbox" name="fulltext" checked>{$ibforums->lang["use_fulltext_search"]} <i>(beta)</i></label><br>
	  {$search_txt}<!--IBF.BOOLEAN_EXPLAIN-->{$where}

	</td>
	<td class="row1" valign="top">
	<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center">
	<tr>
	 <td><input type="text" maxlength="100" size="50" name="namesearch" class="forminput"></td>
	</tr>
	<tr>
	<td width="40%"><input type="checkbox" name="exactname" id="matchexact" value="1" class="checkbox"><label for="matchexact">{$ibforums->lang["match_name_ex"]}</label></td>
   </tr>
</table>
</td>
</tr>
</table>
</div>
<br>
<div class="tableborder">
<table cellpadding="4" cellspacing="0" border="0" width="100%">
<tr>
	<td colspan="2" class="maintitle"  align="center">{$ibforums->lang["search_options"]}</td>
</tr>
<tr>
	<td class="pformstrip" width="50%" valign="middle">{$ibforums->lang["search_where"]}</td>
	<td class="pformstrip" width="50%" valign="middle">{$ibforums->lang["search_refine"]}</td>
</tr>
<tr>
	<td class="row1" valign="middle">
	  $forums
	  <br>
	  <input type="checkbox" name="searchsubs" value="1" id="searchsubs" checked="checked">&nbsp;<label for="searchsubs">{$ibforums->lang["search_in_subs"]}</label>
	</td>
	<td class="row1" valign="top">
		<table cellspacing="4" cellpadding="0" width="100%" align="center" border="0">
		<tr>
		 <td valign="top">
		   <fieldset class="search">
		     <legend><strong>{$ibforums->lang["search_from"]}</strong></legend>
			 <select name="prune" class="forminput">
			 <option value="1">{$ibforums->lang["today"]}</option>
			 <option value="7">{$ibforums->lang["this_week"]}</option>
			 <option value="30">{$ibforums->lang["this_month"]}</option>
			 <option value="60">{$ibforums->lang["this_60"]}</option>
			 <option value="90">{$ibforums->lang["this_90"]}</option>
			 <option value="180">{$ibforums->lang["this_180"]}</option>
			 <option value="365">{$ibforums->lang["this_year"]}</option>
			 <option value="0" selected="selected">{$ibforums->lang["ever"]}</option>
			 </select>
			 <br>
			 <input type="radio" name="prune_type" id="prune_older" value="older" class="radiobutton">&nbsp;<label for="prune_older">{$ibforums->lang["older"]}</label>
			 <br>
			 <input type="radio" name="prune_type" id="prune_newer" value="newer" class="radiobutton" checked="checked">&nbsp;<label for="prune_newer">{$ibforums->lang["newer"]}</label>
		  </fieldset>
		</td>
		<td valign="top">
		  <fieldset class="search">
		     <legend><strong>{$ibforums->lang["sort_results"]}</strong></legend>
			 <select name="sort_key" class="forminput">
			 <option value="last_post">{$ibforums->lang["last_date"]}</option>
			 <option value="posts">{$ibforums->lang["number_topics"]}</option>
			 <option value="starter_name">{$ibforums->lang["poster_name"]}</option>
			 <option value="forum_id">{$ibforums->lang["forum_name"]}</option>
			 <option value="relevancy">{$ibforums->lang["relevance"]}</option>
			 </select>
			 <br><input type="radio" name="sort_order" id="sort_desc" class="radiobutton" value="desc" checked="checked"><label for="sort_desc">{$ibforums->lang["descending"]}</label>
			 <br><input type="radio" name="sort_order" id="sort_asc" class="radiobutton" value="asc"><label for="sort_asc">{$ibforums->lang["ascending"]}</label>
		  </fieldset>
		</td>
		</tr>
		<tr>
		 <td nowrap="nowrap">
		   <fieldset class="search">
		     <legend><strong>{$ibforums->lang["search_where"]}</strong></legend>
			 <input type="radio" name="search_in" class="radiobutton" id="search_in_posts" value="posts" checked="checked"><label for="search_in_posts">{$ibforums->lang["in_posts"]}</label>
			 <br>
			 <input type="radio" name="search_in" class="radiobutton" id="search_in_titles" value="titles"><label for="search_in_titles">{$ibforums->lang["in_topics"]}</label>
		   </fieldset>
		 </td>
		 <td>
		    <fieldset class="search">
		     <legend><strong>{$ibforums->lang["result_type"]}</strong></legend>
		     <input type="radio" name="result_type" class="radiobutton" value="topics" id="result_topics" checked="checked"><label for="result_topics">{$ibforums->lang["results_topics"]}</label>
		     <br>
		     <input type="radio" name="result_type" class="radiobutton" value="posts" id="result_posts"><label for="result_posts">{$ibforums->lang["results_post"]}</label>
		   </fieldset>
		 </td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="pformstrip" colspan="2" align="center"><input type="submit" value="{$ibforums->lang["do_search"]}" class="forminput"><!--IBF.SIMPLE_BUTTON--></td>
</tr>
</table>
</div>
</form>


EOF;
    }


    function end($Data)
    {
        if (!$Data["modform_close"]) {
            $Data["modform_close"] = "</table>";
        }
        $legend = View::make('global.topicsListLegend');
        return <<<EOF

{$Data["modform_close"]}
<div class="titlemedium b-search-topics-list-footer-row">&nbsp;</div>
</div>
<div class="b-list-pages-wrapper b-forum-list-pages b-forum-list-pages-bottom">{$Data[SHOW_PAGES]}</div>
<div class="b-legend-row-wrapper clearfix">
{$legend}
</div>
EOF;
    }


    function boolean_explain_page()
    {
        global $ibforums;
        return <<<EOF

<div class="tableborder">
 <div class="maintitle"><{CAT_IMG}>&nbsp;{$ibforums->lang["be_link"]}</div>
 <table width="100%" cellpadding="0" cellspacing="1">
 <tr>
  <th width="30%" class="pformstrip">{$ibforums->lang["be_use"]}</th>
  <th width="70%" class="pformstrip">{$ibforums->lang["be_means"]}</th>
 </tr>
 <tr>
  <td class="pformleft">{$ibforums->lang["be_u1"]}</td>
  <td class="pformleft">{$ibforums->lang["be_m1"]}</td>
 </tr>
 <tr>
  <td class="pformleft">{$ibforums->lang["be_u2"]}</td>
  <td class="pformleft">{$ibforums->lang["be_m2"]}</td>
 </tr>
 <tr>
  <td class="pformleft">{$ibforums->lang["be_u3"]}</td>
  <td class="pformleft">{$ibforums->lang["be_m3"]}</td>
 </tr>
 <tr>
  <td class="pformleft">{$ibforums->lang["be_u4"]}</td>
  <td class="pformleft">{$ibforums->lang["be_m4"]}</td>
 </tr>
 <tr>
  <td class="pformleft">{$ibforums->lang["be_u5"]}</td>
  <td class="pformleft">{$ibforums->lang["be_m5"]}</td>
 </tr>
 </table>
</div>

EOF;
    }


    function checkbox($data)
    {
        global $ibforums;
        return <<<EOF

<input type="checkbox" name="{$data["qid"]}{$data["id"]}" value="1" {$data["sh"]}>

EOF;
    }


    function start($Data, $button = "")
    {
        global $ibforums;
        return <<<EOF

<table class="b-forums-list-actions-row b-list-actions-row">
{$Data[SEARCH_DAYS]}
<tr>
 <td class="b-list-pages-wrapper">{$Data[SHOW_PAGES]}</td>
 <td class="b-list-actions-wrapper">{$Data[BUTTON]}</td>
</tr>
</table>
<div class="tableborder topics-wrapper b-search_results-topics-wrapper">
<div class="maintitle b-topics-list-title"><{CAT_IMG}><span class="e-topics-list-title-text">{$ibforums->lang["your_results"]}</span><span class="b-action-button"><a class="e-action-button-link" href="{$ibforums->base_url}act=Login&amp;CODE=05">{$ibforums->lang["mark_search_as_read"]}</a></span></div>
{$Data["MOD_CONTROL"]["modform_open"]}
<table class="tablebasic topics b-topics-list">
  <thead>
  <tr class="topics-header">
	 <th class="titlemedium b-topics-list-column-status">&nbsp;</th>
	 <th class="titlemedium b-topics-list-column-icon">&nbsp;</th>
	 <th class="titlemedium b-topics-list-column-title">{$ibforums->lang["h_topic_title"]}</th>
	 <th class="titlemedium b-topics-list-column-forum">{$ibforums->lang["h_forum_name"]}</th>
	 <th class="titlemedium b-topics-list-column-author">{$ibforums->lang["h_topic_starter"]}</th>
	 <th class="titlemedium b-topics-list-column-posts_num">{$ibforums->lang["h_replies"]}</th>
	 <th class="titlemedium b-topics-list-column-views_num">{$ibforums->lang["h_hits"]}</th>
	 <th class="titlemedium b-topics-list-column-last_post">{$ibforums->lang["h_last_action"]}</th>
	 {$Data["MOD_CONTROL"]["mod_column"]}
  </tr>
  </thead>
EOF;
    }


    function simple_form($forums, $search_txt = "", $where = "")
    {
        global $ibforums, $print;
        $print->js->addLocal('search.js');
        $print->js->addVariable('current_forum', Ibf::app()->input['f']);
        return <<<EOF
<form action="{$ibforums->base_url}act=Search&amp;CODE=simpleresults&amp;mode=simple" method="post" name="sForm">
<div class="tableborder">
  <div class="maintitle"  align="center">{$ibforums->lang["search_options"]}</div>
  <div class="pformstrip" align="center">{$ibforums->lang["key_search"]}</div>
  <div class="tablepad" align="center">
    <input type="text" maxlength="100" size="40" id="keywords" name="keywords" class="forminput">
	<br>
	<label><input type="checkbox" name="fulltext">Use fulltext engine</label>
	<br>
	<label for="keywords">{$search_txt}</label> <!--IBF.BOOLEAN_EXPLAIN-->
    {$where}
  </div>
  <div class="pformstrip" align="center">{$ibforums->lang["search_where"]}</div>
   <div class="tablepad" align="center">
    $forums
    <br><br>
    <strong>{$ibforums->lang["sf_show_me"]}</strong>
      <input type="radio" name="sortby" value="relevant" id="sortby_one" checked="checked" class="radiobutton">
      <label for="sortby_one">{$ibforums->lang["sf_most_r_f"]}</label>
      &nbsp;
      <input type="radio" name="sortby" value="date" id="sortby_two" class="radiobutton">
      <label for="sortby_two">{$ibforums->lang["sf_most_date"]}</label>
   </div>
  <div class="pformstrip" align="center">
    <input type="submit" value="{$ibforums->lang["do_search"]}" class="forminput">
    &nbsp;
    <input type="button" value="{$ibforums->lang["so_more_opts"]}" onclick="go_gadget_advanced()" class="forminput">
  </div>
</div>
</form>


EOF;
    }


//------------------------------
// Yandex and Google search form

    function alien_form($message = "")
    {
        global $ibforums;
        return <<<EOF

<div class="tableborder">
<div class="maintitle"  align="center">{$ibforums->lang["search_form"]}</div>

<table class="pformstrip" cellspacing=0 cellpadding=8 width="100%">
  <tr align="center">
   <td>
   <a href="http://www.yandex.ru" target="_blank" title="Яndex: Найдется ВСЁ!"><img src="/img/yandex.gif" border=0 width=76 height=48></a>
   </td>

   <td class=tableborder width=1><img src=/html/sys-img/blank.gif></td>

   <td width="50%">
   <a href="http://www.google.ru/webhp?hl=ru" target=_blank title="Google: а ничего и не терялось!"><img src="/img/google.gif" width=150 height=55 border=0 vspace=12></a>
   </td>
  </tr>
  <tr align="center">
   <td width="50%">
<FORM NAME="web" METHOD="get" ACTION="http://www.yandex.ru/yandsearch">
<INPUT TYPE="text" NAME="text" SIZE=40 VALUE="" MAXLENGTH=160>
<INPUT TYPE="hidden" NAME="serverurl" VALUE="forum.sources.ru">
<INPUT TYPE="hidden" NAME="server_name" VALUE="forum.sources.Ru">
<INPUT TYPE="hidden" NAME="referrer1" VALUE="http://forum.sources.ru/">
<INPUT TYPE="hidden" NAME="referrer2" VALUE="forum.sources.ru">
<INPUT TYPE=SUBMIT VALUE="Search">
</form>
   </td>
   <td class=tableborder width=1><img src=/html/sys-img/blank.gif></td>
   </td>
   <td>
<form name=gs method=GET action=http://www.google.ru/search>
<input type=hidden name=hl value="ru">
<input type=hidden name=as_sitesearch value="forum.sources.ru">
<input type=text name=q size=41 maxlength=2048 value="" title="">
<input type=submit name="btnG" value="Search">
</form>
   </td>
  </tr>
</table>
</div>
<br>
EOF;
    }




    function boardlay_between($data, $checkbox = "")
    {
        global $ibforums;
        return <<<EOF

<tr>
 <td class="{$data["css"]}">{$checkbox}</td>
 <td class="{$data["css"]}">{$data["sub"]}{$data["name"]}</td>
</tr>

EOF;
    }


    function boardlay_start()
    {
        global $ibforums;
        $action = "Search";
        if (preg_match("/act\=Select/", $_SERVER["REQUEST_URI"])) {
            $action = "Select";
        }

        return <<<EOF

	<div class="pformstrip">{$ibforums->lang["boardlay_title"]}</div>
	<div align="center" class="tableborder">
	<form action="{$ibforums->base_url}" name="forums_select" method="post">
	<input type="hidden" name="act" value="{$action}">
	<input type="hidden" name="CODE" value="03">
		<table width="100%" cellspacing="1" cellpadding="4">
		<tr>
			<td class="titlemedium" width="10%" align="center">{$ibforums->lang["boardlay_sh"]}</td>
			<td class="titlemedium" width="90%" align="center">{$ibforums->lang["boardlay_catfor"]}</td>
		</tr>
		<tr><td colspan="2" id="submenu"><center>{$ibforums->lang["boardlay_note"]}</center></td></tr>

EOF;
    }


    function boardlay_end()
    {
        global $ibforums;
        return <<<EOF

		<tr><td class="pformstrip" colspan="2" align="center"><input type="submit" class="forminput" value="{$ibforums->lang["submit"]}"></td></tr>
		</table>
		</form>
	</div>

EOF;
    }


    function boardlay_successful()
    {
        global $ibforums;
        return <<<EOF

<div id="submenu"><center>{$ibforums->lang["boardlay_successful"]}</center></div>
<center><a href="javascript:self.close();">Закрыть окно</a></center>


EOF;
    }


    function form_simple_button()
    {
        global $ibforums;
        return <<<EOF

&nbsp;<input type="button" value="{$ibforums->lang["so_less_opts"]}" onclick="go_gadget_simple()" class="forminput">

EOF;
    }


    function active_start($data)
    {
        global $ibforums, $print;
        $print->exportJSLang(['active_js_error']);
        return <<<EOF
<br>
<form action="{$ibforums->base_url}act=Search&amp;CODE=getactive" method="post" name="dateline" onsubmit="return checkvalues();">
<div class="pagelinks">{$data["SHOW_PAGES"]}</div>
<div align="right" style="width:35%;text-align:center;margin-right:0;margin-left:auto">
 <fieldset class="search">
   <legend><strong>{$ibforums->lang["active_st_text"]}</strong></legend>
   <label for="st_day">{$ibforums->lang["active_mid_text"]}</label>&nbsp;
   <select name="st_day" id="st_day" class="forminput">
	<option value="s1">{$ibforums->lang["active_yesterday"]}</option>
	<option value="s2">2 {$ibforums->lang["active_days"]}</option>
	<option value="s3">3 {$ibforums->lang["active_days"]}</option>
	<option value="s4">4 {$ibforums->lang["active_days"]}</option>
	<option value="s5">5 {$ibforums->lang["active_days"]}</option>
	<option value="s6">6 {$ibforums->lang["active_days"]}</option>
	<option value="s7">{$ibforums->lang["active_week"]}</option>
	<option value="s30">{$ibforums->lang["active_month"]}</option>
   </select>
   &nbsp;
   <label for="end_day">{$ibforums->lang["active_end_text"]}</label>&nbsp;
   <select name="end_day" id="end_day" class="forminput">
	<option value="e0">{$ibforums->lang["active_today"]}</option>
	<option value="e1">{$ibforums->lang["active_yesterday"]}</option>
	<option value="e2">2 {$ibforums->lang["active_days"]}</option>
	<option value="e3">3 {$ibforums->lang["active_days"]}</option>
	<option value="e4">4 {$ibforums->lang["active_days"]}</option>
	<option value="e5">5 {$ibforums->lang["active_days"]}</option>
	<option value="e6">6 {$ibforums->lang["active_days"]}</option>
	<option value="e7">{$ibforums->lang["active_week"]}</option>
   </select>
   &nbsp;
   <input type="submit" value="&gt;&gt;" title="{$ibforums->lang["active_label"]}" class="forminput">
 </fieldset>
</div>
</form>
<br>
<div class="tableborder">
  <div class="maintitle"><{CAT_IMG}>{$ibforums->lang["active_topics"]}</div>
  <table class="tablebasic" cellspacing="1" cellpadding="4">
	<tr>
	   <td class="titlemedium" colspan="2" >&nbsp;</td>
	   <th align="left" class="titlemedium">{$ibforums->lang["h_topic_title"]}</th>
	   <th align="center" class="titlemedium">{$ibforums->lang["h_forum_name"]}</th>
	   <th align="center" class="titlemedium">{$ibforums->lang["h_topic_starter"]}</th>
	   <th align="center" class="titlemedium">{$ibforums->lang["h_replies"]}</th>
	   <th align="center" class="titlemedium">{$ibforums->lang["h_hits"]}</th>
	   <th class="titlemedium">{$ibforums->lang["h_last_action"]}</th>
	</tr>

EOF;
    }

    function start_search_days()
    {
        global $ibforums;
        return <<<EOF

<tr>
 <td class="b-search-filter-wrapper">
  <form action="{$ibforums->base_url}" method="get" class="b-search-filter">
  <input type="hidden" name="act" value="Search">
  <input type="hidden" name="CODE" value="change_days">
  <input type="hidden" name="CODE_MODE" value="{$ibforums->input["CODE_MODE"]}">
  <select name="search_days" class="forminput">

EOF;
    }

    function end_search_days()
    {
        global $ibforums;
        return <<<EOF

 </select>
 <input type="submit" value="{$ibforums->lang["do_search"]}" class="forminput form-submit">
 </form>
 </td>
</tr>

EOF;
    }


    function search_days($days, $title, $check = "")
    {
        return <<<EOF

<option value="$days"$check>$title</option>

EOF;
    }
}
