CREATE TABLE `locations` (
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `accuracy` int(11) DEFAULT NULL,
  `altitude` int(11) DEFAULT NULL,
  `battery_level` int(11) DEFAULT NULL,
  `heading` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `radius` int(11) DEFAULT NULL,
  `trig` varchar(1) DEFAULT NULL,
  `tracker_id` char(2) DEFAULT NULL,
  `epoch` int(11) DEFAULT NULL,
  `vertical_accuracy` int(11) DEFAULT NULL,
  `velocity` int(11) DEFAULT NULL,
  `pressure` decimal(9,6) DEFAULT NULL,
  `connection` varchar(1) DEFAULT NULL,
  `place_id` int(11) DEFAULT NULL,
  `osm_id` int(11) DEFAULT NULL,
  `display_name` text,
  `inregions` VARCHAR(1024) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE `steps` (
  `tst` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `from` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `to` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `steps` int(11) NOT NULL,
  `distance` decimal(20,10) DEFAULT NULL,
  `floorsdown` int(11) DEFAULT NULL,
  `floorsup` int(11) DEFAULT NULL,
  `tid` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `steps`
  ADD PRIMARY KEY (`tst`,`from`,`to`,`tid`);
