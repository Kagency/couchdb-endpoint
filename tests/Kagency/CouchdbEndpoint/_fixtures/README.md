### Data Fixtures

This directory contains data fixtures to test replication. You can create them
using the following two commands.

To record the HTTP trafic you can set up a reverse proxy using mitmdump:

    mitmdump -P http://localhost:5984 -p 5985 --anticache -z -w <number>_<name>

Then you can just replicate (this uses a CouchDB, but you could also do this
using TouchDB or PouchDB) – mind the port of the target, it uses the proxy we
jsut created:

    curl -X POST http://127.0.0.1:5984/_replicate -d '{"source":"source", "target":"http://127.0.0.1:5985/master"}' -H "Content-Type: application/json"
