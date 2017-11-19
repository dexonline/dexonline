<?php
/**
 * See also: https://github.com/dexonline/dexonline/issues/632
 *
 * This script goes through all the definitions and meanings in the database and
 * tries to automatically clean up redundant links. It generates a report to
 * stderr, which means that the report can be obtained separately by redirecting
 * stderr. It interactively asks the user to fix the remaining links by
 * removing the link or replacing it with a new one.
 *
 * For all modified links, it generates an SQL patch to apply the updates to the
 * database.
 *
 * To generate the list of links and verdicts, answer "ignore" to interactive
 * queries and capture automatically-cleaned up links from stderr:
 * `yes '3' | php tools/updateLinks.php 2> output.md`
 *
 * To interactively fix links:
 * `php tools/updateLinks.php 2> /dev/null`
 */

require_once __DIR__ . '/../phplib/Core.php';

define('DEF_CHECKPT_FILE', '/tmp/updateLinksCheckpt.txt');
define('CREATE_PATCH_FILE', false);
define('URL', 'https://dexonline.ro/');

/* determine name for new patch */
$lastPatch = scandir(__DIR__ . '/../patches', $sorting_order=SCANDIR_SORT_DESCENDING)[0];
$newPatchNumber = intval(explode('.', $lastPatch)[0], $base=10) + 1;
$newPatch = __DIR__ . '/../patches/00' . $newPatchNumber . ".sql";

function updateEntity($e, $isDefinition)
{
  global $newPatch;

  $definitionShown = false;

  $links = AdminStringUtil::findRedundantLinks($e->internalRep);

  foreach ($links as $link) {
    $entityName = $isDefinition ? "definiție" : "sens";

    file_put_contents('php://stderr', $link["original_word"] . ",", FILE_APPEND);
    file_put_contents('php://stderr', $link["linked_lexem"] . ",", FILE_APPEND);
    file_put_contents('php://stderr', $e->id . ",", FILE_APPEND);
    file_put_contents('php://stderr', $link["short_reason"] . ",", FILE_APPEND);
    file_put_contents('php://stderr', $entityName . ",", FILE_APPEND);
    file_put_contents('php://stderr', "[definiție](" . URL . "definitie/" . $e->id . "),", FILE_APPEND);
    if ($isDefinition) {
      file_put_contents('php://stderr', "[editează](" . URL . "admin/definitionEdit.php?definitionId=" . $e->id . ")\n", FILE_APPEND);
    } else {
      file_put_contents('php://stderr', "[editează](". URL . "editTree.php?id=" . $e->id . ")\n", FILE_APPEND);
    }

    $originalLink = "|" . $link["original_word"] . "|" . $link["linked_lexem"] . "|";

    $didChange = ($link['short_reason'] !== "nemodificat");

    if ($link['short_reason'] !== "nemodificat") {
      $e->internalRep = str_replace($originalLink, $link["original_word"], $e->internalRep);
    } else {

      if ($definitionShown === false) {
        print $e->internalRep . "\n\n";
        $definitionShow = true;
      }

      print $originalLink . "\n";
      do {
        $validInput = true;
        $action = readline("Acțiune: 1 (șterge trimiterea); 2 (înlocuiește trimiterea); 3 (ignoră): ");
        if ($action === "1") {
          $e->internalRep = str_replace(
            $originalLink,
            $link["original_word"],
            $e->internalRep
          );

          $didChange = true;
        } else if ($action === "2") {
          $replaceWith = readline("Înlocuiește cu: ");
          $e->internalRep = str_replace(
            $originalLink,
            "|" . $link["original_word"] . "|" . $replaceWith . "|",
            $e->internalRep
          );

          $didChange = true;
        } else {
          $validInput = ($action === "3");
        }
      } while ($validInput === false);
    }

    if ($didChange) {
      // TODO meanings have no sourceId field
      $e->htmlRep = AdminStringUtil::htmlize($e->internalRep, $e->sourceId);
      $e->save();

      $tableName = $isDefinition ? 'Definition' : 'Meaning';

      if (CREATE_PATCH_FILE) {
        $line = sprintf("UPDATE %s SET internalRep = '%s', htmlRep = '%s' WHERE id = %s;\n",
                        $tableName, $e->internalRep, $e->htmlRep, $e->id);
        file_put_contents($newPatch, $line, FILE_APPEND);
      }
    }
  }
}

// read the checkpoint file or use 0 if the file does not exist
$lastDefId = @file_get_contents(DEF_CHECKPT_FILE);

$definitions = Model::factory('Definition')
  ->where_in('status', [Definition::ST_ACTIVE, Definition::ST_HIDDEN])
  ->where_like('internalRep', '%|%|%|%')
  ->where_gt('id', $lastDefId)
  ->order_by_asc('id')
  ->find_many();

printf("Prelucrare definiții (%d)\n", count($definitions));
print "====================\n";

foreach ($definitions as $d) {
  updateEntity($d, true);
  file_put_contents(DEF_CHECKPT_FILE, $d->id);
}

$meanings = Model::factory('Meaning')
->where_like('internalRep', '%|%|%|%')
->find_many();

print "Prelucrare sensuri\n";
print "==================\n";

foreach ($meanings as $m) {
  updateEntity($m, false);
}

