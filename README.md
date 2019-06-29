## Laravel AntiBotLink

## Installation

```
	composer require hailkongsan/laravel-antibotlink
```

### Setup
This package supports the auto-discovery feature of Laravel 5.5 and above, So skip these `Setup` instructions if you're using Laravel 5.5 and above.

In `app/config/app.php` add the following :

1- The ServiceProvider to the providers array :

```php
Hailkongsan\AntiBotLink\AntiBotLinkServiceProvider::class,

```

2- The class alias to the aliases array :
```php
'AntiBotLink' => 'Hailkongsan\AntiBotLink\Facades\AntiBotLink::class'
```
3- Publish vendor

```shell
php artisan vendor:publish --tag=config,public,resource
```

#### Validation
```php
$validate = Validator::make(Input::all(), [
		'antibotlink' => 'required|antibotlink'
	]);
```
##### Custom Validation Message
Add the following values to the `custom` array in the `validation` language file :

```php
'custom' => [
    'antibotlink' => [
        'required' => 'Please verify that you are not a robot.',
        'antibotlink' => 'Invalid AntiBotLink verification!',
    ],
],
```
### Todo
* Clean up and refactor code.
* Add more option to render image function.
* Finish readme (usage).