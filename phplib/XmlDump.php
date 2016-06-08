<?php

/**
 * Tools for collecting and listing XML dumps.
 **/

class XmlDump {
  private $remoteFolder;
  private $numFiles;
  private $numDiffFiles;
  private $staticFiles;
  private $today;

  // supported versions: 4, 5
  function __construct($version) {
    switch ($version) {
      case 4:
        $this->remoteFolder = 'download/xmldump';
        $this->numFiles = 6;
        $this->numDiffFiles = 3;
        break;
      case 5:
        $this->remoteFolder = 'download/xmldump/v5';
        $this->numFiles = 7;
        $this->numDiffFiles = 4;
        break;
    }
    $this->staticFiles = file(Config::get('static.url') . 'fileList.txt');
    $this->today = date("Y-m-d");
  }

  function getUrl() {
    return Config::get('static.url') . $this->remoteFolder;
  }

    // Do not return a dump for today, in case it is still being built
  function getLastDumpDate() {
    // Group existing files by date, excluding the diff files
    $map = [];
    foreach ($this->staticFiles as $file) {
      $matches = [];
      if (preg_match(":^{$this->remoteFolder}/(\\d\\d\\d\\d-\\d\\d-\\d\\d)-[a-z]+.xml.gz:",
                     $file, $matches)) {
        $date = $matches[1];
        if ($date < $this->today) {
          if (array_key_exists($date, $map)) {
            $map[$date]++;
          } else {
            $map[$date] = 1;
          }
        }
      }
    }

    // Now check if the most recent date has the expected number of files
    if (count($map)) {
      krsort($map);
      $date = key($map); // First key
      return ($map[$date] == $this->numFiles) ? $date : null;
    } else {  
      return null;
    }
  }

  // Return diffs between the given date and today, exclusively.
  // Do not return diffs for today, in case they are still being built.
  function getDiffsSince($date) {
    // Group existing diff files by date
    $map = [];
    foreach ($this->staticFiles as $file) {
      $matches = [];
      if (preg_match(":^{$this->remoteFolder}/(\\d\\d\\d\\d-\\d\\d-\\d\\d)-[a-z]+-diff.xml.gz:",
                     $file, $matches)) {
        $diffDate = $matches[1];
        if ($diffDate > $date && $diffDate < $this->today) {
          if (array_key_exists($matches[1], $map)) {
            $map[$matches[1]]++;
          } else {
            $map[$matches[1]] = 1;
          }
        }
      }
    }
    ksort($map);

    // Now returns those having the expected number of diff files
    $results = [];
    foreach ($map as $date => $numFiles) {
      if ($numFiles == $this->numDiffFiles) {
        $results[] = $date;
      }
    }
    return $results;
  }
}
