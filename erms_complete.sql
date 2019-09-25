--
-- Database: 'erms'
--

CREATE DATABASE IF NOT EXISTS erms;
USE erms;


-- --------------------------------------------------------
--
-- Table structure for table 'User'
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE IF NOT EXISTS `User` (
   username varchar(50) NOT NULL,
   `name` varchar(50) NOT NULL,
   `password` varchar(50) NOT NULL,
   `type` varchar(1) NOT NULL,
   PRIMARY KEY (username)
);

-- --------------------------------------------------------
--
-- Table structure for table 'Municipality'
--

DROP TABLE IF EXISTS Municipality;
CREATE TABLE IF NOT EXISTS Municipality (
  username varchar(50) NOT NULL,
  pop_size integer NOT NULL,
  PRIMARY KEY (username),
  FOREIGN KEY (username)
	REFERENCES `User`(username)
);

-- --------------------------------------------------------
--
-- Table structure for table 'Individual'
--

DROP TABLE IF EXISTS Individual;
CREATE TABLE IF NOT EXISTS Individual (
  username varchar(50) NOT NULL,
  job_title varchar(50) NOT NULL,
  hire_date date NOT NULL,
  PRIMARY KEY (username),
  FOREIGN KEY (username)
	REFERENCES `User`(username)
);


-- --------------------------------------------------------
--
-- Table structure for table 'Government_Agency'
--

DROP TABLE IF EXISTS Government_Agency;
CREATE TABLE IF NOT EXISTS Government_Agency (
  username varchar(50) NOT NULL,
  jurisdiction varchar(50) NOT NULL,
  PRIMARY KEY (username),
  FOREIGN KEY (username)
	REFERENCES `User`(username)
);

-- --------------------------------------------------------
--
-- Table structure for table 'Company'
--

DROP TABLE IF EXISTS Company;
CREATE TABLE IF NOT EXISTS Company(
  username varchar(50) NOT NULL,
  HQ_location varchar(50) NOT NULL,
  PRIMARY KEY (username),
  FOREIGN KEY (username)
	REFERENCES `User`(username)
);

-- --------------------------------------------------------
--
-- Table structure for table 'Incident'
--

DROP TABLE IF EXISTS Incident;
CREATE TABLE IF NOT EXISTS Incident(
  ID integer  NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  description varchar(500) NOT NULL,
  latitude decimal(9,6) NOT NULL,
  longitude decimal(9,6) NOT NULL,
  `owner` varchar(50) NOT NULL,
  PRIMARY KEY (ID),
  FOREIGN KEY (owner)
       REFERENCES `User`(username)
);

-- --------------------------------------------------------
--
-- Table structure for table 'ESF'
--

DROP TABLE IF EXISTS ESF;
CREATE TABLE IF NOT EXISTS ESF(
  ID integer NOT NULL,
  description varchar(75) NOT NULL,
  PRIMARY KEY (ID)
);

-- --------------------------------------------------------
--
-- Table structure for table 'Cost_unit'
--

DROP TABLE IF EXISTS Cost_unit;
CREATE TABLE IF NOT EXISTS Cost_unit (
  time_unit varchar(50) NOT NULL,
  PRIMARY KEY (time_unit)
);


-- --------------------------------------------------------
--
-- Table structure for table 'Resource'
--
-- possible status values are 0 (available), 1 (in use), 2 (in repair)

DROP TABLE IF EXISTS Resource;
CREATE TABLE IF NOT EXISTS Resource(
   ID integer NOT NULL AUTO_INCREMENT,
   `name` varchar(50) NOT NULL,
   `status` integer DEFAULT 0 NOT NULL,
   model varchar(50),
   latitude decimal(9,6) NOT NULL,
   longitude decimal(9,6) NOT NULL,
   primary_esf integer NOT NULL,
   cost_amount decimal(10,4) NOT NULL,
   cost_unit varchar(50) NOT NULL,
   `owner` varchar(50) NOT NULL,
   PRIMARY KEY (ID),
   FOREIGN KEY (primary_esf)
       REFERENCES ESF(ID),
   FOREIGN KEY (cost_unit)
       REFERENCES Cost_unit(time_unit),
     FOREIGN KEY (owner)
       REFERENCES `User`(username)
);

-- --------------------------------------------------------
--
-- Table structure for table 'Request'
--
-- possible status values are 0 for repair, 1 for borrow, 2 for deploy

DROP TABLE IF EXISTS Request;
CREATE TABLE IF NOT EXISTS Request (
   resource_id integer NOT NULL,
   requester_id varchar(50) NOT NULL,
   start_date datetime NOT NULL,
   end_date datetime NOT NULL,
   incident_id integer,
   `type` integer NOT NULL,
   `owner` varchar(50) NOT NULL,
   UNIQUE(resource_id, requester_id, incident_id),
   FOREIGN KEY (resource_id)
       REFERENCES Resource(ID),
   FOREIGN KEY (requester_id)
       REFERENCES `User`(username),
   FOREIGN KEY (owner)
       REFERENCES `User`(username),
   FOREIGN KEY (Incident_id)
       REFERENCES Incident(ID)
);

-- --------------------------------------------------------
--
-- Table structure for table 'Resource_Secondary_ESF'
--

DROP TABLE IF EXISTS Resource_Secondary_ESF;
CREATE TABLE IF NOT EXISTS Resource_Secondary_ESF(
  ESF_ID integer NOT NULL,
  Resource_ID integer NOT NULL,
  UNIQUE (ESF_ID, Resource_ID),
  FOREIGN KEY (ESF_ID)
       REFERENCES ESF(ID),
  FOREIGN KEY (Resource_ID)
       REFERENCES Resource(ID)
);

-- --------------------------------------------------------
--
-- Table structure for table 'Resource_Capabilities'
--

DROP TABLE IF EXISTS Resource_Capabilities;
CREATE TABLE IF NOT EXISTS Resource_Capabilities (
   resource_ID integer NOT NULL,
   capability varchar(50) NOT NULL,
   UNIQUE (resource_ID, capability),
   FOREIGN KEY (resource_ID)
       REFERENCES Resource(ID)
);


-- --------------------------------------------------------
--
-- Dumping test data for all tables
--

-- USERS
INSERT INTO User(username, name, password, type) VALUES('jsmith', 'John Smith', 'password1', 'I');
INSERT INTO Individual(username, job_title, hire_date) VALUES ('jsmith', 'Software Engineer','20120618');

INSERT INTO User(username, name, password, type) VALUES('delta', 'Delta Airlines', 'password2', 'C');
INSERT INTO Company(username, HQ_location) VALUES ('delta', 'Atlanta');

INSERT INTO User(username, name, password, type) VALUES('cityofatlanta', 'City of Atlanta', 'password3', 'M');
INSERT INTO Municipality(username, pop_size) VALUES ('cityofatlanta', '450000');

INSERT INTO User(username, name, password, type) VALUES('usarmy', 'U.S. Army', 'password4', 'G');
INSERT INTO Government_Agency(username, jurisdiction) VALUES ('usarmy', 'United States');

INSERT INTO User(username, name, password, type) VALUES('bcremins', 'Bobby Cremins', 'password5', 'I');
INSERT INTO Individual(username, job_title, hire_date) VALUES ('bcremins', 'Hardwood Engineer','19810414');

INSERT INTO User(username, name, password, type) VALUES('dduval', 'David Duval', 'password6', 'I');
INSERT INTO Individual(username, job_title, hire_date) VALUES ('dduval', 'Links Engineer','1996-09-01');

INSERT INTO User(username, name, password, type) VALUES('coke', 'Coca-Cola', 'password7', 'C');
INSERT INTO Company(username, HQ_location) VALUES ('coke', 'Atlanta');

INSERT INTO User(username, name, password, type) VALUES('soco', 'The Southern Company', 'password8', 'C');
INSERT INTO Company(username, HQ_location) VALUES ('soco', 'Atlanta');

INSERT INTO User(username, name, password, type) VALUES('townofmableton', 'Town of Mableton', 'password9', 'M');
INSERT INTO Municipality(username, pop_size) VALUES ('townofmableton', '37115');

INSERT INTO User(username, name, password, type) VALUES('cityofmarietta', 'City of Marietta', 'password10', 'M');
INSERT INTO Municipality(username, pop_size) VALUES ('cityofmarietta', '56579');

INSERT INTO User(username, name, password, type) VALUES('afcema', 'Atlanta-Fulton County Emergency Management Agency', 'password11', 'G');
INSERT INTO Government_Agency(username, jurisdiction) VALUES ('afcema', 'Fulton County, GA');

INSERT INTO User(username, name, password, type) VALUES('gemhs', 'Georgia Emergency Management & Homeland Security', 'password12', 'G');
INSERT INTO Government_Agency(username, jurisdiction) VALUES ('gemhs', 'State of Georgia');


-- ESF TABLE (from spec doc)
INSERT INTO ESF(ID, description) VALUES(1, 'Transportation');
INSERT INTO ESF(ID, description) VALUES(2, 'Communications');
INSERT INTO ESF(ID, description) VALUES(3, 'Public Works and Engineering');
INSERT INTO ESF(ID, description) VALUES(4, 'Firefighting');
INSERT INTO ESF(ID, description) VALUES(5, 'Emergency Management');
INSERT INTO ESF(ID, description) VALUES(6, 'Mass Care, Emergency Assistance, Housing, and Human Services');
INSERT INTO ESF(ID, description) VALUES(7, 'Logistics Management and Resource Support');
INSERT INTO ESF(ID, description) VALUES(8, 'Public Health and Medical Services');
INSERT INTO ESF(ID, description) VALUES(9, 'Search and Rescue');
INSERT INTO ESF(ID, description) VALUES(10, 'Oil and Hazardous Materials Response');
INSERT INTO ESF(ID, description) VALUES(11, 'Agriculture and Natural Resources');
INSERT INTO ESF(ID, description) VALUES(12, 'Energy');
INSERT INTO ESF(ID, description) VALUES(13, 'Public Safety and Security');
INSERT INTO ESF(ID, description) VALUES(14, 'Long-Term Community Recovery');
INSERT INTO ESF(ID, description) VALUES(15, 'External Affairs');


-- COST UNIT
INSERT INTO Cost_unit(time_unit) VALUES ('hour');
INSERT INTO Cost_unit(time_unit) VALUES ('day');
INSERT INTO Cost_unit(time_unit) VALUES ('week');


-- INCIDENTS
INSERT INTO Incident(date, description, latitude, longitude, owner) VALUES ('20161127', 'Flash Floods in Fulton County', 33.662379, -84.516327,'jsmith');
INSERT INTO Incident(date, description, latitude, longitude, owner) VALUES ('20161128', 'North GA Landslide', 34.297404, -83.819744,'usarmy');
INSERT INTO Incident(date, description, latitude, longitude, owner) VALUES ('20161129', 'Midtown Building Collapse', 33.789197, -84.384288,'delta');
INSERT INTO Incident(date, description, latitude, longitude, owner) VALUES ('20161128', 'Cat Stuck in Tree', 33.785404, -84.374516,'cityofatlanta');
INSERT INTO Incident(date, description, latitude, longitude, owner) VALUES ('20161125', 'Creepy Clowns on the Loose', 33.730994, -84.379675,'cityofatlanta');
INSERT INTO Incident(date, description, latitude, longitude, owner) VALUES ('20161128', 'Fire on East Lake Golf Club 1st Fairway', 33.743678, -84.302544,'coke');
INSERT INTO Incident(date, description, latitude, longitude, owner) VALUES ('20161127', 'Power Lines Down in Lions Park', 33.820468, -84.571315,'townofmableton');


-- RESOURCES
INSERT INTO Resource(name, model, latitude, longitude, primary_esf, cost_amount, cost_unit, owner, status)
  VALUES ('Hummer', '2015 Hummer', 33.564689, -84.576253, 1, 800, 'week', 'jsmith', 1);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (1, 9);
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (1, 'GPS');
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (1, 'OnBoard Computer');

INSERT INTO Resource(name, model, latitude, longitude, primary_esf, cost_amount, cost_unit, owner)
  VALUES ('Gasoline Generator', 'Honda EU2000i 1600 Watt', 33.564689, -84.576253, 8, 50, 'day', 'jsmith');
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (2, 5);
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (2, 'Generator');

INSERT INTO Resource(name, model, latitude, longitude, primary_esf, cost_amount, cost_unit, owner, status)
  VALUES ('Apache Helicopter', 'Boeing AH-64 Apache', 33.899537, -84.420021, 1, 50000, 'week', 'usarmy', 1);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (3, 9);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (3, 13);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (3, 8);
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (3, 'GPS');
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (3, 'First Aid');
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (3, 'Machine Gun');

INSERT INTO Resource(name, model, latitude, longitude, primary_esf, cost_amount, cost_unit, owner, status)
  VALUES ('500 Ton Crane', '2007 Liebherr LTM 1500', 33.747436, -84.393043, 3, 300, 'day', 'cityofatlanta', 1);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (4, 1);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (4, 5);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (4, 9);
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (4, 'GPS');

INSERT INTO Resource(name, model, latitude, longitude, primary_esf, cost_amount, cost_unit, owner)
  VALUES ('Cessna Airplane', 'Cessna 162 Skycatcher', 33.641392, -84.427715, 1, 2000, 'week', 'delta');
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (5, 4);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (5, 9);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (5, 13);
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (5, 'GPS');
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (5, 'First Aid');
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (5, 'Life Jackets');
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (5, 'Supply Drop');

INSERT INTO Resource(name, model, latitude, longitude, primary_esf, cost_amount, cost_unit, owner, status)
  VALUES ('Fire Truck', '2014 RBM Commander', 33.757329, -84.387551, 4, 3000, 'day', 'afcema', 0);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (6, 1);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (6, 6);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (6, 13);
INSERT INTO Resource_Capabilities (resource_id, capability) VALUES (6, '5,000 Gallon Capacity');

INSERT INTO Resource(name, model, latitude, longitude, primary_esf, cost_amount, cost_unit, owner)
  VALUES ('Stretcher', 'MOBI Pro X-Frame EMS Stretcher', 33.564689, -84.576253, 12, 5, 'day', 'jsmith');
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (7, 9);
INSERT INTO Resource_Secondary_ESF (resource_id, esf_id) VALUES (7, 13);


-- REQUESTS
INSERT INTO Request(resource_id, requester_id, start_date, end_date, incident_id, type, owner)
  VALUES(3,'jsmith','20161130', '20161201', 1, 1, 'usarmy');
INSERT INTO Request(resource_id, requester_id, start_date, end_date, incident_id, type, owner)
  VALUES(1,'delta','20161203', '20161222', 3, 1, 'jsmith');
INSERT INTO Request(resource_id, requester_id, start_date, end_date, incident_id, type, owner)
  VALUES(4,'jsmith','20161129', '20161224', 1, 2, 'cityofatlanta');
INSERT INTO Request(resource_id, requester_id, start_date, end_date, incident_id, type, owner)
  VALUES(5,'jsmith','20161130', '20161221', 1, 1, 'delta');
INSERT INTO Request(resource_id, requester_id, start_date, end_date, type, owner)
  VALUES(2,'jsmith','20161129', '20161204', 0, 'jsmith');
INSERT INTO Request(resource_id, requester_id, start_date, end_date, incident_id, type, owner)
  VALUES(2,'cityofatlanta','20161206', '20161216', 4, 1, 'jsmith');
INSERT INTO Request(resource_id, requester_id, start_date, end_date, incident_id, type, owner)
  VALUES(7,'afcema','20161203', '20161204', 2, 1, 'jsmith');





