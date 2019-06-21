# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
#
# This file assumes you are using Windows with vagrant-winnfsd installed.
# If you do not have that installed yet, just run:
# > vagrant plugin install vagrant-winnfsd
#
# We also assume that the Vagrantfile is in the directory your project is
# in.  This one is built for Magento 2 on 5.6, and will install it, setup
# the cron, and xdebug.
#
# Verify that the base url in the setup:install matches your Host or
# whatever address you decide.
#
# Quick Info:
# - Host: 192.168.56.103
# - Xdebug Port: 9001
#
Vagrant.configure("2") do |config|

    config.vm.box = "geerlingguy/ubuntu1604"
    config.vm.network "private_network", ip: "192.168.56.103"

    config.vm.provider "virtualbox" do |vb|
      vb.memory = "4096"
      vb.customize ['modifyvm', :id, '--name', 'local-dev.memorial-bracelets.com', '--memory', '4096', '--ioapic', 'on', '--cpus', '4']
      vb.customize ['setextradata', :id, 'VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root', '1']
    end

    config.vm.synced_folder ".", "/var/www/html", type: "nfs"

    config.vm.provision "shell", inline: "apt-get -y update"
    config.vm.provision "shell", inline: "apt-get install -y python-software-properties"
    config.vm.provision "shell", inline: "add-apt-repository -y ppa:ondrej/php"
    config.vm.provision "shell", inline: "add-apt-repository -y ppa:chris-lea/redis-server"
    config.vm.provision "shell", inline: "apt-get -y update"
    config.vm.provision "shell", inline: "apt-get install -y apache2"
    config.vm.provision "shell", inline: "apt-get install -y php5.6 libapache2-mod-php5.6 php5.6 php5.6-common php5.6-gd php5.6-mysql php5.6-mcrypt php5.6-curl php5.6-intl php5.6-xsl php5.6-mbstring php5.6-zip php5.6-bcmath php5.6-iconv  php5.6-curl  php5.6-xml php5.6-dev"
    config.vm.provision "shell", inline: "apt-get remove -y php7.0-common"
    config.vm.provision "shell", inline: "apt-get install -y php-pear"
    config.vm.provision "shell", inline: "pecl install xdebug"

    config.vm.provision "shell", inline: "wget http://downloads3.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz"
    config.vm.provision "shell", inline: "tar -zxf ioncube_loaders_lin_x86-64.tar.gz"
    config.vm.provision "shell", inline: "mkdir /usr/local/ioncube"
    config.vm.provision "shell", inline: "cp ioncube/ioncube_loader_lin_5.6.so /usr/local/ioncube/"
    config.vm.provision "shell", inline: 'echo -e "zend_extension=/usr/local/ioncube/ioncube_loader_lin_5.6.so\n" >> /etc/php/5.6/mods-available/ioncube.ini'
    config.vm.provision "shell", inline: 'ln -s /etc/php/5.6/mods-available/ioncube.ini /etc/php/5.6/apache2/conf.d/00-ioncube.ini'
    config.vm.provision "shell", inline: 'ln -s /etc/php/5.6/mods-available/ioncube.ini /etc/php/5.6/cli/conf.d/00-ioncube.ini'

    config.vm.provision "shell", inline: 'echo -e "zend_extension=xdebug.so\nxdebug.remote_enable=true\nxdebug.remote_port=9001\nxdebug.remote_connect_back=1\nxdebug.max_nesting_level=10000" >> /etc/php/5.6/mods-available/xdebug.ini'
    config.vm.provision "shell", inline: 'ln -s /etc/php/5.6/mods-available/xdebug.ini /etc/php/5.6/apache2/conf.d/30-xdebug.ini'

    config.vm.provision "shell", inline: "a2enmod rewrite"
    config.vm.provision "shell", inline: 'echo -e "<Directory /var/www/html>\n\tAllowOverride All\n</Directory>" > /etc/apache2/conf-enabled/allow-override.conf'
    config.vm.provision "shell", inline: 'echo -e "EnableSendfile off" > /etc/apache2/conf-enabled/disable-sendfile.conf'
    config.vm.provision "shell", inline: "service apache2 restart"

    config.vm.provision "shell", inline: "debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'"
    config.vm.provision "shell", inline: "debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'"
    config.vm.provision "shell", inline: "apt-get install -y mysql-server-5.7"
    config.vm.provision "shell", inline: 'echo -e "[mysqld]\nbind-address=0.0.0.0" >> /etc/mysql/mysql.conf.d/z-open-all.cnf'
    config.vm.provision "shell", inline: "service mysql restart"

    config.vm.provision "shell", inline: "apt-get install -y redis-server"
    config.vm.provision "shell", inline: "apt-get install -y php-memcached"
    config.vm.provision "shell", inline: "apt-get install -y git"

    config.vm.provision "shell", inline: "curl -sS https://getcomposer.org/installer | php"
    config.vm.provision "shell", inline: "mv composer.phar /usr/local/bin/composer"
    config.vm.provision "shell", inline: "chmod +x /usr/local/bin/composer"

    config.vm.provision "shell", inline: "wget https://files.magerun.net/n98-magerun2.phar"
    config.vm.provision "shell", inline: "mv n98-magerun2.phar /usr/local/bin/magerun"
    config.vm.provision "shell", inline: "chmod +x /usr/local/bin/magerun"
    config.vm.provision "shell", inline: "ln -s /usr/local/bin/magerun /usr/local/bin/magerun2"

    config.vm.provision "shell", inline: "curl -LO http://pestle.pulsestorm.net/pestle.phar"
    config.vm.provision "shell", inline: "mv pestle.phar /usr/local/bin/pestle"
    config.vm.provision "shell", inline: "chmod +x /usr/local/bin/pestle"

    config.vm.provision "shell", inline: "mkdir ~/.composer"
    config.vm.provision "shell", inline: "cp /var/www/html/auth.json ~/.composer"
    config.vm.provision "shell", inline: "composer global require hirak/prestissimo"
    config.vm.provision "shell", inline: "cp -R ~/.composer /home/vagrant/"
    config.vm.provision "shell", inline: "chown -R vagrant:vagrant /home/vagrant/.composer"

    config.vm.provision "shell", inline: 'echo -e "source ~/.bashrc\ncd /var/www/html" >> /home/vagrant/.bash_profile'
    config.vm.provision "shell", inline: "chown vagrant:vagrant /home/vagrant/.bash_profile"

    config.vm.provision "shell", inline: "apt-get -y update"
    config.vm.provision "shell", inline: "mysql -uroot -proot -e 'GRANT ALL PRIVILEGES ON *.* TO \"root\"@\"%\" IDENTIFIED BY \"root\";DROP DATABASE IF EXISTS magento2;CREATE DATABASE magento2;'"

    config.vm.provision "shell", inline: "echo '* * * * * php /var/www/html/bin/magento cron:run | grep -v \"Ran jobs by schedule\" >> /var/www/html/var/log/magento.cron.log' | crontab"
    config.vm.provision "shell", inline: "cd /var/www/html;composer install;"
    config.vm.provision "shell", inline: "cd /var/www/html;php -d memory_limit=2G ./bin/magento setup:install --db-host=127.0.0.1 --db-name=magento2 --db-user=root --db-password=root --base-url=http://192.168.56.103/ --admin-firstname=Briteskies --admin-lastname=Support --admin-email=Briteskies.Support@briteskies.com --admin-user=briteskies --admin-password=Gr33nfr0g\!"
    config.vm.provision "shell", inline: "cd /var/www/html;php ./bin/magento deploy:mode:set production"
end
