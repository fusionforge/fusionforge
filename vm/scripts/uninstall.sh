#!/bin/bash
aptitude purge $(dpkg -l '*forge*' | grep -E '^(ii|rc)' | cut -b5- | awk '{print $1}')
