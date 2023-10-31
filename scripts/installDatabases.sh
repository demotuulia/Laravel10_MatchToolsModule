#
# This scripts reset all of the databases
#  and creates all tables and data again fr0
# migrations and seeds, both in test and dev
#
#

# clean up databases
mysql -hdb -u root -proot <scripts/sql/cleanAll.sql

# create tables in all databases
./scripts/migrate.sh

# seed app  to dev data base
php artisan db:seed
# add users  to dev data base
php artisan module:seed Matches --class="RoleAndPermissionSeeder"
php artisan matches:createUser "admin" "testAdmin@test.nx"  "admin" "123"

# seed demo data to dev data base
#php artisan module:seed Matches --class="Tests\\TestDataSeeder"
echo
echo
echo Instering demo content
php artisan matches:createDemoContent;
