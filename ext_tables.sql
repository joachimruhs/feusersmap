CREATE TABLE tx_feusersmap_domain_model_users (
    latitude decimal(10,8) default NULL,
    longitude decimal(11,8) default NULL,
	mapicon varchar(255) NOT NULL DEFAULT '',
	mapgeocode smallint(1) unsigned NOT NULL DEFAULT '0'
);


#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (

	latitude varchar(255) DEFAULT '' NOT NULL,
	longitude varchar(255) DEFAULT '' NOT NULL,
	leafletmapicon varchar(255) DEFAULT '' NOT NULL,
	mapgeocode int(4) DEFAULT '1' NOT NULL,

	tx_extbase_type varchar(255) DEFAULT '' NOT NULL,

);