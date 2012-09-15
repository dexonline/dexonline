<?php

include_once "../phplib/util.php";

ini_set('max_execution_time', '3600');

$time = explode(" ", microtime());
$start = $time[1] + $time[0];

db_execute("delete from NGram");
db_execute("ALTER TABLE NGram AUTO_INCREMENT = 1");
print "Table content deleted\n" ;

$time = explode(" ", microtime());
$end1 = $time[1] + $time[0];
$diff_time = sprintf('%0.5f', $end1 - $start);
print "Time to delete content:  $diff_time seconds\n";

$dbResult = db_execute("select * from Lexem", PDO::FETCH_ASSOC);

$id = 1;
foreach ($dbResult as $cnt => $row) {
  $lexem = Model::factory('Lexem')->create($row);
  $form = NGram::padWord($lexem->formNoAccent);
	$len = mb_strlen($form);

	for ($i = 0; $i < $len - NGram::$NGRAM_SIZE + 1; $i++) {
		$n = Model::factory('NGram')->create();
		$n->ngram = mb_substr($form, $i, NGram::$NGRAM_SIZE);
		$n->pos = $i;
		$n->lexemId = $lexem->id;
		$n->save();
		$id++;
	}
  if ($cnt % 1000 == 0) {
    print "$cnt lexems processed\n" ;
  }
}

$time = explode(" ", microtime());
$end2 = $time[1] + $time[0];
$diff_time = sprintf('%0.5f', $end2 - $end1);
print "Time to create NGram table:  $diff_time seconds\n";

print "$id lines created\n";

?>
