Wiki Module
================================================================================

Allows you to edit pages on the "front-side"

To add a link, add <% include FrontEndCMSEditLink %> to your template.

Developer
-----------------------------------------------
Nicolaas [at] sunnysideup.co.nz
Robbie [at] sunnysideup.co.nz

Thank you
-----------------------------------------------
Thank you AJ Short And Sam Minnee for invvaluable comments


Requirements
-----------------------------------------------
see composer.json


Documentation
-----------------------------------------------
Please contact author for more details.

Any bug reports and/or feature requests will be
looked at

We are also very happy to provide personalised support
for this module in exchange for a small donation.


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
