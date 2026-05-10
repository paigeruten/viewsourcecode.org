Roughly following: https://robinvanderknaap.dev/blog/how-to-deploy-a-static-site-to-digitalocean/

## Setup firewall

```sh
ufw allow OpenSSH
ufw enable
```

## Allow automatic reboots after unattended upgrades

```sh
vim /etc/apt/apt.conf.d/50unattended-upgrades
# Uncomment the line: Unattended-Upgrade::Automatic-Reboot "true";
systemctl restart unattended-upgrades.service
```

## Nginx

```sh
apt update
apt install nginx
ufw allow 'NGINX Full'

mkdir -p /var/www/viewsourcecode.org/html
vim /var/www/viewsourcecode.org/html/index.html
vim /etc/nginx/sites-available/viewsourcecode.org
# see ./nginx/viewsourcecode.org
ln -s /etc/nginx/sites-available/viewsourcecode.org /etc/nginx/sites-enabled/
systemctl restart nginx
```

## Add non-root user

```sh
adduser paige

usermod -aG sudo paige
visudo
# Add line "paige   ALL=(ALL:ALL) ALL"

mkdir -p /home/paige/.ssh
cp /root/.ssh/authorized_keys /home/paige/.ssh/
chown -R paige:paige /home/paige/.ssh
chmod 700 /home/paige/.ssh
chmod 600 /home/paige/.ssh/authorized_keys

chown -R paige:paige /var/www/viewsourcecode.org
chmod 775 /var/www/viewsourcecode.org

vim /etc/ssh/sshd_config
# Add/uncomment: PasswordAuthentication no
# Add/uncomment: PermitRootLogin prohibit-password
vim /root/.ssh/authorized_keys
# Remove SSH keys after ensuring new user can sudo
systemctl restart ssh
# Now only digitalocean web console can SSH in as root!
```
