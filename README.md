# SelfHelp plugin - Mobisense

This is a SelfhelpPlugin that is used for [Mobisense](https://apps.apple.com/us/app/mobisense-unibe/id1610936488) integration


# Installation

 - Download the code into the `plugin` folder
 - Checkout the latest version 
 - Execute all `.sql` script in the DB folder in their version order
 - Place your SSH private key in the `auth` folder with the filename `ssh_key`
 - Set proper permissions for the SSH key file:
   - On Linux: `chmod 600 auth/ssh_key`
   - On Windows: Right-click > Properties > Security > Advanced > Disable inheritance > Remove all permissions > Add SYSTEM and your user with Read permissions only

# Requirements

 - SelfHelp v7.3.0+ 
 - `php8.1-pgsql` and `php8.1-ssh2`:  `sudo apt-get install php8.1-pgsql php8.1-ssh2`

# SSH Key Setup

The plugin requires an SSH key for secure connection to the Mobisense database server:

1. The SSH private key must be placed in the `auth` folder with the exact filename `ssh_key`
2. The key file must have restricted permissions:
   - Only the system user (SYSTEM on Windows) and your user account should have access
   - Other users should have no access to the file
   - The file should be read-only for authorized users
3. The corresponding public key must be installed on the Mobisense server


# Cronjob

The plugin requires a cronjob to pull the data from the Mobisense database server:
```
0 * * * * php --define apc.enable_cli=1 /home/user/selfhelp/server/plugins/Mobisense/server/cronjobs/MobisensePullData.php
```

Please set a proper path to the cronjob