![OLAINDEX-LARGE.png](https://i.loli.net/2018/11/22/5bf6b12a9367b.png)

🍀 另一个OneDrive命令行客户端.

OLAINDEX-CMD 是命令行版的 OLAINDEX.

## 项目地址

- 项目地址 : [https://github.com/WangNingkai/OLAINDEX-CMD](https://github.com/WangNingkai/OLAINDEX-CMD)

## 特点

- 支持通过地址或 id 列举目录和文件；
- 独立的文件操作；
- 展示详细的文件信息；
- 支持单文件上传下载，并显示进度；
- 支持获取永久下载直链和分享直链
- 支持离线下载 (接口仅对个人版开放)；
- 支持 office365 和 世纪互联；
- 一键安装.
- 本地化配置文件 (/storage/app/config.json).

## 安装

### 手动安装

```bash
mkdir -p olaindex && cd olaindex
git clone https://github.com/WangNingkai/OLAINDEX-CMD.git tmp  && mv tmp/.git . && rm -rf tmp && git reset --hard
composer install
chmod -R 755 storage
chown -R www:www *
cp .env.example .env
cp storage/app/config.sample.json storage/app/config.json
chmod 777 storage/app/config.json
chmod +x olaindex
./olaindex install
```

### 自动安装

```bash
wget -N --no-check-certificate https://raw.githubusercontent.com/WangNingkai/OLAINDEX-CMD/master/install.sh && chmod +x install.sh && bash install.sh
```

## 使用方法

```bash
OLAINDEX-CMD

  USAGE: olaindex <命令> [可选参数] [参数]

  account       Account Info # 用户信息
  cp            Copy Item # 复制
  direct        Create Direct Share Link # 分享直链
  download      Download Item # 下载
  find          Find Item # 搜索
  install       Install App # 安装
  login         Account Login # 登陆
  logout        Account Logout # 退出
  ls            List Items # 列表
  mkdir         Create A New Folder # 新建目录
  mv            Move Item # 移动
  quota         OneDrive Info # 使用概况
  reset         Reset App # 重置
  rm            Delete Item # 删除
  share         Create Download Link # 分享下载直链
  upload        Upload File # 上传
  whereis       Find The Item\'s Remote Path # id转目录

  cache:clear   Flush the application cache 
  cache:forget  Remove an item from the cache

  config:cache  Create a cache file for faster configuration loading
  config:clear  Remove the configuration cache file

  refresh:token Refresh Token # 刷新token
```

## 作者

Blog : [https://imwnk.cn](https://imwnk.cn)

Email : [imwnk@live.com](mailto:imwnk@live.com)

## 支持开发
如果你喜欢此作品，欢迎捐赠

微信/支付宝: [Donate](https://pay.ningkai.wang)

## License
OLAINDEX-CMD is an open-source software licensed under the MIT license.
