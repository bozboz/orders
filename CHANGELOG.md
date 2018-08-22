# Changelog for Orders package 

## v1.0.0 (2018-08-15)
Version used by sites such as Drusillas and Benton as of July 2018, before updates made for Wired Sussex project.
These sites should be upadated to use this version rather than dev-master.

## v1.0.1 (2018-08-22)
Remove Benton fix.  Benton had an issue where order confirmation emails were 
being sent multiple times and a fix was put in Order to forget and readd the event.  
However this broke confirmation emails for Drusillas. The Benton fix is now 
removed from the main branch.