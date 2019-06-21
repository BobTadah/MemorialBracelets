export backupDate=$(date '+%F-%H-%M')
file_limit=3
backup_dir="Backups"

# Backup current installation
mkdir -p $backup_dir
tar --exclude='Magento/vendor' --exclude='Magento/pub/media/catalog' --exclude='Magento/pub/media/import/*' --exclude='Magento/var/import/*' --exclude='Magento/var/cache' --exclude='Magento/var/log' --exclude='Magento/puppet' --exclude='Magento/var/page_cache' --exclude='Magento/var/report' --exclude='Magento/var/session' -czhf "$backup_dir/Magento-$backupDate.tar.gz" Magento/

# Enable Maintenance
echo "Start Downtime: $(date)"
php Magento/bin/magento maintenance:enable

# Rsync over Release Candidate
time rsync -OvzrLt --delete --checksum --exclude-from='Magento-rc/dev/tools/deployment/excluded-files' 'Magento-rc/' 'Magento/' | tee "$backup_dir/Magento-$backupDate.rsync.log"

# Upgrade Magento
php Magento/bin/magento cache:flush
time php Magento/bin/magento setup:db-schema:upgrade
time php Magento/bin/magento setup:db-data:upgrade

# Disable Maintenance
php Magento/bin/magento maintenance:disable
echo "End Downtime: $(date)"

# Log Cleanup
rm -rf Magento/var/log/support_report.log Magento/var/log/debug.log Magento/var/log/exception.log

# Delete old backups and rsync logs
find "$backup_dir" -mindepth 1 -maxdepth 1 -type f -iname 'Magento-*.tar.gz'    -exec stat -c "%y %n" {} + | sort -n | cut -d ' ' -f 4- | head -n -"$file_limit" | xargs -I{} rm "{}"
find "$backup_dir" -mindepth 1 -maxdepth 1 -type f -iname 'Magento-*.rsync.log' -exec stat -c "%y %n" {} + | sort -n | cut -d ' ' -f 4- | head -n -"$file_limit" | xargs -I{} rm "{}"

# Close SSH Connection
exit
