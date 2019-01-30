{extends "layout.tpl"}

{block "title"}Corector diacritice{/block}

{block "content"}
  <link rel="StyleSheet" type="text/css" href="/DEX/wwwbase/css/diacritics_fix.css">

  <div id="textareaSubtitle">
	  <center><span>Corector diacritice</span></center>
  </div>
  <form id="textareaForm" action="" method="POST">
	  <div id="textareaDiv" align="center">
		  {$textarea}
		  {$hiddenText}
		  <input type="submit" name="ok" value="{$buttonDisplayText}">
	  </div>
  </form>
{/block}
