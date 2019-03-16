<?php

class Att
{
    var $html = "";
    var $pt = "";

    function __construct()
    {
        global $std;
        $ibforums = Ibf::app();

        if (isset($ibforums->input['mid'])) {
            if (!intval($ibforums->input['mid'])) {
                echo "Error! Invalid user's id!";
                exit;
            } else {
                $mid = intval($ibforums->input['mid']);
            }
        }

        if (isset($ibforums->input['st'])) {
            $st = intval($ibforums->input['st']);
        } else {
            $st = 0;
        }
        $params = [];
        $forumid = null;
        $where_str = '';
        if (isset($ibforums->input['forumid'])) {
            $forumid = intval($ibforums->input['forumid']);
            $where_str .= " AND t2.forum_id = :forum_id ";
            $params[':forum_id'] = $forumid;
        }

        $topicid = null;
        if (isset($ibforums->input['topicid'])) {
            $topicid = intval($ibforums->input['topicid']);
            $where_str .= " AND t2.topic_id = :topic_id ";
            $params[':topic_id'] = $topicid;
        }

        $count_on_page = 25;

        $stmt = $ibforums->db->prepare("
			SELECT DISTINCTROW
			count(*) AS cc
			FROM ibf_post_attachments AS t,
				ibf_posts AS t2
			WHERE t2.author_id = :mid " . $where_str . "
			AND t.post_id = t2.pid");
        $params[':mid'] = (int)$mid;//todo $mid can be not initialized
        $stmt->execute($params);

        $count = (int)$stmt->fetchColumn();

        unset($stmt);

        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_global', $ibforums->lang_id);
        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_topic', $ibforums->lang_id);
        $ibforums->lang = $std->load_words($ibforums->lang, 'lang_att', $ibforums->lang_id);

        $username = $ibforums->db->prepare("
			SELECT name
			FROM ibf_members
			WHERE id = :mid")
            ->bindParam(':mid', $mid, PDO::PARAM_INT)
            ->execute()
            ->fetchColumn();

        $this->pt = sprintf($ibforums->lang['title'], $ibforums->lang['attachments'], $username);

        if ($count == 0) {
            $this->html = sprintf($ibforums->lang['error_has_not'], $username);
            return 0;
        }

        if (($st < 0) || ($st >= $count)) {
            echo "Error! Invalid page number!";
            exit();
        }

        $sort = 0;
        if (isset($ibforums->input['sort'])) {
            switch ($ibforums->input['sort']) {
                case "date":
                    $sort = 0;
                    break;
                case "filename":
                    $sort = 1;
                    break;
                case "size":
                    $sort = 2;
                    break;
                case "hits":
                    $sort = 3;
                    break;
                case "post":
                    $sort = 4;
                    break;
                case "topic":
                    $sort = 5;
                    break;
                case "forum":
                    $sort = 6;
                    break;
            }
        }

        $desc = $sort === 0 ? 1 : 0;
        if (isset($ibforums->input['desc'])) {
            if ($ibforums->input['desc'] == 1) {
                $desc = 1;
            } else {
                $desc = 0;
            }
        }

        $sort_str = array("pdate", "filename", "size", "hits" ,"pid", "ttitle", "fname");
        $link_sort_str = array("date", "filename", "size", "hits", "post", "topic", "forum");

        $q = "SELECT DISTINCTROW
			t1.attach_id	AS	attach_id,
			t1.post_id		AS	post_id,
			t1.filename		AS	filename,
			t1.type			AS	type,
			t1.size			AS	size,
			t1.hits			AS	hits,
			t2.post_date	AS	pdate,
			t2.topic_id		AS	tid,
			t2.forum_id		AS	fid,
			t3.title		AS	ttitle,
			t4.name			AS	fname
			FROM
			ibf_posts As t2
			JOIN
				ibf_post_attachments AS t1
				ON t1.post_id = t2.pid
			JOIN
				ibf_topics AS t3
				ON t3.tid = t2.topic_id
			JOIN
				ibf_forums AS t4
				ON t4.id = t2.forum_id
			WHERE
			t2.author_id = :mid " . $where_str . " ORDER BY " . ($sort_str[$sort]) . ($desc == 1 ? " DESC" : "") . " LIMIT " . $st . ", " . $count_on_page;

        $stmt = $ibforums->db->prepare($q);
        $stmt->execute($params);

        //output:

        $base_link = $ibforums->base_url . "act=Select&CODE=getalluseratt&amp;mid=" . $mid;
        $location_vars = (($topicid != null) ? "&amp;topicid=" . $topicid : "") . (($forumid != null) ? "&amp;forumid=" . $forumid : "");
        $view_vars = "&amp;sort=" . $link_sort_str[$sort] . "&amp;desc=" . $desc;

        //make page's buttons

        /*$pages_list = "<div><a title='".$ibforums->lang['tpl_jump']."' href='javascript:multi_page_jump(&quot;".$base_link.$location_vars.$view_vars."&quot;, ".$count.", ".$count_on_page.");'>".$ibforums->lang['tpl_pages']."</a> (".intval(ceil($count / $count_on_page)).")&nbsp;";
        if($st >= $count_on_page)
        {
            $pages_list .= "<a href='".$base_link.$location_vars.$view_vars."&amp;st=0'>".$ibforums->lang['ps_first']."</a>&nbsp;";
            $pages_list .= "<a href='".$base_link.$location_vars.$view_vars."&amp;st=".($st - $count_on_page)."'>".$ibforums->lang['ps_previous']."</a>&nbsp;";
        }
        $pages_list .= intval(ceil(($st + 1) / $count_on_page));
        if($st + $count_on_page < $count)
        {
            $pages_list .= "&nbsp;<a href='".$base_link.$location_vars.$view_vars."&amp;st=".($st + $count_on_page)."'>".$ibforums->lang['ps_next']."</a>&nbsp;";
            $pages_list .= "<a href='".$base_link.$location_vars.$view_vars."&amp;st=".(intval(ceil($count / $count_on_page)) - 1) * $count_on_page."'>".$ibforums->lang['ps_last']."</a>";
        } */

        $pages_list = $std->build_pagelinks(array(

                            'TOTAL_POSS'  => $count,
                            'PER_PAGE'    => $count_on_page,
                            'CUR_ST_VAL'  => $st,
                            'L_SINGLE'    => "",
                            'L_MULTI'     => $ibforums->lang['search_pages'],
                            'BASE_URL'    => $base_link . $location_vars . $view_vars,

        ));
        //make table:
        //title:
        $this->html = $pages_list;

        $this->html .= "<div class='tableborder'>";

        $this->html .= "<div class='maintitle'><img src='style_images/1/nav_m.gif' alt='&gt;' border='0'>&nbsp;" . sprintf($ibforums->lang['title'], ("<a href='" . $base_link . $view_vars . "'>" . $ibforums->lang['attachments'] . "</a>"), ("<a href='{$ibforums->base_url}showuser={$mid}'>" . $username . "</a>")) . "</div>";

        $this->html .= "<table width='100%' cellpadding='2' cellspacing='1' class='tablebasic'>
			<tr>
			<td align='center' class='titlemedium'><a href='" . $base_link . $location_vars . "&amp;st=" . $st . "&amp;sort=filename" . (($sort == 1) ? ($desc ? "'>&#9650;" : "&amp;desc=1'>&#9660") : "'>") . $ibforums->lang['link'] . "</a></td>
			<td align='center' class='titlemedium'><a href='" . $base_link . $location_vars . "&amp;st=" . $st . "&amp;sort=size" . (($sort == 2) ? ($desc ? "&amp;desc=0'>&#9650;" : "&amp;desc=1'>&#9660") : "'>") . $ibforums->lang['size'] . "</a></td>
			<td align='center' class='titlemedium'><a href='" . $base_link . $location_vars . "&amp;st=" . $st . "&amp;sort=hits" . (($sort == 3) ? ($desc ? "&amp;desc=0'>&#9650;" : "&amp;desc=1'>&#9660") : "'>") . $ibforums->lang['hits'] . "</a></td>
			<td align='center' class='titlemedium'><a href='" . $base_link . $location_vars . "&amp;st=" . $st . "&amp;sort=date" . (($sort == 0) ? ($desc ? "&amp;desc=0'>&#9650;" : "&amp;desc=1'>&#9660") : "'>") . $ibforums->lang['date'] . "</a></td>
			<td align='center' class='titlemedium'><a href='" . $base_link . $location_vars . "&amp;st=" . $st . "&amp;sort=post" . (($sort == 4) ? ($desc ? "&amp;desc=0'>&#9650;" : "&amp;desc=1'>&#9660") : "'>") . $ibforums->lang['post'] . "</a></td>
			<td align='center' class='titlemedium'><a href='" . $base_link . $location_vars . "&amp;st=" . $st . "&amp;sort=topic" . (($sort == 5) ? ($desc ? "&amp;desc=0'>&#9650;" : "&amp;desc=1'>&#9660") : "'>") . $ibforums->lang['topic'] . "</a></td>
			<td align='center' class='titlemedium'><a href='" . $base_link . $location_vars . "&amp;st=" . $st . "&amp;sort=forum" . (($sort == 6) ? ($desc ? "&amp;desc=0'>&#9650;" : "&amp;desc=1'>&#9660") : "'>") . $ibforums->lang['forum'] . "</a></td>
			</tr>
			";

        //main body:

        $attfile = new Attachment();

        $countrec = $stmt->rowCount() >= $count_on_page ? $count_on_page : $stmt->rowCount();
        for ($i = 0; $i < $countrec; $i++) {
            $res = $stmt->fetch();

            $attfile = Attachment::createFromRow($res);

            $this->html .= "
						   <tr><td class='row4'>" . ($attfile->getLink()) . "</td>
			               <td class='row4'>" . $attfile->sizeAsString() . "</td>
			               <td class='row4'>" . ($res['hits']) . "</td>
			               <td class='row4'>" . date("d.m.Y", $res['pdate']) . "</td>
			               <td class='row4'><a href='" . $ibforums->base_url . "showtopic=" . $res['tid'] . "&amp;view=findpost&amp;p=" . $res['post_id'] . "'>" . $res['post_id'] . "</a></td>
			               <td class='row4'><a href='" . $base_link . $view_vars . "&amp;topicid=" . $res['tid'] . "'>" . $ibforums->lang['attchments_in'] . "<a href='" . $ibforums->base_url . "showtopic=" . $res['tid'] . "'>" . $res['ttitle'] . "</a></td>
			               <td class='row4'><a href='" . $base_link . $view_vars . "&amp;forumid=" . $res['fid'] . "'>" . $ibforums->lang['attchments_in'] . "<a href='" . $ibforums->base_url . "showforum=" . $res['fid'] . "'>" . $res['fname'] . "</a></td></tr>";
        }

        $this->html .= "</table></div>";

        $this->html .= $pages_list;

        unset($stmt);
    }
}
