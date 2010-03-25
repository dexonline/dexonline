<?
// TODO: Impose upper limit on the date, so we can start talking about "past" and "present" contributions.
require_once("../../phplib/util.php"); 
util_assertFlexModeratorStatus();
util_assertNotMirror();

$income = util_getRequestParameterWithDefault('income', 10000);

// Categories of contributions
define('CAT_CHARS', 0);
define('CAT_CODE', 1);
define('CAT_EMAIL', 2);
define('CAT_FLEXOR', 3);
define('CAT_MODERATOR', 4);
define('CAT_SAVINGS', 5);
define('NUM_CATEGORIES', 6);

$CATEGORIES = array(
  CAT_CHARS => new Category('Caractere', 'caractere', 0.30),
  CAT_CODE  => new Category('Cod + analiză', 'procente', 0.25),
  CAT_EMAIL => new Category('Emailuri', 'procente', 0.05),
  CAT_FLEXOR => new Category('Flexor', 'procente', 0.10),
  CAT_MODERATOR => new Category('Moderare', 'procente', 0.20),
  CAT_SAVINGS => new Category('Economii', 'procente', 0.10),
);
assertCorrectCategories();

$MDN_IMPORT_DATE = '2007-09-15';
$MDN = Source::get("shortName = 'MDN'");
$CATA = User::get("nick = 'cata'");
$MATEI = User::get("nick = 'gall'");
$RADU = User::get("nick = 'raduborza'");
$TAVI = User::get("nick = 'tavi'");
$CODEX = createCodexUser(); // The company, who retains 10% of the income as savings
$IGNORE_USERIDS = ignoreUserIds(array('siveco', 'RACAI')); // And anonymous

// Count the number of characters submitted by users
// Exclude the dictionaries we imported in bulk (MDN and the ones from Litera)
$mdnBulkImportChars = db_getSingleValue("select sum(length(internalRep)) from Definition where sourceId = {$MDN->id} and status = 0 " .
                                        "and userId = {$RADU->id} and left(from_unixtime(createDate), 10) = '{$MDN_IMPORT_DATE}'");

$totalChars = db_getSingleValue("select sum(length(internalRep)) from Definition where status = 0 and userId not in ($IGNORE_USERIDS)");
$totalChars -= $mdnBulkImportChars;

$total = new Fin(null);
$total->values[CAT_CHARS] = $totalChars;
$total->values[CAT_CODE] = 1.0;
$total->values[CAT_EMAIL] = 1.0;
$total->values[CAT_FLEXOR] = 1.0;
$total->values[CAT_MODERATOR] = 1.0;
$total->values[CAT_SAVINGS] = 1.0;

$fins = array(); // userId -> financial record for that user
$dbResult = db_execute("select userId, sum(length(internalRep)) as n from Definition where status = 0 and userId not in ($IGNORE_USERIDS) group by userId");
while (!$dbResult->EOF) {
  $fin = new Fin(User::get("id = {$dbResult->fields[0]}"));
  $fin->values[CAT_CHARS] = $dbResult->fields[1];
  $fins[$fin->user->id] = $fin;
  $dbResult->MoveNext();
}
$fins[$RADU->id]->values[CAT_CHARS] -= $mdnBulkImportChars;

// Assign the other categories -- only Matei, Radu, Tavi and Cata participated here.
// These will, in time, be replaced by more accurate measurements once we decide on a methodology
$fins[$CATA->id]->values[CAT_CODE] = 0.75;
$fins[$RADU->id]->values[CAT_CODE] = 0.25;
$fins[$CATA->id]->values[CAT_EMAIL] = 0.20;
$fins[$RADU->id]->values[CAT_EMAIL] = 0.80;
$fins[$CATA->id]->values[CAT_FLEXOR] = 1/6;
$fins[$MATEI->id]->values[CAT_FLEXOR] = 0.5;
$fins[$RADU->id]->values[CAT_FLEXOR] = 1/6;
$fins[$TAVI->id]->values[CAT_FLEXOR] = 1/6;
$fins[$CATA->id]->values[CAT_MODERATOR] = 0.1;
$fins[$MATEI->id]->values[CAT_MODERATOR] = 0.7;
$fins[$RADU->id]->values[CAT_MODERATOR] = 0.1;
$fins[$TAVI->id]->values[CAT_MODERATOR] = 0.1;
$codexFin = new Fin($CODEX);
$codexFin->values[CAT_SAVINGS] = 1.0;
$fins[] = $codexFin;

// Now compute the weights and total share for each user
foreach ($fins as $fin) {
  $fin->computeWeights();
}

// Sort the users by total share
usort($fins, "shareCmp");

// foreach($fins as $fin) {
//   print "{$fin->user->id} {$fin->user->nick} {$fin->values[0]} {$fin->weights[0]} {$fin->share}<br/>\n";
// }

smarty_assign('fins', $fins);
smarty_assign('total', $total);
smarty_assign('categories', $CATEGORIES);
smarty_assign('income', $income);
smarty_displayWithoutSkin('private/financials.ihtml');

/*************************************************************************/

class Fin {
  public $user;
  public $values; // For each category, the net amount of this user's contribution, e.g. number of chars or number of OTRS time units
  public $weights; // For each category, the weight of this user's contribution out of 100% for that category
  public $share;  // The weighted sum of $weights[$i] * $categories[$i]->weight. This is the total financial share of this user.

  function __construct($user) {
    $this->user = $user;
    $this->values = array_fill(0, NUM_CATEGORIES, 0);
    $this->weights = array_fill(0, NUM_CATEGORIES, 0.0);
  }

  function computeWeights() {
    global $CATEGORIES;
    global $total;

    foreach ($this->values as $i => $value) {
      $this->weights[$i] = $total->values[$i] ? $value / $total->values[$i] : 0.0;
    }

    $this->share = 0.0;
    foreach ($this->weights as $i => $weight) {
      $this->share += $weight * $CATEGORIES[$i]->weight;
    }
  }
}

class Category {
  public $name;
  public $unit;
  public $weight;

  function __construct($name, $unit, $weight) {
    $this->name = $name;
    $this->unit = $unit;
    $this->weight = $weight;
  }
}

function shareCmp($a, $b) {
  if ($a->share == $b->share) {
    return 0;
  }
  return ($a->share > $b->share) ? -1 : 1;
}

/**
 * Returns a comma-separated string of userIds to be ignored, given their nicks. Automatically adds userId = 0 to the list.
 **/
function ignoreUserIds($nicks) {
  $userIds = array(0);
  foreach ($nicks as $nick) {
    $user = User::get("nick = '$nick'");
    $userIds[] = $user->id;
  }
  return implode(',', $userIds);
}

function assertCorrectCategories() {
  global $CATEGORIES;
  $sum = 0.0;
  foreach ($CATEGORIES as $cat) {
    $sum += $cat->weight;
  }
  assert(floatEquals($sum, 1.0));
}

function floatEquals($a, $b) {
  return abs($a - $b) < 0.00001;
}

function createCodexUser() {
  $codex = new User();
  $codex->nick = 'Codex Publicus';
  $codex->id = 1000000;
  return $codex;
}

?>
