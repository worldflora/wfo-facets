# session data will grow out of hand
# if it isn't purged occassionally

# delete files that are older than 7 days.
find ../data/session_data/*  -type f  -mtime +7 -delete
find ../data/session_data/*  -type d -empty -delete

