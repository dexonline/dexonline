{extends "layout.tpl"}

{block "title"}Utilizator: {$user->nick}{/block}

{block "content"}
  <div class="userProfileHeader">
    <h3>
    </h3>
  </div>

  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      {include "bits/avatar.tpl" user=$user}
      <span class="mx-2 flex-grow-1">{$user->nick|escape}</span>
      {if $user->id == User::getActiveId()}
        <a
          class="btn btn-outline-secondary btn-sm"
          href="{Router::link('user/preferences')}">
          editează profilul
        </a>
      {/if}
    </div>

    <div class="card-body">
      {if $user->detailsVisible && ($user->name || $user->email)}
        <dl class="row">
          {if $user->detailsVisible && $user->name}
            <dt class="col-md-3">Nume</dt>
            <dd class="col-md-9">{$user->name|escape}</dd>
          {/if}

          {if $user->detailsVisible && $user->email}
            <dt class="col-md-3">Adresă de e-mail</dt>
            <dd class="col-md-9">{$user->email|escape}</dd>
          {/if}
        </dl>
      {else}
        Numele și adresa de e-mail nu sînt vizibile.
      {/if}
    </div>
  </div>

  {if isset($userData.numDefinitions) || isset($userData.numImages)}
    <div class="card mb-3">
      <div class="card-header">Contribuții</div>
      <div class="card-body">
        <dl class="row">

          {if isset($userData.numDefinitions)}
            <dt class="col-md-3">Definiții trimise</dt>
            <dd class="col-md-9">
              {$userData.numDefinitions} (locul {$userData.rankDefinitions})
            </dd>

            <dt class="col-md-3">Lungime totală</dt>
            <dd class="col-md-9">
              {$userData.numChars} caractere (locul {$userData.rankChars})
            </dd>

            <dt class="col-md-3">Ultima contribuție</dt>
            <dd class="col-md-9">
              {$userData.lastSubmission|date_format:"%d %B %Y"}
            </dd>
          {/if}

          {if isset($userData.numImages)}
            <dt class="col-md-3">Ilustrații desenate</dt>
            <dd class="col-md-9">
              {$userData.numImages} ilustrații
            </dd>
          {/if}
        </dl>
      </div>
    </div>
  {/if}

  <div class="card mb-3">
    <div class="card-header d-flex align-items-center">
      <span class="flex-grow-1">Medalii</span>
      {if User::can(User::PRIV_ADMIN)}
        <a
          class="btn btn-sm btn-outline-secondary"
          data-bs-toggle="collapse"
          href="#medalEditDiv">
          editează medaliile
        </a>
      {/if}
    </div>
    <div class="card-body">
      {if User::can(User::PRIV_ADMIN)}
        <form id="medalEditDiv" method="post" class="collapse">
          <input type="hidden" name="userId" value="{$user->id}">

          {foreach $allMedals as $mask => $params}
            {capture 'label'}
            {$params.name}
            <span class="form-text ms-2">{$params.description}</span>
            {/capture}
            {include "bs/checkbox.tpl"
              name="medalsGranted[]"
              label=$smarty.capture.label
              checked=array_key_exists($mask, $medals)
              value=$mask}
          {/foreach}

          <div class="mt-3">
            <button class="btn btn-primary" type="submit" name="medalSaveButton">
              {include "bits/icon.tpl" i=save}
              salvează
            </button>
          </div>
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
        Utilizatorul {$user->nick|escape} nu are medalii.
      {/if}
    </div>
  </div>
{/block}
