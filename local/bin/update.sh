#!/usr/bin/env bash
cd og/support
echo ""
echo "Updating cogs SUPPORT packages..."
composer update
cd ../..
echo "... done updating SUPPORT."
echo ""
