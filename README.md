ONGR Category Manager bundle
============================

Installation
------------

Update AppKernel.php:

    $bundles = array(
        ...
        new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
        new ONGR\ElasticsearchBundle\ONGRElasticsearchBundle(),
        new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
        new ONGR\CategoryManagerBundle\ONGRCategoryManagerBundle(),
    );

Update config.yml:

    stof_doctrine_extensions:
        default_locale: en_US
        orm:
            default:
                tree: true
                translatable: true

    ongr_category_manager:
        connection:
            index_name: category_manager
        translations:
            enabled: true
            default_locale: en
            locales:
                - en
                - lt

    ongr_elasticsearch:
        connections:
            default:
                hosts:
                    - { host: '127.0.0.1:9200' }
                index_name: 'ongr-category'
                settings:
                    refresh_interval: -1
                    number_of_replicas: 0
        managers:
            default:
                connection: default
                mappings:
                    - ONGRCategoryManagerBundle

Add composer dependency:

    "require": {
        "ongr/category-manager": "dev-master"
    }
    
    $ composer update
