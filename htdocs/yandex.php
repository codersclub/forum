<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Getta Yur Mikhail (Song)
|   (c) 2004 - 2005 Sources.RU
|   http://www.sources.ru
|   ========================================
|   Web: http://forum.sources.ru
|   Email: song@sources.ru
|   Licence Info: Copyright 2005 (c) Song
+---------------------------------------------------------------------------
|
|   RSS output Script File for Invision Power Board
|   To use it, look at description http://forum.sources.ru/index.php?showtopic=81342
|
|   THIS IS NOT FREEWARE PRODUCT. If you have this one, you buy it or you are friend of a author :)
|   DO NOT DISTRIBUTE.
|   You can donate some money to author of script: WM R252761111989, Z845880689560
+--------------------------------------------------------------------------
*/

//-----------------------------------------------
// USER CONFIGURABLE ELEMENTS
//-----------------------------------------------


//-----------------------------------------------
// NO USER EDITABLE SECTIONS BELOW
//-----------------------------------------------



require __DIR__ . '/../app/bootstrap.php';



//--------------------------------
// The clocks a' tickin'
//--------------------------------

$Debug = Debug::instance();
$Debug->startTimer();

//--------------------------------
// Load the DB driver and such
//--------------------------------


try {
    Ibf::registerApplication(new RssApplication());
    $ibforums       = Ibf::app();
    //stub delete after all
    $std = &$ibforums->functions;
    $sess = &$ibforums->session;
    //
    $ibforums->init();

    //--------------------------------

    $std->flood_begin();

    // cats
    $categories = array();

    if ($ibforums->input['c']) {
        $categories = explode(",", $ibforums->input['c']);
        foreach ($categories as $idx => $cat) {
            $categories[ $idx ] = intval($cat);
        }
    }

    // forums
    $forums = array();

    if (!count($categories)) {
        if ($ibforums->input['f']) {
            $forums = explode(",", $ibforums->input['f']);
            foreach ($forums as $idx => $forum) {
                $forums[ $idx ] = intval($forum);
            }
        }
    }

    // tids
    $tids = array();

    if ($ibforums->input['t']) {
        $tids = explode(",", $ibforums->input['t']);
        foreach ($tids as $idx => $tid) {
            $tids[ $idx ] = intval($tid);
        }
    }

    // pids
    $pids = array();

    if ($ibforums->input['p']) {
        $pids = explode(",", $ibforums->input['p']);
        foreach ($pids as $idx => $pid) {
            $pids[ $idx ] = intval($pid);
        }
    }

    $ibforums->base_url = $ibforums->vars['board_url'] . '/index.' . $ibforums->vars['php_ext'] . '?';

    //----------------------------------------
    // Header parse...
    //----------------------------------------

    $template = "
      <item>
        <guid isPermaLink='true'>{post_url}</guid>
        <pubDate>{rfc_date}</pubDate>
        <title>{thread_title}</title>
        <link>{post_url}</link>
        <description><![CDATA[{author}: {text}]]></description>
        <author>{author}</author>
        <category>{forum_name}</category>
      </item>
	";

    $to_echo = "<?xml version='1.0' encoding=\"utf-8\"?>
      <rss version='2.0'>
      <channel>
      <title>Форум на Исходниках.RU</title>
      <link>http://forum.sources.ru</link>
      <description>Форум на Исходниках.RU</description>
      <generator>Форум на Исходниках.RU</generator>
  	";

    $mask = array();
    $frms = array();

    $stmt = $ibforums->db->query("SELECT id, parent_id, read_perms, password, status, name, category FROM ibf_forums");

    while ($row = $stmt->fetch()) {
        if ($row['password'] or $std->check_perms($row['read_perms']) != true or !$row['status']) {
            $mask[ $row['id'] ] = $row['id'];
        }

        $frms[ $row['id'] ] = $row['name'];

        if (in_array($row['category'], $categories) or ( $ibforums->input['view'] == 'sub' and in_array($row['parent_id'], $forums) )) {
            $forums[] = $row['id'];
        }
    }

    if (count($categories) and !count($forums)) {
        fatal_error("Не найдено разделов для категорий cat_id=" . implode(",", $categories));
    }

    $query = "SELECT pid, author_name, post_date, forum_id, topic_id, author_id, post, use_sig, queued FROM ibf_posts ";

    $query_last = "";
    $params = [];

    if (count($mask)) {
        $query_last .= "not (forum_id IN(" . IBPDO::placeholders($mask) . ")) and ";
        $params = array_merge($params, (array)$mask);
    }

    if (count($pids)) {
        $query_last .= "pid IN(" . IBPDO::placeholders($pids) . ") and ";
        $params = array_merge($params, (array)$pids);
    }

    if (count($tids)) {
        $query_last .= "topic_id IN (" . IBPDO::placeholders($tids) . ") and ";
        $params = array_merge($params, (array)$tids);
    }

    if (count($forums)) {
        $query_last .= "forum_id IN (" . IBPDO::placeholders($forums) . ") and post_date > (?-60*60*24*5) and ";
        $params = array_merge($params, (array)$forums);
        $params[] = time();
    }

    if ($query_last) {
        $query_last = mb_substr($query_last, 0, mb_strlen($query_last) - 4);

        $query .= "WHERE " . $query_last;
    }

    $query .= "ORDER BY pid DESC LIMIT 75";

    $stmt = $ibforums->db->prepare($query);
    $stmt->execute($params);

    if (!$stmt->rowCount()) {
        $std->flood_end();

        fatal_error("Запрос не вернул результатов. Проверьте правильность аргументов вызова.");
    }

    unset($tids);
    unset($forums);

    $posts  = array();
    $tids   = array();

    foreach ($stmt as $row) {
        // if access denied
        if (isset($mask[ $row['forum_id'] ]) or $row['use_sig'] or $row['queued']) {
            continue;
        }

        // store post
        $posts[] = $row;

        // store topic id
        $tids[ $row['topic_id'] ] = $row['topic_id'];
    }

    // querying title of topics and club property
    if (count($tids)) {
        $stmt = $ibforums->db->prepare("SELECT tid,title,club FROM ibf_topics WHERE tid IN (" . IBPDO::placeholders($tids) . ")");
        $stmt->execute(array_values($tids));

        foreach ($stmt as $topic) {
            if ($topic['club'] and $std->check_perms($ibforums->member['club_perms']) != false) {
                $topic['club'] = 0;
            }

            $tids[ $topic['tid'] ] = $topic;
        }
    }

    $p = 0;

    if (count($posts)) {
        $parser = new PostParser(1);
        $parser->rss_mode = true;
        foreach ($posts as $post) {
            // if we cannot see club topics
            if (!$tids[ $post['topic_id'] ]['title'] or $tids[ $post['topic_id'] ]['club']) {
                continue;
            }

                        $txt           = $parser->prepare(
                            array(
                            'TEXT'          => $post['post'],
                            'SMILIES'       => 1,
                            'CODE'          => 1,
                            'SIGNATURE'     => 0,
                            'HTML'          => 1,
                            'HID'         => -1,
                            'TID'         => $post['topic_id'],
                             )
                        );
            if (!trim($txt)) {
                continue;
            }

            $p++;
            $thread_title      = preg_replace("'&'si", "&amp;", $tids[ $post['topic_id'] ]['title']);

            $author = $post['author_name'];
            $author = str_replace('<', '&lt;', $author);
            $author = str_replace('>', '&gt;', $author);
                    $author = str_replace("\r", "", $author); // \015 = 13 = 0x0D
                    $author = preg_replace('#[\000-\010]#', " ", $author);
                    $author = preg_replace('#[\013-\037]#', " ", $author);

            $author = trim(stripslashes($author));


            $to_echo      .= parse_template(
                $template,
                array (
                    'thread_url'    => $ibforums->base_url . "act=ST&f=" . $post['forum_id'] . "&t=" . $post['topic_id'] . "&hl=&#entry" . $post['pid'],
                    'thread_title'  => $thread_title,
                    'forum_url'     => $ibforums->base_url . "act=SF&f=" . $post['forum_id'],
                    'topic_id'  => $post['topic_id'],
                    'post_url'      => $ibforums->base_url . "showtopic=" . $post['topic_id'] . "&amp;view=findpost&amp;p=" . $post['pid'],
                    'forum_name'    => $frms[ $post['forum_id'] ],
                    'date'          => $std->old_get_date($post['post_date'], 'LONG'),
                    'rfc_date'      => date('r', $post['post_date']),
                    'author'        => $author,
                    'text'          => $txt,
                    'profile_link'  => $ibforums->base_url . "act=Profile&CODE=03&MID=" . $post['author_id']
                )
            );
        }
    }

    $std->flood_end();

    if (!$p) {
        fatal_error("Запрос не вернул результатов. Проверьте правильность аргументов вызова.");
    }

    $to_echo  .= "
      </channel>
      </rss>
	";

    @header('Content-Type: text/xml');
    @header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    @header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    @header('Pragma: public');

    if ($ibforums->vars['disable_gzip'] != 1) {
        $buffer = ob_get_contents();
        ob_end_clean();
        ob_start('ob_gzhandler');
        print $buffer;
    }

    print $to_echo;
} catch (exception $e) {
    fatal_error("Невозможно соединиться с БД.");
}

exit();


//+-------------------------------------------------
// GLOBAL ROUTINES
//+-------------------------------------------------

function parse_template($template, $assigned = array())
{

    foreach ($assigned as $word => $replace) {
        $template = preg_replace("/\{$word\}/i", "$replace", $template);
    }

    return $template;
}



function fatal_error($message = "")
{

    echo($message);

    exit();
}
