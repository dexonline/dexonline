<form class="d-flex align-items-center mx-auto">
  <a class="prevPage btn btn-link" title="pagina anterioară">
    {include "bits/icon.tpl" i=chevron_left}
  </a>

  {include "bits/sourceDropDown.tpl"
    id=false
    name="showPageSourceId"
    parent="#pageModal"
    sources=Source::getSourcesWithPageImages()
    skipAnySource="1"
    width="300px"}

  <input
    type="text"
    class="pageForWord form-control ms-2"
    placeholder="navighează la un cuvânt...">

  <a class="nextPage btn btn-link" title="pagina următoare">
    {include "bits/icon.tpl" i=chevron_right}
  </a>
</form>
