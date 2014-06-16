<?php
require_once('../phplib/util.php');

print "\n\nAcest script nu a fost testat după conversia AdoDB -> Idiorm.\n";
print "El este corect d.p.d.v. sintactic, dar atât.\n";
print "Ștergeți aceste linii și asigurați-vă că scriptul face ceea ce trebuie.\n\n\n";
exit(1);

/*** Get options ***/
$shortopts = "vdi"; 
$options = getopt($shortopts);
$verbose = false;
if (isset($options['v'])) {
  $verbose = true;
}

$dryrun = false;
if (isset($options['d'])) {
  $dryrun = true;
}

$allowInflected = false;
if (isset($options['i'])) {
  $allowInflected = true;
}

/*** Get lexems ***/
$modlexems = Model::factory('Lexem')
  ->raw_query("select distinct l.* from Lexem l, LexemDefinitionMap m, Definition d where l.id = m.lexemId and m.definitionId = d.id and d.sourceId = 28 and l.form like '% %'" )
  ->find_many();

/*** main loop ***/
if($verbose) echo("Everything OK, start processing...\n");

foreach($modlexems as $modlexem) {
  $ldms = LexemDefinitionMap::get_all_by_lexemId($modlexem->id);
  $names = explode(" ", str_replace(array(",", "~"), "", $modlexem->form));

  if($verbose) echo(sprintf("Found %d definitions corresponding to this lexem ({$modlexem->form})\n", count($ldms)));

  if($verbose) echo("Deleting {$modlexem->form}...\n");
  if (!$dryrun) $modlexem->delete();

  foreach($names as $name) {
    // dacă nu începe cu literă mare sau nu se termină cu punct sau paranteză închisă {
    if (preg_match("/^[A-Z]/", $name) or preg_match("/[\.\)]$/", $name)) {
      continue;
    }

    if($verbose) echo("\t * Process part: {$name}\n");

    // procedura de refolosire a lexemului sau de regenerare
    $lexems = Lexem::get_all_by_form($name);
    if (!count($lexems)) {
      $lexems = Lexem::get_all_by_formNoAccent($name);
    }

    if ($allowInflected) {
      if (!count($lexems)) {
        $lexems = Model::factory('Lexem')->table_alias('l')->select('l.*')->join('InflectedForm', 'l.id = i.lexemId', 'i')
          ->where('i.formNoAccent', $name)->find_many();
        if ( count($lexems) ) {
          if($verbose) echo("\t\tFound inflected form {$name} for lexem {$lexems[0]->id} ({$lexems[0]->form})\n");
        }
      }
    }

    if (count($lexems)) {
      // Reuse existing lexem.
      $lexem = $lexems[0];
      if($verbose) echo("\t\tReusing lexem {$lexem->id} ({$lexem->form})\n");
    } else {
      if($verbose) echo("\t\tCreating a new lexem for name {$name}\n");
      if (!$dryrun) {
        // Create a new lexem.
        $lexem = Lexem::create($name, 'T', '1', '');
        $lexem->save();
        $lexem->regenerateParadigm();
        if($verbose) echo("\t\tCreated lexem {$lexem->id} ({$lexem->form})\n");
      }
    }

    // procedura de asociere a definiției cu lexemul de mai sus
    foreach($ldms as $map) {
      if($verbose) echo("\t\tAssociate lexem {$name} ({$lexem->id}) to definition ({$map->definitionId})\n");
      if (!$dryrun) {
        LexemDefinitionMap::associate($lexem->id, $map->definitionId);
      }
    }
  }
  if($verbose) echo("\n");
}
