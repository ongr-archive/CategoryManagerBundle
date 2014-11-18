CREATE TABLE `ongr_sync_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(1) COLLATE utf8_unicode_ci NOT NULL COMMENT 'C-CREATE(INSERT),U-UPDATE,D-DELETE',
  `document_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `document_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `update_type` smallint(6) NOT NULL DEFAULT '1' COMMENT '0-partial,1-full',
  `timestamp` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-new,1-done',
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
