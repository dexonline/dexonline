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
          <td class="text-center align-middle">
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
            {include "bits/icon.tpl" i=info}
          </th>
          {foreach $clients as $c}
            <td class="text-center">
              {if $c.options[$id]}
                {include "bits/icon.tpl" i=done}
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

  <div class="text-end">
    <a class="btn btn-link"
      data-bs-toggle="collapse"
      href="#tableLicense"
      aria-expanded="false"
      aria-controls="tableLicense">
      {t}license on the data in this table{/t}
    </a>
  </div>

  <div id="tableLicense" class="collapse">
    {notice type=info}
      <strong>{cap}{t}license{/t}{/cap}:</strong>

      {t 1="http://creativecommons.org/licenses/by-sa/3.0/"}
      The information in this table is dual-licensed under the GNU General
      Public License (as applicable to the entire site) and the
      <a href="%1">Creative Commons Attribution-ShareAlike 3.0</a> license.
      The information can be used (copied, modified, adapted etc.) by the
      website http://wikipedia.org. The data are gathered from the mailing
      list discutii@dexonline.ro (formerly dictionar@yahoogroups.com) in
      collaboration with the application developers.{/t}
    {/notice}
  </div>

{/block}
