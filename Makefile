localclear:
	php artisan auth:clear-resets
	php artisan config:clear
	php artisan cache:clear
	php artisan config:cache
	php artisan event:clear
	php artisan optimize:clear
	php artisan queue:clear database
	#php artisan queue:clear --queue=default redis
	#php artisan queue:clear --queue=cache redis
	#php artisan queue:clear --queue=session redis
	php artisan route:clear
	php artisan schedule:clear-cache
	php artisan view:clear
	composer dump-autoload
	npm run build