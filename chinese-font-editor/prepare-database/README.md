This folder contains the data to seed the initial database based on EasyRPG
fonts.

First, you need to create a SQLite3 database and run `init.sql`

After this, you need to create SQL for fonts via `generate_font_sql.rb`.
You might want to check this file to make sure you don't add things that you
don't want to see in your file. The generated file is saved in
`add_fonts.sql`. After this, run the generated SQL.

If you want to display forms from the Taiwanese standard, run
`init-taiwanese-standard/generate_taiwanese_standard.sh`
and then run the result. This script is slow and optional.
It stores images from the official PDF of the Taiwanese Ministry of Education
showing the standard forms, for reference.

If you want to show similar characters, you need to install decomposition
database, `cjkvi-ids`. It's installed by doing `git submodule init`, and
after this, you need to run the perl script to convert it into SQL,
and run that SQL.

Example (for bash):

    ./generate_font_sql.rb
    sqlite3 db.sqlite3 <init.sql
    sqlite3 db.sqlite3 <add_fonts.sql

    # for Taiwanese standard images
    init-taiwanese-standard/generate_taiwanese_standard.sh
    sqlite3 db.sqlite3 <taiwanese_standard.sql

    # for decompositions
    git submodule init
    init-decompositions/generate_decomposition_sql.pl \
        < init-decompositions/cjkvi-ids/ids.txt \
        > decompositions.sql
    sqlite3 db.sqlite3 <decompositions.sql


