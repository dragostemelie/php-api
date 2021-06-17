# Authentication API

Simple authentication, authorization and access control API written in PHP. It uses [php-jwt](https://github.com/firebase/php-jwt) with Bearer authentication support. Customizable to meet the requirements of any application.

Settings for token secret and db connection and are found inside **api/config.php**:

- $secret_key = "JWT_SECRET_KEY";
- $db_host = "my host";
- $db_name = "db name";
- $db_user = "user";
- $db_password = "pwd";

##### Requirements

- PHP 5.6.0+
- PDO (PHP Data Objects) extension (pdo)
- MySQL 5.5.3+ or MariaDB

###### &nbsp;

## Register user

---

#### Endpoint: **api/users/register/**

##### Request body:

###### &nbsp;

```
{
  "username": "User",
  "password": "Password",
  "first_name": "First name",
  "last_name": "Last name",
  "email": "E-mail"
}
```

##### Normal response:

`{ "message": "New user registered." }`

##### Error response:

`{ "error": "Some error." }`

## Login user

---

#### Endpoint: **api/users/login/**

##### Request body:

###### &nbsp;

```
{
  "username": "User",
  "password": "Password",
}
```

##### Normal response:

###### &nbsp;

```
{
  "message": "Successful login.",
  "token": "Token",
  "email": "E-mail",
  "expireAt": "dd-mm-yyyy"
}
```

##### Error response:

`{ "error": "Some error." }`

## List users

---

#### Endpoint: **api/users/**

##### Request header:

`{ "Authentication": "Bearer Token" }`

##### Normal response:

###### &nbsp;

```
[
    {
      "username": "User 1",
      "firstname": "First name 1",
      "lastname": "Last name 1",
      "avatar": "Link 1"
    },
    {
      "username": "User 2",
      "firstname": "First name 2",
      "lastname": "Last name 2",
      "avatar": "Link 2"
    },
    ...
]
```

##### Error response:

`{ "error": "Some error." }`

## Database

---

#### Table [**users**] structure:

###### &nbsp;

```
CREATE TABLE `users` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
   PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Table [**profiles**] structure:

###### &nbsp;

```
CREATE TABLE `profiles` (
  `profileID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `avatar` varchar(200) NOT NULL,
   PRIMARY KEY (`profileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### SQL Procedure [ **ADDUSER(user, pwd, fname, lname, email)** ]:

###### &nbsp;

```
DELIMITER $$
CREATE PROCEDURE `ADDUSER`(
        IN `USERNAME_VAR` VARCHAR(100),
        IN `PASSWORD_VAR` VARCHAR(100),
        IN `FIRSTNAME_VAR` VARCHAR(100),
        IN `LASTNAME_VAR` VARCHAR(100),
        IN `EMAIL_VAR` VARCHAR(100)
    ) NO SQL
BEGIN
    INSERT INTO  users (username, password)
        VALUES (USERNAME_VAR, PASSWORD_VAR);
    INSERT INTO profiles (userID, firstname, lastname, email)
        VALUES ( (SELECT MAX(userID) FROM users), FIRSTNAME_VAR, LASTNAME_VAR, EMAIL_VAR);
END$$
DELIMITER ;
```
