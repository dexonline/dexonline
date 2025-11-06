<div id="player-container" style="width: 640px; margin: 0 auto;">
  <h5>Exemple de pronunție a termenului „{$pronTerm}” ({$pronunciations|@count} clipuri)</h5>

  <div style="margin-top: 10px; float:right;">
    <button id="prevBtn">⏮️ Precedentul</button>
    <button id="nextBtn">⏭️ Următorul</button>
  </div>
  <div id="player"></div>
  <div id="subtitles"
       style="font-size: 1.2em; margin-top: 10px; color: white;
              background: rgba(0,0,0,0.6); padding: 10px; border-radius: 8px;
              min-height: 2em; transition: opacity 0.3s ease;">
  </div>
</div>

<script src="https://www.youtube.com/iframe_api"></script>
<script>

  // Lista de segmente (fiecare video are subtitrarea sa)
  const segments = {$pronunciations|@json_encode};
  {literal}
  let dexRoot = 'https://dexonline.ro';
  for (idx in segments) {segments[idx].subs = dexRoot + '/subs/' + segments[idx].id + '.ro.srt'}
  {/literal}

  let player;
  let subtitles = [];
  let currentSubtitle = null;
  let currentIndex = 0;

  // Încarcă subtitrările pentru segmentul curent
  function loadSubtitles(file) {
    return fetch(file)
      .then(res => res.text())
      .then(parseSRT)
      .then(data => {
        subtitles = data;
        currentSubtitle = null;
        document.getElementById('subtitles').innerHTML = '';
      })
      .catch(err => console.error('Eroare la încărcarea subtitrărilor:', err));
  }

  // Parser pentru format .SRT
  function parseSRT(data) {
    const srt = data.trim().split(/\r?\n\r?\n/);
    const toSeconds = t => {
      const [h, m, s] = t.replace(',', '.').split(':').map(Number);
      return h * 3600 + m * 60 + s;
    };
    const cues = [];
    for (const entry of srt) {
      const lines = entry.split(/\r?\n/);
      if (lines.length >= 2) {
        const timeMatch = lines[1].match(/(\d+:\d+:\d+,\d+)\s*-->\s*(\d+:\d+:\d+,\d+)/);
        if (timeMatch) {
          const start = toSeconds(timeMatch[1]);
          const end = toSeconds(timeMatch[2]);
          const text = lines.slice(2).join('<br>');
          cues.push({ start, end, text });
        }
      }
    }
    return cues;
  }

  // Inițializează playerul YouTube
  function onYouTubeIframeAPIReady() {
    const seg = segments[currentIndex];

    loadSubtitles(seg.subs).then(() => {
      player = new YT.Player('player', {
        height: '360',
        width: '640',
        videoId: seg.id,
        playerVars: { 'playsinline': 1, 'autoplay': 0, 'cc_load_policy': 1, 'cc_lang_pref': 'ro', 'start': seg.start },
        events: { 'onReady': onPlayerReady }
      });
    });
  }

  // După ce playerul e gata
  function onPlayerReady() {
    setupNavigationButtons();
    player.seekTo(segments[currentIndex].start, true);
    // player.playVideo();
    setInterval(checkSubtitle, 200);

    // Creează un observator pentru vizibilitate
    const container = document.getElementById('player-container');
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // Playerul este vizibil => pornește
          player.playVideo();
        } else {
          // Playerul iese din ecran => pune pauză
          player.pauseVideo();
        }
      });
    }, { threshold: 0.5 }); // Pornește doar dacă 50% din player e vizibil

    observer.observe(container);
  }

  // Actualizează subtitrările
  function checkSubtitle() {
    if (!player || !subtitles.length) return;
    const time = player.getCurrentTime();

    // găsește toate subtitrările active (nu doar una)
    const actives = subtitles.filter(s => time >= s.start && time <= s.end);

    // combină textele tuturor subtitrărilor active
    const combinedText = actives.map(s => s.text).join('<br>');

    if (combinedText !== currentSubtitle) {
      currentSubtitle = combinedText;
      const subtitleDiv = document.getElementById('subtitles');
      subtitleDiv.style.opacity = 0;
      setTimeout(() => {
        subtitleDiv.innerHTML = combinedText;
        subtitleDiv.style.opacity = 1;
      }, 100);
    }
  }


  // Butoane navigare
  function setupNavigationButtons() {
    document.getElementById('prevBtn').addEventListener('click', () => changeSegment(-1));
    document.getElementById('nextBtn').addEventListener('click', () => changeSegment(1));
  }

  // Schimbă segmentul (video + subtitrări)
  function changeSegment(direction) {
    currentIndex += direction;
    if (currentIndex < 0) currentIndex = segments.length - 1;
    if (currentIndex >= segments.length) currentIndex = 0;

    const seg = segments[currentIndex];

    loadSubtitles(seg.subs).then(() => {
      player.loadVideoById({ videoId: seg.id, startSeconds: seg.start });
    });
  }
</script>
