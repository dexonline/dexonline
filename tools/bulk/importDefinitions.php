<?php
require_once('../phplib/util.php');

print "\n\nAcest script nu a fost testat după conversia AdoDB -> Idiorm.\n";
print "El este corect d.p.d.v. sintactic, dar atât.\n";
print "Ștergeți aceste linii și asigurați-vă că scriptul face ceea ce trebuie.\n\n\n";
exit(1);

$shortopts = "f:u:s:c:t:x:p:hvid"; 
$options = getopt($shortopts);

function HELP() {
  exit("
Usage: From tools/ directory run: 
	php bulk/importDefinitions.php options

Options:
	-f fileName (mandatory)
	-u userId (mandatory)
	-s sourceId (mandatory)
	-c check against sourceId)
	-t status (mandatory: 0 - active, 1 - temporary, 2 - deleted)
	-i = use inflections for multilexem entries
    -x = exclude lexems beginning with
    -p = split lexem using this char
	-d = dry run
	-h = help
	-v = verbose

Example:
	php importDefinition.php -f definitions.txt -u 471 -s 19 -t 0

");
}

function CHECK($option, $c) {
  if (!is_array($option)) 
    exit("Error reading options\n");

  if (!isset($option[$c])) {
    echo "Missing mandatory option $c\n";
    HELP();
  }
}

/*** check options ***/

if(isset($options['h'])) HELP();

CHECK($options, 'u');
$userId = $options['u']; 

CHECK($options, 's');
$sourceId = $options['s'];

CHECK($options, 'f');
$fileName = $options['f'];
if (!file_exists($fileName)) exit("Error: file $fileName doesn't exist!\n");

CHECK($options, 't');
$status = $options['t'];

$checkSourceId = NULL;
if (isset($options['c'])) {
  $checkSourceId = $options['c'];
}

$excludeChar = NULL;
if (isset($options['x'])) {
  $excludeChar = $options['x'];
}

$splitChar = NULL;
if (isset($options['p'])) {
  $splitChar = $options[''];
}

$verbose = false;
if (isset($options['v'])) {
  $verbose = true;
}

$allowInflected = false;
if (isset($options['i'])) {
  $allowInflected = true;
}

$dryrun = false;
if (isset($options['d'])) {
  $dryrun = true;
}

/*** main loop ***/
if($verbose) echo("Everything OK, start processing...\n");

$lines = file($fileName);
if($verbose) echo("File read. Start inserting the definitions...\n");

$i = 0;
while ($i < count($lines)) {
  $def = $lines[$i++];
  preg_match('/^@([^@]+)@/', $def, $matches);
  $lname = preg_replace("/[!*'^1234567890]/", "", $matches[1]);

  if (isset($checkSourceId)) {
    if($verbose) echo(" * Check if the definition already exists\n");
    $def = Model::factory('Definition')->where('lexicon', $name)->where('sourceId', $sourceId)->find_many();
    if ( count($def) ) {
      if($verbose) echo("\t Definition already introduced\n");
      continue;
    }
    else {
      $def = Model::factory('Definition')->where('lexicon', $name)->where('sourceId', $checkSourceId)->find_many();
    }

    if ( count($def) ) {
      if($verbose) echo("\t Definition exists in the checked dictionary\n");
      // TODO: check the other possible differences
      continue;
    }
    else {
      if($verbose) echo("\t Definition not exist – it will be added!\n");
    }
  }

  if($verbose) echo(" * Inserting definition for '$lname'...\n");
  $definition = Model::factory('Definition')->create();
  $definition->displayed = 0;
  $definition->userId = $userId;
  $definition->sourceId = $sourceId;
  $definition->status = $status;
  $definition->internalRep = AdminStringUtil::internalizeDefinition($def, $sourceId);
  $definition->htmlRep = AdminStringUtil::htmlize($definition->internalRep, $sourceId);
  $definition->lexicon = AdminStringUtil::extractLexicon($definition);
  $definition->save();
  if($verbose) echo("\tAdded definition {$definition->id} ({$definition->lexicon})\n");

  $lname = addslashes(AdminStringUtil::formatLexem($lname));
  $names = preg_split("/[-\s,\/()]+/", $lname);
  foreach ($names as $name) {
    if ($name == '') continue;
    if (isset($excludeChar) && ($name[0])==$excludeChar) continue;
    $name = str_replace("'", '', $name);
    $name = str_replace("\\", '', $name);

    if($verbose) echo("\t * Process part: '{$name}'\n");

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

    // procedura de refolosire a lexemului sau de regenerare
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
    if($verbose) echo("\t\tAssociate lexem {$name} ({$lexem->id}) to definition ({$definition->id})\n");
    if (!$dryrun) {
      LexemDefinitionMap::associate($lexem->id, $definition->id);
    }
  }
}

?>
