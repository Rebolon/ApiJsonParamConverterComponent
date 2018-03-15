# API Json ParamConverter for Symfony4

<p align="center">

  [![Build Status](https://travis-ci.org/Rebolon/php-sf-flex-webpack-encore-vuejs.png?branch=master)](https://travis-ci.org/Rebolon/php-sf-flex-webpack-encore-vuejs)
  [![Known Vulnerabilities](https://snyk.io/test/github/rebolon/php-sf-flex-webpack-encore-vuejs/badge.svg?targetFile=package.json)](https://snyk.io/test/github/rebolon/php-sf-flex-webpack-encore-vuejs?targetFile=package.json)

</p>

## requirements

You need PHP (7.x), composer, and Symfony 4

## explanation

Working with ApiPlatform, i wanted to use custom POST route where i could send complex json data which represents nested entities.
To realize this i choose to use the ParamConverters. So with little convention (json props must be the same as php entity props)
and few ParamConverters (one per entity) extending the Rebolon/Request/AbstractConverter, it works !

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
        "serie": 4
    }
}
```

The AbstractConverter is able to deduplicate entity (if there is more than one the same entity in the json). It's also able to
retreive information from database if you put ID instead of object inside the json (2nd sample above).

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
