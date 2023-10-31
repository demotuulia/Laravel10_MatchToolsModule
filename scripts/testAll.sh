echo ==============================================
echo Fix by codeSniffer
echo ==============================================
# we do automatic fixes before testing
./vendor/bin/phpcbf Modules Tests App
echo
read -n 1 -s -r -p "Press any key to continue"
echo
echo
echo ==============================================
echo Unit tests
echo ==============================================

echo
echo "===> TESTING tests"
echo
./vendor/bin/phpunit  tests
sleep 10

echo
echo "===> TESTING Modules/Matches/Tests/Feature/"
echo
./vendor/bin/phpunit  Modules/Matches/Tests/Feature/
sleep 10

echo
echo "===> TESTING Modules/Matches/Tests/Integration/"
echo
./vendor/bin/phpunit  Modules/Matches/Tests/Integration/

echo
echo ==============================================
echo Phpstan
echo ==============================================
./scripts/phpstan.sh

echo
echo ==============================================
echo Code sniffer
echo ==============================================
./scripts/codeSniffer.sh
