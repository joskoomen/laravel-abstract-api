# Laravel & Lumen Abstract API Security

### Laravel
1. Add the Service Provider to `config/app.php`
```
/*
 * Package Service Providers...
 */
\JosKoomen\AbstractApi\AbstractApiServiceProvider::class,
```
2. Run `php artisan vendor:publish` to publish the config file;
3. Add the `.env` variables and their values;
4. Add the middleware in `App\Http\Kernel.php` to `$routeMiddleware`
```
'abstract.api' => \JosKoomen\AbstractApi\AbstractApiMiddleware::class,
```
5. And i advice to add it to the `api` group in the same file:
```
'api' => [
    'throttle:60,1',
    'bindings',
    'abstract.api
],
```

6. For sending you can add the `AbstractApiValidationTrait` to your controller.
7. Right before your API request you can use the following method:
```
$form_params = $this->addTimeAndSignature(request()->all());
```
That's it!

### Lumen
1. Add the `.env` variables and their values;
```
JOSKOOMEN_ABSTRACT_API_TIME_DIFFERENCES=30
JOSKOOMEN_ABSTRACT_API_HASH_SECRET="${APP_KEY}"
JOSKOOMEN_ABSTRACT_API_HASHTYPE=sha512
JOSKOOMEN_ABSTRACT_API_DEBUG=true
JOSKOOMEN_ABSTRACT_API_DISABLE=false
```
2. Add the middleware in your bootstrap file.
```
$app->routeMiddleware([
    'abstract.api' => \JosKoomen\AbstractApi\AbstractApiMiddleware::class,
]);,
```
3. Add the middleware to your routes you want to secure like any other middleware in Lumen.
4. For sending you can add the `AbstractApiValidationTrait` to your controller.
5. Right before your API request you can use the following method:
```
$form_params = $this->addTimeAndSignature(request()->all());
```
That's it!