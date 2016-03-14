CREATE TABLE auth_rule
(
    id MEDIUMINT(8) UNSIGNED PRIMARY KEY NOT NULL,
    name VARCHAR(80) DEFAULT '' NOT NULL,
    title VARCHAR(20) DEFAULT '' NOT NULL,
    type TINYINT(1) DEFAULT '1' NOT NULL,
    status TINYINT(1) DEFAULT '1' NOT NULL,
    `condition` CHAR(100) DEFAULT '' NOT NULL,
    pid SMALLINT(5) NOT NULL COMMENT '父级ID',
    sort SMALLINT(5) NOT NULL COMMENT '排序',
    create_time INT(11) COMMENT '创建时间',
    icon VARCHAR(50)
);
CREATE TABLE admin
(
    id INT(11) UNSIGNED PRIMARY KEY NOT NULL COMMENT '管理员ID',
    account VARCHAR(32) COMMENT '管理员账号',
    password VARCHAR(36) COMMENT '管理员密码',
    mobile VARCHAR(11) COMMENT '手机号',
    login_time INT(11) COMMENT '最后登录时间',
    login_ip VARCHAR(15) COMMENT '最后登录IP',
    login_count MEDIUMINT(8) NOT NULL COMMENT '登录次数',
    email VARCHAR(40) COMMENT '邮箱',
    status TINYINT(1) DEFAULT '1' NOT NULL COMMENT '账户状态，禁用为0   启用为1',
    create_time INT(11) COMMENT '创建时间'
);
CREATE TABLE auth_group
(
    id MEDIUMINT(8) UNSIGNED PRIMARY KEY NOT NULL,
    title CHAR(100) DEFAULT '' NOT NULL,
    status TINYINT(1) DEFAULT '1' NOT NULL,
    rules CHAR(80) DEFAULT '' NOT NULL,
    create_time INT(11) DEFAULT '0'
);
CREATE TABLE auth_group_access
(
    uid MEDIUMINT(8) UNSIGNED NOT NULL,
    group_id MEDIUMINT(8) UNSIGNED NOT NULL
);
CREATE UNIQUE INDEX name ON auth_rule (name);
CREATE INDEX group_id ON auth_group_access (group_id);
CREATE INDEX uid ON auth_group_access (uid);
CREATE UNIQUE INDEX uid_group_id ON auth_group_access (uid, group_id);