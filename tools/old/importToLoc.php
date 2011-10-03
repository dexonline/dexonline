<?php
/**
 * This script applies data migration patches, including SQL code and PHP scripts.
 * Overview:
 * - Looks in the Variable table for Schema.version, which is a 5-digit number;
 * - Reads files of the form "patches/%d%d%d%d%d.extension" in increasing numberical order;
 * - Ignores files older than, or equal to, Schema.version;
 * - Files with the .sql extension are piped into SQL;
 * - Files with the .php extension are executed within this script;
 * - Files with other extensions are ignored.
 *
 * Use with the --dry-run to see what the script would do without actually
 * executing anything.
 **/

require_once('../phplib/util.php');

define('SRC_DN', 17);
define('SRC_MDN', 21);
define('SRC_DLRM', 15);

importLexems(SRC_DN, 'DN');
importLexems(SRC_MDN, 'MDN');
importLexems(SRC_DLRM, 'DLRM');

/**************************************************************************/

function importLexems($srcId, $srcName) {
  $dbResult = db_execute("select l.*, d.internalRep from Lexem l, LexemDefinitionMap ldm, Definition d where l.id = ldm.lexemId and ldm.definitionId = d.id " .
                         "and d.status = 0 and d.sourceId = $srcId and not l.isLoc order by l.formNoAccent");
  $rowCount = $dbResult->RowCount();
  print "************** Import $rowCount lexeme din $srcName\n";
  while (!$dbResult->EOF) {
    $internalRep = $dbResult->fields['internalRep'];
    unset($dbResult->fields['internalRep']);
    unset($dbResult->fields['16']);
    $l = new Lexem();
    $l->set($dbResult->fields);
    $dbResult->MoveNext();
    print sprintf("%d\t%-20s\t%-60s\t%-50s\n", $l->id, $l->formNoAccent, mb_substr($internalRep, 0, 58), "http://dexonline.ro/lexem/{$l->formNoAccent}/{$l->id}");
    $l->isLoc = 1;
    $l->save();
  }
}


?>
