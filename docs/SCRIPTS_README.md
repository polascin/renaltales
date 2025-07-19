# Scripts Directory

This directory contains utility scripts for the Renal Tales application.

## Available Scripts

### cleanup.php
Performs routine cleanup tasks including:
- Removes expired password reset tokens
- Removes expired email verification tokens
- Cleans up old session files (older than 24 hours)
- Removes old log files (older than 30 days)
- Clears cache files
- Clears temporary files

**Usage:**
```bash
php scripts/cleanup.php
```

**Recommended:** Run this script daily via cron job for automated maintenance.

## Cron Job Setup

Add to crontab for daily cleanup at 2 AM:
```bash
0 2 * * * /usr/bin/php /path/to/renaltales/scripts/cleanup.php >> /path/to/renaltales/storage/logs/cleanup.log 2>&1
```

## Note

All scripts should be run from the project root directory to ensure proper relative paths.
