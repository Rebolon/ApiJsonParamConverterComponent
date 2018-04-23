# API Json ParamConverter for Symfony4

<p align="center">

  [![Build Status](https://travis-ci.org/Rebolon/ApiJsonParamConverterComponent.svg?branch=master)](https://travis-ci.org/Rebolon/php-sf-flex-webpack-encore-vuejs)
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FRebolon%2FApiJsonParamConverterComponent.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2FRebolon%2FApiJsonParamConverterComponent?ref=badge_shield)
  [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Rebolon/ApiJsonParamConverterComponent/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Rebolon/ApiJsonParamConverterComponent/badges/quality-score.png?b=master)
  [![Code Coverage](https://scrutinizer-ci.com/g/Rebolon/ApiJsonParamConverterComponent/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Rebolon/ApiJsonParamConverterComponent/?branch=master)
  [![Code Intelligence Status](https://scrutinizer-ci.com/g/Rebolon/ApiJsonParamConverterComponent/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
  [![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FRebolon%2FApiJsonParamConverterComponent.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2FRebolon%2FApiJsonParamConverterComponent?ref=badge_shield)
  
</p>

## requirements

You need PHP (7.x), composer, and Symfony 4

To get code coverage, don't forget that you need xDebug when you run PHPUnit or you will get this message: `Error:         No code coverage driver is available`
If xDebug is not in the php configuration registered as your default php, you can run it manually:

```
PathToYouPHPWithXDebug vendor\phpunit\phpunit\phpunit
```

## explanation

Working with ApiPlatform, i wanted to use custom POST route where i could send complex json data which represents nested entities.
To realize this i choose to use the ParamConverters. So with little convention (json props must be the same as php entity props)
and few ParamConverters (one per entity) extending the Rebolon/Request/ItemAbstractConverter (for one entity) or ListAbstractConverter (for collection of entities), it works !

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


## License
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FRebolon%2FApiJsonParamConverterComponent.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2FRebolon%2FApiJsonParamConverterComponent?ref=badge_large)