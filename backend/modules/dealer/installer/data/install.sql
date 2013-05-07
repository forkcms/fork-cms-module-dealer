CREATE TABLE IF NOT EXISTS `dealer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The unique ID for this dealer.',
  `language` varchar(10) NOT NULL COMMENT 'The language of this dealer.',
  `meta_id` int(11) NOT NULL COMMENT 'The meta_id for indexing.',
  `extra_id` int(11) NOT NULL COMMENT 'The extra_id for the widgets.',
  `name` varchar(128) NOT NULL COMMENT 'The name of this dealer.',
  `street` varchar(255) NOT NULL COMMENT 'The street of this dealer.',
  `number` varchar(255) NOT NULL COMMENT 'The number of this dealer.',
  `zip` varchar(255) NOT NULL COMMENT 'The zip of this dealer.',
  `city` varchar(255) NOT NULL COMMENT 'The city of this dealer.',
  `country` varchar(255) NOT NULL COMMENT 'The country of this dealer.',
  `tel` varchar(255) DEFAULT NULL COMMENT 'The phone of this dealer.',
  `fax` varchar(255) DEFAULT NULL COMMENT 'The fax of this dealer.',
  `website` varchar(255) DEFAULT NULL COMMENT 'The site of this dealer.',
  `email` varchar(255) DEFAULT NULL COMMENT 'The email of this dealer.',
  `avatar` varchar(255) NOT NULL COMMENT 'The avatar of this dealer.',
  `hidden` enum('N','Y') NOT NULL COMMENT 'Whether this dealer is shown or not.',
  `lat` float NOT NULL COMMENT 'The latitude of this dealer.',
  `lng` float NOT NULL COMMENT 'The longitude of this dealer.',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `dealer_brands` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The unique ID for this brand.',
  `name` varchar(255) NOT NULL COMMENT 'The name for this brand.',
  `meta_id` int(11) NOT NULL COMMENT 'The meta_id for indexing.',
  `image` varchar(255) NOT NULL COMMENT 'The image filename for this brand.',
  `language` varchar(10) NOT NULL COMMENT 'The language for this brand.',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `dealer_index` (
  `dealer_id` int(11) NOT NULL COMMENT 'The dealer ID.',
  `brand_id` int(11) NOT NULL COMMENT 'The brand ID.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;