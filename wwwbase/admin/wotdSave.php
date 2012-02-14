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
   * The image file name
   * @var string $image
   * @access protected
   **/
  protected $image;

  /**
   * The old definition ID (used for editing purposes)
   * This is currently unused -- cata
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
   * @param string $image The image file [OPTIONAL]
   * @param int $oldDefinitionId The old definition identifier [OPTIONAL]
   * @access public
   * @return void
   **/
  public function __construct($id, $displayDate = null, $priority = null, $refId = null, $refType = null, $image = null, $oldDefinitionId = null){
    $this->id = $id;
    $this->displayDate = $displayDate;
    $this->priority = $priority;
    $this->refId = $refId;
    $this->refType = $refType;
    $this->image = $image;
    $this->oldDefinitionId = $oldDefinitionId;
  }

  /**
   * Saves the data
   * @access protected
   * @return void
   * */
  protected function doSave() {
    if ($this->id != null){
      $wotd = WordOfTheDay::get_by_id($this->id);
      // $wotd->oldDefinitionId = $this->oldDefinitionId;
    } else {
      $wotd = Model::factory('WordOfTheDay')->create();
    }

    if ($this->displayDate) {
      $wotd->displayDate = $this->displayDate;
    }
    $wotd->userId = session_getUserId();
    $wotd->priority = $this->priority;
    $wotd->image = $this->image;
    $wotd->save();

    $wotdr = WordOfTheDayRel::get_by_wotdId($wotd->id);
    if (!$wotdr) {
      $wotdr = Model::factory('WordOfTheDayRel')->create();
    }
    $wotdr->refId = $this->refId ? $this->refId : $this->oldDefinitionId; // No idea what's going on here, but this fixes it -- cata
    $wotdr->refType = $this->refType ? $this->refType : 'Definition';
    $wotdr->wotdId = $wotd->id;
    $wotdr->save();
    return '';
  }

  /**
   * Deletes a row from the wotd table
   * @access protected
   * @return string
   * */
  protected function doDelete() {
    $wotd = WordOfTheDay::get_by_id($this->id);
    $wotd->delete();

    $wotdr = WordOfTheDayRel::get_by_wotdId($this->id);
    $wotdr->delete();
    return '';
  }

  /**
   * Runs the application
   * @param string $oper The operation to perform (edit|del) [OPTIONAL]
   * @access public
   * @return void
   * */
  public function run($oper) {
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
util_assertModerator(PRIV_WOTD);
util_assertNotMirror();

$oper = util_getRequestParameter('oper');
$id = util_getRequestParameter('id');
$displayDate = util_getRequestParameter('displayDate');
$priority = util_getRequestParameter('priority');
$definitionId = util_getRequestParameter('definitionId');
$refType = util_getRequestParameter('refType');
$image = util_getRequestParameter('image');
$oldDefinitionId = util_getRequestParameter('oldDefinitionId');

switch ($oper) {
case 'edit': $app = new wotdSave($id, $displayDate, $priority, $definitionId, $refType, $image, $oldDefinitionId); break;
case 'del': $app = new wotdSave($id); break;
case 'add': $app = new wotdSave(null, $displayDate, $priority, $definitionId, $refType, null, null); break;
}
$app->run($oper);

?>
