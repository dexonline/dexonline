<?php
class wotdTableRows{
  /**
   * The page numbe
   * @var int $page
   * @access protected
   **/
  protected $page;
  
  /**
   * The number of records to fetch
   * @var int $limit
   * @access protected
   **/
  protected $limit;
  
  /**
   * The index row - i.e.: user click to sort
   * @var int $sidx
   * @access protected
   **/
  protected $sidx;
  
  /**
   * The sort order
   * @var string $sord
   * @access protected
   **/
  protected $sord;
  
  /**
   * The list of fields to select from the database
   * @var string $sql_fields
   * @access protected
   **/
  protected $sql_fields;
  
  /**
   * The sql base
   * @var string $sql_base
   * @access protected
   **/
  protected $sql_base;
  
  /**
   * The sql where condition
   * @param string $where_condition
   * @access protected
   */
  protected $where_condition;

  /**
   * Constructs the object
   * @param int $page The page number
   * @param int $limit The number of records to retrieve
   * @param int $sidx The index row - i.e. user click to sort
   * @param string $sord The sord order
   * @param array $search If this is a search, just set the values for an associative array with the keys: field, value, operator [OPTIONAL]
   * @access public
   * @return void
   **/
  public function __construct($page, $limit, $sidx, $sord, $search = null){
    $this->page = $page;
    $this->limit = $limit;
    $this->sidx = $sidx;
    if ($this->sidx == ''){
      $this->sidx = 'displayDate';
    }
    $this->sord = $sord;
     
    $this->sql_fields = <<<sql
w.id, s.shortName, d.lexicon, d.htmlRep, u.name, w.displayDate, w.priority, wr.refType, d.id as definitionId
sql;
    $this->sql_base = <<<sql
WordOfTheDay w inner join WordOfTheDayRel wr on w.id = wr.wotdId
inner join Definition d on wr.refId = d.id
inner join Source s on d.sourceId = s.id
inner join User u on w.userId = u.id
sql;

    if ($search == null){
      $this->where_condition = '';
    }
    else {
      $value = str_replace("'", "\\'", $search['value']);
      switch ($search['operator']){
        case 'eq': $op = '='; $value = "'" . str_replace("'", "\\'", $value) . "'"; break;
        case 'ne': $op = '<>'; $value = "'" . str_replace("'", "\\'", $value) . "'"; break;
        case 'lt': $op = '<'; $value = "'" . str_replace("'", "\\'", $value) . "'"; break;
        case 'gt': $op = '>'; $value = "'" . str_replace("'", "\\'", $value) . "'"; break;
        case 'ge': $op = '>='; $value = "'" . str_replace("'", "\\'", $value) . "'"; break;
        case 'bw': $op = 'like'; $value = "'" . str_replace("'", "\\'", $value) . "%'"; break;
        case 'bn': $op = 'not like'; $value = "'" . str_replace("'", "\\'", $value) . "%'"; break;
        case 'in': 
        case 'ni':
          $op = ($search['operator'] == 'in' ? 'in' : 'not in');
          $values = explode(',', $value);
          $v = '';
          foreach ($values as $value){
            $v .= ($v == '' ? '' : ', ') . "'" . str_replace("'", "\\'", trim($value)) . "'";
          }
          $value = "({$v})";
        break;
        case 'ew': $op = 'like'; $value = "'%" . str_replace("'", "\\'", $value) . "'"; break;
        case 'en': $op = 'not like'; $value = "'%" . str_replace("'", "\\'", $value) . "'"; break;
        case 'cn': $op = 'like'; $value = "'%" . str_replace("'", "\\'", $value) . "%'"; break;
        case 'nc': $op = 'not like'; $value = "'%" . str_replace("'", "\\'", $value) . "%'"; break;
      }
      $this->where_condition = <<<sql
where
 `{$search['field']}` {$op} {$value}
sql;
    }

  }

  /**
   * Creates an XML node
   * @param string $name The name of the node
   * @param string $value The value of the node
   * @param DOMDocument $doc The DOM document to which the node will be appended
   * @param boolean $cdata If true, the node is created as a CDATA section [OPTIONAL]
   * @access protected
   * @return DOMElement
   **/
  protected function newNode($name, $value, $doc, $cdata = false) {
    $node = $doc->createElement($name);
    if ($cdata){
      $c = $doc->createCDATASection($value);
      $node->appendChild($c);
    }
    else {
      $node->nodeValue = $value;
    }

    return $node;
  }

  /**
   * Gets the table rows
   * @param boolean $string If true, then return the result as string instead of DOMDocument [OPTIONAL]
   * @access protected
   * @return DOMDocument | string
   **/
  protected function getRows($string = true){
    $limit_from = ($this->page - 1) * $this->limit;
    $limit_to = $this->limit + $limit_from;

    $sql = <<<sql
select count(*) from {$this->sql_base} {$this->where_condition}
sql;
    $count = db_getSingleValue($sql);

    $sql = <<<sql
    select
      {$this->sql_fields}
    from
      {$this->sql_base}
    {$this->where_condition}
    order by
      {$this->sidx} {$this->sord}
    limit
      {$limit_from}, {$limit_to}
sql;

    $rows = db_execute($sql);
    $doc = new DOMDocument('1.0', 'UTF-8');
    $root = $doc->createElement('rows');

    $root->appendChild($this->newNode('page', $this->page, $doc));
    $root->appendChild($this->newNode('total', ceil($count / $this->limit), $doc));
    $root->appendChild($this->newNode('records', $count, $doc));

    while (!$rows->EOF){
      $row = $doc->createElement('row');
      $row->setAttribute('id', $rows->fields['id']);
      foreach ($rows->fields as $key => $cell){
        if (!is_numeric($key) && $key != 'id'){
          $row->appendChild($this->newNode('cell', $rows->fields[$key], $doc, ($key == 'htmlRep' ? true : false)));
        }
      }

      $root->appendChild($row);
      $rows->MoveNext();
    }

    $doc->appendChild($root);

    return ($string ? $doc->saveXML() : $doc);
  }

  /**
   * Runs the application
   * @access public
   * @return void
   * */
  public function run() {
    header('Content-Type: text/xml; charset=UTF-8');
    echo $this->getRows();
  }


}

require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$app = new wotdTableRows($_GET['page'], $_GET['rows'], $_GET['sidx'], $_GET['sord'], (array_key_exists('_search', $_GET) && $_GET['_search'] == 'true' ? array('field' => $_GET['searchField'], 'value' => $_GET['searchString'], 'operator' => $_GET['searchOper']) : null));
$app->run();

?>