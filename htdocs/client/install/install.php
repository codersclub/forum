<?
// (c) Alexander Vaulin
die('Access disabled.');

   require "../conf_global.php";

   require "../sources/functions.php";
   $std   = new FUNC;
   $print = new display();
   $sess  = new session();

   $to_require = "../sources/Drivers/".$INFO['sql_driver'].".php";
   require ($to_require);

// connect to database
   $DB = new db_driver;
   $DB->obj['sql_database']     = $INFO['sql_database'];
   $DB->obj['sql_user']         = $INFO['sql_user'];
   $DB->obj['sql_pass']         = $INFO['sql_pass'];
   $DB->obj['sql_host']         = $INFO['sql_host'];
   $DB->obj['sql_charset']      = $INFO['sql_charset'];
   $DB->obj['sql_tbl_prefix']   = $INFO['sql_tbl_prefix'];
   $DB->obj['debug']            = ($INFO['sql_debug'] == 1) ? $_GET['debug'] : 0;
   $DB->connect();

   $query = "
CREATE TABLE client_members (
  nickname varchar(30) NOT NULL default '',
  email varchar(50) NOT NULL default '',
  date date NOT NULL default '0000-00-00',
  paid smallint(6) NOT NULL default '20',
  comment varchar(100) default NULL,
  UNIQUE KEY nickname (nickname)
) TYPE=MyISAM";

   $DB->query( $query );
