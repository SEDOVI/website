(() => {
  // headline word reveal
  let i = 0;
  document.querySelectorAll('[data-split]').forEach(el => {
    el.innerHTML = el.textContent.split(/(\s+)/).map(tok => {
      if (!tok || /^\s+$/.test(tok)) return tok;
      return '<span class="word-wrap"><span class="word" style="--i:' + (i++) + '">' + tok + '</span></span>';
    }).join('');
  });
  const ready = () => requestAnimationFrame(() => document.body.classList.add('ready'));
  (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(ready);
  setTimeout(ready, 1500); // font fallback

  // copy email
  document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const label = btn.textContent;
      try { await navigator.clipboard.writeText(btn.dataset.copy); btn.textContent = btn.dataset.done; }
      catch (e) { btn.textContent = btn.dataset.fail; }
      setTimeout(() => { btn.textContent = label; }, 1600);
    });
  });

  // header border once the page scrolls
  const top = document.querySelector('.top');
  const onScroll = () => top.classList.toggle('scrolled', scrollY > 8);
  addEventListener('scroll', onScroll, { passive: true }); onScroll();
})();
