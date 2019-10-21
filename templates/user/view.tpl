{extends "layout.tpl"}

{block "title"}Utilizator: {$user->nick}{/block}

{block "content"}
  <div class="userProfileHeader">
    <h3>
    </h3>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">
      {include "bits/avatar.tpl" user=$user}
      <span>{$user->nick|escape}</span>
      {if $user->id == User::getActiveId()}
        <a
          class="btn btn-default btn-sm pull-right"
          href="{Router::link('user/preferences')}">editează profilul</a>
      {/if}

    </div>
    {if $user->detailsVisible && ($user->name || $user->email)}
      <div class="panel-body">
        <dl class="dl-horizontal">
          {if $user->detailsVisible && $user->name}
            <dt>Nume</dt>
            <dd>{$user->name|escape}</dd>
          {/if}

          {if $user->detailsVisible && $user->email}
            <dt>Adresă de e-mail</dt>
            <dd>{$user->email|escape}</dd>
          {/if}
        </dl>
      </div>
    {/if}
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">Contribuții</div>
    <div class="panel-body">
      <dl class="dl-horizontal">

        <dt>Definiții trimise</dt>
        <dd>
          {$userData.numDefinitions|default:0}
          {if isset($userData.numDefinitions)}(locul {$userData.rankDefinitions}){/if}
        </dd>

        <dt>Lungime totală</dt>
        <dd>
          {$userData.numChars|default:0} caractere
          {if isset($userData.numChars)}(locul {$userData.rankChars}){/if}
        </dd>

        {if isset($userData.lastSubmission)}
        <dt>Ultima contribuție</dt>
        <dd>
          {$userData.lastSubmission|date_format:"%d %B %Y"}
        </dd>
        {/if}

          {if $userData.numImages}
            <dt>Ilustrații desenate</dt>
            <dd>
                {$userData.numImages} ilustrații
            </dd>
          {/if}
      </dl>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">
      Medalii
      {if User::can(User::PRIV_ADMIN)}
        <button class="btn btn-xs btn-default pull-right" data-toggle="collapse" data-target="#medalEditDiv">
          editează medaliile
        </button>
      {/if}
    </div>
    <div class="panel-body">
      {if User::can(User::PRIV_ADMIN)}
        <form id="medalEditDiv" method="post" class="collapse">
          <div class="medalCheckboxes">
            <input type="hidden" name="userId" value="{$user->id}">
            {foreach $allMedals as $mask => $params}
              <div class="checkbox">
                <label>
                  <input type="checkbox"
                         name="medalsGranted[]"
                         id="cb_{$mask}"
                         value="{$mask}"
                         {if array_key_exists($mask, $medals)}checked{/if}>
                  {$params.name} {$params.description}
                </label>
              </div>
            {/foreach}
          </div>
          <input class="btn btn-default" type="submit" name="medalSaveButton" value="Salvează">
        </form>
      {/if}

      {if $medals}
        <div class="text-center">
          {foreach $medals as $params}
            <img
              src="../img/medals/{$params.pic}"
              alt="{$params.name}"
              title="{$params.name} {$params.description}">
          {/foreach}
        </div>
      {else}
        <span class="userNoMedals">Utilizatorul {$user->nick|escape} nu are medalii.</span>
      {/if}
    </div>
  </div>
{/block}
