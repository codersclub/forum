<?php
require __DIR__ . '/../app/bootstrap.php';

Ibf::registerApplication(new ConsoleApplication());
$ibforums = Ibf::app();
//stub
//
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

$body .= "Connecting to the DataBase\.\.\. ";
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
	]
	#	{
	#		'TITLE'		=> "5 Delete delayed posts:\n",
	#		'SELECT'	=> '',
	#		'DELETE'	=> 'DELETE FROM ibf_posts WHERE delete_after != 0 and delete_after < '.time(),
	#		'DTL'		=> 30
	#	]
];

########################################
# Time Periods

$onehour	= 60*60;
$oneday	= $onehour*24;
$h24		= $oneday;
$oneweek	= $oneday*7;
$onemonth	= $oneday*30;
$fivemonthes	= $onemonth*5;
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

		$request = $ibforums->db->query($sql);
		while ($row = $request->fetchAll(PDO::FETCH_NUM) ) {
			$body .= "\t- ".$row[0]." = ".$row[1]."\n";
		}
		$request->finish;
	}

	##########################################
	# Delete obsolete Records
	##########################################

	if ($task['DELETE']) {
		$sql = $task['DELETE'];
		$sql = str_replace('$age', $age, $sql);
			
		$body .= "Kill before: $day_to_live days (".timetostr($age).", TimeStamp=".$age.")\n";
		$body .= "SQL: ".$sql.")\n";
	
		$request = $ibforums->db->prepare($sql);
		$request->execute();

		$rows_affected = $request->rowCount();
		$totalrecords += $rows_affected;

		//$request->closeCursor();
	
	}
$body .= "* rows_affected: $rows_affected.\n";
$body .= "---------------------------------------------------------\n";

}

print $body;

function timetostr($n) {
	return date('r', $n);
}

