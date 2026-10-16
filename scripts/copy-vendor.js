// Copies the compiled Bootstrap files from node_modules into public/assets/vendor
// so the site is fully self-hosted (no CDN). Run automatically after `npm install`.
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const files = [
  ['node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/assets/vendor/bootstrap/css/bootstrap.min.css'],
  ['node_modules/bootstrap/dist/css/bootstrap.min.css.map', 'public/assets/vendor/bootstrap/css/bootstrap.min.css.map'],
  ['node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js'],
  ['node_modules/bootstrap/dist/js/bootstrap.bundle.min.js.map', 'public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js.map'],
  ['node_modules/bootstrap/LICENSE', 'public/assets/vendor/bootstrap/LICENSE'],
];

for (const [from, to] of files) {
  const dest = path.join(root, to);
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.copyFileSync(path.join(root, from), dest);
  console.log('copied', to);
}
