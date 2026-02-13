# Stage 1: Development
FROM node:22-alpine AS development

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

EXPOSE 3000 24678

CMD ["npm", "run", "dev"]

# Stage 2: Builder
FROM node:22-alpine AS builder

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

RUN npm run build

# Stage 3: Production
FROM node:22-alpine AS production

WORKDIR /app

COPY --from=builder /app/.output /app/.output
COPY --from=builder /app/package*.json ./

ENV NODE_ENV=production
ENV HOST=0.0.0.0
ENV PORT=3000

EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]
