<h3>Exemple de pronunție a termenului „{$searchTerm}”</h3>

<a id="yg-widget-0" data-delay-load="1" width="640" class="youglish-widget" data-query="{$searchTerm}" data-lang="romanian" data-components="8264" data-auto-start="0" data-bkg-color="theme_light"  rel="nofollow" href="https://youglish.com/romanian">Visit YouGlish.com</a>
<script async src="https://youglish.com/public/emb/widget.js" charset="utf-8"></script>

<script>
let widget = null;
let missedSearchTerm = null;
function onYouglishAPIReady(){
   widget = YG.getWidget("yg-widget-0");
   if (missedSearchTerm) {
     widget.fetch(missedSearchTerm, "romanian");
     missedSearchTerm = null;
   }
}

function searchYGTerm() {
  if (widget) {
    widget.fetch("{$searchTerm}", "romanian");
  }
  else {
    missedSearchTerm = "{$searchTerm}";
    console.log("YG widget not yet loaded");
  }
}
</script>
