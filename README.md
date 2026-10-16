# Eventzpro Consult Ltd. — Corporate Website

Corporate website for **Eventzpro Consult Ltd.**, built from the official *Eventzpro Group Profile*.

- **Stack:** PHP 8.1+ (no framework, no Composer), Bootstrap 5.3.3 (self-hosted), vanilla JavaScript
- **Brand:** charcoal `#282929` · amber `#FCBD11` · Montserrat (headings) + Inter (body)
- **Review preview:** published automatically to GitHub Pages on every push to `main`

## Run locally

```bash
npm install                     # installs Bootstrap and copies it into public/assets/vendor
php -S 127.0.0.1:8099 -t public public/index.php
```

Open <http://127.0.0.1:8099>. On XAMPP/Apache you can also drop the folder into `htdocs`; the root `.htaccess` routes everything into `public/`.

## Project structure

```
app/                     ← application code, never web-accessible
  bootstrap.php          ← boot: env, error handling, security headers
  config/app.php         ← settings (override per environment in app/.env)
  data/                  ← ALL site content (company, services, projects, clients, ventures, team)
  src/                   ← helpers, router, security (CSP, CSRF, rate limit), contact handler
  views/layouts|partials|pages
  storage/               ← enquiries (JSONL), rate-limit state, logs (git-ignored)
public/                  ← the only web root
  index.php              ← front controller (the only executable PHP file)
  assets/css/main.css    ← design system + Bootstrap brand integration
  assets/js/main.js      ← interactions (menu, reveal, counters, filters, ScrollSpy)
  assets/vendor/bootstrap← Bootstrap 5.3.3 CSS + JS bundle (no CDN)
  assets/img, assets/docs
scripts/
  copy-vendor.js         ← copies Bootstrap from node_modules into public/assets/vendor
  export-static.php      ← renders every page to static HTML (GitHub Pages preview)
.github/workflows/pages.yml ← builds & deploys the preview
```

## Editing content

Text, figures, clients, projects and leadership bios live in `app/data/*.php` — templates never need to change for content edits. Leader photos go in `public/assets/img/team/` and are referenced by the `photo` key in `app/data/team.php` (set `null` to show initials). Social links in `app/data/company.php` appear in the footer once filled in.

## Bootstrap

Bootstrap loads before `main.css`. The **Bootstrap 5 integration** block near the top of `main.css` maps Bootstrap's CSS variables (`--bs-primary`, `--bs-btn-*`, `--bs-nav-link-*`, …) to the brand tokens, so Bootstrap utilities and components can be used freely and stay on-brand. CSS classes use a `block-element` / `block--modifier` naming convention (e.g. `hero-title`, `btn--primary`).

To upgrade Bootstrap: change the version in `package.json`, run `npm install`, commit `public/assets/vendor`.

## Security

- Strict Content-Security-Policy (no inline scripts), HSTS on HTTPS, `X-Frame-Options: DENY`, `nosniff`, Referrer/Permissions policies
- Only `public/` is served; `app/`, storage, dotfiles and backup files are blocked by `.htaccess`
- Contact form: CSRF token, honeypot, minimum fill time, per-IP rate limit (hashed), server-side validation, header-injection-safe mail
- Session cookie only on the contact page (`HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS)

## Deployment

**Live site (Hostinger, Apache/LiteSpeed + PHP):** upload the *contents* of `public/` to `public_html/`, and upload `app/` next to `public_html` (one level up). Copy `app/.env.example` to `app/.env` and set `APP_URL` and `MAIL_TO`. Make `app/storage/*` writable.

**Developer preview (Render, full PHP):** `render.yaml` + `Dockerfile` run the real app (PHP 8.4 + Apache, only `public/` served) on Render's free plan, so the contact form works. In Render: *New → Blueprint → select this repo → Apply*. Every push to `main` redeploys. The preview sends `X-Robots-Tag: noindex`; mail is off (Render has no mail server), and enquiries are kept in `app/storage/messages` until the next deploy. The free plan sleeps after 15 minutes idle, so the first visit afterwards takes ~30–60 s.

**Static review copy (GitHub Pages):** pushes to `main` run `.github/workflows/pages.yml`, which lints the PHP, exports static HTML and deploys it. One-time setup: *Settings → Pages → Source: GitHub Actions*. The contact form is disabled in the preview because Pages cannot run PHP.

To build the preview locally:

```bash
APP_BASE_PATH=/eventzpro-website php scripts/export-static.php dist
# Git Bash on Windows: prefix with MSYS_NO_PATHCONV=1
```
