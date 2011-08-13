<?php

require_once "Attach.php";

class Attach3 extends Attach2
{
	function assignFromArray($arr)
	{
		if(isset($arr['aid'])){
			$this->setAttachId($arr['aid']);
		}
		if(isset($arr['pid'])){
			$this->setPostId($arr['pid']);
		}
		if(isset($arr['fn'])){
			$this->setFilename($arr['fn']);
		}
		if(isset($arr['r_fn'])){
			$this->setRealFilename($arr['r_fn']);
		}
		if(isset($arr['size'])){
			$this->setSize($arr['size']);
		}
		if(isset($arr['type'])){
			$this->setType($arr['type']);
		}
		if(isset($arr['hits'])){
			$this->setHits($arr['hits']);
		}
	}

	function getLink()
	{
		global $ibforums;
		return ($this->getImageOfType())."<a href='".($ibforums->base_url)."act=Attach&amp;type=post&amp;id=".($this->postId())."&amp;attach_id=".($this->attachId())."' title='Скачать файл' target='_blank'>".($this->filename())."</a>";
	}
}

class Att 
{
	var $html = "";
	var $pt = "";
	
	function Att()
	{
		global $ibforums, $DB, $std;
		
		if (isset($ibforums->input['mid']))
		{
			if(!intval($ibforums->input['mid']))
			{
				echo "Error! Invalid user's id!";
				exit;
			}
			else
			{
				$mid = intval($ibforums->input['mid']);
			}
		}
				
		if(isset($ibforums->input['st']))
		{
			$st = intval($ibforums->input['st']);
		}
		else
		{
			$st = 0;
		}		
		
		$forumid = NULL;
		if(isset($ibforums->input['forumid']))
		{
			$forumid = intval($ibforums->input['forumid']);
			$where_str = " AND t2.forum_id = ".$forumid;
		}
		
		$topicid = NULL;
		if(isset($ibforums->input['topicid']))
		{
			$topicid = intval($ibforums->input['topicid']);
			$where_str = " AND t2.topic_id = ".$topicid;
		}

		define('COUNT', 25);
		
		$DB->query("
			SELECT DISTINCTROW
			count(*) AS cc
			FROM ibf_post_attachments AS t,
				ibf_posts AS t2
			WHERE t2.author_id = ".$mid.$where_str." "."
				AND t.post_id = t2.pid");
			
		$count = $DB->fetch_row();
		
		$DB->free_result();
		
		$count = intval($count['cc']);
				
		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_global', $ibforums->lang_id);
		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_topic', $ibforums->lang_id);
		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_att', $ibforums->lang_id);
		
		$DB->query("
			SELECT name
			FROM ibf_members
			WHERE id = ".$mid);
		$username = $DB->fetch_row();	
		
		$DB->free_result();
		
		$username = strval($username['name']);
		
		$this->pt = sprintf($ibforums->lang['title'], $username);
		
		if($count == 0)
		{
			$this->html = sprintf($ibforums->lang['error_has_not'], $username);
			return 0;
		}
		
				if(($st < 0) || ($st >= $count))   
		{
			echo "Error! Invalid page number!";
			exit();
		}
					
		$sort = 0;
		if (isset($ibforums->input['sort']))
		{
			switch($ibforums->input['sort'])
			{
				case "date" :
					$sort = 0;
					break;
				case "filename" :
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
		
		$desc = 0;
		if(isset($ibforums->input['desc']))
		{
			if($ibforums->input['desc'] == 1)
			{
				$desc = 1;
			}
		}
		
		$sort_str = array("pdate", "fn", "size", "hits" ,"pid", "ttitle", "fname");
		$link_sort_str = array("date", "filename", "size", "hits", "post", "topic", "forum");		
		
		$q = "SELECT DISTINCTROW
			t1.attach_id AS aid,
			t1.post_id AS pid,
			t1.filename AS fn,
			t1.type AS type,
			t1.size AS size,
			t1.hits AS hits,
			t2.post_date AS pdate,
			t2.topic_id AS tid,
			t2.forum_id AS fid,
			t3.title AS ttitle,
			t4.name AS fname
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
			t2.author_id = ".$mid.$where_str." ORDER BY ".($sort_str[$sort]).($desc == 1 ? " DESC" : "")." LIMIT ".$st.", ".COUNT;
			
		$DB->query($q);
		
		//output:		
		
		$temp_link = $ibforums->base_url."act=Select&CODE=getalluseratt&amp;mid=".$mid.(($topicid != NULL) ? "&amp;topicid=".$topicid : "").(($forumid != NULL) ? "&amp;forumid=".$forumid : "");
		$temp_link_2 = $temp_link."&amp;sort=".$link_sort_str[$sort]."&amp;desc=".$desc;
		
		//make page's buttons
		
		$pages_list = "<div><a title='".$ibforums->lang['tpl_jump']."' href='javascript:multi_page_jump(&quot;".$temp_link_2."&quot;, ".$count.", ".COUNT.");'>".$ibforums->lang['tpl_pages']."</a> (".intval(ceil($count / COUNT)).")&nbsp;";
		if($st >= COUNT)
		{
			$pages_list .= "<a href='".$temp_link_2."&amp;st=0'>".$ibforums->lang['ps_first']."</a>&nbsp;";
			$pages_list .= "<a href='".$temp_link_2."&amp;st=".($st - COUNT)."'>".$ibforums->lang['ps_previous']."</a>&nbsp;";
		}
		$pages_list .= intval(ceil(($st + 1) / COUNT));
		if($st + COUNT < $count)
		{
			$pages_list .= "&nbsp;<a href='".$temp_link_2."&amp;st=".($st + COUNT)."'>".$ibforums->lang['ps_next']."</a>&nbsp;";
			$pages_list .= "<a href='".$temp_link_2."&amp;st=".(intval(ceil($count / COUNT)) - 1) * COUNT."'>".$ibforums->lang['ps_last']."</a>";
		}

		//make table:
		
		$this->html = $pages_list;
		
		$this->html .= "<div class='tableborder'>";
		
		$this->html .= "<div class='maintitle'><img src='style_images/1/nav_m.gif' alt='&gt;' border='0'>&nbsp;".$this->pt."</div>";
		
		$this->html .= "<table width='100%' cellpadding='2' cellspacing='1' class='tablebasic'>
			<tr>
			<td align='center' class='titlemedium'><a href='".$temp_link."&amp;st=".$st."&amp;sort=filename".(($sort == 1) ? ($desc ? "'>&#9650;" : "&amp;desc=1'>&#9660") : "'>").$ibforums->lang['link']."</a></td>
			<td align='center' class='titlemedium'><a href='".$temp_link."&amp;st=".$st."&amp;sort=size".(($sort == 2) ? ($desc ? "'>&#9650;" : "&amp;desc=1'>&#9660") : "'>").$ibforums->lang['size']."</a></td>
			<td align='center' class='titlemedium'><a href='".$temp_link."&amp;st=".$st."&amp;sort=hits".(($sort == 3) ? ($desc ? "'>&#9650;" : "&amp;desc=1'>&#9660") : "'>").$ibforums->lang['hits']."</a></td>
			<td align='center' class='titlemedium'><a href='".$temp_link."&amp;st=".$st."&amp;sort=date".(($sort == 0) ? ($desc ? "'>&#9650;" : "&amp;desc=1'>&#9660") : "'>").$ibforums->lang['date']."</a></td>
			<td align='center' class='titlemedium'><a href='".$temp_link."&amp;st=".$st."&amp;sort=post".(($sort == 4) ? ($desc ? "'>&#9650;" : "&amp;desc=1'>&#9660") : "'>").$ibforums->lang['post']."</a></td>
			<td align='center' class='titlemedium'><a href='".$temp_link."&amp;st=".$st."&amp;sort=topic".(($sort == 5) ? ($desc ? "'>&#9650;" : "&amp;desc=1'>&#9660") : "'>").$ibforums->lang['topic']."</a></td>
			<td align='center' class='titlemedium'><a href='".$temp_link."&amp;st=".$st."&amp;sort=forum".(($sort == 6) ? ($desc ? "'>&#9650;" : "&amp;desc=1'>&#9660") : "'>").$ibforums->lang['forum']."</a></td>
			</tr>
			";
		
		$attfile = new Attach3;				
		
		$countrec = $DB->get_num_rows() >= COUNT ? COUNT : $DB->get_num_rows();		
		for ($i = 0; $i < $countrec; $i++)
		{
			$res = $DB->fetch_row();
			
			$attfile->assignFromArray($res);
			
			$this->html .= "
						   <tr><td class='row4'>".($attfile->getLink())."</td>
			               <td class='row4'>".$attfile->sizeAsString()."</td>
			               <td class='row4'>".($res['hits'])."</td>
			               <td class='row4'>".date("d.m.Y", $res['pdate'])."</td>
			               <td class='row4'><a href='".$ibforums->base_url."showtopic=".$res['tid']."&amp;view=findpost&amp;p=".$res['pid']."'>".$res['pid']."</a></td>
			               <td class='row4'><a href='".$ibforums->base_url."act=Select&amp;CODE=getalluseratt&amp;mid=".$mid."&amp;topicid=".$res['tid']."&amp;sort=".$link_sort_str[$sort].(($desc == 1) ? "&amp;desc=1" : "")."'>".$ibforums->lang['attchments_in']."<a href='".$ibforums->base_url."showtopic=".$res['tid']."'>".$res['ttitle']."</a></td>
			               <td class='row4'><a href='".$ibforums->base_url."act=Select&amp;CODE=getalluseratt&amp;mid=".$mid."&amp;forumid=".$res['fid']."&amp;sort=".$link_sort_str[$sort].(($desc == 1) ? "&amp;desc=1" : "")."'>".$ibforums->lang['attchments_in']."<a href='".$ibforums->base_url."showforum=".$res['fid']."'>".$res['fname']."</a></td></tr>";
		}
		
		$this->html .= "</table></div>";
		
		$this->html .= $pages_list;		
		
		$DB->free_result();
	}
}
