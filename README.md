dad-bo
======

A Symfony project created on November 28, 2016, 8:34 pm.


php app/console server:run


MySQL commands :
change password : mysqladmin -u root password newpassword
connect to database : mysql -u root -pfoo42!
change database : USE databaseName;
export database : mysqldump -u root -pfoo42! foo > foo.sql

php app/console generate:bundle

Create entity : php app/console doctrine:generate:entity
Update entity : php app/console doctrine:generate:entities BackendAdminBundle:_My_Entity_

Update Database : php app/console doctrine:schema:update --force
Generate form : php app/console generate:doctrine:form AcmeBlogBundle:Post
Clear cache (dev) : php app/console ca:cl -e=dev
Clear cache (prod) : php app/console ca:cl -e=prod
Debug routes : php app/console debug:router

// ^[\w._-]+@(digitas|digitaslbi)+\.[\w]{2,}$

Publish assets from public folder : php app/console assets:install


http://curlgenerator.com/
https://github.com/nelmio/NelmioApiDocBundle/blob/master/Resources/doc/sandbox.rst


SET FOREIGN_KEY_CHECKS=0; -- to disable them
SET FOREIGN_KEY_CHECKS=1;

ALTER TABLE tablename AUTO_INCREMENT = 1;

### URL :

http://127.0.0.1:8000/_console

https://knpuniversity.com/screencast/symfony-rest4/create-json-web-token


 SELECT image_id, title, id FROM classified_advertisement;