-- users
create table users (
  id int(11) unsigned not null auto_increment,
  wx_openid varchar(100),
  wx_session_key varchar(100),
  wx_nick_name varchar(100) not null,
  wx_avatar_url varchar(200),
  wx_gender tinyint(1) not null default 0,
  wx_city varchar(100),
  wx_province varchar(100),
  wx_country varchar(100),
  wx_language varchar(100),
  api_token varchar(100),
  api_token_refresh_at datetime,
  register_at datetime,
  unique key u_wx_openid (wx_openid),
  unique key u_api_token (api_token),
  primary key (id)
) engine=innodb default charset=utf8;

-- 帖子
create table articles (
  id int(11) unsigned not null auto_increment,
  name varchar(255) not null,
  description text,
  user_id int(11) not null,
  publish_at datetime,
  create_at datetime,
  update_at datetime,
  delete_at datetime,
  primary key (id)
) engine=innodb default charset=utf8;

-- 帖子图片
create table article_images (
  id int(11) unsigned not null auto_increment,
  article_id int(11) unsigned not null,
  image_path varchar(100) not null,
  seq int(11) not null default 0,
  primary key (id)
) engine=innodb default charset=utf8;

-- 帖子订阅
create table subscribe (
  id int(11) unsigned not null auto_increment,
  article_id int(11) unsigned not null,
  user_id int(11) unsigned not null,
  subscribe_time datetime,
  primary key (id)
) engine=innodb default charset=utf8;