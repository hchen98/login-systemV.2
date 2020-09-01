-- SQL script for creating project db

-- basic db
CREATE TABLE  IF NOT EXISTS usr(
    usr_id int NOT NULL AUTO_INCREMENT,
    email varchar(225) NOT NULL,
    register_date datetime NOT NULL,
    verify int(2) NOT NULL,
    tok varchar(225) NOT NULL,
    passwrd varchar(225) NOT NULL,
    PRIMARY KEY(usr_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- tbl for resetting the password
CREATE TABLE IF NOT EXISTS rest_passwrd(
    log_id int NOT NULL AUTO_INCREMENT,
    email varchar(225) NOT NULL,
    when_ datetime NOT NULL,
    location_ip varchar(45) NOT NULL,
    temp_token varchar(225) NOT NULL,
    rest int(2) NOT NULL,
--     0 for user haven't reset the password, 1 for user rested the password
--     further details pls check the code comments
    PRIMARY KEY(log_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- optional tbl. Depends on the business model
CREATE TABLE IF NOT EXISTS adres(
    adres_id int NOT NULL AUTO_INCREMENT,
    usr_id int NOT NULL,
    email varchar(225) NOT NULL,
    adres_L1 varchar(225) NOT NULL,
    adres_L2 varchar(225),
--     address line two is optional since some ppl do not have that
    city varchar(45) NOT NULL,
    state_ varchar(45) NOT NULL,
    country varchar(225) NOT NULL,
    zip int(11) NOT NULL,
    tel int,
--     not everyone has a tell/cellphone
    PRIMARY KEY(adres_id, usr_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


