# FROM node:20-alpine

# # Crear usuario no root (opcional pero recomendado)
# RUN addgroup -S nodegrp && adduser -S nodeusr -G nodegrp

# WORKDIR /app

# # Instalar deps
# COPY package*.json ./
# RUN npm ci --omit=dev || npm i --omit=dev

# # Copiar app
# COPY index.js ./

# # Puerto documentado
# EXPOSE 80 443

# # Ejecutar como usuario no root
# USER nodeusr

# CMD ["node", "index.js"]

FROM node:20-alpine

WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev || npm i --omit=dev
COPY . .

EXPOSE 80 443
ENV NODE_ENV=production
CMD ["node", "index.js"]
