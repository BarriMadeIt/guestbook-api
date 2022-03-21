# build stage
FROM node:lts-alpine as build-stage

WORKDIR /app

EXPOSE 3000

ENTRYPOINT [ "ash" ]