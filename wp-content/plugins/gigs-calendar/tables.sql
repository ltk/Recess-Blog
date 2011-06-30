-- Database tables for the Gigs Calendar Plugin v0.2
-- Database version: 2

-- Note: Ignore this file unless your database tables are not created automatically.
-- Replace "[Your database prefix here]" with the appropriate database prefix, for example "wp_".

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `[Your database prefix here]gigs_gig` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `venueID` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `notes` text NOT NULL,
  `postID` bigint(20) unsigned NOT NULL,
  `eventName` varchar(255) NOT NULL,
  `tour_id` INT UNSIGNED NULL,
  PRIMARY KEY  (`id`)
);

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `[Your database prefix here]gigs_performance` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `gigID` int(10) unsigned NOT NULL,
  `time` time default NULL,
  `link` varchar(255) NOT NULL,
  `shortNotes` varchar(255) NOT NULL,
  `ages` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
);

-- --------------------------------------------------------

CREATE TABLE `[Your database prefix here]gigs_tour` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `name` VARCHAR( 255 ) NOT NULL ,
  `notes` MEDIUMTEXT NOT NULL ,
  `pos` INT UNSIGNED NOT NULL
);
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `[Your database prefix here]gigs_venue` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `postalCode` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `private` tinyint(4) NOT NULL,
  `apiID` int(10) unsigned NOT NULL,
  `deleted` tinyint(4) NOT NULL,
  `customMap` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
);
