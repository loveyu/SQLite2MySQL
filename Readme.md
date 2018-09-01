## SQLite Migrate To MySQL

Simply convert and transfer sqlite database to mysql database.

### Install

```bash
git clone project
composer install
```

### Usage

Try example:
```bash
php bin\convert.php -s sqlite3.db -m mysql://root:123456@127.0.0.1:3306/WxDB_2017
```