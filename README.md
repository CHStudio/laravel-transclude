# Laravel Transclude

[![Build Status](https://travis-ci.org/CHStudio/laravel-transclude.svg?branch=master)](https://travis-ci.org/CHStudio/laravel-transclude)
[![Coverage Status](https://coveralls.io/repos/github/CHStudio/laravel-transclude/badge.svg?branch=master)](https://coveralls.io/github/CHStudio/laravel-transclude?branch=master)

This package allow to use [transclusion](https://en.wikipedia.org/wiki/Transclusion) from Blade template engines.
It's very useful when you want to handle views as component. It's inspired by the Angular transclude logic.

Installing
----------
[![Latest Stable Version](https://img.shields.io/packagist/v/chstudio/laravel-transclude.svg)](https://packagist.org/packages/chstudio/laravel-transclude)
[![Total Downloads](https://img.shields.io/packagist/dm/chstudio/laravel-transclude.svg)](https://packagist.org/packages/chstudio/laravel-transclude)

This project can be installed using Composer. Add the following to your `composer.json`:

```JSON
{
    "require": {
        "chstudio/laravel-transclude": "~2.0"
    }
}
```

or run this command:

```Shell
composer require chstudio/laravel-transclude
```

After updating composer, add the `ServiceProvider` to the providers array in `config/app.php`.

### Laravel 5.x:

```php
CHStudio\LaravelTransclude\TranscludeServiceProvider::class,
```

Then you can use the new blade directives in your views !

Usage
-----

This package register three new Blade directives :

* `@transclude` / `@endtransclude` to write inside a transcluded block,
* `@transcluded` to declare a space where the transclusion will be written.

For example, take the [Bootstrap](http://getbootstrap.com/) form elements, they are all using the same global
structure. Then in that structure there are different html blocks depending on the form element.

### Create template files

#### `input-group.blade.php`

```twig
<div class="form-group">
    <label for="{{ $name }}" class="control-label">{{$label}}</label>

    @transcluded
</div>
```

#### `radio.blade.php`

```twig
@transclude('input-group')
    @foreach($options as $option)
    <div class="radio">
        <label>
            <input name="{{$name}}" type="radio" {{$option['value']==$selected?' checked':''}} value="{{$option['value']}}" />
            {{$option['label']}}
        </label>
    </div>
    @endforeach
@endtransclude
```

### Use the new blocks

Then after writing this 3 files, you can add an element using the `@include` directive :

```twig
<form>
    @include('radio', [
        'options' => [
            ['value' => '1', 'label' => 'Option 1']
        ],
        'selected' => '1',
        'label' => 'My radio button'
        'name' => 'my-radio'
    ])
</form>
```

This code will generate a full radio element with a combination of input-group and radio templates :

```html
<form>
    <div class="form-group">
        <label for="my-radio" class="control-label">My radio button</label>

        <div class="radio">
            <label>
                <input name="my-radio" type="radio" checked value="1" />
                Option 1
            </label>
        </div>
    </div>
</form>
```

## Contributing

We welcome everyone to contribute to this project. Below are some of the things that you can do to contribute.

- Read [our contributing guide](CONTRIBUTING.md).
- [Fork us](https://github.com/chstudio/laravel-transclude/fork) and [request a pull](https://github.com/chstudio/laravel-transclude/pulls) to the [master](https://github.com/chstudio/laravel-transclude/tree/master) branch.
- Submit [bug reports or feature requests](https://github.com/chstudio/laravel-transclude/issues) to GitHub.
