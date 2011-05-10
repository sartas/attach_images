<?php if(!defined('DEBUG')) die;

/**
 * Frog CMS - Content Management Simplified. <http://www.madebyfrog.com>
 * Copyright (C) 2008 Philippe Archambault <philippe.archambault@gmail.com>
 * Copyright (C) 2008 Martijn van der Kleijn <martijn.niji@gmail.com>
 * Copyright (C) 2010 Maslakov Alexandr <jmas.ukraine@gmail.com>
 *
 * This file is part of Frog CMS.
 *
 * Frog CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Frog CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Frog CMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Frog CMS has made an exception to the GNU General Public License for plugins.
 * See exception.txt for details and the full text.
 */

/**
 * The Comment plugin provides an interface to enable adding and moderating page comments.
 *
 * @package frog
 * @subpackage plugin.comments
 *
 * @author Philippe Archambault <philippe.archambault@gmail.com>
 * @author Bebliuc George <bebliuc.george@gmail.com>
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @author Maslakov Alexandr <jmas.ukraine@gmail.com>
 * @version 1.2.0
 * @since Frog version 0.9.3
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Philippe Archambault, Bebliuc George & Martijn van der Kleijn, 2008
 */

 
 
/*
	Get connection
*/
$PDO = Record::getConnection();
$driver = strtolower($PDO->getAttribute(Record::ATTR_DRIVER_NAME));



/*
	Table structure for table: comment
*/
if( $driver == 'mysql' )
{
	$PDO->exec("CREATE TABLE ".TABLE_PREFIX."ai_image (
		id int(11) NOT NULL auto_increment,
		page_id int(11) NOT NULL,
		file_name varchar(256) NOT NULL,
		alternate varchar(256) NOT NULL,
		created_date timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8");
}
elseif( $driver == 'sqlite')
{
	// SQLite now supported
	$PDO->exec("CREATE TABLE ai_image (
		id INTEGER NOT NULL PRIMARY KEY,
		page_id int(11) ,
		file_name varchar(256),
		alternate varchar(256)
	)");
}
