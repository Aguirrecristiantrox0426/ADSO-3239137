FROM node:22-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
# API is exposed through the same-origin Nginx reverse proxy.
ENV VITE_API_URL=/api/v1
RUN npm run build

FROM nginx:1.27-alpine
COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx-app.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
