#!/bin/bash

MAVEN_HOME=/usr/local/maven_gforge/ && export MAVEN_HOME
maven -b pdf:pdf xdoc
cp docs/project.pdf gforge.pdf
zip -q -r gforge_html.zip docs/
rm maven.log velocity.log
