USE 'mydb'; 
INSERT INTO User(firstName, lastName, email, password) VALUES
	("Jane", "Doe", "jane@smu.edu", "mapple"),
	("Bob", "Doe", "bob@smu.edu", "bacon"),
	("Matt", "Doe", "matt@smu.edu", "salt");
INSERT INTO Coordinates(x,y,z) VALUES
	(19.313657,-99.113975,5),
	(29.313657,-89.113975,2),
	(39.313657,-79.113975,1),
	(49.313657,-69.113975,3),
	(59.313657,-59.113975,1),
	(69.313657,-49.113975,3);
INSERT INTO Location(buildName, roomName, roomNumber, Coordinates_idCoordinates) VALUES 
	("Embrey", NULL, NULL,1),
	("Embrey", NULL, 216,1),
	("Embrey", NULL, 215,2),
	("Embrey", NULL, 214,3),
	("Embrey", "Huit-Zollars", 312,4),
	("Embrey", "Dean's Suite", 112,5);
INSERT INTO Favorites(User_idUser,Location_idLocation) VALUES 
	(1,2),
	(1,3),
	(1,4),
	(2,5);
INSERT INTO Classes(User_idUser, classTime, day, Location_idLocation) VALUES
	(1,'9999-12-31 23:59:59','MWF',2),
	(1,'9999-12-31 23:59:59','TH',3),
	(1,'9999-12-31 23:59:59','MW',4),
	(2,'9999-12-31 23:59:59','MWF',3),
	(2,'9999-12-31 23:59:59','TH',4),
	(2,'9999-12-31 23:59:59','MW',5);



