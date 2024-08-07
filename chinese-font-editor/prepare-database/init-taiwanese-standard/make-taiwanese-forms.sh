#!/bin/bash
# This script downloads the Chart of Standard Forms of Common National
# Characters (常用國字標準字體表) and formats the data from it as
# SQL for inserting into SQLite3 database.
#
# Requires wget, pdftoppm, pdftotext, imagemagick, xxd
#
# It's extra-slow but should only be run once.


CHARTURL='https://language.moe.gov.tw/001/Upload/Files/site_content/download/mandr/%E6%95%99%E8%82%B2%E9%83%A84808%E5%80%8B%E5%B8%B8%E7%94%A8%E5%AD%97.pdf'
MINPAGE=2
MAXPAGE=119

TMPDIR='./taiwanese-standard-tmp'
PAGEDIR="$TMPDIR/img/page"
CHARDIR="$TMPDIR/img/char"
PDFPATH="$TMPDIR/chart.pdf"
TXTDIR="$TMPDIR/txt/"
PAGETXTDIR="$TXTDIR/pages/"
SPLITTXTDIR="$TXTDIR/splits/"
OUTFILE="../taiwanese_standard.sql"

mkdir -p "$TMPDIR"
mkdir -p "$PAGEDIR"
mkdir -p "$CHARDIR"
mkdir -p "$TXTDIR"
mkdir -p "$PAGETXTDIR"
mkdir -p "$SPLITTXTDIR"

if [ \! -f "$PDFPATH" ]; then
	wget "$CHARTURL" -O "$PDFPATH"
fi


for PAGE in $(seq $MINPAGE $MAXPAGE); do
	echo "Processing page $PAGE"
	PAGETXT="$PAGEDIR/$PAGE.txt"
	PAGECHARDIR="$CHARDIR/$PAGE/"
	PAGEIMG="$PAGEDIR/pg-$PAGE"
	THISSPLITSDIR="$SPLITTXTDIR/pg-$PAGE/"
	IMGS="$THISSPLITSDIR/imgs.txt"
	PAGESQLFILE="$THISSPLITSDIR/insert.sql"
	if [ -f "$PAGESQLFILE" ]; then
		echo "Skipping this page, because $PAGESQLFILE exists."
		continue
	fi
	mkdir -p "$PAGECHARDIR"
	mkdir -p "$THISSPLITSDIR"

	pdftotext -f $PAGE -l $PAGE -colspacing 0.3 "$PDFPATH" "$PAGETXT"
	csplit -f "$THISSPLITSDIR/split-" "$PAGETXT" "/^$/" "{*}"

	pdftoppm -f $PAGE -png -singlefile "$PDFPATH" "$PAGEIMG"

	sed -e "s/^/(0x/" -e "s/$/, /" "$THISSPLITSDIR/split-02" >"$THISSPLITSDIR/char_ids.txt"
	sed -e "s/^/'/" -e "s/$/', /" "$THISSPLITSDIR/split-01" >"$THISSPLITSDIR/codes.txt"

	echo "" > "$IMGS" #first line is empty (to match csplit output)
	FIRST_ITEM=0
	LAST_ITEM=40
	for I in $(seq $FIRST_ITEM $LAST_ITEM); do
		SEPARATOR=','
		if [ $I -eq $LAST_ITEM ]; then
			SEPARATOR=';'
		fi
		CHARIMG="$PAGECHARDIR/$I.png";
		convert -crop 30x37+605+$(expr 110 + $I \* 37) "$PAGEIMG.png" "$CHARIMG"
		xxd -ps -c0 "$CHARIMG" | sed -e "s/^/X'/" -e "s/$/')$SEPARATOR/" >>"$IMGS"
	done

	FIRST_LINE_TO_SHOW=2
	if [ $PAGE -eq 2 ]; then
		FIRST_LINE_TO_SHOW=3
	fi
	MAX_NUM_LINES_TO_SHOW=9999
	if [ $PAGE -eq 119 ]; then
		MAX_NUM_LINES_TO_SHOW=12
	fi
	echo $'INSERT INTO taiwanese_standard(char_id, code, image)\nVALUES' >"$PAGESQLFILE"
	paste -d' ' "$THISSPLITSDIR/char_ids.txt" "$THISSPLITSDIR/codes.txt" "$IMGS" \
		| tail -n +$FIRST_LINE_TO_SHOW \
		| head -n $MAX_NUM_LINES_TO_SHOW \
		>>"$PAGESQLFILE"
done


echo "Joining pages"
echo "-- Autogenerated file" >"$OUTFILE"
for PAGE in $(seq $MINPAGE $MAXPAGE); do
	PAGESQL="$SPLITTXTDIR/pg-$PAGE/insert.sql"
	cat "$PAGESQL">>"$OUTFILE"
done

#for I in $(seq 0 41); do convert -crop 30x37+605+$(expr 110 + $I \* 37) twforms-img--002.png sym/$I.png; done

#for I in $(seq 0 41); do convert -crop 30x37+605+$(expr 110 + $I \* 37) twforms-img--003.png sym2/$I.png; done


