FROM node:20-slim

WORKDIR /app

# Skopiuj wszystko
COPY . .

# Zainstaluj zależności
RUN npm ci

EXPOSE 5174

CMD ["npx", "vite", "--host", "0.0.0.0", "--port", "5174"]