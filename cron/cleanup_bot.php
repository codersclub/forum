<?php
require __DIR__ . '/../app/bootstrap.php';

Ibf::registerApplication(new ConsoleApplication());
$ibforums = Ibf::app();

Debug::instance()->startTimer();

$ibforums->init();

$time = time();

$body	 = "";

$rows_affected = 0;
$totalrecords  = 0;

$body .= '---------------------------------------------------------
Forum Cron Tasks for IBF v1.2
written by Valery Votintsev <admin@sources.ru>, http://www.sources.ru/
---------------------------------------------------------
';

$body .= "Start time:  ".timetostr($time)."\n";



#############################################################
#############################################################
# Connect to the DataBase....

$body .= "Connecting to the DataBase... ";
/*
my $dbh = DBI->connect("DBI:mysql:database=$db_name;
host=$db_host;
port=$db_port",
$db_user,
$db_password,
{'RaiseError'=>1 }
);
 */
$body .= "ok\n";
$body .= "---------------------------------------------------------\n";


$task_list = [
	[
		'TITLE'		=> "1. Remove Topics Marked as Moved (older 7 days):\n",
		'SELECT'	=> '', ###"SELECT tid, title FROM ibf_topics WHERE state='link' and link_time < $age",
		'DELETE'	=> 'DELETE FROM ibf_topics WHERE state=\'link\' and link_time < $age LIMIT 50',
		'DTL'		=> 7
	],
	[
		'TITLE'		=> "2. Remove old search queries (older 24 hours):\n",
		'SELECT'	=> '', ###"SELECT tid, title FROM ibf_topics WHERE state='link' and link_time < $age",
		'DELETE'	=> 'DELETE FROM ibf_search_results WHERE search_date < $age LIMIT 1000',
		'DTL'		=> 1
	],
	[
		'TITLE'		=> "3. Remove topic visit logs (older 30 days ):\n",
		'SELECT'	=> '',
		'DELETE'	=> 'DELETE FROM ibf_log_topics WHERE NOT (tid IN (SELECT tid FROM ibf_topics WHERE pinned=1 and approved=1)) AND logTime<$age LIMIT 1000',
		'DTL'		=> 30
	],
	[
		'TITLE'		=> "4. Clear posts edit history (items older 180 days ):\n",
		'SELECT'	=> '',
		'DELETE'	=> 'DELETE FROM ibf_post_edit_history WHERE edit_time < $age LIMIT 1000',
		'DTL'		=> 180
	],
	[
		'TITLE'		=> "5. Clear topic drafts without attachments (items older 14 days ):\n",
		'SELECT'	=> '',
		'DELETE'	=> 'DELETE FROM ibf_topic_draft WHERE created < $age AND not exists (SELECT * FROM ibf_attachments_link WHERE item_type = \'topic_draft\' AND item_id = ibf_topic_draft.id ) LIMIT 1000',
		'DTL'		=> 14
	],
	[
		'TITLE'		=> "6.1 Remove topic visits logs:\n",
		'SELECT'	=> '',
		'DELETE'	=> 'DELETE FROM ibf_m_visitors WHERE `month` = month(current_timestamp - interval 2 month) LIMIT 1000',
		'DTL'		=> 28
	],
	[
		'TITLE'		=> "6.2 Remove topic visits logs:\n",
		'SELECT'	=> '',
		'DELETE'	=> 'DELETE FROM ibf_g_visitors WHERE `month` = month(current_timestamp - interval 2 month) LIMIT 3000',
		'DTL'		=> 28
	],
	[
		'TITLE'		=> "6.3 Remove topic visits logs:\n",
		'SELECT'	=> '',
		'DELETE'	=> 'DELETE FROM ibf_b_visitors WHERE `month` = month(current_timestamp - interval 2 month) LIMIT 3000',
		'DTL'		=> 28
	],
	[
		'TITLE'		=> "6.4 Remove topic visits logs:\n",
		'SELECT'	=> '',
		'DELETE'	=> 'DELETE FROM ibf_users_stat WHERE `month` = month(current_timestamp - interval 2 month) LIMIT 1000',
		'DTL'		=> 28
	],
	[
		'TITLE'		=> "7.1 Erase rejected posts:\n",
		'SELECT'	=> 'SELECT pid, attach_id, topic_id, forum_id, attach_exists FROM ibf_posts WHERE use_sig=1 and delete_after != 0 and delete_after < $age LIMIT 50',
		'DELETE'	=> '',
		'DTL'		=> 1,
		'CALLBACK'	=> 'clear_posts'
	],
	[
		'TITLE'		=> "7.2 Erase deleted posts:\n",
		'SELECT'	=> 'SELECT pid, attach_id, topic_id, forum_id, attach_exists FROM ibf_posts WHERE use_sig=2 and edit_time < $age LIMIT 50',
		'DELETE'	=> '',
		'DTL'		=> 0,
		'CALLBACK'	=> 'clear_posts'
	],
	[
		'TITLE'		=> "7.3 Erase delayed posts:\n",
		'SELECT'	=> 'SELECT pid, attach_id, topic_id, forum_id, attach_exists FROM ibf_posts WHERE delete_after != 0 and delete_after < '.time(),
		'DELETE'	=> '',
		'DTL'		=> 0,
		'CALLBACK'	=> 'clear_posts'
	]
];

########################################
# Time Periods

$onehour	= 60*60;
$oneday	= $onehour*24;
$h24		= $oneday;
$oneweek	= $oneday*7;
$onemonth	= $oneday*30;
$fivemonthes= $onemonth*5;
$halfyear	= $onemonth*6;


foreach($task_list as $task) {

	$body .= $task['TITLE']."\n";;

	$day_to_live = $task['DTL'];
	$age = $time - $day_to_live * $h24;

	$rows_affected = 0;

	##########################################
	# Select Records if required to show
	##########################################

	if ($task['SELECT']) {
		$sql = $task['SELECT'];
		$sql = str_replace('$age', $age, $sql);
		$body .= "Processing before: $day_to_live days (".timetostr($age).", TimeStamp=".$age.")\n";
		$body .= "SQL [SELECT]: ".$sql."\n";
		
		$request = $ibforums->db->query($sql);
		if ($task['CALLBACK']) {
			$task['CALLBACK']($request);
		} else {
			while ($row = $request->fetchAll(PDO::FETCH_NUM) ) {
				$body .= "\t- ".$row[0]." = ".$row[1]."\n";
			}
		}
		
		$rows_affected = $request->rowCount();
		$totalrecords += $rows_affected;
		
	}

	##########################################
	# Delete obsolete Records
	##########################################

	if ($task['DELETE']) {
		$sql = $task['DELETE'];
		$sql = str_replace('$age', $age, $sql);
			
		$body .= "Kill before: $day_to_live days (".timetostr($age).", TimeStamp=".$age.")\n";
		$body .= "SQL: ".$sql."\n";
	
		$request = $ibforums->db->prepare($sql);
		$request->execute();

		$rows_affected = $request->rowCount();
		$totalrecords += $rows_affected;

	
	}
	$body .= "* rows_affected: $rows_affected.\n";
	$body .= "---------------------------------------------------------\n";

}

$body .= "All done.\n";
$body .= 'Queries used: ' . Debug::instance()->stats->queriesCount . "\n";
$body .= 'Script Execution Time: ' . sprintf('%.4f', Debug::instance()->executionTime())."\n";


print $body;

function timetostr($n) {
	return date('r', $n);
}

function clear_posts(PDOStatementWrapper $stmt)
{
	global $ibforums;
	$topics = [];
	$forums = [];
	foreach ($stmt as $row)
	{
		$forums[$row['forum_id']] = $row['forum_id'];
		$topics[$row['topic_id']] = $row['topic_id'];

		// delete attached files
		Attachment::deleteAllPostAttachments($row);
		
		delete_post($row['pid']);
	}
	
	topic_recount($topics);
	forum_recount($forums);
}

function delete_post($post_id) {
	global $ibforums;
	
	$post_id = intval($post_id);
	$ibforums->db->exec("DELETE FROM ibf_posts WHERE pid = $post_id");
}

function topic_recount($tids = array())
{
	global $ibforums;

	if (!count($tids)) {
		return;
	}

	foreach ($tids as $tid) {
		$stmt = $ibforums->db->query("SELECT COUNT(pid) AS posts
			    FROM ibf_posts
			    WHERE
				topic_id='" . $tid . "' and
				queued != 1");

		if ($posts = $stmt->fetch()) {
			$posts = $posts['posts'] - 1;

			$stmt = $ibforums->db->query("SELECT
					post_date,
					author_id,
					author_name
				    FROM ibf_posts
				    WHERE
					topic_id='" . $tid . "' and
					queued != 1
				    ORDER BY pid DESC
				    LIMIT 1");

			$last_post = $stmt->fetch();

			$ibforums->db->exec("UPDATE ibf_topics
				    SET
					last_post='" . $last_post['post_date'] . "',
					last_poster_id='" . $last_post['author_id'] . "',
					last_poster_name='" . $last_post['author_name'] . "',
					posts='" . $posts . "'
				    WHERE tid='" . $tid . "'");
		}
	}
}

//----------------------------------------------------
function forum_recount($fids = array())
{
	$ibforums = Ibf::app();

	if (!count($fids)) {
		return;
	}

	foreach ($fids as $fid) {
		// Get the topics..
		$topics = $ibforums->db->query("SELECT COUNT(tid) as count
			    FROM ibf_topics
			    WHERE
				approved=1 and
				forum_id='" . $fid . "'")->fetch();

		// Get the posts..
		$posts = $ibforums->db->query("SELECT COUNT(pid) as count
			    FROM ibf_posts
			    WHERE
				queued != 1 and
				forum_id='" . $fid . "'")->fetch();

		// Get the forum last poster..
		$last_post = $ibforums->db->query("SELECT
				tid,
				title,
				last_poster_id,
				last_poster_name,
				last_post
			    FROM ibf_topics
			    WHERE
				approved=1 and
				forum_id='" . $fid . "' and
				club=0
			    ORDER BY last_post DESC
			    LIMIT 1")->fetch();

		// Get real post count by removing topic starting posts from the count
		$real_posts = $posts['count'] - $topics['count'];

		// Reset this forums stats
		$params = array(
				'last_poster_id'   => $last_post['last_poster_id'],
				'last_poster_name' => $last_post['last_poster_name'],
				'last_post'        => $last_post['last_post'],
				'last_title'       => $last_post['title'],
				'last_id'          => $last_post['tid'],
				'topics'           => $topics['count'],
				'posts'            => $real_posts
		);

		$stmt = $ibforums->db->prepare("UPDATE ibf_forums
			    SET " . IBPDO::compileKeyPairsString($params) . "
			    WHERE id='" . $fid . "'");
		$stmt->execute($params);
	}
}


