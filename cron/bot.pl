#!/usr/bin/perl -w
#################################################################
# BOT.PL
# Perform Forum Cron Tasks
# written by Valery Votintsev <admin@sources.ru>
#            http://www.sources.ru
# May 03, 2005
#
# modified by negram at sources.ru
# Aug 03, 2010
#################################################################
# placement: /usr/local/etc
#################################################################


use DBI;
use Time::Local;
#use strict; 

########################################
# Load Bot Settings

our ($need2send, $db_host, $db_port, $db_user, $db_password, $db_name);

require '/www/sources.forum/cron/bot_config.pl';


########################################
# Time Periods

my $onehour	= 60*60;
my $oneday	= $onehour*24;
my $h24		= $oneday;
my $oneweek	= $oneday*7;
my $onemonth	= $oneday*30;
my $fivemonthes	= $onemonth*5;
my $halfyear	= $onemonth*6;


#########################################
# Temporary Variables:

my $time = time();

my $body	 = "";

my $rows_affected = 0;
my $totalrecords  = 0;

my $request;



####################################################################
####################################################################

$body .= "---------------------------------------------------------\n";
$body .= "Forum Cron Tasks for IBF v1.2
written by Valery Votintsev <admin\@sources.ru>, http://www\.sources\.ru/\n";
$body .= "---------------------------------------------------------\n";

$body .= "Start time:  ".timetostr($time)."\n";



#############################################################
#############################################################
# Connect to the DataBase....

$body .= "Connecting to the DataBase $db_name\.\.\. ";

my $dbh = DBI->connect("DBI:mysql:database=$db_name;
                   host=$db_host;
                   port=$db_port",
                   $db_user,
                   $db_password,
                   {'RaiseError'=>1 }
                 );

$body .= "ok\n";
$body .= "---------------------------------------------------------\n";

#PressAnyKey();



###################################################################
# Cron Task List
###################################################################


my @task_list = (
	{
		'TITLE'		=> "1. Remove Topics Marked as Moved (older 7 days):\n",
		'SELECT'	=> '', ###"SELECT tid, title FROM ibf_topics WHERE state='link' and link_time < $age",
		'DELETE'	=> 'DELETE FROM ibf_topics WHERE state=\'link\' and link_time < $age',
		'DTL'		=> 7
	},
	{
		'TITLE'		=> "2. Remove old search queries (older 24 hours):\n",
		'SELECT'	=> '', ###"SELECT tid, title FROM ibf_topics WHERE state='link' and link_time < $age",
		'DELETE'	=> 'DELETE FROM ibf_search_results WHERE search_date < $age',
		'DTL'		=> 1
	},
	{
		'TITLE'		=> "3. Remove topic visit logs (older 30 days ):\n",
		'SELECT'	=> '',
		'DELETE'	=> 'DELETE FROM ibf_log_topics WHERE NOT (tid IN (SELECT tid FROM ibf_topics WHERE pinned=1 and approved=1)) AND logTime<$age',
		'DTL'		=> 30
	},
	{
		'TITLE'		=> "4. Clear posts edit history (items older 180 days ):\n",
		'SELECT'	=> '',
		'DELETE'	=> 'DELETE FROM ibf_post_edit_history WHERE edit_time < $age',
		'DTL'		=> 30
 	}
#	{
#		'TITLE'		=> "5 Delete delayed posts:\n",
#		'SELECT'	=> '',
#		'DELETE'	=> 'DELETE FROM ibf_posts WHERE delete_after != 0 and delete_after < '.time(),
#		'DTL'		=> 30
#	}
);

##########################################
# Perform Each Task
##########################################

foreach my $task (@task_list) {
	
	$body .= $task->{'TITLE'}."\n";;

	my $day_to_live = $task->{'DTL'};
	my $age = $time - $day_to_live * $h24;
	my $sql;

	$rows_affected = 0;


	##########################################
	# Select Records if required to show
	##########################################
	
	if ($task->{'SELECT'}) {
		$sql = $task->{'SELECT'};
		$sql =~ s/\$age/$age/;
        
		$request = $dbh->prepare($sql);
		$request->execute();
		while (my @row = $request->fetchrow_array() ) {
		  $body .= "\t- ".$row[0]." = ".$row[1]."\n";
		}
		$request->finish;
	}
	
	##########################################
	# Delete obsolete Records
	##########################################
	
	if ($task->{'DELETE'}) {
		$sql = $task->{'DELETE'};
		$sql =~ s/\$age/$age/;

		$body .= "Kill before: $day_to_live days (".timetostr($age).", TimeStamp=".$age.")\n";
		$body .= "SQL: ".$sql.")\n";

		$request = $dbh->prepare($sql);
		$request->execute();

		$rows_affected = $request->rows;
		$totalrecords += $rows_affected;

		$request->finish;

	}
	$body .= "* rows_affected: $rows_affected.\n";
	$body .= "---------------------------------------------------------\n";
	
}



#########################################################
# Close the database connection
#########################################################

$dbh->disconnect;

$body .= "Disconnected from the DataBase $db_name\.\n";

my ($t1,$t2,$t3,$t4) = times;

$body .= "All done.\n";
$body .= "Run time: $t1 sec.\n\n";






#########################################################
# Print the Result ( & Send to the root)
#########################################################

if ($need2send) {
  print $body;
}


exit;


######################################
sub PressAnyKey {
  my $title = shift;
  if ($title) {
  	print "\* $title"
  } else {
  	print "* Press Enter: "
  }
  my $tmp = <>;
}

#####################################################################
sub timetostr {
  my $n = shift;
  return "".localtime($n);
}

