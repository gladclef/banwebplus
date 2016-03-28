<?php

$a_basic_tables_structure = array(
	"access_log" => array(
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"username" =>              array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"initial_access" =>        array("type" => "DATETIME",     "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"accesses" => array(
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"name" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"level" =>                 array("type" => "INT",          "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"buglog" => array(
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"datetime" =>              array("type" => "DATETIME",     "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"deleted" =>               array("type" => "TINYINT",      "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"status" =>                array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"classes" => array(
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"crn" =>                   array("type" => "INT",          "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"year" =>                  array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => FALSE, "special" => ""),
		"semester" =>              array("type" => "VARCHAR(3)",   "indexed" => TRUE,  "isPrimaryKey" => FALSE, "special" => ""),
		"subject" =>               array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"course" =>                array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"campus" =>                array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"days" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"days_times_locations" =>  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"start_date" =>            array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"end_date" =>              array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"time" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"location" =>              array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"hours" =>                 array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"title" =>                 array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"instructor" =>            array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"seats" =>                 array("type" => "INT",          "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"limit" =>                 array("type" => "INT",          "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"enroll" =>                array("type" => "INT",          "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"parent_class" =>          array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"subclass_identifier" =>   array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"user_ids_with_access" =>  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"last_mod_time" =>         array("type" => "DATETIME",     "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
	),
	"feedback" => array( // same as buglog
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"datetime" =>              array("type" => "DATETIME",     "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"deleted" =>               array("type" => "TINYINT",      "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"status" =>                array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"generated_settings" => array(
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"user_id" =>               array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => FALSE, "special" => ""),
		"private_icalendar_key" => array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"students" => array(
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"disabled" =>              array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"username" =>              array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"email" =>                 array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"pass" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"semester_whitelist" => array(
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"user_id" =>               array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => FALSE, "special" => ""),
		"year" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"semester" =>              array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"json" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"semester_blacklist" => array( // same as semester_whitelist
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"user_id" =>               array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => FALSE, "special" => ""),
		"year" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"semester" =>              array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"json" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"semester_classes" => array( // same as semester_whitelist
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"user_id" =>               array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => FALSE, "special" => ""),
		"year" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"semester" =>              array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"json" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"subjects" => array(
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"abbr" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"title" =>                 array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"semester" =>              array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"year" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"tabs" => array(
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"_deleted" =>              array("type" => "TINYINT",      "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"order" =>                 array("type" => "INT",          "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"access" =>                array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"name" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	),
	"user_settings" => array(
		"id" =>                    array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => TRUE,  "special" => "AUTO_INCREMENT"),
		"user_id" =>               array("type" => "INT",          "indexed" => TRUE,  "isPrimaryKey" => FALSE, "special" => ""),
		"type" =>                  array("type" => "VARCHAR(255)", "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => ""),
		"share_schedule_with" =>   array("type" => "TEXT",         "indexed" => FALSE, "isPrimaryKey" => FALSE, "special" => "")
	)
);

?>