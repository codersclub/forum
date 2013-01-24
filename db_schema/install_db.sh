#!/bin/bash

#TEMP=`getopt -o n --long no-create-db -n 'install_db.sh' -- "$@"`
#eval set -- "$TEMP"

if [[ $1 == "-n" || "$1" == "--no-create-db" ]] ; then
    SKIPDB_CREATION=1
fi

LINK=`readlink $0`
if [[ -z ${LINK} ]]; then
    LINK=$0
fi
BASEDIR=`dirname ${LINK}`

sed='/bin/sed'
mysql='/usr/bin/mysql'

#------
echo 'mysql userame:'
read USER

echo 'mysql password:'
read PASS

echo 'forum database name:'
read DB_NAME

#oh... f*king slashes
RBASEDIR=`echo $BASEDIR | sed -e 's!/!\\\/!g'`
COMMANDS="s/#USER#/$USER/
s/#PASS#/$PASS/
s/#DB#/$DB_NAME/
s/#PATH#/$RBASEDIR\/db\//"

$sed -e "$COMMANDS" $BASEDIR/config/db_config.init > $BASEDIR/config/db_config.php

mysqle="$mysql -u $USER -p$PASS $DB_NAME"
if [[ $SKIPDB_CREATION != 1 ]]
then
    echo "Initializing database..."
    #creating our database
    $sed -e "s/#DB#/$DB_NAME/"  $BASEDIR/db/initial/db_create.sql | $mysql -u $USER -p$PASS
    $mysqle < $BASEDIR/db/initial/db_struct.sql
    $mysqle < $BASEDIR/db/initial/db_insert.sql
fi

#Because we install migrations manually we need install db too, 
#or trying if database doesn't exist and creation was skipped
$mysqle < $BASEDIR/db/initial/migrations_table.sql >/dev/null 2>&1

echo 
echo "Done. Please run 'php $BASEDIR/migrate.php latest' to apply existing migrations"
echo "You can change your mysql settings by editing $BASEDIR/config/db_config.php"
