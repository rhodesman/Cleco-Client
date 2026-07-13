# syntax=docker/dockerfile:1

# ---- Stage 1: assemble the static site tree ----
FROM alpine:3.20 AS builder
RUN apk add --no-cache bash rsync
WORKDIR /src
COPY . /src
RUN bash scripts/assemble.sh /src /site

# ---- Stage 2: serve with nginx ----
FROM nginx:1.27-alpine
COPY --from=builder /site /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
