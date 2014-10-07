#!/bin/bash
# Called by ~/.mini-dinstall.conf:release_signscript
rm -f Release.gpg  # no way to overwrite in gpg
GNUPGHOME=/usr/src/gnupg/ gpg --no-tty --batch --detach-sign -o Release.gpg $1
