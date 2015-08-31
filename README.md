ActiveRecord Variation Extension for Yii 2
==========================================

This extension provides support for ActiveRecord relation role (table inheritance) composition.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/yii2tech/ar-role/v/stable.png)](https://packagist.org/packages/yii2tech/ar-role)
[![Total Downloads](https://poser.pugx.org/yii2tech/ar-role/downloads.png)](https://packagist.org/packages/yii2tech/ar-role)
[![Build Status](https://travis-ci.org/yii2tech/ar-role.svg?branch=master)](https://travis-ci.org/yii2tech/ar-role)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2tech/ar-role
```

or add

```json
"yii2tech/ar-role": "*"
```

to the require section of your composer.json.


Usage
-----

This extension provides support for ActiveRecord relation role composition, which is also known as table inheritance.

For example: assume we have a database for the University. There are students studying in the University and there are
instructors teaching the students. Student has a study group and scholarship information, while instructor has a rank
and salary. However, both student and instructor have name, address, phone number and so on. Thus we can split
their data in the three different tables:
 - 'Human' - stores common data
 - 'Student' - stores student special data and reference to the 'Human' record
 - 'Instructor' - stores instructor special data and reference to the 'Human' record

DDL for such solution may look like following:

```sql
CREATE TABLE `Human`
(
   `id` integer NOT NULL AUTO_INCREMENT,
   `name` varchar(64) NOT NULL,
   `address` varchar(64) NOT NULL,
   `phone` varchar(20) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE InnoDB;

CREATE TABLE `Student`
(
   `humanId` integer NOT NULL,
   `studyGroupId` integer NOT NULL,
   `hasScholarship` integer(1) NOT NULL,
    PRIMARY KEY (`humanId`)
    FOREIGN KEY (`humanId`) REFERENCES `Human` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
) ENGINE InnoDB;

CREATE TABLE `Instructor`
(
   `humanId` integer NOT NULL,
   `rankId` integer NOT NULL,
   `salary` integer NOT NULL,
    PRIMARY KEY (`humanId`)
    FOREIGN KEY (`humanId`) REFERENCES `Human` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
) ENGINE InnoDB;
```
