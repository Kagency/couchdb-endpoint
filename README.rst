=======
Kagency
=======

.. image::  https://secure.travis-ci.org/Kagency/couchdb-endpoint.png
   :alt:    Travis Status
   :target: https://secure.travis-ci.org/Kagency/couchdb-endpoint
   :align:  right

This is a implementation of the CouchDB replication API in PHP.

The implementation is framework agnostic. The demo currently works with
Symfony2, but you can integrate it with any other Framework.

You can use custom storage engines. The tests currently run against a in-memory
storage, but we successfully demoed it against a MySQL storage. Thus
replicating a CouchDB into a MySQL database and back into another CouchDB.

.. warning::
    This is work in progress. Do not rely on anything in here. While finishing
    the implementation basically everything might change.

..
   Local Variables:
   mode: rst
   fill-column: 79
   End: 
   vim: et syn=rst tw=79
