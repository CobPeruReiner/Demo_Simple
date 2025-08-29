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

// Sirve estáticos de tu web (opcional)
app.use(express.static(path.join(__dirname, "public")));

// 🔐 Webroot para ACME HTTP-01: Certbot escribirá aquí.
// Montaremos esta ruta en el contenedor: /var/www/certbot
const ACME_WEBROOT = "/var/www/certbot";
app.use(
  "/.well-known/acme-challenge",
  express.static(path.join(ACME_WEBROOT, ".well-known", "acme-challenge"))
);

// Ruta simple
// app.get("/", (_req, res) => {
//   res.send("Express listo. HTTP:80 y (si hay cert) HTTPS:443 ✅");
// });

// HTTP siempre (requiere puerto 80 para validación ACME)
http.createServer(app).listen(80, "0.0.0.0", () => {
  console.log("HTTP listo en :80");
});

// HTTPS si existen certificados de Let's Encrypt
const DOMAIN = process.env.DOMAIN || "geocampo.online";
const liveDir = `/etc/letsencrypt/live/${DOMAIN}`;

const keyFile = path.join(liveDir, "privkey.pem");
const certFile = path.join(liveDir, "fullchain.pem");

if (fs.existsSync(keyFile) && fs.existsSync(certFile)) {
  const options = {
    key: fs.readFileSync(keyFile),
    cert: fs.readFileSync(certFile),
  };
  https.createServer(options, app).listen(443, "0.0.0.0", () => {
    console.log(`HTTPS listo en :443 con Let's Encrypt para ${DOMAIN}`);
  });
} else {
  console.warn(
    "⚠️ No hay certificados en /etc/letsencrypt/live/<dominio>. Se sirve solo HTTP hasta emitir/renovar."
  );
}
