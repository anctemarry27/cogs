#!/usr/bin/env bash
clear
cd og/src/support
echo ""
echo "Updating cogs SUPPORT packages..."
composer update
cd ../../..
echo "... done updating SUPPORT."
echo ""
