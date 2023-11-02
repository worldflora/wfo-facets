CREATE DATABASE `wfo_facets` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;


CREATE TABLE `facet_names` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  `uri` varchar(100) DEFAULT NULL,
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_unique` (`title`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `facet_values` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_id` int DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text,
  `uri` varchar(100) DEFAULT NULL,
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name_idx` (`name_id`),
  CONSTRAINT `name` FOREIGN KEY (`name_id`) REFERENCES `facet_names` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=280 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  `uri` varchar(100) DEFAULT NULL,
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `wfo_scores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wfo_id` varchar(15) NOT NULL,
  `facet_value_id` int NOT NULL,
  `source_id` int NOT NULL,
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wfo` (`wfo_id`),
  KEY `facet_id` (`facet_value_id`) USING BTREE,
  KEY `source_idx` (`source_id`),
  CONSTRAINT `facet_value` FOREIGN KEY (`facet_value_id`) REFERENCES `facet_values` (`id`) ON DELETE CASCADE,
  CONSTRAINT `source` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3891697 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

