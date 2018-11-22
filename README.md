# OLAINDEX-CMD

```
   ____  __    ___    _____   ______  _______  __      ________  _______ 
  / __ \/ /   /   |  /  _/ | / / __ \/ ____/ |/ /     / ____/  |/  / __ \
 / / / / /   / /| |  / //  |/ / / / / __/  |   /_____/ /   / /|_/ / / / /
/ /_/ / /___/ ___ |_/ // /|  / /_/ / /___ /   /_____/ /___/ /  / / /_/ / 
\____/_____/_/  |_/___/_/ |_/_____/_____//_/|_|     \____/_/  /_/_____/ 
```


🍀 Another OneDrive Command Line Client.

Based on [Laravel-Zero](https://laravel-zero.com) , with lots of modifications.

This is very much a copycat of [onedrivecmd](https://github.com/cnbeining/onedrivecmd) , but in different language.

OLAINDEX-CMD is a console version of OLAINDEX.

## Proeject

- Project Address : [https://github.com/WangNingkai/OLAINDEX-CMD](https://github.com/WangNingkai/OLAINDEX-CMD)

## Features

- Ability to access files and folders using a path URI or file ID
- Individual file put and get operations
- List operation (shows file size and other file information)
- Download and upload with native progress bar.
- Get share link and direct download link!
- Remote download links to your drive(NEW! Not even available via Web console) (Only available at personal due to API limit).
- Supports Office 365 and China 21Vianet.
- Based on PHP,Easy installation.
- Local Configuration file (/storage/app/config.json).

## Installation

```bash
git clone https://github.com/WangNingkai/OLAINDEX-CMD.git tmp 
mv tmp/.git . 
rm -rf tmp 
git reset --hard 
composer install -vvv
chmod -R 755 storage
chown -R www:www *
cp .env.example .env
php olaindex install
chmod 777 storage/app/config.json
```

## Usage

```bash
OLAINDEX-CMD

  USAGE: olaindex <command> [options] [arguments]

  account       Account Info
  cp            Copy Item
  direct        Create Direct Share Link
  download      Download Item
  find          Find Item
  install       Install App
  login         Account Login
  logout        Account Logout
  ls            List Items
  mkdir         Create A New Folder
  mv            Move Item
  quota         OneDrive Info
  reset         Reset App
  rm            Delete Item
  share         Create Download Link
  test          Command Test
  upload        Upload File
  whereis       Find The Item\'s Remote Path

  cache:clear   Flush the application cache
  cache:forget  Remove an item from the cache

  config:cache  Create a cache file for faster configuration loading
  config:clear  Remove the configuration cache file

  refresh:token Refresh Token
```

## Author

Blog : [https://imwnk.cn](https://imwnk.cn)

Email : [imwnk@live.com](mailto:imwnk@live.com)

## Support the development
Do you like this project? Support it by donating

Wechat/AliPay: [Donate](https://pay.ningkai.wang)

## License
OLAINDEX-CMD is an open-source software licensed under the MIT license.


