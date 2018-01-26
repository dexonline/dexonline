<?php
class wotdTableRows{
  /**
   * The page number
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
   * @param string $sord The sort order
   * @param array $filters: an array of (field, op, data) triplets
   * @access public
   * @return void
   **/
  function __construct($page, $limit, $sidx, $sord, $filters = null) {
    $this->page = $page;
    $this->limit = $limit;
    $this->sidx = $sidx;
    if ($this->sidx == ''){
      $this->sidx = 'displayDate';
    }
    $this->sord = $sord;

    $this->sql_fields = <<<sql
w.id, d.lexicon, s.shortName, d.htmlRep, w.description as descr, w.displayDate, u.name, w.priority, wr.refType, w.image, w.description, d.id as definitionId
sql;
    $this->sql_base = <<<sql
WordOfTheDay w inner join WordOfTheDayRel wr on w.id = wr.wotdId
inner join Definition d on wr.refId = d.id
inner join Source s on d.sourceId = s.id
inner join User u on w.userId = u.id
sql;

    if (empty($filters) || empty($filters['rules'])){
      $this->where_condition = '';
    } else {
      $filterClauses = [];
      foreach ($filters['rules'] as $filter) {
        // Treat all searches like substring searches
        $filterClauses[] = sprintf('(%s like "%%%s%%")', $filter['field'], $filter['data']);
      }
      $this->where_condition = 'where ' . implode(' and ', $filterClauses);
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

    $count = DB::getSingleValue($sql);

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

    $rows = DB::execute($sql, PDO::FETCH_ASSOC);
    $doc = new DOMDocument('1.0', 'UTF-8');
    $root = $doc->createElement('rows');

    $root->appendChild($this->newNode('page', $this->page, $doc));
    $root->appendChild($this->newNode('total', ceil($count / $this->limit), $doc));
    $root->appendChild($this->newNode('records', $count, $doc));

    foreach ($rows as $dbRow) {
      $row = $doc->createElement('row');
      $row->setAttribute('id', $dbRow['id']);
      foreach ($dbRow as $key => $cell){
        if (!is_numeric($key) && $key != 'id'){
          $row->appendChild($this->newNode('cell', $dbRow[$key], $doc, ($key == 'htmlRep' ? true : false)));
        }
      }

      $root->appendChild($row);
    }

    $doc->appendChild($root);

    return ($string ? $doc->saveXML() : $doc);
  }

  /**
   * Runs the application
   * @access public
   * @return void
   * */
  function run() {
    header('Content-Type: text/xml; charset=UTF-8');
    echo $this->getRows();
  }


}

require_once("../../phplib/Core.php");
User::mustHave(User::PRIV_WOTD);
Util::assertNotMirror();

$filters = Request::getJson('filters', null, true);
$app = new wotdTableRows($_GET['page'], $_GET['rows'], $_GET['sidx'], $_GET['sord'], $filters);
$app->run();
