This folder contains the data to seed the initial database based on EasyRPG
fonts.

First, you need to create a SQLite3 database and run `init.sql`

After this, you need to create SQL for fonts via `generate_font_sql.rb`.
You might want to check this file to make sure you don't add things that you
don't want to see in your file. The generated file is saved in
`add_fonts.sql`. After this, run the generated SQL.

Example (for bash):

    ./generate_font_sql.rb
    sqlite3 db.sqlite3 <init.sql
    sqlite3 db.sqlite3 <add_fonts.sql
