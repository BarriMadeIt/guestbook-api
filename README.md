# guestbook-api

I have created a guestbook API application, which is built using Laravel PHP framework, Mariadb, and Docker. The guestbook application allows users to register, login, perform CRUD operations on messages. Guestbook API application is also role based, having an admin user who can create, view, update, and delete any message/replies. Caching is also used to help improve performance of API request queries.

## To get started with application
- Clone repository to local machine.
- Docker and docker-compose is used for this application, ensure these are installed on local machine.
- Laravel is used for the application, ensure composer is installed on local machine.
- Postman application is used to test API endpoints, there is a Guestbook API.postman_collection.json in the root of the directory.
- The postman .json file can be imported into the Postman application to get the API endpoints schema and configurations setup 
- There is a docker-compose.yml file in the root of the repository, will be used to build the docker guestbook-api image, containers, services.

## Hello Docker, Laravel, and Postman
- From the repository root
- Run the command `sudo chown -R 1001:docker mariadb/`, this will allow docker to create the db service, will see this in action from following steps.
- Next cd into the src directory `cd ./src` and run command `composer install`. This step will setup laravel composer dependencies.
- Return back to the repository root `cd ../`
- Run the command `docker-compose up --build`, this will use the `docker-compose.yml` file to build the the image and deploy the guestbook-api container and db services. Guestbook API and mariadb service should be running now, initial project setup is successful. Leave this terminal session open for the services to run.
  
- Now in a new terminal session run the command `docker-compose run --rm guestbook-api bash`. This will create a bash instance within the guestbook-api container server application.
- Within the container bash instance run the command `php artisan migrate`. This will run the migration files necessary for the guestbook API to fully function.

- I have also created feature tests for the application endpoints, to run these you can use the command `php artisan test` which will run all test files for the application. If all tests pass, you have successfully setup the guestbook-api application.

- Can now use Postman to test around with endpoints. The Feature tests can also be used as a guide for application use.

- To add an admin user to the application, you can either register a new user using Postman or use an existing user. Update the user field `is_admin` to `true`. This update can be made by running this command in the container guestbook-api instance `php artisan tinker` and then the following:
```
use App\Models\User;
$user = User::findOrFail(<id>); // Do use an actual user id in the place of <id>
$user->is_admin = true;
$user->save();
exit;
```

## On a side note
- To delete active/nonactive processes run command `docker rm -f $(docker ps -aq)`, use with caution as this will remove all docker processes.
- To cleanup images run command `docker image rm $(docker images -q)`, also use this with caution as this will remove all docker images.
