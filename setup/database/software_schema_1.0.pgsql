/*
 * $RCSfile
 *
 * phpBaseClasses - Foundation for any application in php
 * Copyright (C) 2002-2003 Russell Chadwick
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * @version $Revision: 1.1 $ $Date: 2004/06/03 07:58:19 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

CREATE SCHEMA "software";
SET search_path = "software", pg_catalog;

CREATE TABLE process_schedules (
    process_schedule_id serial NOT NULL, 
    path character varying(200) NOT NULL, 
    currently_running boolean DEFAULT false NOT NULL, 
    monitor boolean DEFAULT false NOT NULL, 
    minute character varying(5), 
    hour character varying(5), 
    day character varying(5), 
    month character varying(5), 
    day_of_week character varying(4), 
    interval interval, 
    only_once timestamp(0) without time zone 
);

CREATE TABLE process_schedule_logs (
    process_schedule_log_id serial NOT NULL, 
    process_schedule_id integer NOT NULL, 
    when_run timestamp(0) without time zone, 
    duration interval, 
    output text
);

CREATE TABLE statistic_categories (
    statistic_category_id serial NOT NULL, 
    name character varying(100) NOT NULL
);

CREATE TABLE statistics (
    statistic_id serial NOT NULL, 
    statistic_category_id integer NOT NULL, 
    when_logged timestamp without time zone DEFAULT ('now'::text)::timestamp(0) without time zone NOT NULL, 
    data integer DEFAULT 1 NOT NULL
);


COPY statistic_categories (statistic_category_id, name) FROM stdin;
1	Login
\.


ALTER TABLE ONLY process_schedules
    ADD CONSTRAINT process_schedules_pkey PRIMARY KEY (process_schedule_id);

ALTER TABLE ONLY process_schedule_logs
    ADD CONSTRAINT process_schedule_logs_pkey PRIMARY KEY (process_schedule_log_id);

ALTER TABLE ONLY statistic_categories
    ADD CONSTRAINT statistic_categories_pkey PRIMARY KEY (statistic_category_id);

ALTER TABLE ONLY statistics
    ADD CONSTRAINT statistics_pkey PRIMARY KEY (statistic_id);


ALTER TABLE ONLY process_schedule_logs
    ADD CONSTRAINT fk_process_schedule_logs_process_schedule_id FOREIGN KEY (process_schedule_id) REFERENCES process_schedules(process_schedule_id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY statistics
    ADD CONSTRAINT fk_statistics_statistic_category_id FOREIGN KEY (statistic_category_id) REFERENCES statistic_categories(statistic_category_id) ON UPDATE CASCADE ON DELETE CASCADE;


SELECT pg_catalog.setval ('statistic_categories_statistic_category_id_seq', 1, true);
