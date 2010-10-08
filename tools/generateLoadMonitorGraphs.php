<?

/**
 * Loads a log file and draws load graphs for the last few days.
 **/

define('LOG_FILENAME', '/var/log/load.log');
define('DATA_FILENAME', '/tmp/gnuplot%02d.dat');
define('PNG_FILENAME', dirname($_SERVER['SCRIPT_NAME']) . '/../wwwbase/stat/img/gnuplot%02d-%d.png');
define('DAYS_SINCE_EPOCH', intval(time() / 86400));
define('NUM_DAYS', 7);

$records = loadLogFile(LOG_FILENAME);
outputGnuPlotData($records, DATA_FILENAME);
generatePngs(DATA_FILENAME, PNG_FILENAME);

/*************************************************************************/

function loadLogFile($filename) {
  $lines = file($filename, FILE_IGNORE_NEW_LINES);
  $records = array();
  foreach ($lines as $line) {
    $r = new Record;
    list($date, $loads) = preg_split("/\|/", $line);
    $loads = preg_split("/\s+/", trim($loads));
    $r->dateString = trim($date);
    $r->timestamp = strtotime($r->dateString);
    $r->hour = strftime("%H", $r->timestamp);
    $r->minute = strftime("%M", $r->timestamp);
    $r->date = strftime("%a %d %b %Y", $r->timestamp);
    $r->daysAgo = DAYS_SINCE_EPOCH - intval($r->timestamp / 86400);
    $r->load1 = $loads[0];
    $r->load5 = $loads[1];
    $r->load15 = $loads[2];
    $records[] = $r;
  }
  return $records;
}

function outputGnuPlotData($records, $filename) {
  $handles = array();
  for ($i = 0; $i < NUM_DAYS; $i++) {
    $handle[] = fopen(sprintf($filename, $i), 'w');
  }
  foreach ($records as $r) {
    if ($r->daysAgo < NUM_DAYS) {
      fwrite($handle[$r->daysAgo], "{$r->hour}:{$r->minute} {$r->load1} {$r->load5} {$r->load15}\n");
    }
  }
  foreach ($handles as $f) {
    fclose($f);
  }
}

function generatePngs($dataFilename, $pngFilename) {
  $tmpFilename = '/tmp/gnuplot.conf';
  $titles = array("1-minute", "5-minute", "15-minute");
  for ($i = 0; $i < NUM_DAYS; $i++) {
    for ($j = 0; $j < 3; $j++) {
      $input = sprintf($dataFilename, $i);
      $output = sprintf($pngFilename, $i, $j);
      $dataCol = $j + 2;
      $title = $titles[$j];
      $date = strftime("%m/%d/%Y", time() - 86400 * $i);

      $f = fopen($tmpFilename, 'w');
      fwrite($f, "set terminal png size 400,200\n" .
             "set output \"$output\"\n" .
             "set xdata time\n" .
             "set format x \"%H\"\n" .
             "set timefmt \"%H:%M\"\n" .
             "set xrange [\"00:00\":\"24:00\"]\n" .
             "set yrange [0:10]\n" .
             "set grid\n" .
             "set xlabel \"\"\n" .
             "unset ylabel\n" .
             "set title \"$title load average, $date\"\n" .
             "set key off\n" .
             "plot \"$input\" using 1:$dataCol with lines\n");
      fclose($f);
      exec("gnuplot $tmpFilename");
    }
  }
}

class Record {
  public $dateString;
  public $timestamp;
  public $hour;
  public $minute;
  public $date;
  public $daysAgo;
  public $load1;
  public $load5;
  public $load15;
}


?>
