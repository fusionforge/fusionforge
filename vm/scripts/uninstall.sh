#!/bin/bash
aptitude purge $(aptitude search forge | grep ^i | cut -b5- | awk '{print $1}')
