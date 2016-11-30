dad-bo
======

A Symfony project created on November 28, 2016, 8:34 pm.


php app/console server:run


MySQL commands :
change password : mysqladmin -u root password newpassword
connect to database : mysql -u root -pMYPAssWORD
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

Publish assets from public folder : php app/console assets:install


Fatal error: Allowed memory size of 1073741824 bytes exhausted (tried to allocate 32 bytes) in phar:///usr/local/bin/composer/src/Composer/DependencyResolver/RuleWatchGraph.php on line 52