#cron set korchi
SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/cron.php"

# Create the CRON job to run every 24 hours
(crontab -l 2>/dev/null; echo "0 0 * * * php $SCRIPT_PATH") | crontab -

echo "CRON job has been set up to run every 24 hours at midnight."
