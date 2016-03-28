/* license-start
 * 
 * Copyright (C) 2008 - 2015 Crispico Resonate, <http://www.crispico.com/>.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation version 3.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details, at <http://www.gnu.org/licenses/>.
 * 
 * license-end
 */

DROP TABLE IF EXISTS `resources`;
DROP TABLE IF EXISTS `boards`;
DROP TABLE IF EXISTS `rappgroups`;

CREATE TABLE `boards` (
  `ID` bigint(20) NOT NULL,
  `NAME` varchar(64) NOT NULL,
  `RAPPGROUP_ID` bigint(20) NOT NULL,
  `DOWNLOAD_KEY` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `rappgroups` (
  `ID` bigint(20) NOT NULL,
  `NAME` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `resources` (
  `ID` int(11) NOT NULL,
  `BOARD_ID` bigint(20) DEFAULT NULL,
  `RAPPGROUP_ID` bigint(20) NOT NULL,
  `DATA` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `boards`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `NAME` (`NAME`,`RAPPGROUP_ID`),
  ADD KEY `RAPPGROUP_ID` (`RAPPGROUP_ID`);


ALTER TABLE `rappgroups`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `NAME` (`NAME`);


ALTER TABLE `resources`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `BOARD_ID_2` (`BOARD_ID`,`RAPPGROUP_ID`),
  ADD KEY `RAPPGROUP_ID` (`RAPPGROUP_ID`),
  ADD KEY `BOARD_ID` (`BOARD_ID`) USING BTREE;


ALTER TABLE `boards`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `rappgroups`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `resources`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `boards`
  ADD CONSTRAINT `boards_ibfk_1` FOREIGN KEY (`RAPPGROUP_ID`) REFERENCES `rappgroups` (`ID`);


ALTER TABLE `resources`
  ADD CONSTRAINT `resources_ibfk_1` FOREIGN KEY (`RAPPGROUP_ID`) REFERENCES `rappgroups` (`ID`),
  ADD CONSTRAINT `resources_ibfk_2` FOREIGN KEY (`BOARD_ID`) REFERENCES `boards` (`ID`);

