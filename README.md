# API Json ParamConverter for Symfony4

<p align="center">

  [![Build Status](https://travis-ci.org/Rebolon/ApiJsonParamConverterComponent.svg?branch=master)](https://travis-ci.org/Rebolon/php-sf-flex-webpack-encore-vuejs)

</p>

## requirements

You need PHP (7.x), composer, and Symfony 4
Even if i didn't try without ApiPlatform, it should work without this component. We only use ValidationException from ApiPlatform Sf bridge because ApiPlatform rely on it to return HTTP 4x error.
If your Api doesn't rely on this component, you will just have to add a listener on this kind of exception to manage the Response.   

## explanation

Working with ApiPlatform, i wanted to use custom POST route where i could send complex json data which represents nested entities.
To realize this i choose to use the ParamConverters. So with little convention (json props must be the same as php entity props)
and few ParamConverters (one per entity) extending the Rebolon/Request/ItemAbstractConverter (for one entity) or ListAbstractConverter (for collection of entities), it works !

For instance it works finely in **Creation** mode.
Also, when you send sub-entities with all properties even an ID, then, the component consider that you want to use the existing entity with the specified ID.
It will then ignore all other fields. This is a security to prevent update on sub-entities. But maybe this feature is missing and in this case, open an issue ! 

When you do a PUT HTTP, only the rot entity will be updated. If you have nested entities with associative entities, it's up to you to manage the wished behavior in the Controller.
The ParamConverter will not take any decision so:
 * if you already have entries in this kind of relations, they will be kept, and those you specified in the JSON will be added
 * if you want to replace those relations with the new ones, you will have to delete them (all relations that have ID are the old ones) in the Controller before persist the Book entity
 * if you want to update those relations

Here are some samples of json sent to the custom routes:

```
// The most complete sample, with de-duplication of editor (only once will be created)
{
    "book": {
        "title": "Zombies in western culture",
        "editors": [{
            "publication_date": "1519664915", 
            "collection": "printed version", 
            "isbn": "9781783743230", 
            "editor": {
                "name": "Open Book Publishers"
            }
        }, {
            "publication_date": "1519747464", 
            "collection": "ebooks", 
            "isbn": "9791036500824", 
            "editor": {
                "name": "Open Book Publishers"
            }
        }],
        "authors": [{
            "role": {
                "translation_key": "WRITER"
            }, 
            "author": {
                "firstname": "Marc", 
                "lastname": "O'Brien"
            }
        }, {
            "role": {
                "translation_key": "WRITER"
            }, 
            "author": {
                "firstname": "Paul", 
                "lastname": "Kyprianou"
            }
        }],
        "serie": {
            "name": "Open Reports Series"
        }
    }
}

// This one re-use database information for editor / author / job / serie
{
    "book": {
        "title": "Oh my god, how simple it is !",
        "editors": [{
            "publication_date": "1519664915", 
            "collection": "from my head", 
            "isbn": "9781783742530", 
            "editor": 1
        }, {
            "publication_date": "1519747464", 
            "collection": "ebooks", 
            "isbn": "9782821883963", 
            "editor": {
                "name": "Open Book Publishers"
            }
        }],
        "authors": [{
            "role": 2, 
            "author": 3
        }, {
            "role": {
                "translation_key": "WRITER"
            }, 
            "author": {
                "firstname": "Paul", 
                "lastname": "Kyprianou"
            }
        }],
        "serie": {
            "id": 4,
            "name": "whatever because the paramConverte will only take care of the id property"
    }
}
```

The AbstractConverter is able to deduplicate entity (if there is more than one the same entity in the json). It's also able to
retreive information from database if:
 * you put ID instead of object inside the json (2nd sample above with editors[0].editor or authors[0].role)
 * you send an object that contains the id field (or any other id prop name that you define in the specific Converter)

You can have a look at the tests to get more informations about how to use this component. I have wrote those test with 
sample Entities and Converter to make it more understandable.

## configuration

Add all your ParamConverters in the config/services.yaml file, like this:

```
services:
    ...
    
    App\Request\ParamConverter\Library\BookConverter:
        public: true
        arguments:
            - '@validator'
        tags:
            - { name: request.param_converter, priority: -2, converter: book }
```
