#!/bin/bash

maven -b pdf:pdf xdoc
cp docs/project.pdf gforge.pdf
zip -q -r gforge_html.zip docs/
rm maven.log velocity.log
