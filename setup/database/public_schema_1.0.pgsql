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

SET search_path = "public", pg_catalog;

CREATE TABLE documents (
    document_id serial NOT NULL, 
    name character varying(100) NOT NULL,
    security_object_id integer NOT NULL, 
    security_object_reference_id bigint NOT NULL, 
    document bytea NOT NULL, 
    filename character varying(100) NOT NULL, 
    parent_document_id integer, 
    most_recent boolean NOT NULL DEFAULT 't', 
    version character varying(100), 
    attributes character varying(1000)[]
);

CREATE TABLE notes (
    note_id serial NOT NULL, 
    user_id integer NOT NULL,
    security_object_id integer NOT NULL, 
    security_object_reference_id bigint NOT NULL, 
    when_noted timestamp without time zone DEFAULT ('now'::text)::timestamp(0) with time zone NOT NULL, 
    title character varying(100) NOT NULL, 
    note text NOT NULL, 
    display boolean NOT NULL DEFAULT 't'
);


ALTER TABLE ONLY documents
    ADD CONSTRAINT documents_pkey PRIMARY KEY (document_id);

ALTER TABLE ONLY documents
    ADD CONSTRAINT fk_documents_security_object_id FOREIGN KEY (security_object_id) REFERENCES security.security_objects(security_object_id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY documents
    ADD CONSTRAINT fk_documents_parent_document_id FOREIGN KEY (parent_document_id) REFERENCES documents(document_id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY notes
    ADD CONSTRAINT notes_pkey PRIMARY KEY (note_id);

ALTER TABLE ONLY notes
    ADD CONSTRAINT fk_notes_user_id FOREIGN KEY (user_id) REFERENCES security.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY notes
    ADD CONSTRAINT fk_notes_security_object_id FOREIGN KEY (security_object_id) REFERENCES security.security_objects(security_object_id) ON UPDATE CASCADE ON DELETE CASCADE;
