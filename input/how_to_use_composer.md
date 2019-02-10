---
layout: post
title: 使用Composer
---

使用Composer进行PHP项目的依赖管理可以提高项目开发的效率。Composer还提供了方便使用的autoload机制。

## 创建新的项目
1. 从[Composer的官网](https://getcomposer.org/download/)下载并安装composer
2. 在项目目录下创建`composer.json`文件，并且输入以下的内容：

```JavaScript
{
    "name": "Orq.PHP/Algs",
    "description": "The php implementation of alighrithms 4 book",
    "require": {
        "php": "^7.2"
    },
    "autoload": {
        "psr-4": {
            "Orq\\PHP\\Algs\\": "src"
        }
    },
}
````

2.1 另外的方法是使用`composer init`命令，根据提示输入相关的内容


## autoload
1. 创建`src`子目录
1. 在composer中指明了shiy`[psr-4](https://www.php-fig.org/psr/psr-4/)`标准
2. 使用`composer dumpautoload -o`命令更新autoload文件
3. 在PHP文件里包含`require "vendor/autoload.php";`，就能自动加载需要的文件了。

## 安装github.com的仓库代码
1. 新建composor.json并且输入下面的内容

```JavaScript
{
    "require": {
        "Orq.PHP/Algs": "dev-master"
    },
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/rockysynergy/algs4_php.git"
        }
    ]
}
```

2. 运行`composer update`