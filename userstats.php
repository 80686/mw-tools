#!/usr/bin/php
<?php
  ## creates a CSV file of all users found in a database
  ## stdout is used - run with "php userstats.php > myfile.csv"
  ## showing username, ID, number of edits, number of watchlist items, blocks
  ## this routine will combine data of several wikis if they share the database

  error_reporting( E_ALL & ~E_NOTICE );
  define( 'FRAMEWORK_SQLHOST', 'localhost' );
  define( 'FRAMEWORK_SQLDB', 'db' );
  define( 'FRAMEWORK_SQLUSER', 'root');
  define( 'FRAMEWORK_SQLPW', '' );
  # define the wikis (MediaWiki table name prefix)
  $wikis = array( 'wikia', 'wikib', 'wikic' );
  
  include( 'mysql.php' );
  
  $db = sql_connect();
  # collect all usernames from all wikis
  unset( $users );
  foreach( $wikis as $wiki ) {
    $r1 = sql( 'SELECT user_name, user_id FROM '.$wiki.'_user' );
    while( $wiki_user = mysql_fetch_assoc( $r1 ) ) {
      # store user_id
      $users[$wiki_user['user_name']][$wiki]['id'] = $wiki_user['user_id'];
      # get edit count
      $r2 = sql( 'SELECT COUNT( rev_user ) AS count FROM '.$wiki.'_revision WHERE rev_user = "'.$wiki_user['user_id'].'" GROUP BY rev_user AND rev_deleted = 0;' );
      if( $edits = mysql_fetch_assoc( $r2 ) ) $users[$wiki_user['user_name']][$wiki]['edits'] = $edits['count'];
      # get watched articles
      $r2 = sql( 'SELECT COUNT( wl_user ) AS count FROM '.$wiki.'_watchlist WHERE wl_user = "'.$wiki_user['user_id'].'" GROUP BY wl_user;' );
      if( $watched = mysql_fetch_assoc( $r2 ) ) $users[$wiki_user['user_name']][$wiki]['watchlist'] = $watched['count'];
      # check blocks
      $r2 = sql( 'SELECT ipb_user FROM '.$wiki.'_ipblocks WHERE ipb_user = "'.$wiki_user['user_id'].'" AND ipb_expiry = "infinity";' );
      if( $edits = mysql_fetch_assoc( $r2 ) ) $users[$wiki_user['user_name']][$wiki]['blocked'] = 1;
    } # END while wiki_user
  } # END foreach wikis

  # output all user stats
  echo ';'; foreach( $wikis as $wiki ) echo $wiki.';'.$wiki.';'.$wiki.';'.$wiki.';'; echo "\n";
  echo 'username;'; foreach( $wikis as $wiki ) echo 'user_id;editcount;watchlist;blocked;'; echo 'status'."\n";
  foreach( $users as $username => $user ) {
    $edits = 0;
    $watched = 0;
    $blocked = 0;
    echo $username.';'; foreach( $wikis as $wiki ) {
      echo $user[$wiki]['id'].';'.$user[$wiki]['edits'].';'.$user[$wiki]['watchlist'].';'.$user[$wiki]['blocked'].';';
      $edits += $user[$wiki]['edits'];
      $watched += $user[$wiki]['watchlist'];
      $blocked += $user[$wiki]['blocked'];
    } # END foreach wikis
    if( $watched == 0 && $edits == 0 ) echo 'delete'."\n";
    elseif( $blocked > 0 ) echo 'blocked'."\n";
    else echo 'ok'."\n";
  } # END foreach users
?>