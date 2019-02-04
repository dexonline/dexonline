<table>
  {foreach $forms as $form}
    <tr>
      <td>
        <a href="{Config::URL_PREFIX}definitie/{$form.0}">{$form.0}</a>
      </td>
      <td>
        {$form.surse}
      </td>
    </tr>
  {/foreach}
</table>
