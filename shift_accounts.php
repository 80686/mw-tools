#!/usr/bin/php
<?php
  ## This script is being used to shuffle user IDs.
  ## This is needed when you try to synchronise the user IDs between two wikis,
  ## before you start using a common user table.

  $i = -1;
  # define old and new IDs - replace these lines with your IDs - use as many lines as you need
  $todo[$i++] = array( 'from' => 5, 'to' => 148 );
  $todo[$i++] = array( 'from' => 6, 'to' => 5 );
  $todo[$i++] = array( 'from' => 7, 'to' => 6 );
  $todo[$i++] = array( 'from' => 8, 'to' => 7 );
  $todo[$i++] = array( 'from' => 3, 'to' => 8 );
  $todo[$i++] = array( 'from' => 4, 'to' => 3 );
  $todo[$i++] = array( 'from' => 8, 'to' => 4 );

  # database connection details
  $wgDBServer = 'localhost';
  $wgDBuser = 'username';
  $wgDBname = 'dbname';
  $wgDBpassword = 'password';
  $wgDBprefix = 'mw_';

  $i = -1;
  # Tables to be updates: table name, column name for user ID
  $table[$i++] = array( 'table' => 'archive',		'col' => 'ar_user' );
  $table[$i++] = array( 'table' => 'external_user',	'col' => 'eu_local_id' );
  $table[$i++] = array( 'table' => 'filearchive',	'col' => 'fa_user' );
  $table[$i++] = array( 'table' => 'ipblocks', 		'col' => 'ipb_user' );
  $table[$i++] = array( 'table' => 'ipblocks', 		'col' => 'ipb_by' );
  $table[$i++] = array( 'table' => 'image', 		'col' => 'img_user' );
  $table[$i++] = array( 'table' => 'logging', 		'col' => 'log_user' );
  $table[$i++] = array( 'table' => 'oldimage', 		'col' => 'oi_user' );
  $table[$i++] = array( 'table' => 'page_restrictions',	'col' => 'pr_user' );
  $table[$i++] = array( 'table' => 'protected_titles',	'col' => 'pt_user' );
  $table[$i++] = array( 'table' => 'recentchanges', 	'col' => 'rc_user' );
  $table[$i++] = array( 'table' => 'revision', 		'col' => 'rev_user' );
  $table[$i++] = array( 'table' => 'user', 		'col' => 'user_id' );
  $table[$i++] = array( 'table' => 'user_groups', 	'col' => 'ug_user' );
  $table[$i++] = array( 'table' => 'user_newtalk', 	'col' => 'user_id' );
  $table[$i++] = array( 'table' => 'user_properties', 	'col' => 'up_user' );
  $table[$i++] = array( 'table' => 'watchlist', 	'col' => 'wl_user' );

  # check order:
  $empty = '';
  $i = 0;
  $error = false;
  foreach( $todo as $user ) {
    if( $user['to'] == $empty ) $result = 'ok';
    elseif( $i == 0 ) $result = 'we have to assume that '.$user['to'].' is empty!';
    else {
      $result = 'ERROR: '.$user['to'].' is going to be overwritten!';
      $error = true;
    }
    echo 'from: '.$user['from'].' to: '.$user['to'].' - '.$result."\n";
    $empty = $user['from'];
    $i++;
  }
  if( $error ) die( 'moving order not in order, aborting' );
  
  # connect to database
  if( !$db = mysql_connect( $wgDBServer, $wgDBuser, $wgDBpassword ) )
    die( 'Cannot connect to database server '.$wgDBServer );
  if( !mysql_select_db( $wgDBname, $db ) )
    die( 'Cannot open database '.$wgDBname );
  
  # check tables
  foreach( $table as $task ) {
    $r = mysql_query( 'DESCRIBE '.$wgDBprefix.$task['table'].' '.$task['col'], $db );
    if( !$data = mysql_fetch_assoc( $r ) )
      die( 'Can\'t find column '.$task['col'].' in table '.$wgDBprefix.$task['table'] );
    if( $data['Type'] != 'int(10) unsigned' && $data['Type'] != 'int(11)' )
      die( 'Column '.$task['col'].' in table '.$wgDBprefix.$task['table'].' is not a user id column' );
  }
  echo 'database structure is clean - proceed...'."\n";
  
  # process users and tables
  foreach( $todo as $user ) {
    echo 'moving userid '.$user['from'].' to '.$user['to'].':'."\n";
    foreach( $table as $task ) {
      echo 'processing table '.$wgDBprefix.$task['table'];
      $r = mysql_query( '
        UPDATE '.$wgDBprefix.$task['table'].' 
          SET '.$task['col'].' = '.$user['to'].' 
          WHERE '.$task['col'].' = '.$user['from']
        , $db );
      echo ' done'."\n";
    }
  }
  echo 'done, all users have been moved, all tables are updated.'."\n";
    
  # disconnect from database
  mysql_close( $db );
?>