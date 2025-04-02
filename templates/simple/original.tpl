{extends "layout.tpl"}

{block "title"}
    {cap}Paginile originale{/cap}
{/block}

{block "content"}
  {include "bits/pageModal.tpl"}
    <h3>Paginile originale</h3>
      <a href="#"
        class="dropdown-item"
        title="arată pagina originală cu această definiție"
        data-bs-toggle="modal"
        data-bs-target="#pageModal"
        data-sourceId="1"
        data-word="a"
        data-volume="1"
        data-page="1">
        {include "bits/icon.tpl" i=description}
        arată originalul
      </a>
{/block}
