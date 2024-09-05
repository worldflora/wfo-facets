# will backup the live database as specified in the config file.
# suitable for being run by cron
today=$(date +"%Y-%m-%d-%H-%M-%S")
filename="../data/db_dumps/wfo_facets_${today}.sql"
mysqldump wfo_facets > $filename
echo $filename
gzip $filename

# prevent backups filling disk
find ../data/db_dumps -type f -mtime +30 -delete


