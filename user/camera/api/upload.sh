#!/bin/bash
FILE="$1"
USER_ID="$2"
MIME=$(file -b --mime-type "$FILE")
B64=$(base64 -w0 "$FILE")
curl -X POST http://localhost/virtual-phone/user/upload_photo_api.php \
  -H "Content-Type: application/json" \
  -d "{\"user_id\":$USER_ID,\"image\":\"data:$MIME;base64,$B64\"}"