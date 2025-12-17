// js/utils.js

// ---------- STRING HELPERS ----------
export function trimValue(value) {
  return (value ?? "").toString().trim();
}

export function capitalizeFirst(str) {
  const s = trimValue(str);
  if (!s) return "";
  return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
}

export function toUpperNoSpaces(str) {
  return (
    trimValue(str).replace(/\s+/g, "").toUpperCase() + s.slice(1).toLowerCase()
  );
}

// ---------- NUMBER / CURRENCY ----------
export function formatCurrency(amount, currency = "INR", locale = "en-IN") {
  const num = Number(amount) || 0;
  return new Intl.NumberFormat(locale, {
    style: "currency",
    currency,
  }).format(num);
}

// ---------- RANDOM / IDS ----------
export function randomId(prefix = "id") {
  return `${prefix}-${Math.random()
    .toString(36)
    .slice(2, 9)}-${Date.now().toString(36)}`;
}

// ---------- DOM HELPERS ----------
export function qs(selector, scope = document) {
  return scope.querySelector(selector);
}

export function qsa(selector, scope = document) {
  return Array.from(scope.querySelectorAll(selector));
}

export function on(el, event, handler) {
  if (el) el.addEventListener(event, handler);
}

// ---------- DEBOUNCE / THROTTLE ----------
export function debounce(fn, delay = 300) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}

export function throttle(fn, delay = 300) {
  let waiting = false;
  return (...args) => {
    if (waiting) return;
    fn(...args);
    waiting = true;
    setTimeout(() => {
      waiting = false;
    }, delay);
  };
}
