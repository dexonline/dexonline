<div
  onclick="openPopup(this.querySelector('img').src)"
  class="widget wotd d-flex flex-md-column flex-xl-row"
  style="cursor:pointer;"
>
  <div class="flex-grow-1">
    <h4>Cuvântul anului</h4><br>
    <a href="{Config::URL_PREFIX}definitie/sinecură/definitii" onclick="event.stopPropagation()"><span class="widget-value">sinecură</span></a>
  </div>
  <div>
    <img src="https://dexonline.ro/static/img/top/anual/top2025.jpg" width=88 height=88 alt="iconiță cuvântul din vitrină" class="widget-icon">
  </div>
</div>

<script>
  function openPopup(src) {
    // blochează scroll-ul paginii
    document.body.style.overflow = 'hidden';

    const popup = document.createElement('div');
    popup.id = 'popup';

    const img = document.createElement('img');
    img.src = src;

    // stiluri overlay
    Object.assign(popup.style, {
      position: 'fixed',
      inset: '0',
      background: 'rgba(0,0,0,0.8)',
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'center',
      zIndex: 10000
    });

    Object.assign(img.style, {
      maxWidth: '80%',
      maxHeight: '80%'
    });

    // blochează click-ul în spate
    popup.addEventListener('click', e => {
      if (e.target === popup) closePopup();
    });

    // închidere cu ESC
    const escHandler = e => {
      if (e.key === 'Escape') closePopup();
    };
    document.addEventListener('keydown', escHandler);

    function closePopup() {
      document.body.style.overflow = '';
      document.removeEventListener('keydown', escHandler);
      popup.remove();
    }

    popup.appendChild(img);
    document.body.appendChild(popup);

    // focus capturat în modală
    popup.tabIndex = -1;
    popup.focus();
  }
</script>
