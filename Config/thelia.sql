
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- Replayed on every activation and update: nothing here may drop or alter an existing table.

CREATE TABLE IF NOT EXISTS `delivery_delay_carrier`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `module_id` INTEGER NOT NULL,
    `preparation_days` INTEGER DEFAULT 0 NOT NULL,
    `transit_min_days` INTEGER DEFAULT 0 NOT NULL,
    `transit_max_days` INTEGER DEFAULT 0 NOT NULL,
    `cutoff_time` TIME,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `uniq_delivery_delay_carrier_module_id` (`module_id`),
    CONSTRAINT `fk_delivery_delay_carrier_module_id`
        FOREIGN KEY (`module_id`)
        REFERENCES `module` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `delivery_delay_product`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER NOT NULL,
    `availability_days` INTEGER,
    `restock_days` INTEGER,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `uniq_delivery_delay_product_product_id` (`product_id`),
    CONSTRAINT `fk_delivery_delay_product_product_id`
        FOREIGN KEY (`product_id`)
        REFERENCES `product` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `delivery_delay_order_estimate`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `order_id` INTEGER NOT NULL,
    `module_id` INTEGER,
    `earliest_date` DATE NOT NULL,
    `latest_date` DATE NOT NULL,
    `created_at` DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `uniq_delivery_delay_order_estimate_order_id` (`order_id`),
    CONSTRAINT `fk_delivery_delay_order_estimate_order_id`
        FOREIGN KEY (`order_id`)
        REFERENCES `order` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
