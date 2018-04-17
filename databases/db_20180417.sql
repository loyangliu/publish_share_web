-- users
create table users (
  id int(11) unsigned not null auto_increment,
  api_token varchar(50),
  api_token_create_at datetime,
  register_at datetime,
  wx_openid varchar(50),
  wx_session_key varchar(50),
  wx_nick_name varchar(100) not null,
  wx_avatar_url varchar(100),
  wx_gender tinyint(1) not null default 0,
  wx_city varchar(100),
  wx_province varchar(100),
  wx_country varchar(100),
  wx_language varchar(100),
  primary key (id)
) engine=innodb default charset=utf8;