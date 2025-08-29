FROM node:20-alpine

WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev || npm i --omit=dev
COPY . .

EXPOSE 80 443
ENV NODE_ENV=production
CMD ["node", "index.js"]
