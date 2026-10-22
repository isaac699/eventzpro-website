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
  src/                   ← helpers, router, security (CSP, CSRF, rate limit), contact handler,
                           db (PDO: SQLite/MySQL), mailer (mail()/SMTP), admin panel
  views/layouts|partials|pages|admin|emails
  storage/               ← database (SQLite), fallback enquiries (JSONL), rate-limit state, logs (git-ignored)
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

## Admin panel & database

The contact form saves every enquiry to a database and emails a notification to `MAIL_TO`. The team handles enquiries at **`/admin`**.

- **Dashboard:** new and monthly counts, enquiries awaiting a reply, the latest messages, a 14-day chart and the most-asked topics
- **Enquiries:** filter by status (new, read, replied, archived), search, reply by email, internal notes, CSV export
- **Users:** administrators add, deactivate, promote and reset staff accounts. Staff can handle enquiries but cannot manage users or delete enquiries.
- **Security:** CSRF on every form, sign-in rate limit, hashed passwords, sign-out after one hour of inactivity, and changing a password signs out other devices. Pages are `noindex` and never cached.

**Database.** `DB_CONNECTION=sqlite` (the default) needs no setup: the tables are created in `app/storage/database/eventzpro.sqlite` on first use. For MySQL on Hostinger, create a database and user in hPanel and set `DB_CONNECTION=mysql` plus `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`. The tables are still created automatically. If the database is ever unreachable, enquiries are written to `app/storage/messages/*.jsonl`, so none are lost.

**First administrator.** Set `ADMIN_SETUP_TOKEN` in `app/.env` to a long random value, open `/admin/setup`, and enter it with your details. The page only works while no users exist. Remove the token afterwards. With terminal access you can run `php scripts/create-admin.php` instead.

**Email.** `MAIL_TRANSPORT=mail` uses PHP `mail()`. For reliable delivery (fewer messages in spam), create a mailbox such as `no-reply@eventzproconsult.com` in hPanel and use SMTP:

```
MAIL_TRANSPORT=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=no-reply@eventzproconsult.com
MAIL_PASSWORD=the-mailbox-password
MAIL_FROM=no-reply@eventzproconsult.com
```

Replying to a notification email answers the visitor directly (Reply-To is their address).

## Bootstrap

Bootstrap loads before `main.css`. The **Bootstrap 5 integration** block near the top of `main.css` maps Bootstrap's CSS variables (`--bs-primary`, `--bs-btn-*`, `--bs-nav-link-*`, …) to the brand tokens, so Bootstrap utilities and components can be used freely and stay on-brand. CSS classes use a `block-element` / `block--modifier` naming convention (e.g. `hero-title`, `btn--primary`).

To upgrade Bootstrap: change the version in `package.json`, run `npm install`, commit `public/assets/vendor`.

## Security

- Strict Content-Security-Policy (no inline scripts), HSTS on HTTPS, `X-Frame-Options: DENY`, `nosniff`, Referrer/Permissions policies
- Only `public/` is served; `app/`, storage, dotfiles and backup files are blocked by `.htaccess`
- Contact form: CSRF token, honeypot, minimum fill time, per-IP rate limit (hashed), server-side validation, header-injection-safe mail
- Session cookie only on the contact page (`HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS)

## Deployment

**Live site (Hostinger, Apache/LiteSpeed + PHP):** upload the *contents* of `public/` to `public_html/`, and upload `app/` next to `public_html` (one level up). Copy `app/.env.example` to `app/.env` and set `APP_URL`, `MAIL_TO`, the database and SMTP settings, and `ADMIN_SETUP_TOKEN` (see *Admin panel & database*). Make `app/storage/*` writable, including `app/storage/database` for SQLite.

**Developer preview (Render, full PHP):** `render.yaml` + `Dockerfile` run the real app (PHP 8.4 + Apache, only `public/` served) on Render's free plan, so the contact form works. In Render: *New → Blueprint → select this repo → Apply*. Every push to `main` redeploys. The preview sends `X-Robots-Tag: noindex`; mail is off (Render has no mail server), and enquiries are kept in `app/storage/messages` until the next deploy. The free plan sleeps after 15 minutes idle, so the first visit afterwards takes ~30–60 s.

**Static review copy (GitHub Pages):** pushes to `main` run `.github/workflows/pages.yml`, which lints the PHP, exports static HTML and deploys it. One-time setup: *Settings → Pages → Source: GitHub Actions*. The contact form is disabled in the preview because Pages cannot run PHP.

To build the preview locally:

```bash
APP_BASE_PATH=/eventzpro-website php scripts/export-static.php dist
# Git Bash on Windows: prefix with MSYS_NO_PATHCONV=1
```
