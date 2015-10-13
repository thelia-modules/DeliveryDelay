
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- product_delay
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `product_delay`;

CREATE TABLE `product_delay`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER NOT NULL,
    `delivery_delay_min` INTEGER,
    `delivery_delay_max` INTEGER,
    `restock_delay_min` INTEGER,
    `restock_delay_max` INTEGER,
    `delivery_date_start` VARCHAR(255),
    `delivery_type` VARCHAR(255),
    PRIMARY KEY (`id`),
    INDEX `FI_product_delay_product_id` (`product_id`),
    CONSTRAINT `fk_product_delay_product_id`
        FOREIGN KEY (`product_id`)
        REFERENCES `product` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- undeliverable_date
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `undeliverable_date`;

CREATE TABLE `undeliverable_date`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `date` VARCHAR(255),
    `active` INTEGER,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
