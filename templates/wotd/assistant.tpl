{extends "layout-admin.tpl"}

{$title="Asistent pentru cuvântul zilei: {$yearMonth|date:'MMMM yyyy'}"}

{block "title"}{$title}{/block}

{block "content"}
  <h3>
    {$title}

    <form class="float-end row row-cols-lg-auto g-1">
      <div class="col-12">
        <label class="col-form-label" for="calendar">alege altă lună:</label>
      </div>
      <div class="col-12">
        <input id="calendar" type="text" name="for" value="{$yearMonth}" class="form-control">
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          ok
        </button>
      </div>
    </form>
  </h3>

  {foreach $data as $day => $rec}
    <div class="card card-collapse mt-3">
      <div
        class="card-header {if $rec.allOk}collapsed{/if}"
        data-bs-toggle="collapse"
        href="#collapseDay{$day}">

        {include "bits/icon.tpl" i=expand_less class="chevron"}

        <span class="date">
          {$day} {$yearMonth|date:'MMMM yyyy'}
        </span>

        {if $rec.allOk}
          {include "bits/icon.tpl" i=done class="text-success fw-bold fs-3"}
        {/if}

        <div class="float-end">
          <a
            class="btn btn-link btn-sm"
            href="https://ro.wikipedia.org/wiki/{$day}_{$yearMonth|date:'MMMM'}"
            target="_blank">wikipedia RO</a>
          <a
            class="btn btn-link btn-sm"
            href="https://en.wikipedia.org/wiki/{$enMonthName}_{$day}"
            target="_blank">wikipedia EN</a>

          {if $rec.allOk}
            {$rec.thisYear[0]->lexicon}
          {/if}
        </div>

      </div>

      <div id="collapseDay{$day}" class="card-body collapse {if !$rec.allOk}show{/if}">
        {if empty($rec.thisYear)}
          {notice type="danger"}
            Nu ai ales încă un cuvânt.
          {/notice}
        {else if count($rec.thisYear) > 1}
          {notice type="warning"}
            Există {$rec.thisYear|count} cuvinte.
          {/notice}
        {else if !$rec.thisYear[0]->defHtml}
          {notice type="warning"}
            Există un motiv, dar nu și o definiție.
          {/notice}
        {else if !$rec.thisYear[0]->description}
          {notice type="warning"}
            Există o definiție, dar nu și un motiv.
          {/notice}
        {/if}

        {foreach $rec.duplicates as $dup}
          {notice type="{if $dup.exact}danger{else}warning{/if}"}
            {if $dup.exact}
              Un cuvânt identic,
            {else}
              Un cuvânt asemănător,
            {/if}
            <b>{$dup.oldLexicon}</b>, a fost programat pe
            {strip}
            <a href="{Router::link('wotd/view')}/{$dup.oldDate}">
              {$dup.oldDate|date:'d MMMM yyyy'}
            </a>.
            {/strip}
          {/notice}
        {/foreach}

        {foreach $rec.thisYear as $w}
          <p>
            {if $w->defHtml}
              {$w->defHtml}
            {/if}

            {if $w->description}
              <div>
                <strong>Motiv</strong>: {$w->descHtml}
              </div>
            {/if}
          </p>
        {/foreach}

        {if !empty($rec.otherYears)}
          <hr>
          <h4>În alți ani:</h4>

          <table class="table table-sm">
            <tbody>
              {foreach $rec.otherYears as $w}
                <tr>
                  <td>
                    {if $w->hasFullDate()}
                      {$w->displayDate|date:'yyyy'}
                    {else}
                      <span class="badge bg-secondary">fără an</span>
                    {/if}
                  </td>
                  <td>{$w->lexicon}</td>
                  <td>{$w->descHtml}</td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        {/if}

      </div>
    </div>
  {/foreach}
{/block}
