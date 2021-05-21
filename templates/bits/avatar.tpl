{if $user && $user->hasAvatar}
  <img class="avatar"
    src="{Config::STATIC_URL}img/user/{$user->id}.jpg?cb={1000000000|rand:9999999999}"
    alt="imagine de profil: {$user->nick|escape}">
{else}
  {include "bits/icon.tpl" i=person}
{/if}
