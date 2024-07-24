How to run on local
---
The `run.sh` script is designed to run the application on **local only**. It will build the docker images and run the containers.

After the containers are up and running, the script will do the following actions:
- The migration tool to create the database schema and seed the database with some data.
- Setup the application files nedded to run the application.

After the script is done you should be able to access the application on `http://dex.localhost`.

