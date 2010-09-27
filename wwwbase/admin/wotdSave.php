<?php
class wotdSave{
  /**
   * The wotd identifier
   * @var int $id
   * @access protected
   * */
  protected $id;

  /**
   * The date
   * @var string $displayDate
   * @access protected
   * */
  protected $displayDate;

  /**
   * The priority
   * @var int $priority
   * @access protected
   * */
  protected $priority;

  /**
   * The reference identifier
   * @var int $refId
   * @access protected
   * */
  protected $refId;

  /**
   * The reference type
   * @var string $refType
   * @access protected
   * */
  protected $refType;

  /**
   * The old definition ID (used for editing purposes)
   * @var int $oldDefinitionId
   * @access protected
   * */
  protected $oldDefinitionId;

  /**
   * Constructs the object
   * @param int $id The wotd identifier
   * @param string $displayDate The display date [OPTIONAL]
   * @param int $priority The priority [OPTIONAL]
   * @param int $refId The reference identifier [OPTIONAL]
   * @param string $refType The reference type [OPTIONAL]
   * @param int $oldDefinitionId The old definition identifier [OPTIONAL]
   * @access public
   * @return void
   **/
  public function __construct($id, $displayDate = null, $priority = null, $refId = null, $refType = null, $oldDefinitionId = null){
    $this->id = $id;
    $this->displayDate = $displayDate;
    $this->priority = $priority;
    $this->refId = $refId;
    $this->refType = $refType;
    $this->oldDefinitionId = $oldDefinitionId;
  }

  /**
   * Saves the data
   * @access protected
   * @return void
   * */
  protected function doSave() {
    $table = new WordOfTheDay();
    if ($this->id != null){
      $table->id = $this->id;
      $table->_saved = true;
      $table->oldDefinitionId = $this->oldDefinitionId;
    }
    $table->displayDate = $this->displayDate;
    $table->priority = $this->priority;
    $table->refId = $this->refId;
    $table->defId = $this->refId;
    $table->refType = $this->refType;

    $ok = $table->save();
    if (!$ok) return $table->ErrorMsg();

    return '';
  }

  /**
   * Deletes a row from the wotd table
   * @access protected
   * @return string
   * */
  protected function doDelete() {
    $table = new WordOfTheDay();
    $table->id = $this->id;

    $ok = $table->Delete();
    if (!$ok) return $table->ErrorMsg();

    return '';
  }

    /**
   * Runs the application
   * @param string $oper The operation to perform (edit|del) [OPTIONAL]
   * @access public
   * @return void
   * */
  public function run($oper = 'edit') {
    header('Content-Type: text/plain; charset=UTF-8');
    if ($oper == 'edit' || $oper == 'add'){
      echo $this->doSave();
    }
    else if ($oper == 'del'){
      echo $this->doDelete();
    }
  }


}

require_once("../../phplib/util.php");
require_once("../../phplib/modelObjects.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

if (array_key_exists('oper', $_POST)){
  if ($_POST['oper'] == 'edit'){
    $app = new wotdSave($_POST['id'], $_POST['displayDate'], $_POST['priority'], $_POST['definitionId'], $_POST['refType'],
             (array_key_exists('oldDefinitionId', $_POST) ? $_POST['oldDefinitionId'] : null));
  }
  else if ($_POST['oper'] == 'del'){
    $app = new wotdSave($_POST['id']);
  }
  else if ($_POST['oper'] == 'add'){
    $app = new wotdSave(null, $_POST['displayDate'], $_POST['priority'], $_POST['definitionId'], $_POST['refType'], null);
  }
  $app->run($_POST['oper']);
}

?>