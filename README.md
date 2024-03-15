一、配置环境
1、php 7.4+mysql 8.0 + nginx  php需要安装redis扩展。建议使用宝塔面板

2、vue 3.x 开发环境

3、需要安装node.js（建议最新版本）


二、部署站点
1、将站点的根目录指向到 /www/wwwroot/bx/

2、设置必要重写规则，在站点根目录下有个doc目录，里面有nginx需要的转发规则

3、初始化数据库，在站点根目录下有数据库sql文件

4、配置数据库使用的账号密码 /bx/global/db.php

5、修改数据表内的域名配置，sys_config 表中的域名，相应的域名可以绑定host

6、所有的接口都在 /bx/api/目录下面，admin是后台的，home是前台的，notify是接口异步回调的（支付接口），common是公用函数库目录

7、系统api接口请求路径示例：

```
域名/api/index.php?m=Admin&c=Default&a=index
```

或者 

```
/api/Admin/Default/index   //即：模块、控制器、方法
```

8、系统使用了ThinkORM对数据库的相关操作，也就是和Thinkphp的方式基本相同

9、需要开启收益相关守护进程，用于定期发放收益。CLI模式下执行 切换到目录 /bx/daemon/下，开启相关服务： php ./watch.php start

如果要结束相关服务：

```
php ./watch.php stop
```

注意：需要允许exec函数的可执行

三、vue的开发调试

1、使用WebStorm编辑器打开/bx/source/ 目录，其中admin对应后台，h5对应前台

2、分别编辑 /bx/source/admin/vite.config.ts   /bx/source/h5/vite.config.ts 里面的 target对应的域名为上面数据表中相同的域名

3、开启调试模式  在目录 /bx/source/admin/ 或 /bx/source/h5/ 下运行命令：


```
npm run dev
```


4、在目录 /bx/source/admin/ 或 /bx/source/h5/ 下运行命令：


```
npm run build
```


这将会分别在 /bx/ht8888和 /bx/h5  目录下生成相应的静态站点，直接发布静态站点即可


伪静态：

```

location ~* ^/(cache|uploads|global|vendor|logs|socket|doc)/.*.(php|php5|txt|log|sql|conf|pid|md|svn)$ {deny all;}
location /home { 
	try_files $uri $uri/ /home/index.html =404;
} 
location /h5 { 
  try_files $uri $uri/ /h5/index.html =404;
}
location /admin { 
	try_files $uri $uri/ /admin/index.html =404;
}

location /ws
{
  proxy_pass http://127.0.0.1:9501;
  proxy_http_version 1.1;
  proxy_set_header Upgrade $http_upgrade;
  proxy_set_header Connection "Upgrade";
  proxy_set_header X-Real-IP $remote_addr;
}

location /api {
	rewrite ^/api/([a-z|A-z|0-9]+)$ /api/index.php?m=$1 last;
	rewrite ^/api/([a-z|A-z|0-9]+)/([a-z|A-z|0-9]+)$ /api/index.php?m=$1&c=$2 last;
	rewrite ^/api/([a-z|A-z|0-9]+)/([a-z|A-z|0-9]+)/([a-z|A-z|0-9]+)$ /api/index.php?m=$1&c=$2&a=$3 last;
}

location ~ .*\.(jpg|jpeg|gif|png|js|css)$
{
  expires      30d;
  access_log off;
  add_header Access-Control-Allow-Origin *;
  add_header Access-Control-Allow-Headers X-Requested-With;
  add_header Access-Control-Allow-Methods GET,POST,OPTIONS;
}
```
