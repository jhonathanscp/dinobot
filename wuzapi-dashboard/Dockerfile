# Build stage
FROM node:20-alpine as build
WORKDIR /app

# Recebe variáveis de ambiente do docker-compose
ARG VITE_WUZAPI_TOKEN
ARG VITE_DASHBOARD_PASSWORD

# Define as variáveis para o Vite injetar no build
ENV VITE_WUZAPI_TOKEN=$VITE_WUZAPI_TOKEN
ENV VITE_DASHBOARD_PASSWORD=$VITE_DASHBOARD_PASSWORD
ENV VITE_API_URL=/api/wuzapi

COPY package*.json ./
RUN npm install && npm install @tailwindcss/postcss

COPY . .
RUN npm run build

# Production stage
FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
