<?php

$orm = $_REQUEST['orm'];
$lexemId = $_REQUEST['lexemId'];

switch($orm) {
case 'adodb':
  $ADODB_ASSOC_CASE = 2;
  $db = initAdodb();

  class Comment extends ADOdb_Active_Record { }
  class Definition extends ADOdb_Active_Record { }
  class Source extends ADOdb_Active_Record { }
  class User extends ADOdb_Active_Record { }

  // Load the definitions
  $query = "select distinct D.* from Definition D, LexemDefinitionMap L where D.id = L.definitionId and L.lexemId = $lexemId and D.status = 0 order by D.lexicon";
  $defDbResult = $db->execute($query);

  $defs = [];
  while (!$defDbResult->EOF) {
    $def = new Definition();
    $def->set($defDbResult->fields);
    $defs[] = $def;
    $defDbResult->MoveNext();
  }

  // Load users, sources and comments for the definitions
  $results = [];
  foreach ($defs as $def) {
    $user = new User();
    $user->load("id = {$def->userId}");
    $comment = new Comment();
    $comment->load("definitionId = {$def->id}");
    $source = new Source();
    $source->load("id = {$def->sourceId}");
    $results[] = array('def' => $def,
                       'user' => $user,
                       'comment' => $comment,
                       'source' => $source);
  }
  break;

case 'idiorm':
  initIdiorm();

  class Comment extends Model { public static $_table = 'Comment'; }
  class Definition extends Model { public static $_table = 'Definition'; }
  class Source extends Model { public static $_table = 'Source'; }
  class User extends Model { public static $_table = 'User'; }

  // Load the definitions
  $query = "select distinct D.* from Definition D, LexemDefinitionMap L where D.id = L.definitionId and L.lexemId = $lexemId and D.status = 0 order by D.lexicon";
  $defs = ORM::for_table('Definition')->raw_query($query, null)->find_many();

  // Load users, sources and comments for the definitions
  $results = [];
  foreach ($defs as $def) {
    $results[] = array('def' => $def,
                       'user' => Model::factory('User')->find_one($def->userId),
                       'comment' => Model::factory('Comment')->where('definitionId', $def->id)->find_one(),
                       'source' => Model::factory('Source')->find_one($def->sourceId),
                       );
  }
  break;
}

output($results);
printIncludedFiles();

/*************************************************************************/

function initAdodb() {
  require_once("adodb/adodb.inc.php");
  require_once("adodb/adodb-active-record.inc.php");
  $db = NewADOConnection('mysql://root@localhost/DEX');
  ADOdb_Active_Record::SetDatabaseAdapter($db);
  ADOdb_Active_Record::$_changeNames = false; // Do not pluralize table names
  $db->Execute('set names utf8');
  // $db->debug = true;
  return $db;
}

function initIdiorm() {
  require_once("../phplib/idiorm/idiorm.php");
  require_once("../phplib/idiorm/paris.php");
  ORM::configure('mysql:host=localhost;dbname=DEX');
  ORM::configure('username', 'root');
  ORM::configure('password', '');
  ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
}

function output($results) {
  print "<pre>\n";
  foreach ($results as $r) {
    print "Definition id: {$r['def']->id}\n";
    print "Definition text: " . htmlspecialchars($r['def']->internalRep) . "\n";
    print "User id: {$r['user']->id}\n";
    print "User nick: {$r['user']->nick}\n";
    print $r['comment'] ? ("Comment: " . htmlspecialchars($r['comment']->contents) . "\n") : "Comment: empty\n";
    print "Source id: {$r['source']->id}\n";
    print "Source name: {$r['source']->name}\n";
  }
  print "</pre>\n";
}

function printIncludedFiles() {
  print "<pre>\n";
  $files = get_included_files();
  $sizes = [];
  foreach ($files as $file) {
    $sizes[] = filesize($file);
  }
  array_multisort($sizes, $files);
  foreach($sizes as $i => $size) {
    printf("%7d [%s]\n", $size, $files[$i]);
  }
  print "Total: " . array_sum($sizes) . "\n";
  print "</pre>\n";
}

?>
