CREATE TABLE dada_settings (
list                             varchar(16),
setting                          varchar(64),
value                            text
);

CREATE TABLE "dada_subscribers" (
email_id                         serial,
email                            varchar(80),
list                             varchar(16),
list_type                        varchar(64),
list_status                      char(1)
);


CREATE TABLE dada_profiles (
	profile_id			         serial,
	email                        varchar(80) not null UNIQUE,
	password                     text,
	auth_code                    varchar(64),
	update_email_auth_code       varchar(64),
	update_email                 varchar(80),
	activated                    char(1)
);

CREATE TABLE dada_profile_fields (
	fields_id			         serial,
	email                        varchar(80) not null UNIQUE
);


CREATE TABLE dada_profile_fields_attributes ( 

attribute_id 				serial,
field                       varchar(80) UNIQUE,
label                       varchar(80),
fallback_value              text
-- I haven't made the following, but it seems like a pretty good idea... 
-- sql_col_type              text(16),
-- default                   mediumtext,
-- html_form_widget          varchar(320),
-- required                  char(1),
-- public                    char(1),
);



	

CREATE TABLE dada_archives (
list                          varchar(16),
archive_id                    varchar(32),
subject                       text,
message                       text,
format                        text,
raw_msg                       text
);

CREATE TABLE dada_bounce_scores (
id                            serial, 
email                         varchar(80),
list                          varchar(16),
score                         int4 
);


CREATE TABLE dada_sessions (
    id CHAR(32) NOT NULL PRIMARY KEY,
    a_session BYTEA NOT NULL
);


CREATE TABLE dada_clickthrough_urls (
url_id  serial,
redirect_id varchar(16), 
msg_id text, 
url text
);

CREATE TABLE dada_mass_mailing_event_log (
id serial,
list varchar(16),
timestamp TIMESTAMP DEFAULT NOW(),
remote_addr text, 
msg_id text, 
event text,
details text
); 

CREATE TABLE dada_clickthrough_url_log (
id serial,
list varchar(16),
timestamp TIMESTAMP DEFAULT NOW(),
remote_addr text,
msg_id text, 
url text
);

