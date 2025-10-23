# Руководство по развертыванию CMS Sitebill

## Требования к серверу

### Минимальные требования

- **CPU:** 2 ядра
- **RAM:** 2 GB
- **Диск:** 10 GB свободного места
- **PHP:** 7.1 или выше
- **MySQL/MariaDB:** 5.6+/10.0+
- **Веб-сервер:** Apache 2.4+ или Nginx 1.18+

### Рекомендуемые требования

- **CPU:** 4+ ядер
- **RAM:** 4 GB+
- **Диск:** 50 GB+ (SSD предпочтительно)
- **PHP:** 7.4 или 8.0
- **MySQL/MariaDB:** 8.0+/10.6+
- **Веб-сервер:** Apache 2.4+ или Nginx 1.20+

### Требуемые PHP расширения

```bash
# Проверка установленных расширений
php -m

# Обязательные расширения:
- mysqli или pdo_mysql
- gd или imagick
- mbstring
- xml
- curl
- json
- zip
- session

# Рекомендуемые:
- opcache
- apcu
- memcached
```

## Установка на чистый сервер

### Ubuntu/Debian

#### 1. Обновление системы

```bash
sudo apt update
sudo apt upgrade -y
```

#### 2. Установка веб-сервера (Apache)

```bash
# Установка Apache
sudo apt install apache2 -y

# Включение необходимых модулей
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
sudo a2enmod ssl

# Перезапуск Apache
sudo systemctl restart apache2
```

#### 3. Установка PHP

```bash
# Добавление репозитория PHP (для старых версий Ubuntu)
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Установка PHP 7.4 и расширений
sudo apt install php7.4 php7.4-mysql php7.4-gd php7.4-mbstring \
                 php7.4-xml php7.4-curl php7.4-zip php7.4-json -y

# Проверка версии
php -v
```

#### 4. Установка MySQL/MariaDB

```bash
# Установка MariaDB
sudo apt install mariadb-server mariadb-client -y

# Безопасная настройка
sudo mysql_secure_installation

# Настройка:
# - Set root password: Yes
# - Remove anonymous users: Yes
# - Disallow root login remotely: Yes
# - Remove test database: Yes
# - Reload privilege tables: Yes
```

#### 5. Создание базы данных

```bash
# Вход в MySQL
sudo mysql -u root -p

# Создание БД и пользователя
CREATE DATABASE sitebill CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sitebill'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON sitebill.* TO 'sitebill'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### CentOS/RHEL

#### 1. Обновление системы

```bash
sudo yum update -y
```

#### 2. Установка Apache

```bash
sudo yum install httpd -y
sudo systemctl start httpd
sudo systemctl enable httpd

# Настройка firewall
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

#### 3. Установка PHP

```bash
# Для CentOS 8/RHEL 8
sudo dnf module enable php:7.4 -y
sudo dnf install php php-mysqlnd php-gd php-mbstring \
                 php-xml php-curl php-zip php-json -y

# Перезапуск Apache
sudo systemctl restart httpd
```

#### 4. Установка MariaDB

```bash
sudo yum install mariadb-server mariadb -y
sudo systemctl start mariadb
sudo systemctl enable mariadb
sudo mysql_secure_installation
```

## Развертывание приложения

### 1. Загрузка кода

#### Через Git (рекомендуется)

```bash
cd /var/www/html
sudo git clone https://github.com/rumantic/cms.git sitebill
cd sitebill
```

#### Через FTP/SFTP

1. Скачайте архив с GitHub
2. Распакуйте локально
3. Загрузите через FTP клиент (FileZilla, WinSCP)

### 2. Настройка прав доступа

```bash
# Владелец - веб-сервер
sudo chown -R www-data:www-data /var/www/html/sitebill  # Debian/Ubuntu
# или
sudo chown -R apache:apache /var/www/html/sitebill      # CentOS/RHEL

# Права на файлы
sudo find /var/www/html/sitebill -type f -exec chmod 644 {} \;
sudo find /var/www/html/sitebill -type d -exec chmod 755 {} \;

# Права на writable директории
sudo chmod -R 777 /var/www/html/sitebill/cache
sudo chmod -R 777 /var/www/html/sitebill/img/data
```

### 3. Настройка виртуального хоста

#### Apache

Создайте файл `/etc/apache2/sites-available/sitebill.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    
    DocumentRoot /var/www/html/sitebill
    
    <Directory /var/www/html/sitebill>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/sitebill-error.log
    CustomLog ${APACHE_LOG_DIR}/sitebill-access.log combined
</VirtualHost>
```

Активация конфигурации:

```bash
sudo a2ensite sitebill.conf
sudo systemctl reload apache2
```

#### Nginx

Создайте файл `/etc/nginx/sites-available/sitebill`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    
    root /var/www/html/sitebill;
    index index.php index.html;
    
    # Логи
    access_log /var/log/nginx/sitebill-access.log;
    error_log /var/log/nginx/sitebill-error.log;
    
    # Основная локация
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP обработка
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Защита файлов
    location ~ /\.ht {
        deny all;
    }
    
    # Кеширование статики
    location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml)$ {
        expires 365d;
        add_header Cache-Control "public, immutable";
    }
}
```

Активация:

```bash
sudo ln -s /etc/nginx/sites-available/sitebill /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 4. Запуск установщика

1. Откройте браузер и перейдите:
   ```
   http://yourdomain.com/install/
   ```

2. Следуйте инструкциям установщика:
   - Проверка системных требований
   - Настройка базы данных
   - Создание администратора
   - Завершение установки

3. После установки удалите папку `/install/`:
   ```bash
   sudo rm -rf /var/www/html/sitebill/install
   ```

### 5. Настройка конфигурации

#### Файл настроек (`settings.ini.php`)

```ini
[Settings]
estate_folder = ""
theme = "realia"
```

#### Настройка БД (`inc/db.inc.php`)

Этот файл создается автоматически установщиком, но может быть отредактирован:

```php
<?php
$__server = 'localhost';
$__db = 'sitebill';
$__user = 'sitebill';
$__password = 'your_password';
$__db_prefix = 'sb';
$__db_port = '3306';
```

## SSL/TLS настройка (HTTPS)

### Let's Encrypt (Бесплатный SSL)

#### Apache

```bash
# Установка Certbot
sudo apt install certbot python3-certbot-apache -y

# Получение сертификата
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Автоматическое обновление
sudo certbot renew --dry-run
```

#### Nginx

```bash
# Установка Certbot
sudo apt install certbot python3-certbot-nginx -y

# Получение сертификата
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Автоматическое обновление
sudo certbot renew --dry-run
```

### Платный SSL сертификат

1. Сгенерируйте CSR:
```bash
openssl req -new -newkey rsa:2048 -nodes \
    -keyout yourdomain.key \
    -out yourdomain.csr
```

2. Отправьте CSR провайдеру SSL

3. Получите сертификаты и установите:

**Apache:**
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/sitebill
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/ca_bundle.crt
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # SSL настройки
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
}
```

## Оптимизация производительности

### PHP настройки

Отредактируйте `/etc/php/7.4/apache2/php.ini`:

```ini
; Память
memory_limit = 256M

; Загрузка файлов
upload_max_filesize = 32M
post_max_size = 32M

; Выполнение
max_execution_time = 300
max_input_time = 300

; OPcache
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

### MySQL/MariaDB настройки

Отредактируйте `/etc/mysql/mariadb.conf.d/50-server.cnf`:

```ini
[mysqld]
# InnoDB настройки
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query cache (для MySQL 5.7 и ниже)
query_cache_type = 1
query_cache_size = 128M
query_cache_limit = 2M

# Общие настройки
max_connections = 200
thread_cache_size = 50
table_open_cache = 2000
```

Перезапуск:
```bash
sudo systemctl restart mysql
```

## Резервное копирование

### Автоматический backup скрипт

Создайте `/usr/local/bin/sitebill-backup.sh`:

```bash
#!/bin/bash

# Настройки
BACKUP_DIR="/backup/sitebill"
DATE=$(date +%Y%m%d_%H%M%S)
SITE_DIR="/var/www/html/sitebill"
DB_NAME="sitebill"
DB_USER="sitebill"
DB_PASS="your_password"

# Создание директории backup
mkdir -p $BACKUP_DIR

# Backup базы данных
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup файлов (исключая кеш и временные файлы)
tar -czf $BACKUP_DIR/files_$DATE.tar.gz \
    --exclude='cache/*' \
    --exclude='img/data/*' \
    $SITE_DIR

# Удаление старых backup (старше 30 дней)
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Сделайте скрипт исполняемым:
```bash
sudo chmod +x /usr/local/bin/sitebill-backup.sh
```

Добавьте в cron (ежедневно в 2:00):
```bash
sudo crontab -e

# Добавьте строку:
0 2 * * * /usr/local/bin/sitebill-backup.sh >> /var/log/sitebill-backup.log 2>&1
```

### Восстановление из backup

```bash
# Восстановление БД
gunzip < /backup/sitebill/db_20250101_020000.sql.gz | mysql -u sitebill -p sitebill

# Восстановление файлов
cd /var/www/html
sudo tar -xzf /backup/sitebill/files_20250101_020000.tar.gz
```

## Мониторинг

### Установка мониторинга

```bash
# Установка monitoring tools
sudo apt install htop iotop nethogs -y

# Мониторинг процессов
htop

# Мониторинг диска
df -h
du -sh /var/www/html/sitebill/*

# Мониторинг логов
tail -f /var/log/apache2/sitebill-error.log
tail -f /var/log/nginx/sitebill-error.log
```

### Логирование

Настройка ротации логов (`/etc/logrotate.d/sitebill`):

```
/var/www/html/sitebill/cache/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
}
```

## Обновление CMS

### Процесс обновления

1. **Создайте backup:**
   ```bash
   /usr/local/bin/sitebill-backup.sh
   ```

2. **Включите режим обслуживания:**
   ```bash
   touch /var/www/html/sitebill/maintenance.flag
   ```

3. **Обновите код:**
   ```bash
   cd /var/www/html/sitebill
   git pull origin master
   ```

4. **Запустите обновления БД:**
   ```
   http://yourdomain.com/update.php
   ```

5. **Выключите режим обслуживания:**
   ```bash
   rm /var/www/html/sitebill/maintenance.flag
   ```

6. **Очистите кеш:**
   ```bash
   rm -rf /var/www/html/sitebill/cache/compile/*
   rm -rf /var/www/html/sitebill/cache/smarty/*
   ```

## Безопасность

### Firewall настройка

```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp  # SSH
sudo ufw allow 80/tcp  # HTTP
sudo ufw allow 443/tcp # HTTPS
sudo ufw enable

# Firewalld (CentOS)
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### Fail2Ban

```bash
# Установка
sudo apt install fail2ban -y

# Настройка (/etc/fail2ban/jail.local)
[apache-auth]
enabled = true
port = http,https
filter = apache-auth
logpath = /var/log/apache2/sitebill-error.log
maxretry = 3
bantime = 3600

# Запуск
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### Регулярные обновления

```bash
# Автоматические обновления безопасности (Ubuntu)
sudo apt install unattended-upgrades -y
sudo dpkg-reconfigure -plow unattended-upgrades
```

## Troubleshooting

### Проблема: 500 Internal Server Error

1. Проверьте логи:
   ```bash
   tail -f /var/log/apache2/error.log
   ```

2. Проверьте права на файлы
3. Проверьте .htaccess файл
4. Проверьте PHP настройки

### Проблема: База данных недоступна

1. Проверьте статус MySQL:
   ```bash
   sudo systemctl status mysql
   ```

2. Проверьте настройки в `inc/db.inc.php`
3. Проверьте права пользователя БД

### Проблема: Медленная работа

1. Включите OPcache
2. Оптимизируйте MySQL
3. Настройте кеширование
4. Используйте CDN для статики

## Чеклист развертывания

- [ ] Сервер настроен и обновлен
- [ ] Установлен веб-сервер (Apache/Nginx)
- [ ] Установлен PHP с необходимыми расширениями
- [ ] Установлена MySQL/MariaDB
- [ ] Создана база данных
- [ ] Код загружен на сервер
- [ ] Настроены права доступа
- [ ] Настроен виртуальный хост
- [ ] Запущен установщик
- [ ] Удалена папка /install/
- [ ] Настроен SSL/HTTPS
- [ ] Оптимизированы настройки PHP и MySQL
- [ ] Настроено резервное копирование
- [ ] Настроен мониторинг
- [ ] Настроена безопасность (firewall, fail2ban)
- [ ] Протестирована работа сайта
