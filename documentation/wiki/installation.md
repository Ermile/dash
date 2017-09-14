
1. config virtualHosts on webService
# Ubuntu
## Apache : 
- run : `sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/example.conf`
- run : `sudo vim /etc/apache2/sites-available/example.conf` & change `DocumentRoot & ServerName & Directory` and save change
```
<VirtualHost 127.0.0.2>
 DocumentRoot /var/www/example/public_html/
 ServerName example.dev
 <Directory /var/www/example/public_html/>
  AllowOverride All
  Order allow,deny
  allow from all
  Require all granted
 </Directory>
</VirtualHost>
```
- run : `sudo a2ensite example.conf`
- restart apache service : `sudo service apache2 restart`
- run `sudo vim /etc/hosts` and add code to end of file :
```
 12.0.0.2       example.dev
```
- now type in browser : example.dev run virtual hosts
+ more information in : [apache virtualHosts](https://www.digitalocean.com/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-14-04-lts "Title")
### Tip
- in Apache must enable `.htaccess` mode by : `sudo a2enmod rewrite headers`

## Nginx
- run : `sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-available/example`
- run : `sudo vim /etc/nginx/sites-available/example` & change `root & ServerName ` AND save change
```
 server {
    listen 80 ;
    listen [::]:80 ;
    root directory/public_html;
    index index.html index.htm index.nginx-debian.html;
    server_name example.dev ;
    include sites-available/php.conf;
 }

```
- add `php.conf` in `/etc/nginx/sites-available/php.cong` :
```
index index.php index.html index.nginx-debian.html;

location / {
 # First attempt to serve request as file, then
 # as directory, then fall back to displaying a 404.
 # try_files $uri $uri/ =404;
 try_files $uri $uri/ /index.php$is_args$args;
}

# pass PHP scripts to FastCGI server
location ~ \.php$ {
 include snippets/fastcgi-php.conf;
 fastcgi_pass unix:/var/run/php/php7.1-fpm.sock;
}

# deny access to .htaccess files, if Apache's document root concurs with nginx's one
location ~ /\.ht { deny all;}

location = /favicon.ico { log_not_found off; access_log off; }
location = /robots.txt { log_not_found off; access_log off; allow all; }
location ~* \.(css|gif|ico|jpeg|jpg|js|png)$ { expires max; log_not_found off;}

```
- restart nginx service : `sudo service nginx restart`
- now type in browser : example.dev run virtual hosts
+ more information in : [nginx virtualHosts](https://www.digitalocean.com/community/tutorials/how-to-set-up-nginx-virtual-hosts-server-blocks-on-ubuntu-12-04-lts--3 "Title")
---
# Windows
## Apache (xampp)
- edite `httpd-vhost.conf` in `c:/exampp/apache/conf/extra`, add this code to end of file :
```
<VirtualHost 127.0.0.2>
 DocumentRoot c:/xampp/htdocs/example/public_html/
 ServerName example.dev
 <Directory c:/xampp/htdocs/example/public_html/>
  AllowOverride All
  Order allow,deny
  allow from all
  Require all granted
 </Directory>
</VirtualHost>
```
- restart apache service
- edite `hosts` file in `c:/windows/System32/driver/etc`, add this code to end of file :
 ```
 12.0.0.2       example.dev
 ```
 - for more information : [apache virtualHosts in windows](https://delanomaloney.com/2013/07/10/how-to-set-up-virtual-hosts-using-xampp/)
---
2. install Dash :
## clone repo :
`git clone https://github.com/geeksesi/dash.git`
## download link
[Dash](https://github.com/geeksesi/dash/archive/master.zip)
## composer
`composer require geeksesi/dash`
or add this to `composer.json` :
```
{
	"require": 
	{
            "geeksesi/dash": "dev-master"
        }

}
```
and run : `composer install`

3. To run the project, we need to observe the structure of the folder:
```

 ProjectFolder :
 ├── public_html
 │  ├── index.php
 │  ├── static
 │  └── .htaccess
 ├──Dash
 │  ├── addons
 │  ├── lib
 │  ├── public_html
 │  ├── autoload.php
 │  ├── define.php
 │  └── Twig
 ├── content
 │  └── home
 └──config.me.php
```
- in `public_html/index.php` must be include `Dash/autoload.php` like this :
```
// if Dash exist, require it else show related error message
if ( file_exists( '../dash/autoload.php') )
{
	require_once( '../dash/autoload.php');
}
else
{   // A config file doesn't exist
	exit("<p>We can't find <b>Saloos</b>! Please contact administrator!</p>");
}
```
- in `content` folder must be create project directory like this : 
` test.com/contact ` `=>` ` content/contact/controller.php `
