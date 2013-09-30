Before start:

Please check the write permission on nutshell/_examples/MVCModels/private/application/config before running.

Then, create a config for apache at /etc/apache2/sites-available. In joao's machine, it looks like this:

<VirtualHost *:80>
        ServerAdmin webmaster@localhost

        SetEnv APPLICATION_ENV dev

        DocumentRoot /home/sroot/app/spinifex-connect/private/nutshell/_examples/MVCModels/public
        ServerName mvcmodels.ubuntudev

        <Directory "/home/sroot/app/spinifex-connect/private/nutshell/_examples/MVCModels/public">
                Options FollowSymLinks
                AllowOverride All
        </Directory>
        
        ErrorLog ${APACHE_LOG_DIR}/error.log

        # Possible values include: debug, info, notice, warn, error, crit,
        # alert, emerg.
        LogLevel warn

        CustomLog ${APACHE_LOG_DIR}/access.log combined

</VirtualHost>

After changing "mvcmodels" (above file) at "sites-available", add it using "a2ensite mvcmodels". Then, restart apache with "/etc/init.d/apache2 restart".

Add to your hosts file "127.0.0.1 mvcmodels.ubuntudev".

Then, fix your config file at private/application/config.

