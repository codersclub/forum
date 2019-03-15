# Sources Forum

- See: [forum.sources.ru](https://forum.sources.ru/) 

## Installation

1) `sudo apt install docker docker-compose`
    - For non-linux environment please install [Docker Desktop](https://www.docker.com/products/docker-desktop)
2) `docker-compose up --build`
3) `docker exec -it --user=www-data sources_ru bash`
4) `./db_schema/install_db.sh`
5) `php ./db_schema/migrate.php`

## Usage

1) Open `sources.localhost` in your browser
