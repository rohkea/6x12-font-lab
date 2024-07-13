#!/usr/bin/env perl

# Tiny parser for ids.txt from cjkvi-ids

use v5.30;
use warnings;
use strict;
use utf8;

binmode(STDIN, ":utf8");
binmode(STDOUT, ":utf8");

# reads one char (which might be a hanzi or a decomposition) from a string
# returns this character and the rest of the string
# e.g. '⿲ab⿰xyrest' is split into '⿲ab⿰xy' and 'rest'
sub grab_character {
  my ($input_string) = @_;
  my $char = substr($input_string, 0, 1);
  my $basic_rest = substr($input_string, 1);
  
  if ($char =~ /\p{Blk=Ideographic Description Characters}/) {
    my ($first_arg, $rest_after_first) = grab_character($basic_rest);
    if ($char eq '⿾' || $char eq '⿿') {
      return ($char . $first_arg, $rest_after_first);
    }
    
    my ($second_arg, $rest_after_second) = grab_character($rest_after_first);
    if ($char ne '⿲' && $char ne '⿳') {
      return ($char . $first_arg . $second_arg, $rest_after_second);
    }
    my ($third_arg, $rest_after_third) = grab_character($rest_after_second);
    return ($char . $first_arg . $second_arg . $third_arg, $rest_after_third);
  }
  
  return ($char, $basic_rest);
}

sub to_number_if_possible {
	my ($char) = @_;
	if ($char eq '') {
		return 'NULL';
	}
	my $first = chr(ord($char));
	if ($first ne $char) {
		return 'NULL';
	}

	return ord($char);
}

# Returns reference to hash
sub parse_decomposition {
  my ($string) = @_;
  my $decomposition = $string;
  $decomposition =~ s/\s*$//;
  my $variation = '';
  
  if ($string =~ /(.*)\[(.*)\]/) {
  	$decomposition = $1;
  	$variation = $2;
  }
  
  my $split_type = substr($decomposition, 0, 1);
  if (!($split_type =~ /\p{Blk=Ideographic Description Characters}/)) {
	  my $equivalent_char = $split_type;
	  return {
		  type => '=',
		  first => to_number_if_possible($equivalent_char),
		  second => 'NULL',
		  decomposition => $decomposition,
		  variation => $variation
	  };
  }

  my $first_and_rest = substr($decomposition, 1);
  my ($first, $second_and_rest) = grab_character($first_and_rest);
  my ($second, $rest) = grab_character($second_and_rest);
  return {
	  type => $split_type,
	  first => to_number_if_possible($first),
	  second => to_number_if_possible($second),
	  decomposition => $decomposition,
	  variation => $variation
  };
}

sub make_sql {
	my ($code, @decompositions) = @_;
	if (scalar(@decompositions) == 0) {
		my $char = chr($code);
		return "-- skipped $code ($char)\n";
	}
	my @decomposition_sqls = ();
	for my $d (@decompositions) {
		my $type = %$d{'type'}; #TODO find better way
		my $first = %$d{'first'};
		my $second = %$d{'second'};
		my $decomposition = %$d{'decomposition'};
		my $variation = %$d{'variation'};
		my $dec_sql = "($code, '$type', $first, $second, '$decomposition', '$variation')";
		push @decomposition_sqls, $dec_sql;
	}
	my $all_dec_sqls = join(",\n", @decomposition_sqls);
	
	my $sql = <<END
INSERT INTO decompositions(char_code, type, first_code, second_code, decomposition, variation)
VALUES
$all_dec_sqls;

END
;
	return $sql;
}

while (<>) {
	if (/^#/) {
		s/^#/--/;
		print;
	} else {
		my ($code_with_uplus, $symbol, @decomposition_texts) = split /\t/;
		my @decompositions = ();
		for my $decomposition_text (@decomposition_texts) {
			my $decomposition = parse_decomposition($decomposition_text);
			push @decompositions, $decomposition;
		}

		print make_sql(ord($symbol), @decompositions);
	}

}
