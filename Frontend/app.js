import { events, places } from './mock-data.js';

const app = document.getElementById('app');
const notice = document.getElementById('appNotice');
const viewCache = new Map();
const categories = ['Coffee Bars', 'Sweet Shops', 'Clubs', 'Bars'];

let activeEventTimer;
let activePlaceCategory = 'All Places';
let favorites = loadFavorites();

function loadFavorites() {
  try {
    const saved = JSON.parse(localStorage.getItem('sarajevo2b-demo-favorites') || '[]');
    return new Set(Array.isArray(saved) ? saved.filter((id) => places.some((place) => place.id === id)) : []);
  } catch (error) {
    console.error('Could not read Sarajevo2B demo favorites from browser storage.', error);
    showNotice('Your browser could not load saved demo favorites. You can still explore places, but favorites may not be available.', 'danger');
    return new Set();
  }
}

function saveFavorites() {
  let persisted = true;
  try {
    localStorage.setItem('sarajevo2b-demo-favorites', JSON.stringify([...favorites]));
  } catch (error) {
    console.error('Could not save Sarajevo2B demo favorites to browser storage.', error);
    showNotice('Could not save this favorite in browser storage. Check your browser storage settings and try again.', 'danger');
    persisted = false;
  }
  updateFavoriteCount();
  renderCurrentFavorites();
  return persisted;
}

function showNotice(message, type = 'warning') {
  notice.className = `alert alert-${type} app-notice`;
  notice.textContent = message;
  notice.hidden = false;
  window.clearTimeout(showNotice.timeout);
  showNotice.timeout = window.setTimeout(() => { notice.hidden = true; }, 6000);
}

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  })[character]);
}

function slugify(value) {
  return value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function stars(rating) {
  const rounded = Math.round(rating);
  return `<span class="stars" aria-label="${rating.toFixed(1)} out of 5 stars">${'★'.repeat(rounded)}${'☆'.repeat(5 - rounded)}</span>`;
}

function placeCard(place, { showNewBadge = false, reviewCard = false } = {}) {
  const isFavorite = favorites.has(place.id);
  return `<article class="col-sm-6 col-xl-4">
    <div class="place-card h-100">
      <div class="place-image-wrap">
        <img class="place-image" src="${escapeHtml(place.image)}" alt="${escapeHtml(`${place.name} in ${place.neighborhood}, Sarajevo`)}" loading="lazy" />
        ${showNewBadge && place.isNew ? '<span class="new-badge">NEW</span>' : ''}
        <button class="favorite-button ${isFavorite ? 'is-favorite' : ''}" type="button" data-action="favorite" data-place="${escapeHtml(place.id)}" aria-pressed="${isFavorite}" aria-label="${isFavorite ? 'Remove' : 'Add'} ${escapeHtml(place.name)} ${isFavorite ? 'from' : 'to'} favorites">${isFavorite ? '♥' : '♡'}</button>
      </div>
      <div class="place-card-body">
        <div class="d-flex justify-content-between align-items-center gap-2"><span class="category-label">${escapeHtml(place.category)}</span>${stars(place.rating)}</div>
        <h3>${escapeHtml(place.name)}</h3>
        <p class="place-description">${escapeHtml(place.description)}</p>
        <p class="place-address"><span aria-hidden="true">⌖</span> ${escapeHtml(place.address)}</p>
        ${reviewCard ? `<p class="review-total">${place.reviewCount} community reviews</p>` : ''}
        <div class="card-actions">
          <button class="btn btn-dark-brown" type="button" data-action="${reviewCard ? 'reviews' : 'details'}" data-place="${escapeHtml(place.id)}">${reviewCard ? 'View Reviews' : 'View Details'}</button>
          ${reviewCard ? '' : `<span class="rating-text">${place.rating.toFixed(1)} <span class="visually-hidden">out of 5</span></span>`}
        </div>
      </div>
    </div>
  </article>`;
}

function categoryCard(category, image, description) {
  const destination = category === 'Events' ? '#events' : `#category/${slugify(category)}`;
  return `<div class="col-6 col-lg">
    <a class="category-card" href="${destination}">
      <img src="${image}" alt="${escapeHtml(`${category} in Sarajevo`)}" loading="lazy" />
      <span class="category-overlay"></span>
      <span class="category-card-copy"><strong>${escapeHtml(category)}</strong><small>${escapeHtml(description)}</small></span>
      <span class="category-arrow" aria-hidden="true">↗</span>
    </a>
  </div>`;
}

function routeFromHash() {
  const parts = window.location.hash.replace(/^#\/?/, '').split('/');
  const route = parts[0] || 'home';
  return { route, category: parts[1] || '' };
}

async function navigate() {
  window.clearInterval(activeEventTimer);
  const modal = document.getElementById('detailsModal');
  if (modal.classList.contains('show')) bootstrap.Modal.getOrCreateInstance(modal).hide();
  const { route, category } = routeFromHash();
  const view = route === 'category' ? 'places' : route;
  const validViews = ['home', 'places', 'events', 'reviews', 'favorites'];
  const selectedView = validViews.includes(view) ? view : 'home';
  updateNavigation(selectedView);
  app.setAttribute('aria-busy', 'true');
  app.innerHTML = '<div class="container py-5 text-center" role="status">Finding your Sarajevo…</div>';

  try {
    let template = viewCache.get(selectedView);
    if (!template) {
      const response = await fetch(`views/${selectedView}.html`);
      if (!response.ok) throw new Error(`Could not load the ${selectedView} view (${response.status}).`);
      template = await response.text();
      viewCache.set(selectedView, template);
    }
    app.innerHTML = template;
    document.title = `${selectedView === 'home' ? 'Discover Sarajevo' : selectedView.charAt(0).toUpperCase() + selectedView.slice(1)} | Sarajevo2B`;
    if (selectedView === 'home') renderHome();
    if (selectedView === 'places') renderPlaces(category);
    if (selectedView === 'events') renderEvents();
    if (selectedView === 'reviews') renderReviews();
    if (selectedView === 'favorites') renderFavorites();
    app.setAttribute('aria-busy', 'false');
    closeMobileNavigation();
  } catch (error) {
    console.error('Sarajevo2B could not render this view.', error);
    app.innerHTML = `<section class="container py-5"><div class="alert alert-danger" role="alert"><h1 class="h4">This view could not be loaded</h1><p class="mb-3">${escapeHtml(error.message)}</p><button class="btn btn-dark-brown" type="button" data-action="retry">Try again</button></div></section>`;
    app.setAttribute('aria-busy', 'false');
  }
}

function updateNavigation(route) {
  document.querySelectorAll('.navbar-nav [data-route]').forEach((link) => {
    const active = link.dataset.route === route;
    link.classList.toggle('active', active);
    if (active) link.setAttribute('aria-current', 'page');
    else link.removeAttribute('aria-current');
  });
  updateFavoriteCount();
}

function updateFavoriteCount() {
  const count = document.getElementById('favoriteCount');
  if (count) count.textContent = String(favorites.size);
}

function closeMobileNavigation() {
  const menu = document.getElementById('mainNavigation');
  if (menu.classList.contains('show')) bootstrap.Collapse.getOrCreateInstance(menu).hide();
}

function renderHome() {
  document.getElementById('popularPlaces').innerHTML = places.slice(0, 6).map((place) => placeCard(place)).join('');
  document.getElementById('newPlaces').innerHTML = places.filter((place) => place.isNew).slice(0, 4).map((place) => placeCard(place, { showNewBadge: true })).join('');
  document.getElementById('categoryCards').innerHTML = [
    categoryCard('Coffee Bars', 'https://images.unsplash.com/photo-1445116572660-236099ec97a0?auto=format&fit=crop&w=700&q=80', 'Find your morning ritual'),
    categoryCard('Sweet Shops', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=700&q=80', 'Save room for something sweet'),
    categoryCard('Clubs', 'https://images.unsplash.com/photo-1566737236500-c8ac43014a8e?auto=format&fit=crop&w=700&q=80', 'Stay out a little longer'),
    categoryCard('Bars', 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?auto=format&fit=crop&w=700&q=80', 'Find your evening spot'),
    categoryCard('Events', 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&w=700&q=80', 'See what is on')
  ].join('');
}

function renderPlaces(categorySlug = '') {
  activePlaceCategory = categories.find((category) => slugify(category) === categorySlug) || 'All Places';
  document.getElementById('placeFilters').innerHTML = ['All Places', ...categories].map((category) =>
    `<button type="button" class="filter-button ${activePlaceCategory === category ? 'active' : ''}" data-filter="${escapeHtml(category)}" aria-pressed="${activePlaceCategory === category}">${escapeHtml(category)}</button>`
  ).join('');
  document.getElementById('placeSearch').addEventListener('input', filterPlaces);
  filterPlaces();
}

function filterPlaces() {
  const term = document.getElementById('placeSearch').value.trim().toLocaleLowerCase();
  const filtered = places.filter((place) => {
    const categoryMatches = activePlaceCategory === 'All Places' || place.category === activePlaceCategory;
    return categoryMatches && `${place.name} ${place.address} ${place.neighborhood}`.toLocaleLowerCase().includes(term);
  });
  document.getElementById('placesGrid').innerHTML = filtered.length
    ? filtered.map((place) => placeCard(place, { showNewBadge: true })).join('')
    : '<div class="col-12"><div class="empty-state"><span aria-hidden="true">⌕</span><h2>No places found</h2><p>Try a different name, address, or category.</p></div></div>';
}

function formatDate(date) {
  return new Intl.DateTimeFormat(undefined, { weekday: 'short', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit' }).format(date);
}

function renderEvents() {
  const types = [...new Set(events.map((event) => event.category))];
  document.getElementById('eventType').innerHTML += types.map((type) => `<option value="${escapeHtml(type)}">${escapeHtml(type)}</option>`).join('');
  document.getElementById('eventType').addEventListener('change', filterEvents);
  document.getElementById('eventDate').addEventListener('change', filterEvents);
  filterEvents();
  activeEventTimer = window.setInterval(updateCountdowns, 1000);
}

function filterEvents() {
  const type = document.getElementById('eventType').value;
  const date = document.getElementById('eventDate').value;
  const filtered = events.filter((event) =>
    (type === 'all' || event.category === type) &&
    (!date || event.startsAt >= new Date(`${date}T00:00:00`))
  );
  document.getElementById('eventsGrid').innerHTML = filtered.length
    ? filtered.map((event) => `<article class="col-md-6 col-xl-4">
      <div class="event-card h-100">
        <div class="event-image-wrap"><img src="${escapeHtml(event.image)}" alt="${escapeHtml(`${event.title} event in Sarajevo`)}" loading="lazy" /><span class="event-type">${escapeHtml(event.category)}</span></div>
        <div class="event-card-body"><p class="event-date">${escapeHtml(formatDate(event.startsAt))}</p><h2>${escapeHtml(event.title)}</h2><p class="event-description">${escapeHtml(event.description)}</p>
          <p class="event-location"><strong>${escapeHtml(event.location)}</strong><br />${escapeHtml(event.address)}</p>
          <div class="event-meta"><span>${escapeHtml(event.price)}</span><span class="countdown" data-event="${escapeHtml(event.id)}" aria-live="off"></span></div>
        </div>
      </div>
    </article>`).join('')
    : '<div class="col-12"><div class="empty-state"><span aria-hidden="true">◷</span><h2>No events match those filters</h2><p>Try another event type or date.</p></div></div>';
  updateCountdowns();
}

function updateCountdowns() {
  document.querySelectorAll('.countdown[data-event]').forEach((element) => {
    const event = events.find((item) => item.id === element.dataset.event);
    if (!event) return;
    const now = Date.now();
    if (now >= event.endsAt.getTime()) {
      element.textContent = 'Event Ended';
      return;
    }
    if (now >= event.startsAt.getTime()) {
      element.textContent = 'Event is Live';
      return;
    }
    const secondsRemaining = Math.floor((event.startsAt.getTime() - now) / 1000);
    const days = Math.floor(secondsRemaining / 86400);
    const hours = Math.floor((secondsRemaining % 86400) / 3600);
    const minutes = Math.floor((secondsRemaining % 3600) / 60);
    const seconds = secondsRemaining % 60;
    element.textContent = `${days}d ${String(hours).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}s`;
    element.setAttribute('aria-label', `${days} days, ${hours} hours, ${minutes} minutes, ${seconds} seconds until event starts`);
  });
}

function renderReviews() {
  document.getElementById('reviewSort').addEventListener('change', sortReviews);
  sortReviews();
}

function sortReviews() {
  const order = document.getElementById('reviewSort').value;
  const sorted = [...places].sort((first, second) => {
    if (order === 'lowest') return first.rating - second.rating;
    if (order === 'reviewed') return second.reviewCount - first.reviewCount;
    return second.rating - first.rating;
  });
  document.getElementById('reviewsGrid').innerHTML = sorted.map((place) => placeCard(place, { reviewCard: true })).join('');
}

function renderFavorites() {
  renderCurrentFavorites();
}

function renderCurrentFavorites() {
  const grid = document.getElementById('favoritesGrid');
  if (!grid) return;
  const savedPlaces = places.filter((place) => favorites.has(place.id));
  grid.innerHTML = savedPlaces.length
    ? savedPlaces.map((place) => placeCard(place)).join('')
    : '<div class="col-12"><div class="empty-state"><span aria-hidden="true">♡</span><h2>Your list is waiting for its first favorite</h2><p>Tap the heart on any place to keep it close. Your demo list is stored in this browser only.</p><a class="btn btn-dark-brown mt-2" href="#places">Explore places</a></div></div>';
}

function getPlace(placeId) {
  return places.find((place) => place.id === placeId);
}

function openDetails(placeId, showReviews = false) {
  const place = getPlace(placeId);
  if (!place) {
    showNotice('That place is not available in the demo data.', 'danger');
    return;
  }
  const title = document.getElementById('detailsModalTitle');
  const body = document.getElementById('detailsModalBody');
  title.textContent = showReviews ? `Reviews for ${place.name}` : place.name;
  const isFavorite = favorites.has(place.id);
  const reviews = place.reviews.map((review) => `<article class="review-item"><div class="d-flex justify-content-between align-items-start gap-3"><strong>${escapeHtml(review.name)}</strong>${stars(review.rating)}</div><p>${escapeHtml(review.comment)}</p><small>${escapeHtml(review.date)}</small></article>`).join('');
  body.innerHTML = showReviews
    ? `<p class="detail-rating">${stars(place.rating)} <strong>${place.rating.toFixed(1)}</strong> · ${place.reviewCount} reviews</p><div class="reviews-list">${reviews}</div><div class="login-required mt-4" id="authMessage"><strong>Want to leave a review?</strong><p class="mb-0">You need to log in to submit a review. Authentication is not connected in this frontend demo, so nothing will be submitted or saved.</p></div><form class="review-form mt-3" data-form="review"><fieldset disabled><legend class="fs-6 fw-bold">Write a review</legend><label class="form-label" for="reviewRating">Your rating</label><select class="form-select mb-3" id="reviewRating" aria-describedby="authMessage"><option value="">Choose a rating</option><option value="5">5 stars</option><option value="4">4 stars</option><option value="3">3 stars</option><option value="2">2 stars</option><option value="1">1 star</option></select><label class="form-label" for="reviewComment">Your comment</label><textarea class="form-control mb-3" id="reviewComment" rows="3" placeholder="Share your experience…" aria-describedby="authMessage"></textarea><button class="btn btn-dark-brown" type="submit">Log in to submit</button></fieldset></form>`
    : `<img class="detail-image" src="${escapeHtml(place.image)}" alt="${escapeHtml(`${place.name} in Sarajevo`)}" /><div class="detail-content"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2"><span class="category-label">${escapeHtml(place.category)} · ${escapeHtml(place.neighborhood)}</span><span class="detail-rating">${stars(place.rating)} <strong>${place.rating.toFixed(1)}</strong> (${place.reviewCount} reviews)</span></div><p class="mt-3">${escapeHtml(place.description)}</p><p><strong>Address:</strong> ${escapeHtml(place.address)}<br /><strong>Opening hours:</strong> ${escapeHtml(place.hours)}</p><div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-dark" href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(place.address)}" target="_blank" rel="noopener noreferrer">Get directions <span class="visually-hidden">(opens in a new tab)</span></a><button class="btn btn-dark-brown" type="button" data-action="favorite" data-place="${escapeHtml(place.id)}" aria-pressed="${isFavorite}">${isFavorite ? '♥ Saved to favorites' : '♡ Add to favorites'}</button><button class="btn btn-outline-dark" type="button" data-action="reviews" data-place="${escapeHtml(place.id)}">Read reviews</button></div></div>`;
  bootstrap.Modal.getOrCreateInstance(document.getElementById('detailsModal')).show();
}

document.addEventListener('click', (event) => {
  const button = event.target.closest('[data-action], [data-filter]');
  if (!button) return;
  if (button.hasAttribute('data-filter')) {
    activePlaceCategory = button.dataset.filter;
    document.querySelectorAll('[data-filter]').forEach((filter) => {
      const active = filter.dataset.filter === activePlaceCategory;
      filter.classList.toggle('active', active);
      filter.setAttribute('aria-pressed', String(active));
    });
    filterPlaces();
    return;
  }
  const placeId = button.dataset.place;
  if (button.dataset.action === 'favorite') {
    favorites.has(placeId) ? favorites.delete(placeId) : favorites.add(placeId);
    const persisted = saveFavorites();
    if (persisted) {
      showNotice(favorites.has(placeId)
        ? 'Added to browser-only demo favorites. This is not synced to an account or server.'
        : 'Removed from browser-only demo favorites.', 'info');
    }
    if (button.closest('#detailsModal')) openDetails(placeId);
    else if (button.isConnected) {
      const place = getPlace(placeId);
      const isFavorite = favorites.has(placeId);
      button.classList.toggle('is-favorite', isFavorite);
      button.setAttribute('aria-pressed', String(isFavorite));
      button.setAttribute('aria-label', `${isFavorite ? 'Remove' : 'Add'} ${place.name} ${isFavorite ? 'from' : 'to'} favorites`);
      button.textContent = isFavorite ? '♥' : '♡';
    }
    return;
  }
  if (button.dataset.action === 'details') openDetails(placeId);
  if (button.dataset.action === 'reviews') openDetails(placeId, true);
  if (button.dataset.action === 'retry') navigate();
});

document.addEventListener('submit', (event) => {
  if (event.target.matches('[data-form="review"]')) {
    event.preventDefault();
    showNotice('Please log in before submitting a review. Review submission is unavailable in this demo.', 'warning');
  }
});

app.addEventListener('error', (event) => {
  const image = event.target;
  if (!(image instanceof HTMLImageElement) || image.dataset.fallbackApplied) return;
  image.dataset.fallbackApplied = 'true';
  image.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 800 500%22%3E%3Crect width=%22800%22 height=%22500%22 fill=%22%23e9e2d8%22/%3E%3Cpath d=%22M0 390 220 190l130 120 110-90 340 270H0Z%22 fill=%22%23c6ad8c%22/%3E%3Ccircle cx=%22600%22 cy=%22120%22 r=%2248%22 fill=%22%23d9a441%22/%3E%3C/svg%3E';
}, true);

window.addEventListener('hashchange', navigate);
document.getElementById('currentYear').textContent = String(new Date().getFullYear());
if (!window.location.hash) window.location.hash = '#home';
else navigate();
