<?php

/**
 * Replaces şŞţŢ with șȘțȚ in all fields of all tables.
 **/

require_once __DIR__ . '/../phplib/Core.php';

$fix = false;
foreach ($argv as $i => $arg) {
  if ($i) {
    switch ($arg) {
      case '--fix': $fix = true; break;
      default: print "Unknown flag $arg -- aborting\n"; exit;
    }
  }
}

$files = glob(__DIR__ . '/../phplib/models/*.php');

foreach ($files as $file) {
  $parts = explode('/', $file);
  $class = explode('.', end($parts))[0];

  if (empty($class::$_table)) {
    Log::warning("Skipping irregular class $class");
  } else {
    $table = $class::$_table;

    $tableExists = DB::execute("show tables like '$table'")->rowCount();

    if (!$tableExists) {
      Log::warning("Skipping class $class because there is no corresponding table.");
    } else {
      // See if there is a primary key
      $pkExists = DB::execute("show index from {$table} where !non_unique")->rowCount();

      if (!$pkExists) {
        Log::warning("Skipping class $class because there is no primary key.");
      } else {
        Log::info("Processing class $class (table $table)");
        $columns = DB::execute("show columns from $table");
        foreach ($columns as $c) {
          $column = $c['Field'];
          $colType = $c['Type'];
          if (StringUtil::startsWith($colType, 'char(') ||
              StringUtil::startsWith($colType, 'varchar(') ||
              ($colType == 'mediumtext') ||
              ($colType == 'text')) {
            Log::info("Processing column {$table}.{$column} of type {$colType}");
            $data = Model::factory($class)
                  ->where_raw("binary $column rlike '(ş|Ş|ţ|Ţ)'")
                  ->find_many();
            if (count($data)) {
              if ($fix) {
                Log::notice("Replacing column %s.%s (%d occurrences)", $table, $column, count($data));
                foreach ($data as $rec) {
                  $rec->$column = AdminStringUtil::cleanup($rec->$column);
                  $rec->save();
                }
              } else {
                Log::notice("%d occurrences in column %s.%s (run with --fix to fix)",
                            count($data), $table, $column);
              }
            }
          }
        }
      }
    }
  }
}
