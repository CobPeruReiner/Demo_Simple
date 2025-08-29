// const express = require("express");
// const http = require("http");
// const https = require("https");
// const fs = require("fs");
// const path = require("path");

// const app = express();

// // Endpoint simple
// app.get("/", (_req, res) => {
//   res.send("¡Express funcionando en Docker! HTTP:80 / HTTPS:443 🚀");
// });

// // Arranca HTTP en 0.0.0.0 (dentro del contenedor)
// http.createServer(app).listen(80, "0.0.0.0", () => {
//   console.log("HTTP listo en :80");
// });

// // Intenta levantar HTTPS si existen los certs montados en /certs
// const certDir = "/certs";
// const keyFile = path.join(certDir, "key.pem");
// const certFile = path.join(certDir, "cert.pem");

// if (fs.existsSync(keyFile) && fs.existsSync(certFile)) {
//   const options = {
//     key: fs.readFileSync(keyFile),
//     cert: fs.readFileSync(certFile),
//   };
//   https.createServer(options, app).listen(443, "0.0.0.0", () => {
//     console.log("HTTPS listo en :443");
//   });
// } else {
//   console.warn(
//     "⚠️ No se encontraron /certs/key.pem y /certs/cert.pem. Solo se expondrá HTTP."
//   );
// }

const express = require("express");
const http = require("http");
const https = require("https");
const fs = require("fs");
const path = require("path");

const app = express();

// (Opcional) Forzar HTTPS para tu dominio, dejando pasar el challenge ACME
app.use((req, res, next) => {
  if (req.path.startsWith("/.well-known/acme-challenge")) return next();
  // Si ya viene por HTTPS, sigue
  if (req.secure || req.headers["x-forwarded-proto"] === "https") return next();
  // Solo redirige si es tu dominio (las IPs no tienen cert)
  const host = req.headers.host || "";
  if (host.includes("geocampo.online")) {
    return res.redirect("https://" + host + req.url);
  }
  return next();
});

// Sirve tu sitio estático (si tienes carpeta public/)
app.use(express.static(path.join(__dirname, "public")));

// Webroot ACME (Certbot escribirá aquí los retos HTTP-01)
const ACME_ROOT = "/var/www/certbot";
app.use("/.well-known", express.static(path.join(ACME_ROOT, ".well-known")));

// Prueba
app.get("/health", (_req, res) => res.json({ ok: true }));

// HTTP siempre (requerido por ACME)
http.createServer(app).listen(80, "0.0.0.0", () => {
  console.log("HTTP listo en :80");
});

// HTTPS si hay certificados de Let's Encrypt
const DOMAIN = process.env.DOMAIN || "geocampo.online";
const LIVE_DIR = `/etc/letsencrypt/live/${DOMAIN}`;
const KEY = path.join(LIVE_DIR, "privkey.pem");
const CERT = path.join(LIVE_DIR, "fullchain.pem");

if (fs.existsSync(KEY) && fs.existsSync(CERT)) {
  const options = { key: fs.readFileSync(KEY), cert: fs.readFileSync(CERT) };
  https.createServer(options, app).listen(443, "0.0.0.0", () => {
    console.log(`HTTPS listo en :443 con Let's Encrypt para ${DOMAIN}`);
  });
} else {
  console.warn(
    "⚠️ No hay certificados en /etc/letsencrypt/live/<dominio>. Se sirve solo HTTP hasta emitir/renovar."
  );
}
