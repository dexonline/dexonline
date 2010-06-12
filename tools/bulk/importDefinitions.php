<?
require_once('../phplib/util.php');

$shortopts = "f:u:s:t:hv"; 
$options = getopt($shortopts);

function HELP() {
	exit("
Usage: php importDefinition.php options

Options:
	-f fileName (mandatory)
	-u userId (mandatory)
	-s sourceId (mandatory)
	-t status (mandatory: 0 - active, 1 - temporary, 2 - deleted)
	-h help
	-v verbose

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

$verbose = false;
if (isset($options['v'])) {
	$verbose = true;
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

	if($verbose) echo("Inserting definition for $lname...\n");
    $definition = new Definition();
    $definition->userId = $userId;
    $definition->sourceId = $sourceId;
    $definition->status = $status;
    $definition->internalRep = text_internalizeDefinition($def);
    $definition->htmlRep = text_htmlize($definition->internalRep);
    $definition->lexicon = text_extractLexicon($definition);
    $definition->save();
    log_userLog("Added definition {$definition->id} ({$definition->lexicon})");

    $lname = addslashes(text_formatLexem($lname));
	$names = preg_split("/[\s,]+/", $lname);
	foreach ($names as $name) {
		$lexems = db_find(new Lexem(), "form = '{$name}'");
		if (!count($lexems)) {
		  $lexems = db_find(new Lexem(), "formNoAccent = '{$name}'");
		}
		if (count($lexems)) {
		  // Reuse existing lexem.
		  $lexem = $lexems[0];
		  log_userLog("Reusing lexem {$lexem->id} ({$lexem->form})");
		} else {
		  // Create a new lexem.
		  $lexem = new Lexem($name, 'T', '1', '');
		  $lexem->save();
		  $lexem->regenerateParadigm();
		  log_userLog("Created lexem {$lexem->id} ({$lexem->form})");
		}

		LexemDefinitionMap::associate($lexem->id, $definition->id);
	}
}

?>
