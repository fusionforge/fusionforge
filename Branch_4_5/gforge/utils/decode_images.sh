#! /bin/bash

for u in `find ../www -name '*.uu'` ; do f=${u%.uu} ; uudecode $u -o $f ; done


