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
 * @version $Revision: 1.6 $ $Date: 2004/06/03 08:05:06 $
 * @package phpBaseClasses
 * @link http://www.toolshed51.com/
 * @copyright 2002-2003 Russell Chadwick
 * @author Russell Chadwick <toolshed@toolshed51.com>
 */

CREATE SCHEMA "security";
SET search_path = "security", pg_catalog;

CREATE TABLE roles (
    role_id serial NOT NULL,
    title character varying(50) NOT NULL
);

CREATE TABLE permission_types (
    permission_type_id serial NOT NULL,
    "type" character varying(50) NOT NULL
);

CREATE TABLE security_objects (
    security_object_id serial NOT NULL,
    name character varying(50) NOT NULL,
    foreign_table character varying(50) NOT NULL,
    foreign_id_column character varying(50) NOT NULL,
    foreign_name_column character varying(50) NOT NULL,
    display boolean DEFAULT true NOT NULL
);

CREATE TABLE users (
    user_id serial NOT NULL,
    username character varying(50) NOT NULL,
    "password" character varying(50) NOT NULL,
    first_name character varying(50) NOT NULL,
    last_name character varying(50) NOT NULL,
    email_address character varying(100) NOT NULL,
    last_login timestamp with time zone,
    last_login_from inet,
    enabled boolean DEFAULT false NOT NULL
);

CREATE TABLE users2roles (
    users2roles_id serial NOT NULL,
    user_id integer NOT NULL,
    role_id integer NOT NULL,
    security_object_id integer,
    security_object_reference_id bigint
);

CREATE TABLE users2permissions (
    users2permissions_id serial NOT NULL,
    user_id integer NOT NULL,
    permission_type_id integer NOT NULL,
    security_object_id integer NOT NULL,
    security_object_reference_id bigint
);

CREATE TABLE security_logs (
    security_log_id bigserial NOT NULL,
    user_id integer NOT NULL,
    security_object_id integer NOT NULL,
    security_object_reference_id bigint NOT NULL,
    "action" character(1) NOT NULL,
    datetime timestamp with time zone DEFAULT ('now'::text)::timestamp(0) with time zone NOT NULL,
    description character varying(1000)
);

CREATE TABLE user_preferences (
    user_preference_id serial NOT NULL,
    user_id integer NOT NULL,
    "key" character varying(50) NOT NULL,
    value character varying(4000)
);

CREATE TABLE mail_logs (
    mail_log_id bigserial NOT NULL,
    user_id integer NOT NULL,
    when_mailed timestamp without time zone DEFAULT ('now'::text)::timestamp(0) with time zone NOT NULL,
    "template" character varying(50) NOT NULL
);

CREATE TABLE roles2roles_inheritances (
    roles2roles_inheritance_id serial NOT NULL,
    role_id integer NOT NULL,
    inherited_role_id integer NOT NULL,
    security_object_id integer,
    security_object_reference_id bigint
);

CREATE TABLE roles2permissions_inheritances (
    roles2permissions_inheritance_id serial NOT NULL,
    role_id integer NOT NULL,
    permission_type_id integer NOT NULL,
    security_object_id integer NOT NULL,
    security_object_reference_id bigint
);

CREATE TABLE notifications (
    notification_id serial NOT NULL, 
    user_id integer NOT NULL, 
    sent_by integer, 
    when_sent timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone NOT NULL, 
    message character varying(100) NOT NULL, 
    link_type character varying(10),
    link character varying(200) 
);

CREATE TABLE internal_messages (
    internal_message_id serial NOT NULL, 
    user_id integer NOT NULL, 
    sent_by integer, 
    when_sent timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone NOT NULL, 
    when_viewed timestamp with time zone, 
    subject character varying (100) NOT NULL, 
    body character varying (1000) NOT NULL
);

CREATE TABLE subscription_lists (
    subscription_list_id serial NOT NULL, 
    name character varying(100) NOT NULL
);

CREATE TABLE subscription_list_members (
    subscription_list_id integer NOT NULL, 
    user_id integer NOT NULL
);

CREATE TABLE locks (
    lock_id serial NOT NULL, 
    user_id integer NOT NULL, 
    security_object_id integer NOT NULL,
    security_object_reference_id bigint, 
    when_locked timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone NOT NULL
);

CREATE TABLE one_use_tokens (
    one_use_token_id serial NOT NULL, 
    user_id integer NOT NULL, 
    token character varying(32) NOT NULL, 
    when_created timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone NOT NULL
);


COPY roles (role_id, title) FROM stdin;
1	Uber Admin
2	Admin
\.

COPY permission_types (permission_type_id, "type") FROM stdin;
1	Read
2	Modify
3	Delete
\.

COPY security_objects (security_object_id, name, foreign_table, foreign_id_column, foreign_name_column, display) FROM stdin;
1	Users	security.users	user_id	username	t
\.

COPY users (user_id, username, "password", first_name, last_name, email_address, last_login, last_login_from, enabled) FROM stdin;
1	install	19ad89bc3e3c9d7ef68b89523eff1987	Install	Install	install@domain.com	2000-01-01 04:00:00-08	127.0.0.1	t
\.

COPY users2roles (users2roles_id, user_id, role_id, security_object_id, security_object_reference_id) FROM stdin;
1	1	1	\N	\N
\.

COPY roles2roles_inheritances (roles2roles_inheritance_id, role_id, inherited_role_id, security_object_id, security_object_reference_id) FROM stdin;
1	1	2	\N	\N
\.


ALTER TABLE ONLY roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (role_id);

ALTER TABLE ONLY permission_types
    ADD CONSTRAINT permission_types_pkey PRIMARY KEY (permission_type_id);

ALTER TABLE ONLY security_objects
    ADD CONSTRAINT security_objects_pkey PRIMARY KEY (security_object_id);

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);

ALTER TABLE ONLY users2roles
    ADD CONSTRAINT users2roles_pkey PRIMARY KEY (users2roles_id);

ALTER TABLE ONLY users2permissions
    ADD CONSTRAINT users2permissions_pkey PRIMARY KEY (users2permissions_id);

ALTER TABLE ONLY security_logs
    ADD CONSTRAINT security_logs_pkey PRIMARY KEY (security_log_id);

ALTER TABLE ONLY user_preferences
    ADD CONSTRAINT user_preferences_pkey PRIMARY KEY (user_preference_id);

ALTER TABLE ONLY mail_logs
    ADD CONSTRAINT mail_logs_pkey PRIMARY KEY (mail_log_id);

ALTER TABLE ONLY roles2roles_inheritances
    ADD CONSTRAINT roles2roles_inheritances_pkey PRIMARY KEY (roles2roles_inheritance_id);

ALTER TABLE ONLY roles2permissions_inheritances
    ADD CONSTRAINT roles2permissions_inheritances_pkey PRIMARY KEY (roles2permissions_inheritance_id);

ALTER TABLE ONLY notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (notification_id);

ALTER TABLE ONLY internal_messages
    ADD CONSTRAINT internal_messages_pkey PRIMARY KEY (internal_message_id);

ALTER TABLE ONLY subscription_lists
    ADD CONSTRAINT subscription_lists_pkey PRIMARY KEY (subscription_list_id);

ALTER TABLE ONLY locks
    ADD CONSTRAINT locks_pkey PRIMARY KEY (lock_id);

ALTER TABLE ONLY one_use_tokens 
    ADD CONSTRAINT one_use_tokens_pkey PRIMARY KEY (one_use_token_id);


ALTER TABLE ONLY security_objects
    ADD CONSTRAINT uk_security_objects_name UNIQUE (name);

ALTER TABLE ONLY users
    ADD CONSTRAINT uk_users_username UNIQUE (username);

ALTER TABLE ONLY roles
    ADD CONSTRAINT uk_roles_title UNIQUE (title);

ALTER TABLE ONLY permission_types
    ADD CONSTRAINT uk_permission_types_type UNIQUE ("type");

ALTER TABLE ONLY users
    ADD CONSTRAINT uk_users_first_name_last_name UNIQUE (first_name, last_name);

ALTER TABLE ONLY user_preferences
    ADD CONSTRAINT uk_user_preferences_user_id_key UNIQUE (user_id, "key");

ALTER TABLE ONLY subscription_list_members
    ADD CONSTRAINT uk_subscription_list_members_subscription_list_id_user_id UNIQUE (subscription_list_id, user_id);

ALTER TABLE ONLY one_use_tokens
    ADD CONSTRAINT uk_one_use_tokens_user_id_token UNIQUE (user_id, token);


ALTER TABLE ONLY roles2permissions_inheritances
    ADD CONSTRAINT uk_roles2permissions_inheritances_role_id_permission_type_id_se UNIQUE (role_id, permission_type_id, security_object_id, security_object_reference_id);

ALTER TABLE ONLY roles2roles_inheritances
    ADD CONSTRAINT uk_roles2roles_inheritances_role_id_inherited_role_id_security_ UNIQUE (role_id, inherited_role_id, security_object_id, security_object_reference_id);

ALTER TABLE ONLY users2permissions
    ADD CONSTRAINT uk_users2permissions_user_id_permission_type_id_security_object UNIQUE (user_id, permission_type_id, security_object_id, security_object_reference_id);

ALTER TABLE ONLY users2roles
    ADD CONSTRAINT uk_users2roles_user_id_role_id_security_object_id_security_obje UNIQUE (user_id, role_id, security_object_id, security_object_reference_id);

ALTER TABLE ONLY users2roles
    ADD CONSTRAINT fk_users2roles_role_id FOREIGN KEY (role_id) REFERENCES roles(role_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY users2roles
    ADD CONSTRAINT fk_users2roles_security_object_id FOREIGN KEY (security_object_id) REFERENCES security_objects(security_object_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY users2roles
    ADD CONSTRAINT fk_users2roles_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY users2permissions
    ADD CONSTRAINT fk_users2permissions_permission_type_id FOREIGN KEY (permission_type_id) REFERENCES permission_types(permission_type_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY users2permissions
    ADD CONSTRAINT fk_users2permissions_security_object_id FOREIGN KEY (security_object_id) REFERENCES security_objects(security_object_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY users2permissions
    ADD CONSTRAINT fk_users2permissions_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY security_logs
    ADD CONSTRAINT fk_security_logs_security_object_id FOREIGN KEY (security_object_id) REFERENCES security_objects(security_object_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY security_logs
    ADD CONSTRAINT fk_security_logs_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY user_preferences
    ADD CONSTRAINT fk_user_preferences_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY mail_logs
    ADD CONSTRAINT fk_mail_logs_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY roles2roles_inheritances
    ADD CONSTRAINT fk_roles2roles_inheritances_role_id FOREIGN KEY (role_id) REFERENCES roles(role_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY roles2roles_inheritances
    ADD CONSTRAINT fk_roles2roles_inheritances_inherited_role_id FOREIGN KEY (inherited_role_id) REFERENCES roles(role_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY roles2roles_inheritances
    ADD CONSTRAINT fk_roles2roles_inheritances_security_object_id FOREIGN KEY (security_object_id) REFERENCES security_objects(security_object_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY roles2permissions_inheritances
    ADD CONSTRAINT fk_roles2permissions_inheritances_role_id FOREIGN KEY (role_id) REFERENCES roles(role_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY roles2permissions_inheritances
    ADD CONSTRAINT fk_roles2permissions_inheritances_permission_type_id FOREIGN KEY (permission_type_id) REFERENCES permission_types(permission_type_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY roles2permissions_inheritances
    ADD CONSTRAINT fk_roles2permissions_inheritances_security_object_id FOREIGN KEY (security_object_id) REFERENCES security_objects(security_object_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY notifications 
    ADD CONSTRAINT fk_notifications_user_id FOREIGN KEY (user_id) REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY notifications 
    ADD CONSTRAINT fk_notifications_sent_by FOREIGN KEY (sent_by) REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY internal_messages
    ADD CONSTRAINT fk_internal_messages_user_id FOREIGN KEY (user_id) REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY internal_messages
    ADD CONSTRAINT fk_internal_messages_sent_by FOREIGN KEY (sent_by) REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY subscription_list_members
    ADD CONSTRAINT fk_subscription_list_members_subscription_list_id FOREIGN KEY (subscription_list_id) REFERENCES subscription_lists (subscription_list_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY subscription_list_members
    ADD CONSTRAINT fk_subscription_list_members_user_id FOREIGN KEY (user_id) REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY locks
    ADD CONSTRAINT fk_locks_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY locks
    ADD CONSTRAINT fk_locks_security_object_id FOREIGN KEY (security_object_id) REFERENCES security_objects(security_object_id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE ONLY one_use_tokens 
    ADD CONSTRAINT fk_one_use_tokens_user_id FOREIGN KEY (user_id) REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


SELECT pg_catalog.setval ('roles_role_id_seq', 2, true);

SELECT pg_catalog.setval ('permission_types_permission_type_id_seq', 3, true);

SELECT pg_catalog.setval ('security_objects_security_object_id_seq', 1, true);

SELECT pg_catalog.setval ('users_user_id_seq', 1, true);

SELECT pg_catalog.setval ('users2roles_users2roles_id_seq', 1, true);

SELECT pg_catalog.setval ('roles2roles_inheritances_roles2roles_inheritance_id_seq', 1, true);
