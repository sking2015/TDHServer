-- ----------------------------------------------------------------------------
-- MySQL Workbench Migration
-- Migrated Schemata: tdhgame
-- Source Schemata: tdhgame
-- Created: Fri Oct 31 22:32:38 2025
-- Workbench Version: 8.0.43
-- ----------------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- Schema tdhgame
-- ----------------------------------------------------------------------------
DROP SCHEMA IF EXISTS `tdhgame` ;
CREATE SCHEMA IF NOT EXISTS `tdhgame` ;

-- ----------------------------------------------------------------------------
-- Table tdhgame.account
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tdhgame`.`account` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `account` VARCHAR(50) NOT NULL,
  `userid` INT NOT NULL,
  `money` INT NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `account` (`account` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;

-- ----------------------------------------------------------------------------
-- Table tdhgame.role_data
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tdhgame`.`role_data` (
  `user_id` INT NOT NULL DEFAULT '0' COMMENT '用户ID',
  `lv` INT NOT NULL DEFAULT '0',
  `exp` INT NOT NULL DEFAULT '0',
  `hp` INT NOT NULL DEFAULT '0' COMMENT '最大hp',
  `sp` INT NOT NULL DEFAULT '0' COMMENT '最大sp',
  `atk` INT NOT NULL DEFAULT '0' COMMENT '攻击力',
  `def` INT NOT NULL DEFAULT '0' COMMENT '防御力',
  `cri` INT NOT NULL DEFAULT '0' COMMENT '暴击率',
  `crd` INT NOT NULL DEFAULT '0' COMMENT '暴击伤害',
  `atk_rate` FLOAT NOT NULL DEFAULT '0' COMMENT '攻击频率，数据库里直接存原始数据。显示可能要显示成攻速',
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;
SET FOREIGN_KEY_CHECKS = 1;
