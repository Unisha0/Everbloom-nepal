#!/bin/bash
exec php -d memory_limit=1024M -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING" -d display_errors=1 "$(which wp)" "$@"
