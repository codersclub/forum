<?php

$db_config = (object) array();
$db_config->host = 'mysql';
$db_config->port = '3306';
$db_config->user = 'root';
$db_config->pass = 'root';
$db_config->name = 'invision';
$db_config->db_path = './db_schema/db/';
$db_config->method = 1;
$db_config->migrations_table = 'mpm_migrations';
