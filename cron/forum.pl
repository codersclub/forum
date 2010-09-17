#!/usr/bin/perl -w
#################################################################
# Kill Topics Marked as Moved
# written by Valery Votintsev <admin@sources.ru>
#            http://www.sources.ru
# May 03, 2005
#################################################################

use DBI;
# use Digest::MD5 qw'md5_hex';
use Time::Local;
use strict; 


#########################################
my $mailprog = '/usr/sbin/sendmail';
my $mailto   = 'admin@sources.ru';
my $body     = "";


#########################################
# MySQL parameters:

my $mysql_host = "localhost";
my $mysql_port = "3306";

my $dbuser     = "username";
my $dbpassword = "password";


#########################################
# database parameters:

my $dbname     = "invision";
my $dbprefix   = "ibf_";               # prefix for tables
my $forum_id   = 0;                    # The forum ID to be converted to


my ($id,$pid,$post_title,$text,$author,$mail,$time,$ip);


#########################################
# Temporary Variables:

my %users  = ();
my %posts  = ();
my %topics = ();
my @boards = ('-');

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

# for the forum:
my $boardname   = '';
my $totalboards = 0;
my $old_forum_id= 0;
my $totaltopics = 0;
my $totalposts  = 0;
my $totalusers  = 0;
my $last_title  = '';
my %present     = ();  # topics converted in the previous session

my $name        = "";
my $pname       = "";
my $tmp = "";
my $i = 0;


######################################################
#my $signum = 0;
#my @signs  = ("\|","\/","\-","\\","\|","\/","\-","\\");
#my $delay  = 10;
#print '2000-07-27 at 13:24:28 ', strtotime('2000-07-27 13:24:28'),"\n";
#print '02/06/02 т 00:32:59 ', strtotime('02/06/02 т 00:32:59'),"\n";










######################################################
# Uncomment all the PressAnyKey() calls
# if you want a pause after each converting steps





####################################################################
####################################################################

$body = "---------------------------------------------------------
           Kill Topics Marked as Moved
   written by Valery Votintsev <admin\@sources.ru>
           http://www\.sources\.ru/
---------------------------------------------------------\n";



$time = time();
my $day_to_live = 7;
my $age = $time - 60*60*24*$day_to_live;
my $sql = "SELECT * FROM ".$dbprefix."topics WHERE state\='link' and link_time < ".$age;
my $sql2 = "DELETE FROM ".$dbprefix."topics WHERE state\='link' and link_time < ".$age;
#SELECT title, FROM_UNIXTIME(start_date) FROM ibf_topics WHERE title like 'Перемещ%'

$body .= "Start time:  ".timetostr($time)."\n";
$body .= "Kill before: ".timetostr($age)." (".$age.")\n";
$body .= "(".$sql2.")\n\n";



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

#PressAnyKey();







###################################################################
# 
#
# link_time < NOW()-60*60*24*7

$request = $dbh->prepare($sql);
$request->execute();

$totaltopics = 0;

while (my @row = $request->fetchrow_array() ) {
  $body .= "\t- ".$row[0]." = ".$row[1]."\n";
  $totaltopics++
}
$request->finish;


$request = $dbh->prepare($sql2);
$request->execute();
$request->finish;


$body .= "* Total: $totaltopics topics have beeen removed.\n";


#PressAnyKey();







#########################################################
# Close the connection

$dbh->disconnect;

$body .= "\nDisconnected from the DataBase $dbname\.\n";
$body .= "All done.\n\n";




#print $body;
if ($totaltopics) {
#  &send($mailprog,$mailto,$body);
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
	"From: Cron Daemon <cron\@sources.ru>\n",
	"To: $mailto\n",
	"Subject: Forum Cron Log\n",
	"\n\n",

	$body,

	"---\n",
	"Sincerely yours,\n",
	"          cron daemon at www.sources.ru\n",
	"\n\n";

close(SENDMAIL);
return;

}





######################################
sub getuserid {
 $_ = shift;  # user name
 return exists($users{$_}) ? $users{$_} : 0;
}





#################################################################
sub check_user_id {
  my $name=shift;

  my $request = $dbh->prepare("SELECT id FROM $dbprefix"."members WHERE name='$name' LIMIT 1");
  $request->execute();
  my @row = $request->fetchrow_array();
  my $id     = $row[0];
  $id     = 0 if (!$id);
  $request->finish;
  return ($id);
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

#####################################################################
# Convert Windows-1251 -> DOS-866
sub win2dos {
  $_ = shift;
  tr/\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF/\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF/;
  return $_;
}


