  <Lexem id="{$lexeme->id}">
    <Timestamp>{$lexeme->modDate}</Timestamp>
    <Form>{$lexeme->form|escape}</Form>
    {if $lexeme->description}
      <Description>{$lexeme->description|escape}</Description>
    {/if}
    {assign var="ifs" value=$lexeme->loadInflectedForms()}
    {foreach $ifs as $if}
      <InflectedForm>
        <InflectionId>{$if->inflectionId}</InflectionId>
        <Form>{$if->form|escape}</Form>
      </InflectedForm>
    {/foreach}
  </Lexem>

