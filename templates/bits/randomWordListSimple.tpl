<table>
  {foreach $forms as $form}
    <tr>
      <td>
        <a href="{$wwwRoot}definitie/{$form.0}">{$form.0}</a>
      </td>
      <td>
        {$form.surse}
      </td>
    </tr>
  {/foreach}
</table>
