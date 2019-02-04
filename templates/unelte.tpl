{extends "layout.tpl"}

{block "title"}{cap}{t}tools{/t}{/cap}{/block}

{block "content"}
  <h3>{cap}{t}off-line clients{/t}{/cap}</h3>

  <p>
    {t}These applications download the definitions from <i>dexonline</i> to
    your computer or phone. Then you can see them without an Internet
    connection.{/t}
  </p>

  <table class="table table-bordered">
    <tbody>
      <tr class="active">
        <th>{t}Client{/t}</th>
        {foreach $clients as $c}
          <td class="text-center">
            <a href="{$c.url}">{$c.name}</a>
            {if $c.download}
              <br>
              (<a href="{$c.download}">descarcă</a>)
            {/if}
          </td>
        {/foreach}
      </tr>

      <tr>
        <th>{t}Platforms{/t}</th>
        {foreach $clients as $c}
          <td class="text-center">
            {foreach $c.os as $os}
              <img src="img/os/{$os}.png" alt="{$os}" title="{$osNames[$os]}">
            {/foreach}
          </td>
        {/foreach}
      </tr>

      {foreach $clientOptions as $id => $option}
        <tr>
          <th title="{$option.1}">
            {$option.0}
            <i class="text-info glyphicon glyphicon-info-sign"></i>
          </th>
          {foreach $clients as $c}
            <td class="text-center">
              {if $c.options[$id]}
                <i class="glyphicon glyphicon-ok"></i>
              {/if}
            </td>
          {/foreach}
        </tr>
      {/foreach}

      <tr>
        <th>{t}Requirements{/t}</th>
        {foreach $clients as $c}
          <td>{$c.requires}</td>
        {/foreach}
      </tr>

      <tr>
        <th>{t}Disk space required{/t}</th>
        {foreach $clients as $c}
          <td>{$c.space}</td>
        {/foreach}
      </tr>

      <tr>
        <th>{t}Author{/t}</th>
        {foreach $clients as $c}
          <td>
            {if $c.author.1}
              <a href="{$c.author.1}">{$c.author.0}</a>
            {else}
              {$c.author.0}
            {/if}
          </td>
        {/foreach}
      </tr>

      <tr>
        <th>{cap}{t}license{/t}{/cap}</th>
        {foreach $clients as $c}
          <td>{$c.license}</td>
        {/foreach}
      </tr>

    </tbody>
  </table>

  <div class="clearfix">
    <a class="btn btn-link pull-right"
      data-toggle="collapse"
      href="#tableLicense"
      aria-expanded="false"
      aria-controls="tableLicense">
      {t}license on the data in this table{/t}
    </a>
  </div>

  <div id="tableLicense" class="alert alert-info collapse">
    <p>
      <i class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></i>

      <strong>{cap}{t}license{/t}{/cap}:</strong>

      {t 1="http://creativecommons.org/licenses/by-sa/3.0/"}
      The information in this table is dual-licensed under the GNU General
      Public License (as applicable to the entire site) and the
      <a href="%1">Creative Commons Attribution-ShareAlike 3.0</a> license.
      The information can be used (copied, modified, adapted etc.) by the
      website http://wikipedia.org. The data are gathered from the mailing
      list discutii@dexonline.ro (formerly dictionar@yahoogroups.com) in
      collaboration with the application developers.{/t}
    </p>
  </div>

  <h3>{t}Browser integration{/t}</h3>

  <ul>
    <li>
      {$url="{Config::STATIC_URL}download/dex-ff.xpi"}
      {t
        1=$url
        2="return installFirefoxSpellChecker(event);"}
      A <a href="%1" onclick="%2">spell checker</a> for Firefox.{/t}

      <ul>
        <li>
          {t}To use the spell checker, right-click in any text box, check the
          <em>Check spelling</em> box, right-click once more and select
          <em>Languages > Romanian</em>.{/t}
        </li>
      </ul>
    </li>

    <li>
      {t 1="return addProvider('https://dexonline.ro/download/dex.xml')"}
      <a href="#" onclick="%1">Add <i>dexonline</i> to the search engine list</a>
      of your browser. Thanks to Alexandru Lixandru.{/t}
    </li>

    <li>
      {t 1="https://chrome.google.com/webstore/detail/mfbhbmjeaomdhmkcfhlldgedmohdpeml?hl=ro"}
      A <a href="%1">Chrome extension</a> for right-click searches.{/t}
    </li>

    <li>
      {t 1="download/dex-context-search.xml" 2="Radu George Mureșan"}
      A <a href="%1">Firefox plugin</a> for right-click searches. Save the file
      in the <tt>searchplugins/</tt> directory of Firefox and restart Firefox.
      Thanks to %2.{/t}
    </li>

    <li>
      {t 1="https://wordpress.org/plugins/dexonline-searchbox/"}
      A <a href="%1">WordPress plugin</a> for your blog.{/t}
    </li>

  </ul>
{/block}
