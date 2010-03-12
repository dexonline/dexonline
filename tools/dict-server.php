#!/usr/bin/php -q
<?php
// Author: Catalin Francu
// Implementation of the dict.org protocol for use with DEX online

require_once("../phplib/util.php");

main();

function main() {
  global $argv, $argc;
  if ($argc != 2) {
    print "Usage: " . $argv[0] . " [port]\n";
    exit();
  }
  $port = $argv[1];
  
  error_reporting (E_ALL);
  
  /* Allow the script to hang around waiting for connections. */
  set_time_limit(0);
  
  /* Turn on implicit output flushing so we see what we're getting
   * as it comes in. */
  ob_implicit_flush();
  
  if (($sock = socket_create_listen($port, 10)) === false) {
    print "Cannot listen on $port!\n";
    exit();
  }
  
  $open = array();
  while(true) {
    // Accept a connection and receive a file descriptor
    $sock_array = array($sock);
    $num_changed_sockets = socket_select($sock_array, $write = NULL,
                                         $exc = NULL, 0, 100000); // 0.1 sec
    if ($num_changed_sockets == 1) {
      $fd = socket_accept($sock);
      socket_getpeername($fd, $remoteAddr, $remotePort);
      log_dictLog("Client connected from $remoteAddr");
      socket_write($fd, "220 dict protocol implementation for DEX\r\n");
      array_push($open, $fd);
    }
    
    // Duplicate $open and perform I/O on each available handle
    $dup = $open;
    if (count($dup)) {
      $num_changed_sockets = socket_select($dup, $write = NULL, $exc = NULL,
                                           0, 100000); // 0.1 sec
    }
    
    foreach ($dup as $available_fd) {
      if (handle_line($available_fd) != 0) {
        // Remove $available_fd from $open
        $pos = array_search($available_fd, $open);
        if ($pos !== false)
          array_splice($open, $pos, 1);
      }
    }
  }
}

// Returns 0 on normal cases (including bad input), -1 on EOF or errors
function handle_line($fd) {
  if (($buf = socket_read($fd, 100, PHP_NORMAL_READ)) === false) {
    socket_close($fd);
    return -1;
  }
  if (!$buf = strtolower(trim($buf))) return 0;   // Empty line

  log_dictLog($buf);
  
  $tok = strtok($buf, " \t");
  
  if ($tok == "show") {
    $tok = strtok(" \t");
    if ($tok == "strat" || $tok == "strategies") {
      socket_write($fd, "111 3 strategies present\r\n");
      socket_write($fd, "exact \"Exact match\"\r\n");
      socket_write($fd, "infix \"Substring match\"\r\n");
      socket_write($fd, "approx \"Approximate match\"\r\n");
      socket_write($fd, ".\r\n");
      socket_write($fd, "250 ok\r\n");
    } else if ($tok == "db" || $tok == "databases") {
      $sources = db_find(new Source(), '1');
      socket_write($fd, "110 " . count($sources) .
		   " databases present\r\n");
      foreach ($sources as $source) {
        $formatted = sprintf("%s \"%s\"\r\n", $source->id, $source->name);
        socket_write($fd, $formatted);
      }
      
      socket_write($fd, ".\r\n");
      socket_write($fd, "250 ok\r\n");
    } else {
      socket_write($fd, "501 syntax error, illegal parameters\r\n");
    }
    
  } else if ($tok == "d" || $tok == "define") {
    $arg1 = strtok(" \t");
    $arg2 = strtok("\"");
    if ($arg1 == "" || $arg2 == "") {
      socket_write($fd, "501 syntax error, illegal parameters\r\n");
      return 0;
    }
    lookup($arg1, $arg2, $fd);
    
  } else if ($tok == "m" || $tok == "match") {
    $arg1 = strtok(" \t");
    $arg2 = strtok(" \t");
    $arg3 = strtok("\"");
    if ($arg1 == "" || $arg2 == "" || $arg3 == "") {
      socket_write($fd, "501 syntax error, illegal parameters\r\n");
      return 0;
    }
    match($arg1, $arg2, $arg3, $fd);
    
  } else if ($tok == 'client') {
    socket_write($fd, "250 ok\r\n");      
  } else if ($tok == 'quit') {
    socket_write($fd, "221 bye\r\n");
    socket_close($fd);
    return -1;
  } else {
    socket_write($fd, "500 unknown command\r\n");
  }
  return 0;
}

function match($sourceId, $strategy, $cuv, $fd) {
  if (!source_exists($sourceId)) {
    socket_write($fd, "550 invalid database, use SHOW DB for a list\r\n");
    return;
  }

  if (!strategy_exists($strategy)) {
    socket_write($fd, "551 invalid strategy, use SHOW STRAT for a list\r\n");
    return;
  }

  $cuv = text_cleanupQuery($cuv);
  $arr = text_analyzeQuery($cuv);
  $hasDiacritics = $arr[0];
  $field = $hasDiacritics ? 'lexem_neaccentuat' : 'lexem_utf8_general';

  $query = "select distinct lexems.lexem_neaccentuat, Definition.sourceId " .
    "from lexems " .
    "join LexemDefinitionMap " .
    "on lexem_id = LexemDefinitionMap.lexemId " .
    "join Definition on LexemDefinitionMap.definitionId = Definition.id " .
    "where Definition.status = 0";
  if ($strategy == "." || $strategy == "approx")
    $query .= " and dist2('$cuv', $field)";
  else if ($strategy == "exact")
    $query .= " and $field = '$cuv'";
  else { // infix
    $query .= " and $field like '%$cuv%'";
  }
  if ($sourceId != "*" && $sourceId != '!') {
    $query .= " and Definition.sourceId = '$sourceId'";
  }
  $query .= " order by lexem_neaccentuat, Definition.sourceId";
  $result = mysql_query($query);

  if (!mysql_num_rows($result)) {
    socket_write($fd, "552 no match\r\n");
    return;
  }

  socket_write($fd, "152 " . mysql_num_rows($result) . " match(es) found\r\n");

  while ($row = mysql_fetch_array($result))
    socket_write($fd, $row['sourceId'] . " \"" . $row['lexem_neaccentuat'] . "\"\r\n");

  socket_write($fd, ".\r\n");
  socket_write($fd, "250 ok\r\n");
}

function lookup($sourceId, $cuv, $fd) {
  if (!source_exists($sourceId)) {
    socket_write($fd, "550 invalid database, use SHOW DB for a list\r\n");
    return;
  }

  if ($sourceId == '*' || $sourceId == '!') {
    $sourceId = 0;
  }
  $cuv = text_cleanupQuery($cuv);
  $arr = text_analyzeQuery($cuv);
  $hasDiacritics = $arr[0];

  $lexems = Lexem::searchLexems($cuv, $hasDiacritics);
  $definitions = Definition::loadForLexems($lexems, $sourceId, $cuv);
  $searchResults = SearchResult::mapDefinitionArray($definitions);

  if (!count($definitions)) {
    socket_write($fd, "552 no match\r\n");
    return;
  }

  socket_write($fd, "150 " . count($definitions) . " definition(s) found\r\n");

  foreach ($searchResults as $sr) {
    $def = pretty_print($sr->definition->internalRep);
    socket_write($fd, "151 \"" . $cuv . "\" " . $sr->source->id . " \"" .
		 $sr->source->name . "\"\r\n");
    socket_write($fd, "$cuv\r\n$def");
    socket_write($fd, ".\r\n");
  }

  socket_write($fd, "250 ok\r\n");  
}

function source_exists($sourceId) {
  if ($sourceId == "*" || $sourceId == '!') {
    return true;
  }
  $s = new Source();
  $s->load("id=$sourceId");
  return $s->id;
}

function strategy_exists($strategy) {
  return $strategy == "." || $strategy == "exact" || $strategy == "approx" ||
    $strategy == "infix";
}

// Pretty-print a string according to the dict.org format
function pretty_print($def) {
  // Remove references, bold text, italics and spaced-out text
  $def = preg_replace("/\|([^|]*)\|([^|]*)\|/", "$1", $def);
  $def = preg_replace("/\@([^\@]*)\@/", "$1", $def);
  $def = preg_replace("/\%([^\%]*)\%/", "$1", $def);
  $def = preg_replace("/\\$([^\$]*)\\$/", "$1", $def);

  // Indices
  $def = preg_replace("/\^(\d)/", " $1", $def);

  // Insert a \r\n\t every 70 chars and a \t at the start
  $col_width = 60;
  $wrap = "";
  while ($def != "") {
    if (strlen($def) <= $col_width) {
      $width = strlen($def);
    } else {
      $width = $col_width;
      while (!ctype_space($def[$width - 1]))
        $width--;
    }

    $wrap .= "   " . substr($def, 0, $width) . "\r\n";
    while ($width < strlen($def) && ctype_space($def[$width]))
      $width++;
    $def = substr($def, $width);
  }

  return $wrap;
}

?>
