CREATE TABLE `users` (
  `id` int(12)  NOT NULL AUTO_INCREMENT,
  `username` varchar(255)  NOT NULL,
  `password` varchar(40)  NOT NULL,
  `created` datetime  NOT NULL,
  `modified` datetime  NOT NULL,
  PRIMARY KEY (`id`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;