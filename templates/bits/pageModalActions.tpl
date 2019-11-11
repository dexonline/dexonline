<form class="form-inline">
  <a class="prevPage btn btn-link" title="pagina anterioară">
    <i class="glyphicon glyphicon-chevron-left"></i>
  </a>

  {include "bits/sourceDropDown.tpl"
          name="showPageSourceId"
          sources=Source::getSourcesWithPageImages()
          skipAnySource="1"
          width="300px"}

  <input type="text"
         class="pageForWord form-control"
         placeholder="navighează la un cuvânt..."
         >

  <a class="nextPage btn btn-link" title="pagina următoare">
    <i class="glyphicon glyphicon-chevron-right"></i>
  </a>
</form>
