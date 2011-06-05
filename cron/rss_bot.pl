#!/usr/bin/perl
#################################################################
# RSS Bot for Invision Power Board v.1.2
# written by Valery Votintsev <admin@sources.ru>
#            http://www.sources.ru
# 04.07.2006
#################################################################

# include package
use LWP::Simple;
use XML::Parser;
use DBI;
use Encode;
#use Data::Dumper;

#binmode(STDOUT,":encoding(windows-1251)");
#binmode(STDOUT);

########################################
# Load Bot Settings

our ($debug, $db_host, $db_port, $db_user, $db_password, $db_name, $search_sql_method, $posting_type, $user_id, $user_name);

#require '/www/sources.forum/cron/rss_config.pl';
require './rss_config.pl';
#require '/etc/periodic/hourly/rss_config.pl';


#-----------------------------------------------
# Default Values:

my $url_handler    = 'cnews_url_rewrite';
my $body_handler   = 'cnews_body_rewrite';

#----------------------------------------------

# keep track of which tag is currently being processed
my $currentTag  = "";
my $currentLevel= "";
my $parentLevel = "";

# Parser objects
my %rss         = ();
my %rssImage    = ();
my @rssItems    = ();
my %channel	= ();
my $itemNumber  = 0;
my $fid		= 0;
my $pid		= 0;
my $url		= '';


##############################################################
#-------------------------------------------------------------
# Check for command line arguments

$ARGC = $#ARGV + 1;

  if($debug) {
    print "RSS_BOT (News grabber), (c) 2006 by Valery Votintsev.\n";
    print "Arguments entered: ".$ARGC."\n";
    for(my $i=0;$i<$ARGC;$i++) {
      print "ARGV[".$i."]=".$ARGV[$i]."\n";
    }
  }


##########################
# Check for NOT ENOUGH Arguments
if(($ARGC > 0) && ($ARGC < 2)) {

  print "*** Invalid number of arguments!\n";
  print "USAGE: rss_bot.pl \"url\" forum_id [post_id]\n";
  print "where\n";
  print "      url - news URL with http:// prefix.\n";
  print "      forum_id - ID of the forum to place the news.\n";
  print "      post_id - ID of the post have to be rewritten.\n";

  exit;
}



#############################################################
# Connecting to the DataBase....


print "Connecting to the DataBase $dbname\.\.\. " if ($debug);

#$dbh=DBI->connect("DBI:mysql:database=$db_name;
#                   host=$db_host;
#                   port=$db_port",
#                   $db_user,
#                   $db_password,
#                   {'RaiseError'=>1 }
#                 );

my $conn_string = '';

$conn_string .= 'host='.$db_host.';' if($db_host);
$conn_string .= 'port='.$db_port.';' if($db_port);

$dbh=DBI->connect("DBI:mysql:database=$db_name;"
                  .$conn_string,
                   $db_user,
                   $db_password,
                   {'RaiseError'=>1 }
                 );

print "ok\n" if ($debug);

#---------------------------------------------
#    $sql = "SET NAMES utf8";
    $sql = "SET NAMES cp1251";
    $request = $dbh->prepare($sql);
    $request->execute();
    $request->finish;
#--------------------------------------------

if($ARGC > 0) {

  my $url = $ARGV[0];
  print "url=".$url."\n" if ($debug);

  my $guid = $ARGV[0];
  $guid =~ s/^.+\/(\d+)$/$1/;
  print "guid=".$guid."\n" if ($debug);

  if($ARGC == 2) {
    $fid = $ARGV[1];
    print "fid=".$fid."\n" if ($debug);
  }

  if($ARGC == 3) {
    $pid = $ARGV[2];
    print "pid=".$pid."\n" if ($debug);
  }
  #exit;

  my %item = (
    channel_id    => 1,
    channel_descr => '',
    channel_url   => '',
    source_id     => 1,
    forum_id      => $fid,
    user_id       => $user_id,
    posting_type  => $posting_type,
    user_name     => $user_name,
    url_handler   => $url_handler,
    body_handler  => $body_handler,
    link          => $url,
    post_id       => $pid,
    guid          => $guid,
    is_active     => 1
  );

  ###############################################
  ## Hanle JUST ONE REAL News (Rewrite the Posting!)

  if($pid) {	# If PID entered
    $item{'post_id'} = $pid;

    $query = "SELECT topic_id, forum_id FROM ibf_posts
   	    WHERE 
  		pid   ='".$item{post_id}."'
             ";

    if($debug) {	
      print "Look for post exists query:\n";
      print $query."\n";
      print "============================\n";
    }


    $request = $dbh->prepare($query);
    $request->execute();
    $rows_affected = $request->rows;

    my @row = $request->fetchrow_array();
    $item{topic_id} = $row[0];
    $item{forum_id} = $row[1];
    $request->finish;

    #------------------------------------

    process_item(%item);

  } else {
    if($fid) {	# If FID entered
      $item{'forum_id'} = $fid;

      process_item(%channel,%item);
    }

  }

} else {

  #-------------------------------------------------------------

  # Get RSS Channels:

  my @channels = get_rss_channels();


  foreach my $chan (@channels) {
    %channel = %{$chan};

    if ($debug) {
      foreach $k (keys %channel) {
        print $k."=>".$channel{$k}."\n";
      }
      print "-----------------------------------------------\n"; 
    }

    process_channel(%channel);
  }


}

#process_channel($channel);
#		channel_id    => 'cnews/telecom',
#		channel_descr => ' cNews - ',
#		channel_url   => 'http://www.cnews.ru/inc/rss/telecom_rss.xml',
#		urlhandler    => \&cnews_url_rewrite,
#		bodyhandler   => \&cnews_body_rewrite,
#		forum_id      => 1,
#		user_id       => 1,
#		user_name     => 'RSS_Bot'
#		);

  my ($t1,$t2,$t3,$t4) = times;

  print "Elapsed time: $t1 sec.\n" if ($debug);;


exit;

########################################################
#=======================================

sub get_rss_channels {
  my @chans = ();

  $query = "SELECT
		id,
		descr,
		channel_url,
		source_id,
		forum_id,
		posting_type,
		user_id,
		user_name,
		url_handler,
		body_handler,
		is_active
	   FROM ibf_rss_channels
	   WHERE is_active='1'";

  if ($debug) {
    print "get_rss_channels:\n";
    print "query=".$query."\n";
  }


  $request = $dbh->prepare($query);
  $request->execute();
  $rows_affected = $request->rows;

  print "rows_affected=".$rows_affected."\n" if ($debug);

  my $i = 0;

  #------------------------------------------------------
  while (my $row = $request->fetchrow_hashref()) {

    $chans[$i]{channel_id   } = $row->{'id'};
    $chans[$i]{channel_descr} = $row->{'descr'};
    $chans[$i]{channel_url  } = $row->{'channel_url'};
    $chans[$i]{source_id    } = $row->{'source_id'};
    $chans[$i]{forum_id     } = $row->{'forum_id'};
    $chans[$i]{user_id      } = $row->{'user_id'};
    $chans[$i]{posting_type } = $row->{'posting_type'};
    $chans[$i]{user_name    } = $row->{'user_name'};
    $chans[$i]{url_handler  } = $row->{'url_handler'};
    $chans[$i]{body_handler } = $row->{'body_handler'};
    $chans[$i]{is_active    } = $row->{'is_active'};

    if ($debug) {
      foreach $k (keys %{$row}) {
        print $k."=>".$row->{$k}."\n";
      }
      print "-----------------------------------------------\n"; 
    }

    $i++;
  }

  $request->finish;

  return @chans;
}


#------------------------------------------
sub process_channel {

  my %channel = @_;

  if ($debug) {
    print "process_channel:\n";
    foreach $k (keys %channel) {
      print $k."=>".$channel{$k}."\n";
    }
    print "-----------------------------------------------\n"; 
  }

  my $page = get $channel{channel_url} or die("Can not open url: \"".$channel{channel_url}."\": $!");

  if ($debug) {
#    print "page downloaded:\n";
#    print $page."\n";
#    print "--------------------\n";
  }

  $rss_encoding = getEncoding($page);
  $rss_encoding = lc($rss_encoding);

  if($rss_encoding eq 'utf-8') {
#    $page = utf2win($page);
  }
#  print "MY ENCODING=".$rss_encoding."\n";
#return;


  # initialize parser
  my $xp = new XML::Parser();
  #my $xp = new XML::Parser(ProtocolEncoding => 'windows-1251');

  # set callback functions
  $xp->setHandlers(Start => \&start, End => \&end, Char => \&cdata);

  # parse XML
  $xp->parse($page);

  # get XML PI
  #$decl = $doc->getXMLDecl();

  # get XML version
  #print "Version: ".$decl->getVersion()."\n";
  # get encoding
  #print "Encoding: ".$decl->getEncoding()."\n";



  #--------------------------------------
  # Loop for each News Items
  #--------------------------------------

  foreach $i (@rssItems) {
    process_item(%channel,%{$i});
  }


}	# process_channel


#------------------------------------------------------
# URL Substitute for cNews Printed version (if exists)
#------------------------------------------------------
sub cnews_url_rewrite {
 my %item = @_;

 my $url = $item{link};
#INPUT: http://rss.feedsportal.com/c/803/f/413243/s/154179ee/l/0L0Scnews0Bru0Cnews0Ctop0Cindex0Bshtml0D20A110C0A50C260C441533/story01.htm

 print "url_rewrite: link=".$url."\n" if($debug);

 $url =~ s/^.+0L0S//;
 $url =~ s/0A/0/g;
 $url =~ s/0B/\./g;
 $url =~ s/0C/\//g;
 $url =~ s/0D/\?/g;
 $url =~ s/\/story01.htm//g;

 if($url =~ /\/news\/line\//) {
  #http://telecom.cnews.ru/news/line/index.shtml?2007/06/15/254996
  #http://cnews.ru/news/line/print.shtml?2007/06/15/254996
  $url =~ s/^[^\?]+/cnews.ru\/news\/line\/print.shtml/i;

 } elsif($url =~ /\/news\/top\//) {
  #http://telecom.cnews.ru/news/top/index.shtml?2007/06/19/255541
  #http://cnews.ru/news/top/print.shtml?2007/06/19/255541
  $url =~ s/^[^\?]+/cnews.ru\/news\/top\/print.shtml/i;

 } elsif($url =~ /\/reviews\//) {
  #http://telecom.cnews.ru/reviews/articles/index.shtml?2007/06/19/255387
  #http://cnews.ru/reviews/print.shtml?2007/06/19/255387
  $url =~ s/^[^\?]+/cnews.ru\/reviews\/print.shtml/i;

 }

 $url = 'http://' .$url;

 print "url_rewrite: return=".$url."\n" if($debug);

 return $url;
}


sub old_cnews_url_rewrite {
 my $url = shift;

 if($url =~ /\/news\/line\//) {
  #http://telecom.cnews.ru/news/line/index.shtml?2007/06/15/254996
  #http://cnews.ru/news/line/print.shtml?2007/06/15/254996
  $url =~ s/^[^\?]+/http:\/\/cnews.ru\/news\/line\/print.shtml/i;

 } elsif($url =~ /\/news\/top\//) {
  #http://telecom.cnews.ru/news/top/index.shtml?2007/06/19/255541
  #http://cnews.ru/news/top/print.shtml?2007/06/19/255541
  $url =~ s/^[^\?]+/http:\/\/cnews.ru\/news\/top\/print.shtml/i;

 } elsif($url =~ /\/reviews\//) {
  #http://telecom.cnews.ru/reviews/articles/index.shtml?2007/06/19/255387
  #http://cnews.ru/reviews/print.shtml?2007/06/19/255387
  $url =~ s/^[^\?]+/http:\/\/cnews.ru\/reviews\/print.shtml/i;

 } elsif($url =~ /feedsportal\.com/) {
   my $page=get $url; # or die("Can not open url: \"".$url."\": $!");
   if(!$page) {
     $page='';
     $url = '';
   }
   if($page =~ /<a href\=\"([^\"]+)\"[^>]*><\/a>/) {
     $url = $1;
     print "print link found.\n" if($debug);
     #<a href="http://cnews.ru/news/line/print.shtml?2008/06/20/305982" class="BlueLinkNoDecore"></a>
   }
 }

 return $url;
}

########################################################
#-------------------------------
sub process_item {

  my %item = @_;

  print "PROCESS_ITEM: ENCODING=".$rss_encoding."\n" if($debug);

  if($rss_encoding eq 'UTF-8') {
#    $item{title}    = utf8_to_win1251($item{title});

#    $item{title}       = utf2win($item{title});
#    $item{category}    = utf2win($item{category});
#    $item{description} = utf2win($item{description});

  }

  my $url_handler  = $item{url_handler};
  my $body_handler = $item{body_handler};

  if ($debug) {
    print "process_item: item parameters:\n";
    foreach $k (keys %item) {
      print "process_item: ".$k."=>".$item{$k}."\n";
    }
    $item{description} =~ s/&lt;.*$//g;

    print "process_item: DESCRIPTION=>".$item{description}."\n";
    print "--------------------\n";
  }


  #---------------------------------
  # Is the item downloaded before?
  # Download it if not yet
  #---------------------------------

#  unless(check_item(%item)) {
  if($item{pid} || !check_item(%item)) {
#vot  if($item{pid}) {

    if($item{posting_type} eq 'SHORT') {

      # SHORT page posting (RSS Description only)
      $item{body} = $item{description};

    } else {

      # FULL page posting

      # Rewrite the body URL if required

      if($url_handler) {
#        $item{printlink} = &$url_handler($item{link});
        $item{printlink} = &$url_handler(%item);
      } else {
        $item{printlink} = $item{link};
      }

      if ($debug) {
        print "process_item: printlink=>".$item{printlink}."\n";
      }

      # Exit if error in url_handler (printlink='')
      if(!$item{printlink}) {
        print "process_item: EMPTY printlink, SKIP the item!\n";
        print "------------------------------------\n";
        return;
      }

      # Get the News Content
#Can not open url: 'http://rnd.cnews.ru/tech/news/index_science.shtml?2007/12/26/281282':
#  at ./mmt_rss_bot.pl line 298.

#      my $body = get $item{printlink}  or die("Can not open url: '".$item{printlink}."': $!");
      my $body = get $item{printlink}; #  or die("Can not open url: '".$item{printlink}."': $!");

# Printed Body is in WINDOWS-1251 Encoding!!!
#$body = utf2win($body);
#$body = utf8_to_win1251($body);

      if($body =~ /\[an error occurred while processing this directive\]/) {$body='';}

      $body = "" unless $body;


      $item{body} = $body;

      # Strip the body content if required

      if($body_handler) {
        $item{body} = &$body_handler($item{link},$item{body});
      }

      # Check if TITLE defined

      if(!$item{'title'}) {
        my $title = $item{body};
        $title =~ s/^\[b\](.+?)\[\/b\].*$/$1/is;
        $item{title} = $title;
      }




      # Check for invalid printed page link
      #[an error occurred while processing this directive]
      # inside the &$body_handler
    }

#$item{title}       = utf2win($item{title});
#$item{category}    = utf2win($item{category});
#$item{description} = utf2win($item{description});
#$item{body}       = utf2win($item{body});


    # The body is empty if error or skipped

    if($item{body}) {
      $item{topic_id} = save_body_to_db(%item);
      log_item(%item);
    } else {
      print "process_item: EMPTY BODY, SKIP the item!\n";
      print "------------------------------------\n";
    }

#!!! exit; # EXIT AFTER the FIRST NEWS HANDLED

  }
}	# process_item




##########################################################
sub check_item {

  return 0 if($ARGC);

  my %item = @_;

  $query = "SELECT id,source_id,news_id FROM ibf_rss_logs
	    WHERE source_id='".$item{source_id}."' AND
		  news_id='".$item{guid}."'
	";

  if($debug) {    print "check_item: query: ".$query."\n";  }

  $request = $dbh->prepare($query);
  $request->execute();
  $rows_affected = $request->rows;
  $request->finish;

  my $found = $rows_affected ? 1 : 0;
  my $foundstr = $found ? "found" : "not found";

  if($debug) {
    print "check_item: item ".$item{source_id}."/".$item{guid}." ".$foundstr."\n";
    print "--------------------------------\n";
  }

  return $found;
}




#------------------------------
sub log_item {

  my %item = @_;
  my $forumlink = 'http://forum.sources.ru/index.php?showtopic='.$item{topic_id};
  $query = "INSERT INTO ibf_rss_logs
	    SET 
	    	source_id='".$item{source_id}."',
		news_id='".$item{guid}."',
	    	news_date='".strtotime($item{pubdate})."',
	    	news_url='".$item{link}."',
	    	forum_url='".$forumlink."'
	";

  if($debug) {print "log_item: query=".$query."\n";}

  $request = $dbh->prepare($query);
  $request->execute();
#  $rows_affected = $request->rows;
  $request->finish;

}


#------------------------------
sub save_body_to_db {

  my %item = @_;


  if($item{post_id}) {
    # Update Old Topic
    update_post(%item);
    update_topic(%item);

  } else {
    # Insert New Topic
    $item{pubdate} = strtotime(); # unixtime format for the post date

    $item{topic_id} = insert_topic(%item);

    $item{post_id} = insert_post(%item);

    # Update the forum for last poster info

    update_forum(%item);
  }


  # reindex search words for INDEX method

  if ($search_sql_method eq 'index') {
    index_posts(0, $item{topic_id}, $item{forum_id}, $item{title});
    index_posts($item{post_id}, $item{topic_id}, $item{forum_id}, $item{body});
  }

  return ($item{topic_id});
}


##########################################################
sub insert_topic {

  my %item = @_;

  my $title       = utf2win($item{title});
#  my $description = utf2win($item{description});
  my $description = utf2win($item{channel_descr});

  $query = "INSERT INTO ibf_topics
	    SET	forum_id        ='".$item{forum_id}."',
		title           ='".addslashes($title)."',
		description     ='".addslashes($description)."',
		state           ='open',
		starter_id      ='".$item{user_id}."',
		starter_name    ='".$item{user_name}."',
		start_date      ='".$item{pubdate}."',
		last_poster_id  ='".$item{user_id}."',
		last_poster_name='".$item{user_name}."',
		last_post       ='".$item{pubdate}."',
		icon_id         = 0,
		indexed         = 1,
		posts		= 0,
		views		= 0
	";
  if($debug) {	
    print "insert_topic:\n";
    print $query."\n";
    print "============================\n";
  }

  $request = $dbh->prepare($query);
  $request->execute();
#  $rows_affected = $request->rows;
  $err = $request->err;

  if($err) {
    $tid = 0;
    print "*** ERROR: ".$request->err." ".$request->errstr."\n" if($debug);
  } else {
    # get the last inserted id
    $tid = $request->{'mysql_insertid'};

    print "inserted_topic_id: $tid\n" if($debug);
  }
  $request->finish;

  return $tid;
}




##########################################################
sub update_topic {

  my %item = @_;

#  my $title       = utf2win($item{title});
  my $title       = $item{title};
  Encode::_utf8_off($title);

#  my $description = utf2win($item{description});
  my $description = utf2win($item{channel_descr});
  Encode::_utf8_off($description);

  $query = "UPDATE ibf_topics
	    SET	
		title           ='".addslashes($title)."'
	   WHERE
		forum_id   ='".$item{forum_id}."' AND
		tid        ='".$item{topic_id}."' 
	";
#		, description     ='".addslashes($description)."'

  if($debug) {	
    print "update_topic:\n";
    print $query."\n";
    print "============================\n";
  }

  $request = $dbh->prepare($query);
  $request->execute();

#  $rows_affected = $request->rows;
  $err = $request->err;

  if($err) {
    print "*** ERROR: ".$request->err." ".$request->errstr."\n" if($debug);
  }

  $request->finish;

}




######################################
sub update_post {

  my %item = @_;

#  $item{title} = addslashes($item{title});
#  $item{description} = addslashes($item{description});
#  $item{channel_descr} = addslashes($item{channel_descr});
#  $item{body} = addslashes($item{body});

#  my $title       = utf2win($item{title});
#  my $description = utf2win($item{description});
#  my $description = utf2win($item{channel_descr});
#  my $body        = utf2win($item{body});

  my $body;;
  $body = $item{body};
  Encode::_utf8_off($body);


  $query = "UPDATE ibf_posts
	    SET
		post       ='".addslashes($body)."',
		author_id  ='".$item{user_id}."',
		author_name='".$item{user_name}."',
		ip_address ='127.0.0.1',
		indexed    = 1
	   WHERE 
		pid   ='".$item{post_id}."'
           ";

  if($debug) {	
    print "update_post:\n";
    print $query."\n";
    print "============================\n";
  }


  $request = $dbh->prepare($query);
  $request->execute();
#  $rows_affected = $request->rows;

#  # get the last inserted post id
#  $pid = $req->{'mysql_insertid'};
  $request->finish;

  $pid   = $item{post_id};
  return $pid;
}

######################################
sub insert_post {

  my %item = @_;

#  $item{title} = addslashes($item{title});
#  $item{description} = addslashes($item{description});
#  $item{channel_descr} = addslashes($item{channel_descr});
#  $item{body} = addslashes($item{body});

#  my $title       = utf2win($item{title});
#  my $description = utf2win($item{description});
#  my $description = utf2win($item{channel_descr});
#  my $body        = utf2win($item{body});

 my $page = $item{body};
 if($page =~ /\xD0.\xD0./) {
   print "insert_post: looks like UTF-8!!!\n" if($debug);
   $charset = 'utf-8';
 } else {
   print "insert_post: seems NO utf8\n" if($debug);
   $charset = 'windows-1251';
 }

 # Convert Body to 1251
 if($charset eq 'utf-8') {
#   $page = utf2win($page);
#   print "cnews_body_rewrite: convert to 1251\n" if($debug);
 }
# $page = Encode::decode($page,'windows-1251');
# $page = utf2win($page);

  my $body;;
  $body = $item{body};
  Encode::_utf8_off($body);

#  $body = Encode::decode('WTF-8',$body);
#  $body = Encode::decode('utf8',$body);
  $body = utf2win($body);

  $query = "INSERT INTO ibf_posts
	    SET
		forum_id   ='".$item{forum_id}."',
		topic_id   ='".$item{topic_id}."',
		post_date  ='".$item{pubdate}."',
		post       ='".addslashes($body)."',
		author_id  ='".$item{user_id}."',
		author_name='".$item{user_name}."',
		ip_address ='127.0.0.1',
		indexed    = 1
           ";

  if($debug) {	
    print "insert_post:\n";
    print $query."\n";
    print "============================\n";
  }


  $request = $dbh->prepare($query);
  $request->execute();
#  $rows_affected = $request->rows;

  # get the last inserted post id
  $pid = $req->{'mysql_insertid'};

  print "inserted_post_id: $pid\n" if($debug);

  $request->finish;

  return $pid;
}

##########################################################
sub update_forum {

  my %item = @_;

#  print "update_forum:\n";
#  print "channel_descr: ".$item{channel_descr}."\n";
#  print "forum_id: ".$item{forum_id}."\n";
#  print "user_id: ".$item{user_id}."\n";
#  print "user_name: ".$item{user_name}."\n";
#  print "--------------------\n";
#  print "pubdate: ".$item{pubdate}."\n";
#  print "title: "  .$item{title}."\n";
#  print "post_date: "   .$item{pubdate}."\n";
#  print "topic_id: "   .$item{topic_id}."\n";
#  print "post_id: "   .$item{post_id}."\n";
#
#                posts=posts+1,

  my $title       = utf2win($item{title});
#  my $description = utf2win($item{description});
#  my $description = utf2win($item{channel_descr});
#  my $body        = utf2win($item{body});

  $query = "UPDATE ibf_forums
	    SET
                topics           = topics+1,
		last_id          = '".$item{'topic_id'}."',
		last_title       = '".addslashes($title)."',
		last_post        = '".$item{'pubdate'}."',
		last_poster_id   = '".$item{'user_id'}."',
		last_poster_name = '".$item{'user_name'}."'
            WHERE id='".$item{'forum_id'}."'";

  if($debug) {	
    print "update_forum:\n";
    print $query."\n";
    print "============================\n";
  }

  $dbh->do($query);

}




#----------------------------------------
# vot: new extract body algorithm
sub cnews_body_rewrite {

 my $link = shift;
 my $page = shift;

#  my $body;;
#  $body = $item{body};
 Encode::_utf8_off($page);
 $page =~ s/\r//g;

 # Check for Page Charset
#<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />

 $charset = 'windows-1251';

 if($page =~ /<meta http-equiv\=\"Content-Type\" content\=\"text\/html; charset=([^\"]+)\">/i) {
   $charset = $1;
 }
 print "cnews_body_rewrite: charset=".$charset."\n" if($debug);

 if($page =~ /\xD0.\xD0./) {
   print "cnews_body_rewrite: looks like UTF-8!!!\n" if($debug);
   $charset = 'utf-8';
 } else {
   print "cnews_body_rewrite: seems NO utf8\n" if($debug);
   $charset = 'windows-1251';
 }

 # Convert Body to 1251
 if($charset eq 'utf-8') {
   $page = utf2win($page);
   print "cnews_body_rewrite: convert to 1251\n" if($debug);
 }
# $page = Encode::decode($page,'windows-1251');
# $page = utf2win($page);


 my $error = 0;

 my $base_url=$link;
 $base_url =~ s/^(http:\/\/[^\/]+)\/.+?$/$1/i;


# # original news page
# $page=~ s/^.+<div id=\"readAlso\">//is;
# $page=~ s/^.*<\/div><\/noindex>//is;
# $page=~ s/<p id\=\"nbWhatToDo\">.+$>//is;
# $page=~ s/<noindex>.+$//is;

 if($debug) {
#   print "cnews_body_rewrite: link =".$link."\n";
#   print "cnews_body_rewrite: base_url =".$base_url."\n";
#   print "cnews_body_rewrite: original body:\n";
#   print $page."\n";
#   print "############ END OF BODY #######################\n";
 }

 # Remove Header
 $page =~ s/^.+<body/<body/is;
 $page =~ s/<body[^>]*>//is;

 if($debug) {
#   print "cnews_body_rewrite: body after remove Header:\n";
#   print $page."\n";
#   print "############ END OF BODY #######################\n";
 }

 # Remove Footer
 $page=~ s/<div align=right><hr>.+$//is;
 $page =~ s/^(.+ : [^\s\r\n]+).*$/$1/is;

 if($debug) {
#   print "cnews_body_rewrite: body after remove Footer:\n";
#   print $page."\n";
#   print "############ END OF BODY #######################\n";
 }


 # Remove Preface & Print Button

 if($page =~ /^.*<hr[^>]*?>/is) {
   # printed news page
#   $page =~ s/^.+<hr[^>]+>//is;
#   $page =~ s/^.+(?<!\<hr)<hr[^>]*?>//is;
#   $page =~ s/^.+<hr( noshade)*?( size\=\d)*?( align\=\w+)*?( width\=\d+)*?>//is;
   $page =~ s/^.*<hr[^>]*?>//is;

   if($debug) {
#     print "cnews_body_rewrite: body after remove Preface:\n";
#     print $page."\n";
#     print "############ END OF BODY #######################\n";
   }


 } else {

    
   $page =~ s/^.+<div id\=\"pr_rewrite\"\>//is;

#   $page =~ s/^.+<h(\d)[^>]*>/<h$1>/is;
   $page =~ s/^.+<h2[^>]*>/<h2>/is;

   if($debug) {
#     print "cnews_body_rewrite: check for <H2:\n";
#     print $page."\n";
#     print "################# END OF BODY #####################\n";
   }

   $page =~ s/\<base href\=\"http:\/\/[^>]+>.+$//is;
#               <base href="http://pr.cnews.ru/">

   if($debug) {
#     print "cnews_body_rewrite: check for <base:\n";
#     print $page."\n";
#     print "################# END OF BODY #####################\n";
   }

   
 }

 $page =~ s/<p><b>Читайте на CNews<\/b>.+<\/p>//is;

 $page =~ s/<style.+<\/style>//igs;
 $page =~ s/<form.+<\/form>//igs;
 $page =~ s/<script.+<\/script>//igs;

#<a href="/company_prs_s.shtml?1/12053">
#  http://pr.cnews.ru/company_prs_s.shtml?1/12053

 $page =~ s/<a href\=\"\/([^\"]+)\">/<a href=\"$base_url\/$1\">/is;


 $page =~ s/<\!-- \!+-->.+<\!-- \!+-->//is;


 if($page =~ /\[an error occurred while processing this directive\]/i) {
   $page = "";
   if($debug) {print "cnews_body_rewrite: SSI ERROR. clean result body.\n";}
 }

 if($page) {
  if($page !~ / : <b>http:/) {
#   $page .= "<br><br> : $link";
  }
 }


 if($page =~ /this\.href\=mmDecode/) {
   $page = "";
 } else {
   $page = rebuild_html($page);
 }

 if($debug) {
#   print "cnews_body_rewrite: result body:\n";
#   print $page."\n";
#   print "################# END OF BODY #####################\n";
 }
 return $page;
}

#---------------------------------------------------------
sub rebuild_html
{
#  my $newpage=$_[0];
  my $newpage=shift;

  $newpage=~s/\r//g;			# vot: remove all CR

  $newpage=~s/\n/ /g;			# \n => " "

  $newpage=~s/\t/ /g;			# vot: remove middle spaces

  $newpage=~s/&nbsp;/ /ig;		# &nbsp; => " "

#  $newpage=~s/-/ - /ig;			# - => " - "

#  $newpage=~s/&quot;/"/ig;		# &quot; => "
#  $newpage=~s/\"/\&quot;/ig;		# &quot; => "

  $newpage=~s/<br[^>]*>/[br]/ig;	# <br> => \n

#  $newpage=~s/<i>/[i]/ig;		# <i> => [i]
#  $newpage=~s/<\/i>/[\/i]/ig;		# <i> => [i]

  $newpage=~s/<b[^>]*>/[b]/ig;		# <b> => [b]
  $newpage=~s/<\/b>/[\/b]/ig;		# <b> => [/b]

  $newpage=~s/<strong>/[b]/ig;		# <strong> => [b]
  $newpage=~s/<\/strong>/[\/b]/ig;	# </strong> => [/b]

  $newpage=~s/<h\d>/[b]/ig;		# <hn>  => [b]
  $newpage=~s/<\/h\d>/[\/b]/ig;		# </hn> => [/b]

  $newpage=~s/<small[^>]*>/[size=1]/ig;	# <small> => [size=1]
  $newpage=~s/<\/small>/[\/size]/ig;	# </small>  [/size]

  $newpage=~s/<P align=center>([^>]+)<\/P>/[c]${1}[\/c]/ig;	# <P align=center> </P> => [c] [/c]
  $newpage=~s/<P[^>]*>/[br][br]/ig;	# <P> => <br><br>
  $newpage=~s/<\/P>/\n/ig;		# <P> => \n 

  # convert links to BB-Code

# !!! DOES NOT WORK

  $newpage=~s/<a[^>]*href=\s*[\"\']*([^\'\">]+)[\"\']*[^>]*>/[url=$1]/ig;	# <a> => [url]
  $newpage=~s/<\/a>/[\/url]/isg;		# <a> => [/url]
# !!! DOES NOT WORK
  $newpage=~s/<img[^>]*src=["']([^"']+)["'][^>]*>/[img]${1}[\/img]/ig;	# <img> => [img][/img]	


  $newpage=~s/<[^>]+>/ /g;		# remove all html tags

  $newpage=~s/\[br\]/\n/ig;		# [br] => \n

#  $newpage =~ s/\\\"/"/gm;



  # remove repeated \n & spaces

  $newpage=~s/^\s+//m;			# vot: remove leading spaces & \n
  $newpage=~s/^\s+//gm;			# vot: remove leading spaces

  $newpage=~s/\s+$//gm;			# vot: remove trailing spaces

  $newpage=~s/\n+/\n\n/g;

  $newpage=~s/ +/ /gm;			# vot: remove middle spaces





  # fix html bugs
  $newpage=~s/##[^[]*\[\/img\]/[\/img]/isg;
  $newpage=~s/\[url\=\s*/[url=/ig;


  # Expand relative pathes
#  $newpage=~s/\[url\=\#\]/[url=${page_url}]/isg;
#  $newpage=~s/\[url=\/([^\]]+)\]/[url=${base_url}${1}]/g;

  # Make auto-URL
  $newpage=~s/\[b\](http:.+)\[\/b\]$/$1/i;

 #debug:
 if($debug) {
#   print "REBUILD_HTML:\n";
#   print $newpage."\n";
#   print "-------END OF REBUILD HTML------------\n";
 }
  
  return $newpage;
}



#----------------------------------------
sub save_body_to_file {
  my $file = shift;
  my $body = shift;

  open(OUT, ">$file");
  print OUT $body;
  close(OUT);
}

#--------------------------------------------
sub clean_html
{
  my $body=shift;

  $body=~s/\r//g;		# vot: remove all CR
  $body=~s/\n/ /g;		# \n => " "
  $body=~s/&amp;/\&/ig;		# &amp; => "&"

  $body=~s/<[^>]+>/ /g;		# remove all html tags


  # remove repeated \n & spaces

  $body=~s/\n+/\n\n/sg;
  $body=~s/^\s+//g;		# vot: remove leading spaces
  $body=~s/\s+$//g;		# vot: remove trailing spaces
  $body=~s/\s+/ /g;		# vot: remove middle spaces

  return $body;
}


#--------------------------------------------------------
sub trim {
  my $content = shift;

  $content=~s/^\s+//ms;			# vot: remove leading spaces
  $content=~s/\s+$//ms;			# vot: remove trailing spaces

  return $content;
}


#--------------------------------------------------------
sub addslashes {
  my $s = shift;

  $s = "" if (!$s);
  chomp ($s);
  $s =~ s/\r//gm;
  $s =~ s/\\/\\\\/gm;
#  $s =~ s/\n/\<br\>/gm;
  $s =~ s/\n/\\n/gm;
  $s =~ s/\t/\\t/gm;
  $s =~ s/\$/\\\$/gm;
#  $s =~ s/\'/\\\'/gm;
#  $s =~ s/\"/\\\"/gm;
#  $s =~ s/\"/\\"/gm;
#  $s =~ s/\%/\\%/gm;
#  $s =~ s/\%/\\%/gm;
  $s =~ s/\'/\\'/gm;
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


#--------------------------------------------------------
# Returns a string with backslashes stripped off. (\' becomes ' and so on.)
# Double backslashes are made into a single backslash
sub stripslashes {
  my $s = shift;
  chomp ($s);
  $s =~ s/\\(.)/$1/gm;
  return $s;
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
#02/06/02  00:32:59
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



#---------------------------------------------------------------

sub index_posts {

  my $post_id  = shift;
  my $topic_id = shift;
  my $forum_id = shift;
  my $post     = shift; # This is a Post or Topic Title

  my $dsql = "";
  my $req  = "";


  # Parse the post body

  my @unique = stripwords($post);


  $post_id = '0' unless($post_id); # set post id to "0" for topic indexing


  # delete indexed earlier words for this post/topic

  if($post_id) {
    $dsql = "DELETE FROM ibf_search WHERE pid='" . $post_id . "'";
  } else {
    $dsql = "DELETE FROM ibf_search WHERE tid='" . $topic_id . "' AND pid=0";
  }

  $req = $dbh->prepare($dsql);
  $req->execute();
  $req->finish;

  if (scalar(@unique)) {

    foreach (@unique){
      chomp($_);
      my $id = 0;

      # get id of existing word
      $dsql = "SELECT id FROM ibf_search_words WHERE word='".$_."'";
      $req = $dbh->prepare($dsql);
      $req->execute();
      my @row = $req->fetchrow_array();

      $id = $row[0];                    # ID
      $req->finish;


      # if the word is present in a database
      if ( $id ) {
        # print "$_ id\=$id$BR\n";
      } else {
        # insert the word
        # print "$_ not found$BR\n";
#        print "$_ ";
        $dsql = "INSERT INTO ibf_search_words (word) VALUES ('".$_."')";
        $req = $dbh->prepare($dsql);
        $req->execute();

        # get the last inserted word id
        $id = $req->{'mysql_insertid'};

        $req->finish;
      }



      # add the word id to the post record

      if ( $id ) {
        $dsql = "INSERT INTO ibf_search VALUES (".$post_id.",".$topic_id.",".$forum_id.",".$id.")";
        if($debug) {	
          print "index_posts query:\n";
          print $dsql."\n";
          print "============================\n";
        }
        $req = $dbh->prepare($dsql);
        $req->execute();

        # get the last inserted word id
#           $id = $req->{'mysql_insertid'};

        $req->finish;
      }
    }
  }
#  print "$BR\n";
#
#  if($post_id) {
#    $dsql = "UPDATE ibf_posts SET indexed=1 WHERE pid='".$post_id."'";
#  } else {
#    $dsql = "UPDATE ibf_topics SET indexed=1 WHERE tid='".$topic_id."'";
#  }
#  $req = $dbh->prepare($dsql);
#  $req->execute();
#  $req->finish;
}


#############################################
# Parse the content, strip unique words
sub stripwords {

  my $post = shift;

  my $min_length = 3;  # minimum word length to index
  my $max_length = 32;  # max word length to index
  my $CAP_LETTERS = '\xC0-\xDF';  # Capital letters
  my $LOW_LETTERS = '\xE0-\xFF';  # Lower case letters
  my $numbers = '0-9';  # Numbers


#  print to_lower_case ("UPPER CASE")."<br>\n";

  $post = to_lower_case($post);

  $post =~ s/<script.*?<\/script>/ /igs;
  $post =~ s/<style.*?<\/style>/ /igs;

  #    ($plain_text = $html_text) =~ s/<(?:[^>'"]*|(['"]).*?\1)*>//gs;

  # Clean UBB quote tags
#[QUOTE=Summit-,5.11.03, 15:58] +> R  -R <R ......  -R_   R < -? 
#      _ R_ RR _R R<!     ;D [/QUOTE]
#[QUOTE=perch,5.11.03, 21:20] ( -R  Rc _ , R<R RR< _-_ R..., R_- -  :blink:  ) [/QUOTE]-R
#<!--quotebegin--> ...... <!--quoteend-->
#<!--quotebegin-oleggg+6.11.03, 15:51--> ..... <!--quoteend-->
#<!--quoteebegin--> .... <!--quoteeend-->
#<b>quote</b>
#[quote=flex ferrum, 05.11.03, 10:24:35].   ![/quote]

  $post =~ s#<\!--quotebegin-([^\-\-\>]+)-->#<\!--quotebegin-->#igs;
  $post =~ s#<\!--quoteebegin-->#<\!--quotebegin-->#igs;
  $post =~ s#<\!--quoteeend-->#<\!--quoteend-->#igs;

  while ( $post =~ m#<\!--quotebegin-->(?!<\!--quotebegin-->).+<\!--quoteend-->#is) {
    $post =~ s#<\!--quotebegin-->(?!<\!--quotebegin-->).+<\!--quoteend--># #is;
  }

  $post =~ s#\[quote[^\]]*\](.+?)\[/quote\]# #igs;
  

#    while ( $post =~ m#<\!--quotebegin-->(.+?)<\!--quoteend-->#is) {
#      $post =~ s#<\!--quotebegin-->(.+?)<\!--quoteend--># #igs;
#    }

#    while ( $post =~ m#<\!--quotebegin-->(?<!<\!--quoteend-->)<\!--quoteend-->#is) {
#      $post =~ s#<\!--quotebegin-->(?<!<\!--quoteend-->)<\!--quoteend--># #is;
#    }

#    $post =~ s#<\!--quotebegin-(.+?)-->(.+?)<\!--quoteeend--># #igs;
#    $post =~ s#<\!--quotebegin-->(.+?)<\!--quoteeend--># #igs;
#    $post =~ s#<\!--quoteebegin-->(.+?)<\!--quoteend--># #igs;
#    $post =~ s#<\!--quotebegin-->(.+?)<\!--quoteend--># #igs;

  # Clean smiles
  $post =~ s#<\!--emo\&(.+?)-->(.+?)<\!--endemo-->#$1#igs;
  $post =~ s/:[\w\d]*:/ /gs;
#<!--emo&:)--><img src='http://forum.sources.ru/html/emoticons/smile.gif' border='0' style='vertical-align:middle' alt='smile.gif' /><!--endemo-->

  # Clean UBB tags
  $post =~ s/\[[^\]]*\]/ /gs;

  # Clean HTTP:// prefixes
  $post =~ s/(https|http|ftp):\/\// /gs;

  # Clean HTML tags
  $post =~ s/<[^>]*>/ /gs;

#    if ($use_esc eq "YES") { $post =~ s/(&.*?;)/&esc2char($1)/egs; }
  $post =~ s/(&.*?;)/ /gs;


  $post =~ s/\&amp;/\&/gs;
  $post =~ s/\&lt;/</gs;
  $post =~ s/\&gt;/>/gs;
  $post =~ s/\&nbsp;/ /gs;

#  print "stripwords started.\n";
#  print "Post:\n";
#  print $post."\n";
#  print "---------------\n";



  my $chars = "a-zA-Z_".$CAP_LETTERS.$LOW_LETTERS.$numbers;
#    my @wwd = ($post =~ m/([$chars]+-[$chars-]+[$chars]+)/gs);
#    my $wwd = join " ", @wwd;
  $post =~ s/[^$chars]/ /gs;


#    $plain_text = $plain_text." ".$wwd;

  $post =~ s/\s+/ /gs;
  $post =~ s/\s{2,}/ /gs;

  $post = trim($post);

#    $post =~ tr/A-Z/a-z/;
#    $post = to_lower_case($post);


#  print "Cleaned Post:\n";
#  print $post."\n";
#  print "---------------\n";




  my @results=split (/ /,$post);


  my %seen = ();
  my @uniq = ();
  my $i = 1;

#  print "splitted Post:\n";

  foreach my $item (@results) {
#    print "$i: $item\n";

    if ((length($item) >= $min_length) && ((length($item) <= $max_length))) {
      unless ($seen{$item}) {
        $seen{$item} = 1;
        push(@uniq, $item);
      };
#       print "$item ";
    }
    $i++;
  };


#  print "---------------\n";
#print $post;
#print "<hr>\n";
#print "Word Count=".scalar(@results)."<br>\n";


  $i = 1;
  foreach my $item (@uniq) {
#   print "$i: $item<br>\n";
    $i++;
  }


  return @uniq;
}



#----------------------------
sub to_lower_case {
  my $str = lc(shift);
  $str =~ tr{\xC0-\xDF}{\xE0-\xFF};
  return $str;
}


################################################
# XML Parser Handlers:

# this is called when a start tag is found
sub start()
{
  # extract variables
  my ($parser, $name, %attr) = @_;

  $currentTag = lc($name);
  $currentLevel .= "/$currentTag";

  # check for the tag attributes
#  print "[".$currentTag;
#  foreach $k (keys %attr) {
#   print " ".$k."=".$attr{$k};
#  }
#  print "]\n";

}


#--------------------------------------------------------
# this is called when CDATA is found
sub cdata()
{
  my ($parser, $data) = @_;

  $data = $parser->original_string(); #    UTF-8!

  if ($currentTag) {
    $tagvalue .=$data;
  }
}


#--------------------------------------------------------
# this is called when an end tag is found
sub end()
{
  my ($parser, $name) = @_;

  $currentTag = lc($name);

  if ($currentTag)
  {

    $tagvalue = clean_html($tagvalue);

    $parentLevel = $currentLevel;
    $parentLevel =~ s/\/[^\/]+$//;

    if ($tagvalue)
    {
#      print "$currentLevel ";
#      print ", parentLevel:".$parentLevel."\n";

      if ($parentLevel eq "/rss/channel/item")
      {
        $rssItems[$itemNumber]{$currentTag} = $tagvalue;
      } elsif ($parentLevel eq "/rss/channel/image") {
        $rssImage{$currentTag} = $tagvalue;
      } else {
        $rss{$currentTag} = $tagvalue;
      }
#      print "[".$currentTag."]".$tagvalue."[/".$currentTag."]\n";
    }

    if ($currentLevel eq "/rss/channel/item")
    {
      $itemNumber++;
    }
  }

  $tagvalue="";

  # clear value of current tag
  $currentTag = "";
  $currentLevel =~ s/\/[^\/]+$//;
}


#--------------------------------------------------------
sub getEncoding {
  my $content = shift;
  my $encoding = "";

  if($content =~ /\s*<\?xml\s+version\=[\"\']\d\.\d+[\"\']\s+encoding\=[\"\'](.+?)[\"\']\s*\?>/) {
    $encoding = $1;
  }

  return $encoding;
}

#=====================================================================
#
#    Function: utf8_to_win1251
#    Converts UTF-8 to windows-1251
#    Last modified: 22.06.2008 12:53
#
#=====================================================================
sub utf2win {
  my $str = shift;
#  Encode::from_to($str, "utf-8", "windows-1251");
  Encode::from_to($str, "utf8", "windows-1251");
#  $str = encode("cp1251", decode("utf8", $str)); 
  return $str;
}

#=====================================================================
#
#    Function: utf8_to_win1251
#    Converts UTF-8 to windows-1251
#    Last modified: 22.06.2008 12:53
#
#=====================================================================

sub utf8_to_win1251 {

  my $str = shift;

  my %chars = (
  "\xD0\xB0"=>"a", "\xD0\x90"=>"",
  "\xD0\xB1"=>"", "\xD0\x91"=>"",
  "\xD0\xB2"=>"", "\xD0\x92"=>"",
  "\xD0\xB3"=>"", "\xD0\x93"=>"",
  "\xD0\xB4"=>"", "\xD0\x94"=>"",
  "\xD0\xB5"=>"", "\xD0\x95"=>"",
  "\xD1\x91"=>"", "\xD0\x81"=>"",
  "\xD0\xB6"=>"", "\xD0\x96"=>"",
  "\xD0\xB7"=>"", "\xD0\x97"=>"",
  "\xD0\xB8"=>"", "\xD0\x98"=>"",
  "\xD0\xB9"=>"", "\xD0\x99"=>"",
  "\xD0\xBA"=>"", "\xD0\x9A"=>"",
  "\xD0\xBB"=>"", "\xD0\x9B"=>"",
  "\xD0\xBC"=>"", "\xD0\x9C"=>"",
  "\xD0\xBD"=>"", "\xD0\x9D"=>"",
  "\xD0\xBE"=>"", "\xD0\x9E"=>"",
  "\xD0\xBF"=>"", "\xD0\x9F"=>"",
  "\xD1\x80"=>"", "\xD0\xA0"=>"",
  "\xD1\x81"=>"", "\xD0\xA1"=>"",
  "\xD1\x82"=>"", "\xD0\xA2"=>"",
  "\xD1\x83"=>"", "\xD0\xA3"=>"",
  "\xD1\x84"=>"", "\xD0\xA4"=>"",
  "\xD1\x85"=>"", "\xD0\xA5"=>"",
  "\xD1\x86"=>"", "\xD0\xA6"=>"",
  "\xD1\x87"=>"", "\xD0\xA7"=>"",
  "\xD1\x88"=>"", "\xD0\xA8"=>"",
  "\xD1\x89"=>"", "\xD0\xA9"=>"",
  "\xD1\x8A"=>"", "\xD0\xAA"=>"",
  "\xD1\x8B"=>"", "\xD0\xAB"=>"",
  "\xD1\x8C"=>"", "\xD0\xAC"=>"",
  "\xD1\x8D"=>"", "\xD0\xAD"=>"",
  "\xD1\x8E"=>"", "\xD0\xAE"=>"",
  "\xD1\x8F"=>"", "\xD0\xAF"=>"",
  "\xC2\xAB"=>"&laquo;", "\xC2\xBB"=>"&raquo;",
  );   

  $str =~ s|\xE2\x80\x93|&minus;|sg; # minus
  $str =~ s|\xE2\x80\x94|&mdash;|sg; # tire
  $str =~ s|\xE2\x84\xA2|&trade;|sg;
  $str =~ s|([\xD0\xD1\xC2].)|$chars{$1}|sg;

  return $str;
}


#$sub ="s1";
#print $sub."\n";
#$s2 = &$sub();
#print $s2."\n";
#exit;

#sub s1{
#return "qqq";
#}

#exit;


# end