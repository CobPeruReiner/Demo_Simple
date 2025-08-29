const express = require("express");
const http = require("http");
const https = require("https");
const fs = require("fs");
const path = require("path");

const app = express();

// Endpoint simple
app.get("/", (_req, res) => {
  res.send("¡Express funcionando en Docker! HTTP:80 / HTTPS:443 🚀");
});

// Arranca HTTP en 0.0.0.0 (dentro del contenedor)
http.createServer(app).listen(80, "0.0.0.0", () => {
  console.log("HTTP listo en :80");
});

// Intenta levantar HTTPS si existen los certs montados en /certs
const certDir = "/certs";
const keyFile = path.join(certDir, "key.pem");
const certFile = path.join(certDir, "cert.pem");

if (fs.existsSync(keyFile) && fs.existsSync(certFile)) {
  const options = {
    key: fs.readFileSync(keyFile),
    cert: fs.readFileSync(certFile),
  };
  https.createServer(options, app).listen(443, "0.0.0.0", () => {
    console.log("HTTPS listo en :443");
  });
} else {
  console.warn(
    "⚠️ No se encontraron /certs/key.pem y /certs/cert.pem. Solo se expondrá HTTP."
  );
}
