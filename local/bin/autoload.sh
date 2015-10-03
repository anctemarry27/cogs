#!/usr/bin/env sh
echo ""
echo "rebuilding SUPPORT autoload..."
cd og/src/support
composer dumpautoload -o
echo "...done rebuilding SUPPORT."
echo ""
