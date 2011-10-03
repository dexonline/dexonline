<?php
// Fix the rough guide entries entered by schemaChange.sql. Those were taken
// directly from the static HTML page.
require_once("../../phplib/util.php");

$result = mysql_query("select * from GuideEntry");

$now = time();

while ($row = mysql_fetch_assoc($result)) {
  $id = $row['Id'];
  $correct = $row['Correct'];
  $wrong = $row['Wrong'];
  $comments = $row['Comments'];

  $correct = text_internalizeDefinition($correct);
  $wrong = text_internalizeDefinition($wrong);
  $comments = text_internalizeDefinition($comments);

  $correctHtml = text_htmlizeWithNewlines($correct, TRUE);
  $wrongHtml = text_htmlizeWithNewlines($wrong, TRUE);
  $commentsHtml = text_htmlizeWithNewlines($comments, TRUE);

  $correct = addslashes($correct);
  $correctHtml = addslashes($correctHtml);
  $wrong = addslashes($wrong);
  $wrongHtml = addslashes($wrongHtml);
  $comments = addslashes($comments);
  $commentsHtml = addslashes($commentsHtml);

  mysql_query("update GuideEntry set " .
	      "Correct = '$correct', " .
	      "CorrectHtml = '$correctHtml', " .
	      "Wrong = '$wrong', " .
	      "WrongHtml = '$wrongHtml', " .
	      "Comments = '$comments', " .
	      "CommentsHtml = '$commentsHtml', " .
	      "Status = 0, " .
	      "CreateDate = $now, " .
	      "ModDate = $now " .
	      "where Id = $id");
 }
?>
