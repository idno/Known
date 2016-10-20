CREATE TABLE session_2016102001 SELECT * FROM session;
DROP TABLE session;
CREATE TABLE IF NOT EXISTS session (
    session_id varchar(255) NOT NULL,
    session_value text NOT NULL,
    session_lifetime integer NOT NULL,
    session_time integer NOT NULL,
    PRIMARY KEY (session_id)
);
INSERT INTO session SELECT session_id, session_value, 0, session_time FROM session_2016102001;
DROP TABLE session_2016102001;


DELETE FROM versions WHERE label = 'schema';
INSERT INTO versions VALUES('schema', '2016102001');
