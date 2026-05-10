#!/bin/bash

SOURCE="./public/"
DEST="paige@146.190.241.28:/var/www/viewsourcecode.org/"

ORPHANS=$(rsync -rtvznc --delete --out-format='%n' "$SOURCE" "$DEST" | grep -i "^deleting")
if [ -n "$ORPHANS" ]; then
    echo "!! These files exist on the server but not locally:"
    echo "$ORPHANS" | sed 's/^deleting /  - /'
    echo ""
fi

rsync -rtvzc --chmod=D755,F644 --out-format='%n' "$SOURCE" "$DEST" | grep --line-buffered -v '/$'
