#!/usr/bin/php
<?php
  error_reporting( E_ALL & ~E_NOTICE );
  define( 'FRAMEWORK_SQLHOST', 'localhost' );
  define( 'FRAMEWORK_SQLDB', 'db' );
  define( 'FRAMEWORK_SQLUSER', 'root');
  define( 'FRAMEWORK_SQLPW', '' );
  $wikis = array( 'wikia', 'wikib', 'wikic' );
  
  include( 'mysql.php' );
  
  $db = sql_connect();

  if( !isset( $argv[1] ) ) {
    echo 'no username(s) given, aborting...'."\n";
    exit;
  }
  array_shift( $argv );

  foreach( $argv as $user ) {
    # iterate through all wikis
    foreach( $wikis as $wiki ) {
      # get user details for user to delete
      $r1 = sql( 'SELECT * FROM '.$wiki.'_user WHERE user_name = "'.$user.'";' );
      while( $wiki_user = mysqli_fetch_assoc( $r1 ) ) {
        echo 'deleting user '.$user.' from '.$wiki.' with user_id = '.$wiki_user['user_id']."\n";
        # do the cleanup
        sql( 'DELETE FROM '.$wiki.'_archive WHERE ar_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_filearchive WHERE fa_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_image WHERE img_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_ipblocks WHERE ipb_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_logging WHERE log_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_oldimage WHERE oi_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_page_restrictions WHERE pr_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_protected_titles WHERE pt_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_recentchanges WHERE rc_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_revision WHERE rev_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_revision WHERE rev_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_uploadstash WHERE us_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_user WHERE user_id = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_user_former_groups WHERE ufg_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_user_groups WHERE ug_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_user_newtalk WHERE user_id = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_user_properties WHERE up_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_validate WHERE val_user = "'.$wiki_user['user_id'].'";' );
        sql( 'DELETE FROM '.$wiki.'_watchlist WHERE wl_user = "'.$wiki_user['user_id'].'";' );
      } # END while wiki_user
    } # END foreach wikis
  } # END foreach argv
?>