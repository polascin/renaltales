#!/bin/bash

# Rollback Procedure
# This script is used to rollback database changes in the event of a failure.
# It assumes that backups have been created and a restore can be performed to a previous state.

BACKUP_DIR="storage/backups/"

# Function to restore database from the latest backup
function restore_latest_backup() {
    echo "Attempting to restore the latest backup..."

    # Find the latest backup file
    LATEST_BACKUP=$(ls -t $BACKUP_DIR*.sql | head -n 1)

    if [ -z "$LATEST_BACKUP" ]; then
        echo "No backup files found in $BACKUP_DIR. Cannot proceed with rollback."
        exit 1
    fi

    echo "Latest backup file found: $LATEST_BACKUP"

    # Perform the restore process
    mysql -u root -p "renaltales" < "$LATEST_BACKUP"

    if [ $? -eq 0 ]; then
        echo "Rollback successful. Database restored from $LATEST_BACKUP"
    else
        echo "Rollback failed. Please check the error messages above."
        exit 1
    fi
}

# Main script execution
echo "Starting rollback procedure..."

# Call the restore function
restore_latest_backup

# Confirm completion
echo "Rollback procedure completed. Please verify the application state."

exit 0
