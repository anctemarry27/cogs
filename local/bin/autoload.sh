#!/usr/bin/env sh
echo ""
echo "rebuilding SUPPORT autoload..."
cd og/support
composer dumpautoload -o
echo "...done rebuilding SUPPORT."
echo ""
