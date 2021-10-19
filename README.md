# Установка

## Минимальная версия php >= 7.2.5

- Склонировать репозиторий в нужное место таким образом, чтобы директория __public__  была публичной точкой входа в апаче.

Пример настройки виртуального хоста:

```apacheconf
# виртуальный хост сайта smartcontrol.loc
<VirtualHost smartcontrol.loc:80>
    ServerAdmin webmaster@localhost
    ServerName smartcontrol.loc
    ServerAlias www.smartcontrol.loc
    DocumentRoot /var/www/smartcontrol.loc/public
    ErrorLog /var/www/logs/error.log
    CustomLog /var/www/logs/access.log combined
    DirectoryIndex index.php index.htm index.html
</VirtualHost>
```

> Можно так же зайти на сайт как [http://localhost/smartcontrol/public/](http://localhost/smartcontrol/public/), но стабильность работы всех ссылок не гарантируется.

- Запустить установку композер пакетов

```bash
php composer install
```

> Можно также использовать ключ `--no-dev`. В этом случае пакеты, которые нужны только для разработки не будут установлены.

- Если в директории с проектом не появился файл `.env` то необходимо создать его, скопировав пример

```bash
cp .env.example .env
```

И сгенерировать случайный ключ, который запишется в наш `.env` файл

```bash
php artisan key:generate
```

- Открыть `.env` файл, найти строки, которые отвечают за подключение к БД

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartcontrol
DB_USERNAME=root
DB_PASSWORD=123
```

И изменить их на свои. А так же заменить `APP_URL` на свой адрес

- Теперь можно запустить миграцию и создать данные для работы с апи

```bash
php artisan migrate --seed
php artisan passport:client --personal
```

