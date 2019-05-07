CREATE DATABASE RideAlongUsers;

USE RideAlongUsers;

CREATE TABLE Users (
               UserID int NOT NULL AUTO_INCREMENT,
               Name varchar(64) NOT NULL,
               DOB DATE NOT NULL,
               Sex varchar(12) NOT NULL,
               Password varchar(32) NOT NULL,
               VehicleID INT,
               PRIMARY KEY (UserID)
               );

CREATE TABLE Vehicles (
               VID int NOT NULL AUTO_INCREMENT,
               UserID int NOT NULL,
               Make varchar(32) NOT NULL,
               Model varchar(32) NOT NULL,
               ModelYear DATE NOT NULL,
               PRIMARY KEY (VID)
               );

CREATE TABLE Matches (
               Driver int NOT NULL,
               Hitchhiker int NOT NULL,
               VID int NOT NULL,
               Pickup varchar(32) NOT NULL,
               Destination varchar(32) NOT NULL,
               Time DATE NOT NULL,
               PRIMARY KEY (Driver, Hitchhiker, VID)
               );

CREATE TABLE Trips (
               TID int NOT NULL AUTO_INCREMENT,
               UserID int NOT NULL,
               VID int,
               Start varchar(64)  NOT NULL,
               End  varchar(64)  NOT NULL,
               Time DATE,
               PRIMARY KEY (TID)
               );
               




 
