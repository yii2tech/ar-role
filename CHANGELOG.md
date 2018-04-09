Yii 2 ActiveRecord Role Inheritance extension Change Log
========================================================

1.0.3, April 9, 2018
--------------------

- Bug #12: Fixed role relation is lost in case using Yii 2.0.14 (klimov-paul)


1.0.2, February 14, 2017
------------------------

- Bug #7: Fixed support for composite key at role relation (bethrezen, klimov-paul)
- Enh #8: `RoleBehavior::$roleAttributes` values now applied before model validation as well (klimov-paul)


1.0.1, July 6, 2016
-------------------

- Enh #6: Saving on 'slave' role inheritance now skips saving of the role model, if it has not been touched (klimov-paul)


1.0.0, December 29, 2015
------------------------

- Initial release.
