#!/usr/bin/perl

use 5.005;
use strict;
use Config;
use Getopt::Long;
use Pod::Tree::PerlBin;
use Pod::Tree::PerlDist;
use Pod::Tree::PerlFunc;
use Pod::Tree::PerlLib;
use Pod::Tree::PerlMap;
use Pod::Tree::PerlPod;
use Pod::Tree::PerlTop;


#######################################################################

my %Opts;
$Opts{toc} = 1;
my $ok = GetOptions(\%Opts, 
		    "v:i",
		    "toc!", "hr:i", 
		    "css:s");

$ok or die "Bad command line options\n";

my($Perl_Dir, $HTML_Dir) = @ARGV;
$HTML_Dir or die "perl2chm Perl_Dir HTML_Dir\n";

$Perl_Dir =~ s( /$ )()x;
$HTML_Dir =~ s( /$ )()x;

$| = 1;	      
umask 0022;
-d $HTML_Dir or mkdir $HTML_Dir, 0777 or die "Can't mkdir $HTML_Dir: $!\n";

chomp (my $pwd = `pwd`);

my $Perl_Map  = new Pod::Tree::PerlMap;
my $Perl_Bin  = new Pod::Tree::PerlBin  $Perl_Dir, $HTML_Dir, $Perl_Map, %Opts;
my $Perl_Dist = new Pod::Tree::PerlDist $Perl_Dir, $HTML_Dir, $Perl_Map, %Opts;
my $Perl_Func = new Pod::Tree::PerlFunc $Perl_Dir, $HTML_Dir, $Perl_Map, %Opts;
my $Perl_Lib  = new Pod::Tree::PerlLib  $Perl_Dir, $HTML_Dir, $Perl_Map, %Opts;
my $Perl_Pod  = new Pod::Tree::PerlPod  $Perl_Dir, $HTML_Dir, $Perl_Map, %Opts;
my $Perl_Top  = new Pod::Tree::PerlTop  $Perl_Dir, $HTML_Dir, $Perl_Map, %Opts;

$Perl_Pod ->scan;
# FIXME: we don't want all perl binaries in the path...
$Perl_Bin ->scan(split /$Config{path_sep}/, $ENV{PATH});
$Perl_Dist->scan;
$Perl_Func->scan;
$Perl_Lib ->scan(("$pwd/$Perl_Dir/lib",));

$Perl_Bin ->index;
$Perl_Dist->index;
$Perl_Func->index;
$Perl_Lib ->index;
$Perl_Pod ->index;


# Project file
open(HHP, "> $HTML_Dir/perl.hhp") || die;
print HHP 
"[OPTIONS]
Compiled file=perl.chm
Contents file=perl.hhc
Default Window=Main
Default topic=pod/perl.html
Full-text search=Yes
Index file=perl.hhk
Language=0x409 English (United States)
Title=Perl Documentation

[WINDOWS]
Main=,\"perl.hhc\",\"perl.hhk\",\"pod/perl.html\",\"pod/perl.html\",,,,,0x22520,,0x384e,,,,,,,,0

[FILES]
";


# Contents file
my $HHC;
open($HHC, "> $HTML_Dir/perl.hhc") || die;
sitemap_start($HHC);
sitemap_list_start($HHC);
my $entry;

$entry = $Perl_Pod->get_top_entry;
sitemap_list_item($HHC, $entry->{description}, $entry->{URL});
sitemap_list_start($HHC);
my $pods = $Perl_Pod->{pods};
for my $name (sort keys %$pods)
{
	sitemap_list_item($HHC, $name . ' - ' . $pods->{$name}{desc}, $name . '.html');
}
sitemap_list_end($HHC);

$entry = $Perl_Lib->get_top_entry;
sitemap_list_item($HHC, $entry->{description}, $entry->{URL});
sitemap_list_start($HHC);
my $lib_dir   = $Perl_Lib->{lib_dir};
my $index     = $Perl_Lib->{index};
for my $name (sort keys %$index)
{
	sitemap_list_item($HHC, $name, $lib_dir . '/' .  $index->{$name}->{href});
}
sitemap_list_end($HHC);

$entry = $Perl_Bin->get_top_entry;
sitemap_list_item($HHC, $entry->{description}, $entry->{URL});
sitemap_list_start($HHC);
my $bin_dir   = $Perl_Bin->{bin_dir};
my $index     = $Perl_Bin->{index};
for my $name (sort keys %$index)
{
	sitemap_list_item($HHC, $name, $bin_dir . '/' .  $index->{$name}->{file} . '.html');
}
sitemap_list_end($HHC);

$entry = $Perl_Dist->get_top_entry;
sitemap_list_item($HHC, $entry->{description}, $entry->{URL});
sitemap_list_start($HHC);
my $index     = $Perl_Dist->{index};
for my $name (sort keys %$index)
{
	sitemap_list_item($HHC, $name, $name . '.html');
}
sitemap_list_end($HHC);
sitemap_list_end($HHC);
sitemap_end($HHC);


# Index file
my $HHK;
open($HHK, "> $HTML_Dir/perl.hhk") || die;
sitemap_start($HHK);
sitemap_list_start($HHK);
my $pages = $Perl_Map->{page};
for my $name (sort keys %$pages)
{
	sitemap_list_item($HHK, $name, $pages->{$name} . '.html');
}
my $funcs = $Perl_Map->{func};
for my $name (sort keys %$funcs)
{
	sitemap_list_item($HHK, $name, 'pod/func/' . $funcs->{$name} . '.html');
}
sitemap_list_end($HHK);
sitemap_end($HHK);


# HTML files
$Perl_Bin ->translate;
$Perl_Dist->translate;
$Perl_Func->translate;
$Perl_Lib ->translate;
$Perl_Pod ->translate;
$Perl_Top ->translate;


#######################################################################
# HTML Help Sitemap utility functions

sub sitemap_start
{
	my $FH = shift;

	print $FH "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML//EN\">\n";
	print $FH "<HTML>\n";
	print $FH "<BODY>\n";

}

sub sitemap_list_start
{
	my $FH = shift;

	print $FH "<UL>\n";
}

sub html_escape
{
   my $text = shift;

   $text =~ s/^\s+//;
   $text =~ s/\s+$//;
   
   $text =~ s/&/&amp;/g;
   $text =~ s/</&lt;/g;
   $text =~ s/>/&gt;/g;
   $text =~ s/\"/&quot;/g;

   return $text;
}

sub sitemap_list_item
{
	my $FH = shift;
	my $name = html_escape(shift);
	my $link = html_escape(shift);

	print $FH "<LI> <OBJECT type=\"text/sitemap\"> <param name=\"Name\" value=\"$name\"> <param name=\"Local\" value=\"$link\"> </OBJECT>\n";
}

sub sitemap_list_end
{
	my $FH = shift;

	print $FH "</UL>\n";
}

sub sitemap_end
{
	my $FH = shift;

	print $FH "</BODY>\n";
	print $FH "</HTML>\n";
}


########################################################################
# Documentation

__END__

=head1 NAME

perl2chm - generate Perl documentation in CHM

=head1 SYNOPSIS

B<perl2chm> 
[B<-->[B<no>]B<toc>] 
[B<--hr> I<level>] 
[B<--bgcolor> B<#>I<rrggbb>] 
[B<--text> B<#>I<rrggbb>] 
[B<--v> I<verbosity>]
I<PerlDir> I<HTMLDir>

=head1 DESCRIPTION

B<perl2chm> translates Perl documentation to HTML.
I<PerlDir> is the root of the Perl source tree.
The HTML pages are organized into a directory tree rooted at I<HTMLDir>.
A top-level index is written to I<HTMLDir>C</index.html>

In addition to the Perl sources,
B<perl2chm> searches C<@INC> for module PODs,
and C<$ENV{PATH}> for program PODS.

All the HTML pages are created world-readable.

I<Perldir> and I<HTMLDir> must be absolute path names.

=head1 OPTIONS

=over 4

=item C<-->[C<no>]C<toc>

Includes or omits a table of contents in each page.
Default is to include the TOC.

=item C<--hr> I<level>

Controls the profusion of horizontal lines in the output, as follows:

    level   horizontal lines
    0 	    none
    1 	    between TOC and body
    2 	    after each =head1
    3 	    after each =head1 and =head2

Default is level 1.

=item C<--bgcolor> I<#rrggbb>

Set the background color to I<#rrggbb>.
Default is off-white.

=item C<--text> I<#rrggbb>

Set the text color to I<#rrggbb>.
Default is black.

=item C<--v> I<verbosity>

Verbosity level: 0, 1, 2, 3

=back

=head1 REQUIRES

Perl 5
L<C<Getopt::Long>>,
L<C<Pod::Tree>>,

=head1 SEE ALSO

L<C<perl2html>>, 

=head1 AUTHOR

Steven McDougall, swmcd@world.std.com
José Fonseca, jrfonseca@users.berlios.de

=head1 COPYRIGHT

Copyright 2000 by Steven McDougall.  Copyright 2004 by José Fonseca.  This
program is free software; you can redistribute it and/or modify it under the
same terms as Perl.
