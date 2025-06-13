# v1.0.6 Not released
 - Add transaction log for a pull with the postgres query
 - increase timeout to 10 minutes

# v1.0.5
### Bug Fixes
 - pull data only for users with `userid` from mobisense DB

# v1.0.4
### Bug Fixes
 - pull the data even if `last_upload` is not initialized

# v1.0.3
### Bug Fixes
 - make table `last_upload` leading in the query and then add the `coordinates` table data in the query

# v1.0.2
### New Features
 - add support for the plugin for SelfHelp v7.0.0+. Check if `Clockwork` is available.

# v1.0.1
### Bug Fixes
 - fix cronjob logging time in transactions

# v1.0.0
### New Features
 - Mobisense plugin
 - add page `sh_global_mobisense`
  - field `mobisense_server_ip`
  - field `mobisense_ssh_port` 
  - field `mobisense_ssh_user` 
  - field `mobisense_ssh_key` 
  - field `mobisense_db_name` 
  - field `mobisense_db_port` 
  - field `mobisense_db_user` 
  - field `mobisense_db_password` 
  - field `mobisense_pull_data`
  - field `mobisense_local_host`
 - pull all `last_updates` for all users. Add new entry for each `last_update`
 - add manual pull for a selected user
 - add cronjob for pulling all the data for all the users