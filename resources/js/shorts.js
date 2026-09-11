/* ============================================================
   YouTube Shorts strip — click-to-load facades.

   Tiles are built from the list in config/site.php (set via
   SITE_SHORTS in .env), handed to the page as a JSON script tag.
   Thumbnails come straight from i.ytimg.com, and nothing loads
   from youtube.com until a visitor actually clicks: a cold
   YouTube iframe costs ~700KB and would wreck the LCP on a page
   you are paying for clicks on.
   ============================================================ */
(function () {
  'use strict';

  var wrap = document.getElementById('shorts');
  var data = document.getElementById('shorts-data');
  if (!wrap || !data) return;

  var entries = [];
  try { entries = JSON.parse(data.textContent) || []; } catch (e) { return; }

  entries.forEach(function (entry) {
    var parts   = String(entry || '').split('|');
    var raw     = parts[0].trim();
    var caption = (parts[1] || '').trim();
    if (!raw) return;

    // Accept a full watch/shorts/youtu.be URL or a bare 11-character id.
    var match = raw.match(/(?:shorts\/|watch\?v=|youtu\.be\/|embed\/)?([\w-]{11})(?:[?&#].*)?$/);
    if (!match) return;
    var id = match[1];

    var figure = document.createElement('figure');
    figure.className = 'vid vid--tall';

    var button = document.createElement('button');
    button.className = 'vfacade';
    button.type = 'button';
    button.setAttribute('data-track', 'video-play');
    button.setAttribute('aria-label', 'Play video' + (caption ? ': ' + caption : ''));

    var img = document.createElement('img');
    // Shorts publish a vertical maxresdefault; fall back for the odd upload
    // that does not.
    img.src = 'https://i.ytimg.com/vi/' + id + '/maxresdefault.jpg';
    img.alt = '';
    img.width = 405;
    img.height = 720;
    img.loading = 'lazy';
    img.decoding = 'async';
    img.addEventListener('error', function onError() {
      img.removeEventListener('error', onError);
      img.src = 'https://i.ytimg.com/vi/' + id + '/hqdefault.jpg';
    });

    var play = document.createElement('span');
    play.className = 'vfacade__play';
    play.setAttribute('aria-hidden', 'true');
    play.innerHTML = '<svg viewBox="0 0 24 24"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg>';

    button.append(img, play);
    button.addEventListener('click', function () {
      var frame = document.createElement('iframe');
      frame.src = 'https://www.youtube-nocookie.com/embed/' + id +
                  '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
      frame.title = button.getAttribute('aria-label') || 'Rigid box video';
      frame.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
      frame.allowFullscreen = true;
      button.replaceWith(frame);
      if (typeof gtag === 'function') gtag('event', 'video_play', { video_id: id });
    });

    figure.appendChild(button);

    if (caption) {
      var figcaption = document.createElement('figcaption');
      figcaption.textContent = caption;
      figure.appendChild(figcaption);
    }

    wrap.appendChild(figure);
  });

  // Nothing resolved to a playable id? Leave the section hidden rather than
  // showing an empty row of boxes.
  var section = document.getElementById('video');
  if (section) section.hidden = !wrap.children.length;
})();
