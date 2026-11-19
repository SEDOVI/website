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

  // project rail: arrows appear only when the projects overflow
  const rail = document.querySelector('.projects');
  const nav = document.querySelector('.rail-nav');
  if (rail && nav) {
    const prev = nav.querySelector('.prev'), next = nav.querySelector('.next');
    const step = () => (rail.querySelector('.project')?.offsetWidth || rail.clientWidth) + 24;
    const sync = () => {
      const max = rail.scrollWidth - rail.clientWidth;
      nav.hidden = max <= 2;
      prev.disabled = rail.scrollLeft <= 2;
      next.disabled = rail.scrollLeft >= max - 2;
    };
    prev.addEventListener('click', () => rail.scrollBy({ left: -step(), behavior: 'smooth' }));
    next.addEventListener('click', () => rail.scrollBy({ left: step(), behavior: 'smooth' }));
    rail.addEventListener('scroll', sync, { passive: true });
    new ResizeObserver(sync).observe(rail);
    addEventListener('load', sync);
  }

  // contact form in a dialog; without JS the header link stays a mailto
  const sheet = document.getElementById('contact-form');
  const opener = document.querySelector('.nav .cta');
  if (sheet && opener && sheet.showModal) {
    const form = sheet.querySelector('form'), done = sheet.querySelector('.cf-done');
    const status = form.querySelector('.cf-status'), error = form.querySelector('.cf-error');
    const submit = form.querySelector('[type=submit]');
    opener.addEventListener('click', e => {
      e.preventDefault();
      form.hidden = false; done.hidden = true; error.hidden = true; status.textContent = '';
      sheet.showModal();
      form.querySelector('[name=name]').focus();
    });
    sheet.addEventListener('click', e => {
      if (e.target === sheet || e.target.closest('[data-close]')) sheet.close();
    });
    form.addEventListener('submit', async e => {
      e.preventDefault();
      submit.disabled = true; error.hidden = true; status.textContent = form.dataset.sending;
      try {
        const res = await fetch(form.action, { method: 'POST', headers: { Accept: 'application/json' }, body: new FormData(form) });
        const json = await res.json().catch(() => ({}));
        if (!res.ok || !json.success) throw new Error(json.message || res.status);
        form.reset(); form.hidden = true; done.hidden = false;
        done.querySelector('h2').focus();
      } catch (err) {
        error.hidden = false;
        error.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      } finally {
        submit.disabled = false; status.textContent = '';
      }
    });
  }

  // header border once the page scrolls
  const top = document.querySelector('.top');
  const onScroll = () => top.classList.toggle('scrolled', scrollY > 8);
  addEventListener('scroll', onScroll, { passive: true }); onScroll();
})();
