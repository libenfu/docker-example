CREATE TABLE IF NOT EXISTS `__PREFIX__import_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `table` varchar(255) NOT NULL DEFAULT '0' COMMENT '目标表',
  `sheet` int(1) DEFAULT '0' COMMENT '第几个工作表',
  `row` int(11) DEFAULT NULL COMMENT '从第几行导入',
  `head_type` enum('comment','name') NOT NULL DEFAULT 'comment' COMMENT '匹配方式',
  `path` varchar(255) NOT NULL DEFAULT '0' COMMENT '文件路径',
  `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '操作员',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'hidden' COMMENT '状态',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据导入辅助';