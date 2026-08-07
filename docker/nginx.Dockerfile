# nginx front for the production deployment (built as clipboard-server-web:latest).
# The deploy manager's docker_deploy step builds this file automatically when it
# exists. Copies public/ out of the already-built app image, so static files need
# no shared volume and no start-order dependency.
ARG APP_IMAGE=clipboard-server:latest

FROM ${APP_IMAGE} AS app

FROM nginx:1.27-alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /app/public /app/public
