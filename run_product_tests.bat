@echo off
echo Running Product Controller Tests...
echo.

echo Running all ProductController tests...
php artisan test tests/Feature/ProductControllerTest.php --verbose

echo.
echo Running specific test methods (examples):
echo.
echo Test show method:
echo php artisan test tests/Feature/ProductControllerTest.php::test_show_product_success --verbose
echo.
echo Test store method:
echo php artisan test tests/Feature/ProductControllerTest.php::test_store_product_success --verbose
echo.
echo Test update method:
echo php artisan test tests/Feature/ProductControllerTest.php::test_update_product_success --verbose
echo.
echo Test delete method:
echo php artisan test tests/Feature/ProductControllerTest.php::test_delete_product_success --verbose
echo.
echo Test with image upload:
echo php artisan test tests/Feature/ProductControllerTest.php::test_store_product_with_image --verbose

pause
