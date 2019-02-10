---
layout: post
title: PHP程序安全
---

PHP程序的安全隐患主要是涉及到用户输入、cookie和session。以下是编写安全的PHP程序方法：

## 不要轻易相信"用户"
你不能确定坐在另一边电脑旁的是谁，根据实际情况对用户输入进行以下处理：
1. 过滤：使用`filter`函数或`preg_replace`保留合法的部分，丢弃其它部分。
2. 使用`ctype_`函数或`preg_match`验证用户的输入

## 跨站脚本攻击
攻击者把JavaScript代码放到你的网站上，受害者浏览网页的时候浏览器会执行攻击代码。它是造成损失最大的攻击方式之一，但是解决方法确实最简单的。在本文其它方法的基础上使用`htmlentities`转义PHP输出。

## CSRF
攻击者在受害者不知情的情况下，以他的身份执行转账、购物等让受害者蒙受损失的操作。解决方法是：
1. 把`form`组件的method设置成`post`
2. 正常的用户肯定是先查看表单，然后在提交操作。所以每次form输出都生成并包含一个`csrf_token`，提交的时候与session['csrf_token']进行比较，核对。

## Session安全
攻击者可以使用Session Fixation, Capture和Prediction三种方式获取受害者的Senssion完成攻击。PHP生成session id的随机算法大多数情况下让Session Prediction攻击变得不可能。

### Session Fixation的解决方法
1. `session.use_cookies`设置成1或者0
2. `session.use_only_cookies`设置成1，攻击者就不能通过其它方式修改session id了
3. `session.use_trans_sid`设置成0
4. `url_rewriter.tags`设置成空字符串
5. `session.name`设置成PHPSESSID以外的字符。

### Session Capture(Hijacking)
1. 设置`session.cookie_httponly`为1
2. 在`session_start`前比较$_SEVER与$_SESSION里的`Accept-Charset, Accept-Encoding, Accept-Language, User-Agent`

## SQL注入
使用PDO的预处理语句就可以解决。

## 存储密码
假如密码是明文存储在数据库表的，攻击者下载了数据库表之后，就能不费吹灰之力使用受害者身份登录并完成操作了。所以存储密码到数据库以前需要使用以下的代码进行处理：

```PHP
$salt = '378570bdf03b25c8efa9bfdcfb64f99e'; 
// see php doc for hash_hmac for more info
$hash = hash_hmac('SHA-1', $_POST['password'], $salt);
```

## 暴力破解
1. 使用`CAPTCHA`
2. 当错误登录次数到达阈值以后，在一个时间段内拒绝从同一个IP发起的登录请求

## 网络窃听
攻击者可以窃听同一局域网的网络通信，其它的方法都会失效。所以所有的网站都需要使用SSL。
