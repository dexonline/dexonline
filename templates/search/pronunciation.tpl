<h4>Exemple de pronunție a termenului „<a href="https://youglish.com/pronounce/{$searchTerm|escape:'url'}/romanian" target="_blank" rel="nofollow noopener noreferrer">{$searchTerm}</a>”</h4>

<a id="yg-widget-0" data-delay-load="1" width="640" class="youglish-widget" data-query="{$searchTerm}" data-lang="romanian" data-components="8264" data-auto-start="0" data-bkg-color="theme_light"  rel="nofollow" href="https://youglish.com/romanian">Visit YouGlish.com</a>
<script async src="https://youglish.com/public/emb/widget.js" charset="utf-8"></script>

<script>
let widget = null;
let missedSearchTerm = null;
let fetched = false;
function onYouglishAPIReady(){
   widget = YG.getWidget("yg-widget-0");
   if (missedSearchTerm) {
     widget.fetch(missedSearchTerm, "romanian");
     missedSearchTerm = null;
   }
}

function searchYGTerm() {
  if (fetched) return;

  if (widget) {
    widget.fetch("{$searchTerm}", "romanian");
    fetched = true;
  }
  else {
    missedSearchTerm = "{$searchTerm}";
    console.log("YG widget not yet loaded");
  }
}
</script>
