# ANTIGRAVITY MASTER PROMPT
## True Zoom-Out Scaling — Laravel + AdminLTE 3.x
### Claude Opus 4.6 | Antigravity Composer

---

## OBJECTIVE

This Laravel + Blade + AdminLTE 3.x + Bootstrap 4 application looks perfect on **1920×1080**.

The problem: on **1366×768** and **1024×768** screens, pages are getting cut off, sidebar overflows, and content is hidden. User has to scroll horizontally.

**THE SOLUTION:** Apply CSS `zoom` scaling on the root element so the entire page shrinks proportionally — exactly like pressing `Ctrl + -` in the browser. Nothing moves, nothing reorders, nothing changes layout. The whole page just becomes a smaller version of itself and fits inside the screen.

**DO NOT use media queries to change individual element sizes.**
**DO NOT change layout, theme, colors, sidebar structure, or any design.**
**DO NOT touch PHP, controllers, models, routes.**

---

## STEP 1 — DISCOVERY

1. List all files in `resources/views/` recursively.
2. Find the main layout file (e.g. `layouts/app.blade.php` or `master.blade.php`).
3. Find any existing custom CSS files linked in the layout `<head>`.
4. Check `public/css/` for any existing custom CSS files.

Report this. Do not write code yet.

---

## STEP 2 — THE ZOOM FIX

### 2a — Create file: `public/css/zoom-scale.css`

```css
/* =====================================================
   ZOOM SCALE — Full page zoom-out for small screens
   Works like Ctrl+minus in browser
   Base design: 1920×1080
   ===================================================== */

/* ── 1366×768 laptops ── */
@media screen and (max-width: 1400px) {
  body {
    zoom: 0.85;
    -moz-transform: scale(0.85);
    -moz-transform-origin: 0 0;
  }
}

/* ── 1280×1024 / 1024×768 square/small monitors ── */
@media screen and (max-width: 1280px) {
  body {
    zoom: 0.75;
    -moz-transform: scale(0.75);
    -moz-transform-origin: 0 0;
  }
}

/* ── Very small screens under 1100px ── */
@media screen and (max-width: 1100px) {
  body {
    zoom: 0.70;
    -moz-transform: scale(0.70);
    -moz-transform-origin: 0 0;
  }
}

/* ── Fix: after zoom, body width must compensate ── */
/* So page still fills full width and no white gap appears on right */
@media screen and (max-width: 1400px) {
  body {
    width: calc(100% / 0.85);
    min-height: calc(100vh / 0.85);
  }
}

@media screen and (max-width: 1280px) {
  body {
    width: calc(100% / 0.75);
    min-height: calc(100vh / 0.75);
  }
}

@media screen and (max-width: 1100px) {
  body {
    width: calc(100% / 0.70);
    min-height: calc(100vh / 0.70);
  }
}

/* ── Prevent outer horizontal scroll ── */
html {
  overflow-x: hidden;
}
```

### 2b — Link in main layout

In the main layout file (e.g. `resources/views/layouts/app.blade.php`), add this line **last** inside `<head>`, after all other CSS:

```html
<link rel="stylesheet" href="{{ asset('css/zoom-scale.css') }}">
```

---

## STEP 3 — FIREFOX FALLBACK (Optional but recommended)

CSS `zoom` is not supported in Firefox. The `-moz-transform: scale()` approach above handles it, but it behaves slightly differently. Add this JS snippet inside the main layout just before `</body>` to detect Firefox and apply a viewport meta adjustment:

```html
<script>
  // Firefox zoom fallback
  (function() {
    var isFirefox = typeof InstallTrigger !== 'undefined';
    if (!isFirefox) return;
    var w = window.innerWidth;
    var scale = 1;
    if (w <= 1100) scale = 0.70;
    else if (w <= 1280) scale = 0.75;
    else if (w <= 1400) scale = 0.85;
    if (scale < 1) {
      document.body.style.transform = 'scale(' + scale + ')';
      document.body.style.transformOrigin = '0 0';
      document.body.style.width = (100 / scale) + '%';
      document.body.style.minHeight = (100 / scale) + 'vh';
    }
  })();
</script>
```

---

## STEP 4 — VERIFY ACROSS VIEWS

After applying, open each route/view and verify:

| Check | Expected |
|-------|----------|
| 1920×1080 | Page looks exactly as before — no zoom applied |
| 1366×768  | Whole page is 85% size, fits perfectly, no cut-off |
| 1280×1024 | Whole page is 75% size, fits perfectly, no cut-off |
| 1024×768  | Whole page is 70% size, fits perfectly, no cut-off |
| Sidebar   | Shrinks proportionally, all links visible |
| Tables    | Shrink proportionally, no horizontal scroll |
| Cards     | Shrink proportionally, nothing overflows |
| Modals    | Open centered and fit within zoomed viewport |
| Forms     | All inputs and labels visible and usable |

If any view still has horizontal scroll or cut-off content, check if that view has:
- A hardcoded `width` in px on any container
- A `min-width` larger than the zoomed viewport
- An inline style overriding overflow

Fix those by removing the hardcoded px widths or changing `min-width` to use `%` or `vw`.

---

## STEP 5 — NO TOUCH LIST

- ❌ No changes to PHP, controllers, models, routes
- ❌ No changes to existing CSS files
- ❌ No layout structure changes
- ❌ No color or theme changes
- ❌ No JavaScript changes (only addition of Firefox fallback)
- ❌ No Blade file rewrites

---

## STEP 6 — OUTPUT FORMAT

```
FILE CREATED: public/css/zoom-scale.css
LINKED IN: resources/views/layouts/[layout file name]
FIREFOX FALLBACK: added before </body> in [layout file name]
```

If any hardcoded px widths were found and fixed:
```
FILE: resources/views/[path]
ISSUE: hardcoded min-width / width in px causing overflow
FIX: changed to % / removed
```

---

## START

Begin with Step 1 — list all view files and the main layout file.
Then apply Steps 2 and 3 directly. No confirmation needed between steps.
