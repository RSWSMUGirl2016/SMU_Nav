-- MySQL Script generated by MySQL Workbench
-- Tue Oct 28 11:44:47 2014
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`User`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`User` ;

CREATE TABLE IF NOT EXISTS `mydb`.`User` (
  `idUser` INT NOT NULL,
  `email` VARCHAR(45) NULL,
  `firstName` VARCHAR(45) NULL,
  `lastName` VARCHAR(45) NULL,
  PRIMARY KEY (`idUser`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Passwords`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Passwords` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Passwords` (
  `User_idUser` INT NOT NULL,
  `password` VARCHAR(45) NULL,
  PRIMARY KEY (`User_idUser`),
  CONSTRAINT `fk_Password_User`
    FOREIGN KEY (`User_idUser`)
    REFERENCES `mydb`.`User` (`idUser`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Location`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Location` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Location` (
  `idLocation` INT NOT NULL,
  `buildingName` VARCHAR(45) NULL,
  `roomName` VARCHAR(45) NULL,
  `roomNumber` INT NULL,
  PRIMARY KEY (`idLocation`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Favorites`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Favorites` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Favorites` (
  `User_idUser` INT NOT NULL,
  `idFavorites` INT NOT NULL,
  `Location_idLocation` INT NOT NULL,
  PRIMARY KEY (`User_idUser`, `idFavorites`, `Location_idLocation`),
  INDEX `fk_Favorites_User1_idx` (`User_idUser` ASC),
  INDEX `fk_Favorites_Location1_idx` (`Location_idLocation` ASC),
  CONSTRAINT `fk_Favorites_User1`
    FOREIGN KEY (`User_idUser`)
    REFERENCES `mydb`.`User` (`idUser`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Favorites_Location1`
    FOREIGN KEY (`Location_idLocation`)
    REFERENCES `mydb`.`Location` (`idLocation`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Coordinates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Coordinates` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Coordinates` (
  `Location_idLocation` INT NOT NULL,
  `x` DOUBLE NULL,
  `y` DOUBLE NULL,
  `z` INT NULL,
  PRIMARY KEY (`Location_idLocation`),
  INDEX `fk_Coordinates_Location1_idx` (`Location_idLocation` ASC),
  CONSTRAINT `fk_Coordinates_Location1`
    FOREIGN KEY (`Location_idLocation`)
    REFERENCES `mydb`.`Location` (`idLocation`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Classes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Classes` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Classes` (
  `User_idUser` INT NOT NULL,
  `classTime` DATETIME NOT NULL,
  `day` VARCHAR(45) NULL,
  `Location_idLocation` INT NOT NULL,
  PRIMARY KEY (`User_idUser`, `classTime`, `Location_idLocation`),
  INDEX `fk_Classes_User1_idx` (`User_idUser` ASC),
  INDEX `fk_Classes_Location1_idx` (`Location_idLocation` ASC),
  CONSTRAINT `fk_Classes_User1`
    FOREIGN KEY (`User_idUser`)
    REFERENCES `mydb`.`User` (`idUser`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Classes_Location1`
    FOREIGN KEY (`Location_idLocation`)
    REFERENCES `mydb`.`Location` (`idLocation`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Event`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Event` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Event` (
  `idEvent` INT NOT NULL,
  `Location_idLocation` INT NOT NULL,
  `name` VARCHAR(45) NULL,
  `description` VARCHAR(45) NULL,
  `eventDateTime` DATETIME NULL,
  PRIMARY KEY (`idEvent`, `Location_idLocation`),
  INDEX `fk_Event_Location1_idx` (`Location_idLocation` ASC),
  CONSTRAINT `fk_Event_Location1`
    FOREIGN KEY (`Location_idLocation`)
    REFERENCES `mydb`.`Location` (`idLocation`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
