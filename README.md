# Jables (Beta)
Write your Database Schema in JSON. Let Jables handle the Rest. (For Laravel 5)

> Did you ever contemplate how nice it would be if your migration files weren't named with such odd names and how easy it would be if bringing changes to a table meant you only had to change one file, and all of it stored nicely in your version control?
> 
> Yeah we did too, so we created **Jables**.

# Installation

## Grabing It
First, we need to grab it through Composer (composer is required as we also depend on its autoloading facilities)

```
composer require hedronium/jables
```

or by adding it to the require list in you `composer.json` file, then calling `composer install`

```JSON
require: {
    "hedronium/jables": "dev-master"
}
```

## Registering It
This step is very important. You must add Jables to Laravel as a service provider.

To do this, open up your `config/app.php` and add 
`hedronium\Jables\JablesServiceProvider` to the Service Providers list. Like:

```PHP
'providers' => [
    // .... other providers and stuff ...
    hedronium\Jables\JablesServiceProvider::class
]
```

## Check it out!

On the command line run
```
php artisan
```
and check to see if the `jables` command and the `jables` section shows up.

# Configuration
Jables usually works right out of the box with no configuration required, but if you do wanna get pokey, we have 2 configuration options for you.

First publish the configuration files. with
```
php artisan vendor:publish
```

after running that a `jables.php` should show up in your `config` folder with the following contents...

```PHP
<?php
return [
    'table' => 'jables',
    'folder' => 'jables'
];
```

## Options
- **table** - The name of the special table jables creates for tracking which tables have been created and which has not.
- **folder** - The name of the folder within which you store your table schemas. The name is relative to your Laravel installation's `database` folder.

> Yes it is highly suggested that you store it in a folder without your database folder but different from your migrations folder.

# Usage
## Writing Schemas
The schemas files are usually stored in the `database/jables` folder unless you configure it to be otherwise.

The Filename **is** your table name. So if you were to create a `users` table, your file name would be `users.json` nested under `database/jables`

### Hello World! (in Jables)

`food.json`

```
{
    "fields": {
        "name": {
            "type": "string"
        }
    }
}
```

now run `php artisan jables`. This will create a table named `food` with a field named `name` with the type being `varchar`.

### The Formal Breakdown
Well you define all your fields in the `fields` property on your root object of your json file. The `fields` property itself is an object and every property of the `fields` object corresponds to a table field.

Each property (which are the field definitions) within the `fields` object is once again another object. The only hard requirement for it all is the `type` property on them. This tells jables what is the type of the field.

in our 'hello world' example the type of `string` corresponds to `varchar` just like in Laravel migrations (ssssh, we actaully use Laravel's [Schema Builder](http://laravel.com/docs/5.1/migrations) behind the schenes, please don't tell anyone).

## Types Available
Heres a list

- [big-integer](#big-integer)
- [binary](#binary)
- [boolean](#boolean)
- [char](#char)
- [date](#date)
- [date-time](#date-time)
- [decimal](#decimal)
- [double](#double)
- [enum](#enum)
- [float](#fltoat)
- [integer](#integer)
- [json](#json)
- [jsonb](#jsonb)
- [long-text](#long-text)
- [medium-integer](#medium-integer)
- [medium-text](#medium-text)
- [morphs](#morphs)
- [small-integer](#small-integer)
- [string](#string)
- [text](#text)
- [time](#time)
- [tiny-integer](#tiny-integer)
- [timestamp](#timestamp)

## Numbers
### integer
```JSON
"awesomeness": {
    "type": "integer"
}
```

You can write `attributes` which is a list. Currently only supports one attribute, the `unsigned` attribute

like...
```JSON
{
    "type": "integer",
    "attributes": [
        "unsigned"
    ]
}
```

You can only set it to auto-increment with the `ai` (`boolean`) property like...
```JSON
{
    "type": "integer",
    "ai" : true
}
```

### big-integer
Same as the average integer just write the type different like...
```JSON
{
    "type": "big-integer"
}
```

### medium-integer
Same as the average integer just write the type different like...
```JSON
{
    "type": "medium-integer"
}
```

### small-integer
Same as the average integer just write the type different like...
```JSON
{
    "type": "small-integer"
}
```

### tiny-integer
Same as the average integer just write the type different like...
```JSON
{
    "type": "tiny-integer"
}
```

### float
The `FLOAT` type equivalent.
```JSON
{
    "type": "float"
}
```

### double
The `DOUBLE` type equivalent. It requires you to set the `digits` & `precision` properties.

```JSON
{
    "type": "double",
    "digits": 10,
    "precision": 5
}
```

### decimal
The `DECIMAL` type. Properties same as `double`.


## String & Character Types
### string
`string` is the `VARCHAR` type, and it accepts a `length` property like...

```JSON
{
    "type": "string",
    "length": 50
}
```

but the `length` property isn't required.

### char
Its exactly like string it just uses the `CHAR` type and the `length` property is absolutely required. NO QUESTIONS!

```JSON
{
    "type": "char",
    "length": 10
}
```

## Text
### text
Text doesn't require any special properties.

### long-text
Same as `text`.
```JSON
{
    "type": "long-text"
}
```

### medium-text
Same as `text`.
```JSON
{
    "type": "medium-text"
}
```

## Dates & Times
They don't have any special properties.

### date
```JSON
{
    "type": "date"
}
```

### time
```JSON
{
    "type": "time"
}
```

### date-time
```JSON
{
    "type": "date-time"
}
```

### timestamp
```JSON
{
    "type": "timestamp"
}
```

## Others
### enum
for the `ENUM` type. It is required that you set the `values`(`list`) property.
```JSON
{
    "type": "enum",
    "values": ["wizard", "muggle"]
}
```

### boolean
No special properties.
```JSON
{
    "type": "boolean"
}
```

### json
No special properties.
```JSON
{
    "type": "json"
}
```

### jsonb
No special properties.
```JSON
{
    "type": "jsonb"
}
```

### morphs
No special properties.
```JSON
{
    "type": "morphs"
}
```

### binary
No special properties.
```JSON
{
    "type": "binary"
}
```

