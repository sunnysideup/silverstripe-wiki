###############################################
Wiki Module
Pre 0.1 proof of concept
###############################################

Developer
-----------------------------------------------
Nicolaas [at] sunnysideup.co.nz
Robbie [at] sunnysideup.co.nz

Maintainer Contact
-----------------------------------------------
Sam Minnee (Nickname: sminnee)
<sam (at) silverstripe (dot) com>

Thank you
-----------------------------------------------
Thank you AJ Short for valuable comments

Requirements
-----------------------------------------------
SilverStripe 2.3.0 or greater.

Documentation
-----------------------------------------------
Allows you to edit pages on the "front-side"

To add a link, add <% include FrontEndCMSEditLink %> to your template.

Installation Instructions
-----------------------------------------------
add module, review ideas in _config.php,
place configurations into your mysite/_config.php file

To Do
-----------------------------------------------
Change CanEditType to OnlyTheseUsers and
and add:
this->owner->EditorGroups()->add($Group->ID);
this->owner->EditorGroups()->add($AdminGroup->ID);
make sure it does not duplicate.

(a) use getFrontEndField,
(b) check permission model
(c) fix JS hack in template
(d) review which stat vars can be ditched
