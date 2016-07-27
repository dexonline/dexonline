{extends file="widgets/layout.tpl"}

{block name="widget-header"}
  Jocuri
{/block}

{block name="widget-body"}
  <img src="{$imgRoot}/hangman/thumb.png" alt="iconiță spânzurătoarea">
  <ul class="list-unstyled list-games">
    <li><a href="{$wwwRoot}spanzuratoarea">spânzurătoarea</a></li>
    <li><a href="{$wwwRoot}moara">moara cuvintelor</a></li>
    {* <li><a href="{$wwwRoot}omleta">omleta cuvintelor</a></li>*}
  </ul>
{/block}
