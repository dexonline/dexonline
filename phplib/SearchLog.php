<?php

/**
 * Class used to log data when a search is performed
 **/
class SearchLog {

  /**
   * Constructs the object
   * @param string $query The query
   * @param string $query The query before the redirect (e.g. oprobiu before the automatic redirect to oprobriu)
   * @param int $searchType The seach type
   * @param boolean $redirect If true, then the result is a redirect [OPTIONAL]
   * @param Definition[] $results The results [OPTIONAL]
   * @access public
   * @return void
   **/
  public function __construct($query, $queryBeforeRedirect, $searchType, $redirect = false, &$results = null) {
    if (!Config::get('search-log.enabled') || lcg_value() > Config::get('search-log.fraction')) {
      $this->query = null;
      return false;
    }
    $this->query = $query;
    $this->queryBeforeRedirect = $queryBeforeRedirect;
    $this->searchType = $searchType;
    if (session_variableExists('user')) {
      $this->registeredUser = 'y';
      $this->preferences = $_SESSION['user']->preferences; 
    }
    else {
      $this->registeredUser = 'n';
      $this->preferences = session_getCookieSetting('anonymousPrefs');
    }
    $this->skin = session_getSkin();
    $this->resultCount = count($results);
    $this->redirect = ($redirect ? 'y' : 'n');
    $this->resultList = '';
    
    if ($results != null) {
      $numResultsToLog = min(count($results), Config::get('search-log.results'));
      $this->resultList = '';
      for ($i = 0; $i < $numResultsToLog; $i++) {
        $this->resultList .= ($this->resultList ? ',' : '') . $results[$i]->id;
      }
    }
  }
  
  /**
   * Saves an entry into the log table
   * @access public
   * @return boolean
   **/
  public function logData() {
    //If we decide to put the logged data into a table, then call $this->insert()
    if (!$this->query) {
      return false;
    }
    try {
      $f = fopen(Config::get('search-log.file'), 'at');
    }
    catch (Exception $e) {
      try {
        $f = fopen(Config::get('search-log.file'), 'wt');
      }
      catch (Exception $e) {
        throw new Exception('Error trying to access the log file', -1, $e);
      }
    }

    $date = date('Y-m-d H:i:s');
    $millis = DebugInfo::getRunningTimeInMillis();
    $line = "[{$this->query}]\t[{$this->queryBeforeRedirect}]\t{$this->searchType}\t{$this->registeredUser}\t{$this->skin}\t" .
      "{$this->preferences}\t{$this->resultCount}\t{$this->resultList}\t{$this->redirect}\t{$date}\t{$millis}\n";
    fwrite($f, $line);
    fclose($f);
  }
}

?>
