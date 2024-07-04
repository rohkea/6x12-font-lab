CREATE TABLE fonts(
  id INTEGER PRIMARY KEY,
  code TEXT,
  name TEXT,
  frozen INTEGER(1)
);

CREATE TABLE glyphs(
  id INTEGER PRIMARY KEY,
  char_code INTEGER,
  font_id INTEGER,
  added_at INTEGER,
  adder_ip TEXT,
  verified INTEGER(1),
  is_active TINYINT,
  is_fullwidth TINYINT,
  data BLOB
);
CREATE INDEX glyphs_char_id_idx ON glyphs(char_code);

--  DEFAULT unixepoch('now')

CREATE TABLE taiwanese_standard(
  char_id INTEGER PRIMARY KEY,
  code TEXT,
  image BLOB
);
