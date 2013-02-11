SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `AtomBlog` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `AtomBlog` ;

-- -----------------------------------------------------
-- Table `AtomBlog`.`authors`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `AtomBlog`.`authors` ;

CREATE  TABLE IF NOT EXISTS `AtomBlog`.`authors` (
  `id` VARCHAR(45) NOT NULL ,
  `name` VARCHAR(255) NULL ,
  `uri` VARCHAR(255) NULL ,
  `badmin` CHAR(1) DEFAULT 'N',
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `AtomBlog`.`posts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `AtomBlog`.`posts` ;

CREATE  TABLE IF NOT EXISTS `AtomBlog`.`posts` (
  `id` VARCHAR(45) NOT NULL ,
  `authorid` VARCHAR(45) NOT NULL ,
  `title` VARCHAR(255) NULL ,
  `summary` VARCHAR(255) NULL ,
  `contenttype` VARCHAR(45) NULL,
  `content` LONGTEXT NULL ,
  `geoposition` VARCHAR(45) NULL ,
  `geoplacename` VARCHAR(45) NULL ,
  `georegion` VARCHAR(45) NULL ,
  `updated` DATETIME NOT NULL ,
  `published` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `authorid_idx` (`authorid` ASC) ,
  CONSTRAINT `author`
    FOREIGN KEY (`authorid` )
    REFERENCES `AtomBlog`.`authors` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `AtomBlog`.`comments`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `AtomBlog`.`comments` ;

CREATE  TABLE IF NOT EXISTS `AtomBlog`.`comments` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `postid` VARCHAR(45) NOT NULL ,
  `authorid` VARCHAR(45) NOT NULL ,
  `content` VARCHAR(255) NULL ,
  `geoposition` VARCHAR(45) NULL ,
  `geoplacename` VARCHAR(45) NULL ,
  `georegion` VARCHAR(45) NULL ,
  `updated` DATETIME NULL ,
  `published` DATETIME NULL ,
  `approverid` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `postid_idx` (`postid` ASC) ,
  INDEX `authorid_idx` (`authorid` ASC) ,
  INDEX `approverid_idx` (`approverid` ASC) ,
  CONSTRAINT `comments`
    FOREIGN KEY (`postid` )
    REFERENCES `AtomBlog`.`posts` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `commenter`
    FOREIGN KEY (`authorid` )
    REFERENCES `AtomBlog`.`authors` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `approver`
    FOREIGN KEY (`approverid` )
    REFERENCES `AtomBlog`.`authors` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `AtomBlog`.`links`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `AtomBlog`.`links` ;

CREATE  TABLE IF NOT EXISTS `AtomBlog`.`links` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `postid` VARCHAR(45) NULL ,
  `rel` VARCHAR(45) NULL ,
  `href` VARCHAR(255) NULL ,
  `type` VARCHAR(255) NULL ,
  `length` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `postid_idx` (`postid` ASC) ,
  CONSTRAINT `links`
    FOREIGN KEY (`postid` )
    REFERENCES `AtomBlog`.`posts` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `AtomBlog`.`contributors`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `AtomBlog`.`contributors` ;

CREATE  TABLE IF NOT EXISTS `AtomBlog`.`contributors` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `postid` VARCHAR(45) NULL ,
  `authorid` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `authorid_idx` (`postid` ASC, `authorid` ASC) ,
  CONSTRAINT `contributors`
    FOREIGN KEY (`postid`)
    REFERENCES `AtomBlog`.`posts` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `contributor`
    FOREIGN KEY (`authorid` )
    REFERENCES `AtomBlog`.`authors` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

GRANT SELECT ON Posts TO ReadOnly;
GRANT SELECT ON Posts TO AdminReadOnly;
GRANT SELECT ON Posts TO LoggedInReadOnly;
GRANT UPDATE ON Posts TO AdminUpdate;
GRANT INSERT ON Posts TO AdminUpdate;
GRANT DELETE ON Posts TO AdminDelete;

GRANT SELECT ON Links TO ReadOnly;
GRANT SELECT ON Links TO AdminReadOnly;
GRANT SELECT ON Links TO LoggedInReadOnly;
GRANT UPDATE ON Links TO AdminUpdate;
GRANT INSERT ON Links TO AdminUpdate;
GRANT DELETE ON Links TO AdminDelete;

GRANT SELECT ON Contributors TO ReadOnly;
GRANT SELECT ON Contributors TO AdminReadOnly;
GRANT SELECT ON Contributors TO LoggedInReadOnly;
GRANT UPDATE ON Contributors TO AdminUpdate;
GRANT INSERT ON Contributors TO AdminUpdate;
GRANT DELETE ON Contributors TO AdminDelete;

GRANT UPDATE ON Comments TO AdminUpdate;
GRANT INSERT ON Comments TO AdminUpdate;
GRANT DELETE ON Comments TO AdminDelete;
GRANT UPDATE ON Comments TO LoggedInUpdate;
GRANT INSERT ON Comments TO LoggedInUpdate;

GRANT SELECT ON Authors TO ReadOnly;
GRANT SELECT ON Authors TO AdminReadOnly;
GRANT SELECT ON Authors TO LoggedInReadOnly;
GRANT SELECT ON Authors TO AdminUpdate;
GRANT DELETE ON Authors TO AdminUpdate;
GRANT UPDATE ON Authors TO AdminUpdate;
GRANT INSERT ON Authors TO AdminUpdate;
GRANT DELETE ON Authors TO AdminDelete;
GRANT SELECT ON * TO AdminReadOnly;


INSERT INTO `authors` (`id`, `name`, `uri`, `badmin`) VALUES
('google:109619100915933805653', 'Kelly Purdie', 'http://ca.linkedin.com/pub/kelly-purdie/40/919/323', 'N'),
('google:110056483553960735640', 'Rich Hildred', 'http://ca.linkedin.com/in/rhildred/', 'Y'),
('google:116097041182572689610', 'Wolfgang Wurzbacher', 'http://ca.linkedin.com/pub/wolfgang-wurzbacher/40/264/441', 'N');
INSERT INTO `posts` (`id`, `authorid`, `title`, `summary`, `contenttype`, `content`, `geoposition`, `geoplacename`, `georegion`, `updated`, `published`) VALUES
('1b4a3b95-d6bf-4fde-ec5f-c42f3ca96508', 'google:110056483553960735640', 'Simple', 'simple test blog entry', 'text/plain', 'This is a simple test blog entry. This will get more complex as time goes on', '43.457841,-80.560495', 'Regional Municipality of Waterloo, ON, Canada', 'CA/ON', '2013-02-08 20:03:00', '2013-02-08 20:03:09');
INSERT INTO `comments` (`id`, `postid`, `authorid`, `content`, `geoposition`, `geoplacename`, `georegion`, `updated`, `published`, `approverid`) VALUES
(1, '1b4a3b95-d6bf-4fde-ec5f-c42f3ca96508', 'google:116097041182572689610', 'this is a very good post .... enjoyed it very much', '43.434878,-80.630558', 'Regional Municipality of Waterloo, ON, Canada', 'CA/ON', '2013-02-08 20:27:06', '2013-02-08 20:27:11', 'google:110056483553960735640');
INSERT INTO `contributors` (`id`, `postid`, `authorid`) VALUES
(1, '1b4a3b95-d6bf-4fde-ec5f-c42f3ca96508', 'google:109619100915933805653');
INSERT INTO `links` (`id`, `postid`, `rel`, `href`, `type`, `length`) VALUES
(1, '1b4a3b95-d6bf-4fde-ec5f-c42f3ca96508', 'alternate', 'http://farm1.staticflickr.com/12/18582218_81fee512c0_n_d.jpg', 'image/jpeg', NULL);


