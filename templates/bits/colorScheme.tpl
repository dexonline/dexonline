{**
  * Set the color scheme according to (in order of priority):
  *
  * 1. localStorage preference
  * 2. prefers-color-scheme user agent setting
  * 3. server default
  *
  * We do this inside the <head> tag so that the body isn't rendered yet.
  * This prevents a flicker effect.
  *}

{$colorSchemes=Config::COLOR_SCHEMES}

{* this is still a good idea for native controls: date pickers, scrollbars etc. *}
<meta name="color-scheme" content="{' '|implode:$colorSchemes}">

<script>
  function applyColorScheme() {
    {* set class on HTML element *}
    document.documentElement.className = getColorScheme();
  };
  function getColorScheme() {
    var ls = localStorage.getItem('colorScheme');
    if (ls) {
      return ls;
    }
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return 'dark';
    }
    return '{$colorSchemes[0]}';
  }
  function setColorScheme(scheme) {
    localStorage.setItem('colorScheme', scheme);
    applyColorScheme();
  }
  applyColorScheme();
  {* Now that we no longer simply obey prefers-color-scheme, we need to listen *}
  {* to changes and apply them to the current page. *}
  window.matchMedia("(prefers-color-scheme: dark)").addEventListener(
    'change', applyColorScheme
  );
</script>
