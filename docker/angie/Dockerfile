ARG BUILD_SERVER_VERSION=1.10.3-minimal

FROM docker.angie.software/angie:${BUILD_SERVER_VERSION}

WORKDIR "/home/sources/forum.sources.ru"

COPY --chown=root:root ./docker/angie/http.d/forum.sources.ru.conf /etc/angie/http.d/default.conf

COPY --chown=root:root ./docker/angie/certs/localhost.crt /etc/angie/certs/localhost.crt
COPY --chown=root:root ./docker/angie/certs/localhost.key /etc/angie/certs/localhost.key
