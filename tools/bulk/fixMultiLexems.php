<?
require_once('../phplib/util.php');

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
$modlexems = db_getObjects(new Lexem(), db_execute("select distinct l.* from Lexem l, LexemDefinitionMap m, Definition d where l.id = m.lexemId and m.definitionId = d.id and d.sourceId = 28 and l.form like '% %'" ));

/*** main loop ***/
if($verbose) echo("Everything OK, start processing...\n");

foreach($modlexems as $modlexem) {
	$ldms = db_find(new LexemDefinitionMap(), "lexemId = {$modlexem->id}");
	$names = explode(" ", str_replace(array(",", "~"), "", $modlexem->form));

	if($verbose) echo(sprintf("Found %d definitions corresponding to this lexem ({$modlexem->form})\n", count($ldms)));

	if($verbose) echo("Deleting {$modlexem->form}...\n");
	if (!$dryrun) $modlexem->delete();

	foreach($names as $name) {
		# dacă nu începe cu literă mare sau nu se termină cu punct sau paranteză închisă {
		if (preg_match("/^[A-Z]/", $name) or preg_match("/[\.\)]$/", $name)) {
			continue;
		}

		if($verbose) echo("\t * Process part: {$name}\n");

		# procedura de refolosire a lexemului sau de regenerare
		$lexems = db_find(new Lexem(), "form = '{$name}'");
		if (!count($lexems)) {
		  $lexems = db_find(new Lexem(), "formNoAccent = '{$name}'");
		}

		if ($allowInflected) {
			if (!count($lexems)) {
				$lexems = db_getObjects(new Lexem(), db_execute("select l.* from Lexem l, InflectedForm i where l.id=i.lexemId and i.formNoAccent='{$name}'"));
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
			  $lexem = new Lexem($name, 'T', '1', '');
			  $lexem->save();
			  $lexem->regenerateParadigm();
			  if($verbose) echo("\t\tCreated lexem {$lexem->id} ({$lexem->form})\n");
			}
		}

		# procedura de asociere a definiției cu lexemul de mai sus
		foreach($ldms as $map) {
			if($verbose) echo("\t\tAssociate lexem {$name} ({$lexem->id}) to definition ({$map->definitionId})\n");
			if (!$dryrun) {
				LexemDefinitionMap::associate($lexem->id, $map->definitionId);
			}
		}
  	}
	if($verbose) echo("\n");
}
