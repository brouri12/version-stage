// product-detail.js
document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('zoom-container');
  const image = document.getElementById('zoom-image');
  const closeBtn = document.getElementById('close-zoom-btn');
  const thumbnailsEl = document.getElementById('thumbnails');
  const root = document.getElementById('product-detail');
  const initialColor = root ? String(root.getAttribute('data-initial') || 'default') : 'default';

  // Panzoom init
  if (window.Panzoom && !window.matchMedia('(pointer: coarse)').matches && container && image) {
    const panzoom = Panzoom(container, { maxScale: 3, minScale: 1, contain: 'outside', step: 0.2, animate: true, cursor: '' });
    let zoomed = false;
    container.addEventListener('click', function (e) {
      if (e.target === closeBtn) return;
      const rect = container.getBoundingClientRect();
      const x = e.clientX - rect.left; const y = e.clientY - rect.top;
      if (!zoomed) { panzoom.zoomTo(x, y, 2); zoomed = true; image.style.cursor = 'move'; closeBtn.style.display = 'block'; }
      else { panzoom.reset(); zoomed = false; image.style.cursor = 'zoom-in'; closeBtn.style.display = 'none'; }
    });
    container.addEventListener('wheel', function (e) {
      e.preventDefault();
      panzoom.zoomWithWheel(e);
      const z = panzoom.getScale() > 1.01;
      image.style.cursor = z ? 'move' : 'zoom-in';
      closeBtn.style.display = z ? 'block' : 'none';
    }, { passive: false });
    container.addEventListener('mouseenter', function () { image.style.cursor = zoomed ? 'move' : 'zoom-in'; });
    container.addEventListener('mouseleave', function () { image.style.cursor = 'zoom-in'; });
    if (closeBtn) closeBtn.addEventListener('click', function (e) { e.stopPropagation(); panzoom.reset(); zoomed = false; image.style.cursor = 'zoom-in'; closeBtn.style.display = 'none'; });
  }

  // Build productData from JSON script tag
  const dataEl = document.getElementById('product-data');
  let productData = {};
  try { productData = JSON.parse(dataEl ? dataEl.textContent : '{}'); } catch (_) { productData = {}; }
  // Show all thumbnails initially
  let currentColor = 'all';

  const setMainImage = (url) => { if (!image) return; image.style.opacity = '0'; setTimeout(() => { image.src = url; image.style.opacity = '1'; }, 100); };

  const renderThumbnails = () => {
    if (!thumbnailsEl) return;
    thumbnailsEl.innerHTML = '';
    // Show all images across all colors; when filtered, show only selected color
    const imgsRaw = currentColor === 'all'
      ? Object.values(productData).flat()
      : (productData[currentColor] || []);
    // Deduplicate by thumb URL
    const seen = new Set();
    const imgs = [];
    imgsRaw.forEach(im => { if (im && im.thumb && !seen.has(im.thumb)) { seen.add(im.thumb); imgs.push(im); } });
    imgs.forEach((im, idx) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'p-1 w-20 h-20 md:w-24 md:h-24 bg-white border rounded-md overflow-hidden';
      const imgEl = document.createElement('img');
      imgEl.src = im.thumb; imgEl.className = 'w-full h-full object-cover';
      btn.appendChild(imgEl);
      btn.addEventListener('click', () => {
        document.querySelectorAll('#thumbnails button').forEach(b => b.classList.remove('ring-2', 'ring-blue-500'));
        btn.classList.add('ring-2', 'ring-blue-500');
        setMainImage(im.large);
      });
      thumbnailsEl.appendChild(btn);
      if (idx === 0) btn.classList.add('ring-2', 'ring-blue-500');
    });
    if (imgs[0] && image && !image.dataset.locked) setMainImage(imgs[0].large);
  };

  renderThumbnails();

  // Remove color click behavior: always show all images
  document.querySelectorAll('[data-color-key]').forEach(el => {
    el.style.cursor = 'default';
  });

  // Highlight initial color
  const initialColorEl = document.querySelector('[data-color-key="' + currentColor + '"]');
  if (initialColorEl) { initialColorEl.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2'); }
});


