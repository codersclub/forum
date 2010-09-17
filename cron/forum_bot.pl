#!/usr/bin/perl -w
#################################################################
# Kill Topics Marked as Moved
# written by Valery Votintsev <admin@sources.ru>
#            http://www.sources.ru
# May 03, 2005
#################################################################
# placement: /usr/local/etc

use DBI;
# use Digest::MD5 qw'md5_hex';
use Time::Local;
use strict; 


#########################################
my $need2send	= 1;
my $mailprog	= '/usr/sbin/sendmail';
my $site_name   = 'www.mmt.ru';
my $from_email  = "cron\@sources.ru";
my $mailto	= 'postmaster@sources.ru';
my $onehour	= 60*60;
my $oneday	= $onehour*24;
my $h24		= $oneday;
my $onemonth	= $oneday*30;
my $fivemonthes	= $onemonth*5;
my $halfyear	= $onemonth*6;

my $body	= "";


#########################################
# MySQL parameters:

my $mysql_host = "localhost";
my $mysql_port = "3306";

my $dbuser     = "username";
my $dbpassword = "password";


#########################################
# database parameters:

my $dbname     = "invision";


my ($id,$pid,$post_title,$text,$author,$mail,$time,$ip);


#########################################
# Temporary Variables:

my ($dbh, $request, $rows_affected);

# topic variavles:
my ($tid,$title,$description,$state,   # + $forum_id,
    $posts,$views,$starter_id,$start_date,
    $last_poster_id,$last_post,$starter_name, # last_post = LASTDATE !
    $last_poster_name,$author_mode,$icon_id,
    $poll_state,$total_votes,$last_vote,
    $approved,$pinned,$moved_to);

my  ($old_topic_id,$topicsubj,$first_post,$starter_email,$locked);

# posting variavles:

my ($append_edit,$edit_time,$author_id, # +$pid
    $author_name,$use_sig,$use_emo,$ip_address,
    $post_date,$post,$queued,$topic_id,                # + $icon_id,
    $attach_id,$attach_hits,$attach_type,              # + $forum_id,
    $attach_file,$new_topic,$edit_name);               # + $post_title
my ($real_name,$icon);





####################################################################
####################################################################

$body .= "---------------------------------------------------------\n";
$body .= "Timer Tasks for IBF v1.2
written by Valery Votintsev <admin\@sources.ru>, http://www\.sources\.ru/\n";
$body .= "---------------------------------------------------------\n";

$time = time();
$body .= "Start time:  ".timetostr($time)."\n";



#############################################################
#############################################################
# Connecting to the DataBase....

$body .= "Connecting to the DataBase $dbname\.\.\. ";

$dbh=DBI->connect("DBI:mysql:database=$dbname;
                   host=$mysql_host;
                   port=$mysql_port",
                   $dbuser,
                   $dbpassword,
                   {'RaiseError'=>1 }
                 );

$body .= "ok\n";
$body .= "---------------------------------------------------------\n";

#PressAnyKey();







###################################################################
# Kill Topics Marked as Moved
###################################################################

$body .= "Kill Topics Marked as Moved:\n";


my $day_to_live = 7;
my $age = $time - $h24*$day_to_live;

$body .= "Kill before: $day_to_live days (".timetostr($age).", TimeStamp=".$age.")\n";




my $sql = "SELECT tid, title FROM ibf_topics WHERE state\='link' and link_time < ".$age;
my $sql2 = "DELETE FROM ibf_topics WHERE state\='link' and link_time < ".$age;

#SELECT title, FROM_UNIXTIME(start_date) FROM ibf_topics WHERE title like 'Перемещ%'

$body .= "(".$sql2.")\n";



$request = $dbh->prepare($sql);
$request->execute();

my $totaltopics = 0;

while (my @row = $request->fetchrow_array() ) {
  $body .= "\t- ".$row[0]." = ".$row[1]."\n";
  $totaltopics++
}
$request->finish;


$request = $dbh->prepare($sql2);
$request->execute();
$rows_affected = $request->rows;
$request->finish;

$body .= "* rows_affected: $rows_affected.\n";


$body .= "* Total: $totaltopics topics have beeen removed.\n";
$body .= "---------------------------------------------------------\n";


#PressAnyKey();





#########################################################
# Remove old search queries older than 24 hours
#########################################################

$age = $time - $h24;

$sql = "DELETE FROM ibf_search_results WHERE search_date < '$age'";

$body .= "Remove old search queries older than 24 hours:\n";
$body .= "(".$sql.")\n";
		
$request = $dbh->prepare($sql);
$request->execute();
$rows_affected = $request->rows;
$request->finish;

$body .= "* Total: $rows_affected search results have beeen removed.\n";
$body .= "---------------------------------------------------------\n";





#########################################################
# Remove logs visiting of topics ( 30 days )
#########################################################

$body .= "Remove logs visiting of topics ( 30 days ):\n";

$age = $time - $onemonth;
$rows_affected = 0;

# do not delete logs for pinned topics.
# Collect ids of pinned topics

my $tids="";

$sql = "SELECT tid FROM ibf_topics WHERE pinned=1 and approved=1";
$request = $dbh->prepare($sql);
$request->execute();

while (my @row = $request->fetchrow_array() ) {
  $tids .= $row[0].", ";
}
$tids =~ s/,\s$//;
$request->finish;

if($tids) {
  $sql = "DELETE FROM ibf_log_topics WHERE
      	not (tid IN ($tids)) and 
      	logTime<$age";
  $body .= "(".$sql.")\n";

  $request = $dbh->prepare($sql);
  $request->execute();
  $rows_affected = $request->rows;
  $request->finish;
}
$body .= "* Total: $rows_affected visit logs have beeen removed.\n";
$body .= "---------------------------------------------------------\n";

		






#########################################################
# Close the connection
#########################################################

$dbh->disconnect;

my ($t1,$t2,$t3,$t4) = times;

$body .= "Disconnected from the DataBase $dbname\.\n";
$body .= "All done.\n";
$body .= "Run time: $t1 sec.\n\n";






#########################################################
# Print & Send the Result
#########################################################

print $body;

if ($need2send && $totaltopics) {
  &send($mailprog,$mailto,$body);
}



exit;


######################################
#------- Send the message to admin(s)
sub send {
my $mailprog = shift;
my $mailto   = shift;
my $body     = shift;

#print "$mailprog $mailto \n";

#return;

open(SENDMAIL, "|$mailprog -t")
              or print "Can't fork for sendmail: $!\n";

print SENDMAIL
	"From: Cron Daemon <$from_email>\n",
	"To: $mailto\n",
	"Subject: Forum Cron Log\n",
	"\n\n",

	$body,

	"---\n",
	"Sincerely yours,\n",
	"          cron daemon at $site_name\n",
	"\n\n";

close(SENDMAIL);
return;

}










#################################################################
sub md5 {
  my $pass = shift;
  my $ctx=Digest::MD5->new;
  $ctx->add($pass);
  return $ctx->hexdigest;
}

######################################
sub PressAnyKey {
  my $title = shift;
  if ($title) {print "\* $title"}
  else {print "* Press Enter: "}
  my $tmp = <>;
}

#####################################################################
sub timetostr {
  my $n = shift;
  return "".localtime($n);
}

#####################################################################
sub strtotime {
# calculate epoch seconds at midnight on that day in this timezone
#2000-07-27 13:24:28
#02/06/02 т 00:32:59
  my $n;
  my $s = shift;
  if ($s =~ m~(\d\d\d\d)\-(\d\d)\-(\d\d)(.*)(\d\d):(\d\d):(\d\d)~) {
    #$n = timelocal($sec,$min,$hour,$day,$month-1,$year);
    $n = timelocal($7,$6,$5,$3,$2-1,$1);
  } elsif ($s =~ m~(\d\d)\/(\d\d)\/(\d\d)(.*)(\d\d):(\d\d):(\d\d)~) {
    #$n = timelocal($sec,$min,$hour,$day,$month-1,$year);
    $n = timelocal($7,$6,$5,$2,$1-1,$3);
  } else {


    $n = time();
  }
  return $n;
}

######################################################
sub win2koi {
  $_ = shift;
  tr/\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF/\xE1\xE2\xF7\xE7\xE4\xE5\xF6\xFA\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF2\xF3\xF4\xF5\xE6\xE8\xE3\xFE\xFB\xFD\xFF\xF9\xF8\xFC\xE0\xF1\xC1\xC2\xD7\xC7\xC4\xC5\xD6\xDA\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD2\xD3\xD4\xD5\xC6\xC8\xC3\xDE\xDB\xDD\xDF\xD9\xD8\xDC\xC0\xD1/;
  return $_;
}

#####################################################################
# Convert Windows-1251 -> DOS-866
sub win2dos {
  $_ = shift;
  tr/\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF/\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF/;
  return $_;
}

######################################################
sub koi2win {
  $_ = shift;
  tr/\xA3\xB3\xBF\xC0-\xFF/\xB8\xA8\xB9\xFE\xE0\xE1\xF6\xE4\xE5\xF4\xE3\xF5\xE8-\xEF\xFF\xF0-\xF3\xE6\xE2\xFC\xFB\xE7\xF8\xFD\xF9\xF7\xFA\xDE\xC0\xC1\xD6\xC4\xC5\xD4\xC3\xD5\xC8-\xCF\xDF\xD0-\xD3\xC6\xC2\xDC\xDB\xC7\xD8\xDD\xD9\xD7\xDA/;
  return $_;
}

######################################################
sub koi2dos {
  $_ = shift;
  tr/\xA3\xB3\xBF\xC0-\xFF/\xF1\xF0\xFC\xEE\xA0\xA1\xE6\xA4\xA5\xE4\xA3\xE5\xA8-\xAF\xEF\xE0-\xE3\xA6\xA2\xEC\xEB\xA7\xE8\xED\xE9\xE7\xEA\x9E\x80\x81\x96\x84\x85\x94\x83\x95\x88-\x8F\x9F\x90-\x93\x86\x82\x9C\x9B\x87\x98\x9D\x99\x97\x9A/;
  return $_;
}
#####################################################################
sub mysql_escape_string {
  my $s = shift;
  $s = "" if (!$s);
  chomp ($s);
  $s =~ s/\n/\<br\>/gm;
  $s =~ s/\\/\\\\/gm;
  $s =~ s/\r//gm;
#  $s =~ s/\n/\\n/gm;
  $s =~ s/\t/\\t/gm;
  $s =~ s/\$/\\\$/gm;
  $s =~ s/\%/\\%/gm;
  $s =~ s/\'/\\\'/gm;
  $s =~ s/\"/\\\"/gm;
  return $s;
#\n linefeed (LF or 0x0A (10) in ASCII)  
#\r carriage return (CR or 0x0D (13) in ASCII)  
#\t horizontal tab (HT or 0x09 (9) in ASCII)  
#\\ backslash  
#\$ dollar sign  
#\" double-quote  
#\[0-7]{1,3} the sequence of characters matching the regular expression 
# is a character in octal notation   
#\x[0-9A-Fa-f]{1,2} the sequence of characters matching the regular expression 
# is a character in hexadecimal notation   
}


#####################################################################
# Returns a string with backslashes stripped off. (\' becomes ' and so on.)
# Double backslashes are made into a single backslash
sub stripslashes {
  my $s = shift;
  chomp ($s);
  $s =~ s/\\(.)/$1/gm;
  return $s;
}

#####################################################################
sub file {
  my $N = shift;
  chomp ($N);
  my @A = ();
  if (-e $N) {
    open(F,$N) or print "(sub file): File read error: \"$N\"\n";
    @A = <F>;
    close(F); 
    chomp @A;
  }
#  return (join("", @A));
  return (@A);
}


#####################################################################
sub trim {
  my $s = shift;
  $s = "" if (!$s);
  $s =~ s/^\s+//;
  $s =~ s/\s+$//;
  return $s;
}


