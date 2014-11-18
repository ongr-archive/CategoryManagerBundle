
DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` char(32) NOT NULL,
  `title` char(128) NOT NULL default '',
  `root` char(32) NULL default NULL,
  `parent_id` char(32) NULL default NULL,
  `left` int default 0,
  `right` int default 0,
  `level` int default 0,
  `weight` double precision DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `root_index` (`root`),
  INDEX `parent_index` (`parent_id`)
) ENGINE=MyISAM;

INSERT IGNORE INTO `categories` (`id`, `title`, `root`, `parent_id`, `left`, `right`, `level`) VALUES
('53f4590d0ccec9.39288089', 'Kiteboarding', '53f4590d0ccec9.39288089', null, 1, 10, 0),

('53f45976ef75c9.78862935', 'Kites', '53f4590d0ccec9.39288089', '53f4590d0ccec9.39288089', 2, 3, 1),
('53f45979139606.24866601', 'Kiteboards', '53f4590d0ccec9.39288089', '53f4590d0ccec9.39288089', 4, 9, 1),
('53f4597d709631.23677997', 'Small', '53f4590d0ccec9.39288089', '53f45979139606.24866601', 5, 6, 2),
('53f45a8e831510.19801507', 'Large', '53f4590d0ccec9.39288089', '53f45979139606.24866601', 7, 8, 2),

('53f45a96c733f6.75280890', 'Wakeboarding', '53f45a96c733f6.75280890', null, 1, 4, 0),
('53f45cc07f55d2.92980246', 'Sets', '53f45a96c733f6.75280890', '53f45a96c733f6.75280890', 2, 3, 1);

DROP TABLE IF EXISTS `category_matches`;

CREATE TABLE `category_matches` (
  `category` varchar(23) NOT NULL,
  `matched_category` varchar(23) NOT NULL,
  PRIMARY KEY (`category`, `matched_category`)
) ENGINE=MyISAM;
