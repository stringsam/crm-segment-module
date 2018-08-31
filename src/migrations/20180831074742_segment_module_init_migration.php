<?php

use Phinx\Migration\AbstractMigration;

class SegmentModuleInitMigration extends AbstractMigration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';


CREATE TABLE IF NOT EXISTS `segment_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sorting` int(11) NOT NULL DEFAULT '100',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sorting` (`sorting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `segments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` int(11) NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `cache_count` int(11) DEFAULT NULL,
  `fields` text COLLATE utf8mb4_unicode_ci,
  `query_string` text COLLATE utf8mb4_unicode_ci,
  `segment_group_id` int(11) NOT NULL,
  `criteria` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `segment_group_id` (`segment_group_id`),
  CONSTRAINT `segments_ibfk_1` FOREIGN KEY (`segment_group_id`) REFERENCES `segment_groups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `segments_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `segment_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `segment_id` (`segment_id`),
  CONSTRAINT `segments_values_ibfk_1` FOREIGN KEY (`segment_id`) REFERENCES `segments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2018-08-31 07:42:42
SQL;
        $this->execute($sql);
    }

    public function down()
    {
        // TODO: [refactoring] add down migrations for module init migrations
        $this->output->writeln('Down migration is not available.');
    }
}
