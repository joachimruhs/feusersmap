.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Administratormanual
====================


Target group: **Administrators**



Installation
^^^^^^^^^^^^
Import the extension from TER (TYPO3 Extension Repository) like any other extension.

On a page 'feusersmap' or on your root page insert 'Typoscript Include static' (from extension) FeUsersMap (feusersmap).
With the constant editor insert the desired settings of the extension like storage Pid, your page ID's, 
jQuery options, path to templates etc.


The data of fe_users and fe_groups is stored in Frontend Users. Insert in the constant editor
the correct "Default storage PID" of the fe_users records.

Then insert at least one fe_group. The extension tries to fetch the coordinates by itself like the mymap extension.
But https://nominatim.openstreetmap.org did not allow bulk requests for geocoding. So best is to get the coordinates
for the fe_user on other way.

To use your own mapIcons insert your mapIcons into the directory fileadmin/ext/feusersmap/Resources/Public/Icons.
Insert in this directory your icons which then can be selected in your fe_groups data records.


When everything is ok - test it...



Inserting data
^^^^^^^^^^^^^^
In TYPO3 list module select page Data. First insert some usergroups.
Then you can insert some fe_users. If the field mapgeocode is set, the extension tries to get the coordinates. If that fails, the field mapgeocode is set to 0.
If you don't insert some mapicon, the extension uses a default icon for the 
marker.

When everything is done you can start a search in frontend.



Reference
^^^^^^^^^

.. _plugin-tx-feusersmap:


plugin.tx\_feusersmap
^^^^^^^^^^^^^^^^^^^^^


templateRootPath
""""""""""""""""

.. container:: table-row

   Property
         templateRootPath

   Data type
         string

   Description
         path to templates

   Default
         EXT:feusersmap/Resources/Private/Templates/

partialRootPath
""""""""""""""""

.. container:: table-row

   Property
         partialRootPath

   Data type
         string

   Description
         path to partials

   Default
         EXT:feusersmap/Resources/Private/Partials/
     
layoutRootPath
""""""""""""""

.. container:: table-row

   Property
         layoutRootPath

   Data type
         string

   Description
         path to layouts

   Default
         EXT:feusersmap/Resources/Private/Layouts/

     
includejQueryCore
"""""""""""""""""

.. container:: table-row

   Property
        includejQueryCore

   Data type
         int

   Description
         include the jQuery library of myleaflet

   Default
        0





plugin.tx\_feusersmap.settings
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^


detailsPageId
"""""""""""""

.. container:: table-row

   Property
        detailsPageId

   Data type
         int

   Description
         Id of the details page

   Default
        -


initialMapCoordinates
"""""""""""""""""""""

.. container:: table-row

   Property
        initialMapCoordintes

   Data type
         string

   Description
         Initial map coordinates [latitude,longitude]

   Default
        48,8


useGroupLeafletmapicons
"""""""""""""""""""""""

.. container:: table-row

   Property
        useGroupLeafletmapicons

   Data type
         boolean

   Description
         If set, enables the clustering of locations

   Default
        1



enableMarkerClusterer
"""""""""""""""""""""

.. container:: table-row

   Property
        enableMarkerClusterer

   Data type
         boolean

   Description
         If set, enables the clustering of locations

   Default
        0



markerIconWidth
"""""""""""""""

.. container:: table-row

   Property
        markerIconWidth

   Data type
         int

   Description
         The width of the marker icon

   Default
        12

markerIconHeight
""""""""""""""""

.. container:: table-row

   Property
        markerIconHeight

   Data type
         int

   Description
         The height of the marker icon

   Default
        20


Known problems
^^^^^^^^^^^^^^

*No map loaded - ReferenceError: $ is not defined*

Make sure, you have loaded the jQuery on top of the page. This can be done with the constant editor of TYPO3 and the feusersmap
category (plugin.tx_feusersmap.view.includejQueryCore).

FAQ
^^^

*Custom templates and files*

You can use your own template and CSS file or other jQuery library - just go to the TYPO3 constants editor and change
the values for your needs.
