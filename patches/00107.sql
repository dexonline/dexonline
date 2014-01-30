update Definition
  set internalRep = replace(internalRep, '‑', '-'),
      htmlRep = replace(htmlRep, '‑', '-')
  where sourceId = 38;
update InflectedForm
  set form = replace(form, '‑', '-'),
      formNoAccent = replace(formNoAccent, '‑', '-'),
      formUtf8General = replace(formUtf8General, '‑', '-');
update Lexem
  set form = replace(form, '‑', '-'),
      formNoAccent = replace(formNoAccent, '‑', '-'),
      formUtf8General = replace(formUtf8General, '‑', '-');
