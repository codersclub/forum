ARG BUILD_MYSQL_VERSION=9.6

FROM mysql:${BUILD_MYSQL_VERSION}

COPY --chown=root:root ./docker/mysql/conf.d/my.cnf /etc/mysql/conf.d/my.cnf
