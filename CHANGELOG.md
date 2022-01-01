# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unrelease]

Nothing

## [2.4.9] 2022-01-01

### Added
- image insertion in AssetsManager, `img` twig function see https://github.com/phpMv/ubiquity/issues/188
- `reverse` to Transformers
- nonce in default template files

### Fixed
- typo in MultiResourceCrudController (no open issue)
- php 8.1 depreciation warnings (UbiquityException, UCookie, UResponse...)

## [2.4.8] 2021-12-01

### Added
- Pseudo-migrations in webtools and devtools

```bash
Ubiquity info-migrations
Ubiquity migrations
```

- Models creation command in devtools

```bash
Ubiquity new-model User
Ubiquity new-model user,group
```

### Updated
- Db wrappers for migrations
- Added `DbOperations`

### Fixed
- Fix absolute path pb in router with `#/` (no open issue)
- Fix `mainParams` pb in router cache generation (no open issue)

## [2.4.7] 2021-11-01

### Added
- Domain Driven design approach with `DDDManager` class
```php
DDDManager::setDomain('users');
```
Sample file structure:
```
app/
    domains/
        users/
            models/
            controllers/
            views/
            services/
            ...
        posts/
            models/
            controllers/
            views/
            services/
            ...
```

- Route main parameters

```php
#[Route('/foo/{mainParam}')]
public class FooController {
	public $mainParam;
}
```

```php
#[Route('/foo/{_setMainParam()}')]
public class FooController {
        private $mainParam;

	pubic function _setMainParam(string $p){
	    $this->mainParam=$p;
     }
}
```

### Fixed
- Tests pb (codeception vulnerability)
- AssetsManager js and css attributes pb (no open issue)
- default `index.html` W3C validation errors 

### Updated
- light opt : `parseURI` and `getNS` methods

## [2.4.6] 2021-09-06
### Added
- [ORM] Update cascade behavior
- `boolean` transformer
- main params for routes
- `MultiResourceCRUDController` Crud controller with index for several models
- [ORM] aggregate uQueries (count, sum, min, max, avg)

### Fixed
- [DAO] Fix Where pb with `count` method
- [DAO] Fix Where pb with `exists` method

### Updated
- default index page

## [2.4.5] 2021-06-15
Before creating a new project, be sure to update the devtools if they are installed globally:
```bash
composer global update
```
### Added
- mass update in DAO class (`updateAll`)
- type checker for routes params (int, bool=>regex)
- status code for router (200, 404, 405)

### Fixed
- autowiring pb with ReflectionType
- Mysql pb: replace parser cast for Mysql/mariaDB diff
- ManyToMany update pb (no open issue)

### Updated

#### Models generation
- The regeneration of models preserves the code implemented on the existing models.

#### CRUD controllers
- Add custom default buttons to dataTable (returned by `ModelViewer::getDataTableRowButtons()`)
- Add `onNewInstance($instance)` event
- Add `name` paramteter in `onGenerateFormField($field, $nb, $name)` event
- Add methods for modal title and message (`getFormModalTitle($instance)` and `formHasMessage()`)
- Add hook for form modal buttons (`onFormModalButtons($btOkay, $btCancel)`)

#### Application root (breaking change)
- For apache and nginX, root folder is set to public folder

For an old project (created with a version prior to 2.4.5), you have to modify ``index.php`` and move the ``index.php`` and ``.htaccess`` files to the ``public`` folder.
```php
   <?php
   define('DS', DIRECTORY_SEPARATOR);
   //Updated with index.php in public folder
   define('ROOT', __DIR__ . DS . '../app' . DS);
   $config = include_once ROOT . 'config/config.php';
   require_once ROOT . './../vendor/autoload.php';
   require_once ROOT . 'config/services.php';
   \Ubiquity\controllers\Startup::run($config);
```

## [2.4.4] 2021-04-25
Before creating a new project, be sure to update the devtools:
```bash
composer global update
```
### Added
- `UArrayModels` class for array of models manipulation (GroupBy, asKeyValues, sorting...)
- `UModel` class for models manipulation (property updating...)
- `ubiquity-debug` integration
### Fixed
- Boolean types pb [#174](https://github.com/phpMv/ubiquity/issues/174)
- ResponseFormatter import [#173](https://github.com/phpMv/ubiquity/issues/173)
- DAO PostgreSQL
  - ConditionParser pb with cast [#172](https://github.com/phpMv/ubiquity/issues/172)
  - Null values on fk [#171](https://github.com/phpMv/ubiquity/issues/171)
## [2.4.3] 2021-03-07
### Added
- Dark mode for CRUD controllers (`setStyle('inverted')`)
- CRUD hooks
  - `onBeforeUpdate(object $instance, bool $isNew)`
  - `onBeforeUpdateRequest(array &$requestValues, bool $isNew)`
- Twig
  - `isAllowedRoute(role, routeName)` added if **ubiquity-acl** is present.
### Updated
#### Validators
- Model validators can be used on the client side (used by default for CRUD controllers).
#### Routing
- Start router cache indexing (for routes with parameters) => This cache indexing is not yet used in production.

#### Fixed
- fix `Startup::getTemplateEngineInstance` method name.
- AuthController finalize and initialize pb with bad creditentials(no open issue)
- Make manyToOne dropdowns clearable in CRUD controllers for fk null values.
- DI parser pb (no open issue)
### Breaking change possible
#### Rest controllers refactoring
- Removed: `SimpleRestController`, `RestController` => Use the `RestBaseController` or `RestResourceController` class instead
- Added: 
  - `JsonRestController` => for simple Json REST API
  - `RequestFormatter`, `JsonRequestFormatter`, `JsonApiRequestFormatter` => for JSON api, JSON or url-encoded requests
- Updated (for request with authorization - accesstoken): 
  - The `checkPermissions` method in REST controllers must be overridden to check the data associated with an authentication token.
  - `checkPermissions` must be used in conjunction with the `connect` method to override as well.
## [2.4.2] 2021-02-08
### Added
- `ViewRepository` CRUD operations + Automatic passing of the handled objects to the view
- `AbstractRepository` only CRUD operations for overriding
- `Repository` a default repository for any model
- `getAllByIds` method in `DAO` part
### Fixed
- Fix cache generation pb for field names & dbTypes
### Updated
- Update route default name (ControllerName-{controller}.action)
### devtools
- livereload for php embedded web server
## [2.4.1] 2021-01-17
### Fixed
- [consecutive Bulk updates pb](https://github.com/phpMv/ubiquity/issues/166)
### Added
- Dark theme for CRUD controllers
## [2.4.0] 2020-12-31
### Added
- PHP8 attributes support => with PHP8, Ubiquity uses the PHP8 attributes system for annotations. 
- `password_verify` method to `URequest`
### Updated
- CRUD and Auth controllers no longer use twig inheritance on views by default: It is easier to customize the display.
## [2.3.13] 2020-12-11
### Added
- ACL Manager in [ubiquity-acl repository](https://github.com/phpMv/ubiquity-acl)
### Fixed
- Update php version in composer.json for php 8
- DAOUqueries [pb with parentheses in condition](https://github.com/phpMv/ubiquity/issues/159)
- [Password hash algo type](https://github.com/phpMv/ubiquity/commit/ddd8504aaa697b3d15ec2db7bece4b7202b81c7b) (no open issue)
- [DB Logging omitted](https://github.com/phpMv/ubiquity/commit/50f645355db7d395774ad7d9a47e1c4bf91dc0ce) (no open issue)
## [2.3.12] 2020-09-30
### Added
- Named db statements for async platforms
- Rest events on insert and update `BEFORE_INSERT = 'rest.before.insert'` and `BEFORE_UPDATE = 'rest.before.update'`
- `insertGroups` method (inserts in an implicit transaction)
- `quote` options for PDO wrappers
- `ApplicationStorage` for global variables with async platforms (Swoole, Workerman, ngx_php...)
### Fixed
- [orm] `oneToMany` and `manyToMany` loading pb with 2.3.11 version see [#145](https://github.com/phpMv/ubiquity/issues/145)
### Tests
- Adding tests
- increase of coverage to 73%.
## [2.3.11] 2020-07-28
### Added
- `DAOCache` (caches objects loaded by id)
- `MemcachedDriver` system
- `RedisDriver`system
### Updated
- fomantic-ui 2.8.6
- default index view relooking
- Cache system and ArrayCache refactoring
- light opt for async view and dbWrapper getStatement
### Fixed
- [rest] no violations on insert with ValidatorManager see [#122](https://github.com/phpMv/ubiquity/issues/122)
- [rest] Validation on insertion should be complete see [#123](https://github.com/phpMv/ubiquity/issues/123)
- [postgresql] pb wth PgsqlDriverMetas (names protection) see [#128](https://github.com/phpMv/ubiquity/issues/128)
- [postgresql] Insert fail with non autoinc pk see [#129](https://github.com/phpMv/ubiquity/issues/129)
- [webtools][models] click on Nothing to display generates an error see [#130](https://github.com/phpMv/ubiquity/issues/130)
- [webtools][models] instances count not updated see [#131](https://github.com/phpMv/ubiquity/issues/131)
- Session names with non allowed characters see [#134](https://github.com/phpMv/ubiquity/issues/134)
- `SimpleViewAsyncController` pb with cache (no open issue)
## [2.3.10] 2020-06-27
### Added
- transformer for `UCookie` (for Crypto)
- getter on session Csrf protection
- security level to csrf protection (0 => no secure)
### Updated
- fomantic-ui 2.8.5
### Fixed
- DAOPrepared queries pb (with memberList) -> no open issue
- update with field column names different from member names -> no open issue
## [2.3.9] 2020-06-04
### Added
- `put`, `patch`, `delete`, `options` annotations added for router (see [#108](https://github.com/phpMv/ubiquity/issues/108))
### Fixed
- Router cache Content-Type omited see [#120](https://github.com/phpMv/ubiquity/issues/120)
- ORM: pbs on column annotation see [#116](https://github.com/phpMv/ubiquity/issues/116) and [#117](https://github.com/phpMv/ubiquity/issues/117)
## [2.3.8] 2020-05-06
### Added
- Add csrf functions to twig templates
### Updated
- Update client libraries for new projects (Fomantic 2.8.4, jQuery 3.5.1)
- Update to Twig 3.0
## [2.3.7] 2020-04-30
### Added
- add `password_hash` to URequest
- add `exists` method in DAO
- Add Csrf protection to session
### Updated
- AuthController simplification
- remove unnecessary "No route found log"
## [2.3.6] 2020-04-13
### Fixed
- Fix `require php7.4` in composer.json file [#111](https://github.com/phpMv/ubiquity/issues/111)
- Fix `@transient` annotation pb [#113](https://github.com/phpMv/ubiquity/issues/113)
- Fix non autoinc pk not affected on insert [#114](https://github.com/phpMv/ubiquity/issues/114)
## [2.3.5] 2020-04-08
### Fixed
- Fix persistent `/_default/` for default url (twig path) (no open issue)
- Fix redirectToRoute pb (with `_default` route) (no open issue)
## [2.3.4] 2020-03-23
### Added
- `updateGroups` method for batch updates (mysql bulks)
- Aditional fields in queries Fixes [#83](https://github.com/phpMv/ubiquity/issues/83)
- **Composer** part in webTools
### Updated
- PostgreSQL PDO Driver created for PostgreSQL Database Support Fixes [#98](https://github.com/phpMv/ubiquity/issues/98)
- Sqlite PDO Driver created for SQLite Database Support Fixes [#90](https://github.com/phpMv/ubiquity/issues/90)
### Fixed
- `@id`column name pb [#107](https://github.com/phpMv/ubiquity/issues/107)
- [REST] with POST method returns 500 error, and 'controllers/rest' class not found [#89](https://github.com/phpMv/ubiquity/issues/89)
## [2.3.3] 2020-01-25
### Added
- mailer module see https://github.com/phpMv/ubiquity-mailer
- `SimpleViewController`, `SimpleViewAsyncController` for php views (without template engine)
- PHP 7.4 preloading see https://github.com/phpMv/ubiquity/issues/88
- `ObjectCache` cache system
- `SDAO` class for simple objects loading (popo with public members)
- Prepared DAO queries for getOne, getById & getAll (async)
### Improved
- Add warmup methods for controllers & models metas
- `StartupAsync` for asynchronous platforms (Swoole, Workerman)
- unpack replace cufa in `Startup::runAction`

## [2.3.2] 2019-10-28
### Added
- bulk queries in `DAO` class
  - `DAO::toAdd($instance)`
  - `DAO::toUpdate($instance)`
  - `DAO::toDelete($instance)`
  - `DAO::flush()`
- Composer create-project
```
composer create-project phpmv/ubiquity-project {projectName}
```
### Changed
- `MicroTemplateEngine` optimization (cache) 
### Added
## [2.3.1] 2019-09-25
### Added
- `workerman` server
Usage:
```
Ubiquity serve -t=workerman -p=8091
```
- `Memcached` support
- multi db types support (Db Wrapper)
  - `Tarantool` database support on a [proposal](https://github.com/phpMv/ubiquity/issues/64) from [@zilveer](https://github.com/zilveer)
  - `Swoole coroutine Mysql` database support
  - `Mysqli` database support
  - `PDO` default wrapper (updated)
### Updated
- `PhpFastCache` to ^7.0
### Fixed
- UQuery multi models fatal error (see [#63](https://github.com/phpMv/ubiquity/issues/63))

## [2.3.0] 2019-08-01
### Added
- `multi databases` feature on a [proposal](https://github.com/phpMv/ubiquity/issues/60) from [@Gildonei](https://github.com/gildonei)
### Changed
- `Startup` class optimization

#### Breaking change possible
Induced by multi database functionality:
- Database startup with `DAO::startDatabase($config)` in `services.php` file is useless, no need to start the database, the connection is made automatically at the first request.
- Use `DAO::start()` in `services.php` file when using several databases (with `multi db` feature)

For optimization reasons:
- the classes used only in development (common to devtools and webtools) have been relocated in the [phpmv/ubiquity-dev](https://github.com/phpMv/ubiquity-dev) package.

#### Migration 
- Update devtools: ``composer global update``

### Fixed
- route caching pb for routes with variables (no open issue)

### Documentation
- Add [Jquery and Semantic-UI part](https://micro-framework.readthedocs.io/en/latest/richClient/semantic.html)
- Add [Webtools presentation](https://micro-framework.readthedocs.io/en/latest/webtools/index.html)

## [2.2.0] - 2019-07-03
### Added
- Web-tools
  - Maintenance mode (see https://github.com/phpMv/ubiquity/issues/49)
  - Updates checking for caches
  - Customization (tools)

### Deleted/updated
- Webtools removed from Ubiquity main repository and are in there own repo

Use ``composer require phpmv/ubiquity-webtools`` to install them.

#### Breaking change possible:
Classes relocation
- ``Ubiquity\controllers\admin\utils\CodeUtils``->``Ubiquity\utils\base\CodeUtils``
- ``Ubiquity\controllers\admin\interfaces\HasModelViewerInterface``->``Ubiquity\controllers\crud\interfaces\HasModelViewerInterface``
- ``Ubiquity\controllers\admin\viewers\ModelViewer``->``Ubiquity\controllers\crud\viewers\ModelViewer``
- ``Ubiquity\controllers\admin\popo\CacheFile`` -> ``Ubiquity\cache\CacheFile``
- ``Ubiquity\controllers\admin\popo\ControllerSeo`` -> ``Ubiquity\seo\ControllerSeo``
- ``Ubiquity\controllers\admin\traits\UrlsTrait`` -> ``Ubiquity\controllers\crud\traits\UrlsTrait``
  
#### Migration 
- Update devtools: ``composer global update``
- In existing projects:
``composer require phpmv/ubiquity-webtools`` for webtools installation.

### Fixed
- Router: pb with route priority attribute see [#54](https://github.com/phpMv/ubiquity/issues/54)

### Changes
- Models generation (Engineering-Forward) by UbiquityMyadmin interface was updated to avoid wrong outputs from `__toString()` function. [#58](https://github.com/phpMv/ubiquity/issues/58)
    - Field name is checked on different names which could be a hint for a password field.
    - The following field names are supported:
        - American English: password 
        - Brazilian Portuguese: senha 
        - Croatian: lozinka 
        - Czech: heslotajne OR helslo_tajne
        - Danish: password 
        - Dutch: wachtwoord 
        - European Spanish: contrasena
        - Finnish: salasana 
        - French: motdepasse OR mot_de_passe
        - German: passwort
        - Italian: password 
        - Norwegian: passord 
        - Polish: haslo
        - European Portuguese: senha 
        - Romanian: parola
        - Russian: naponb
        - Latin American Spanish: contrasena
        - Swedish: loesenord OR losenord
        - Turkish: sifre
        - Ukrainian: naponb
        - Vietnamese: matkhau OR mat_khau
    
## [2.1.4] - 2019-06-13
### Added
- `Translate` module in webtools
- `transChoice` method for translations with pluralization (`tc` in twig templates)
- Transactions and nested transactions in `Database` and `DAO` classes see [#42](https://github.com/phpMv/ubiquity/issues/42)
- `getById` method in `DAO` class (optimization)
- `Ubiquity-swoole` server (``Ubiquity serve --type=swoole``)
### Fixed
- Fatal error in startup (not 404) fix [#43](https://github.com/phpMv/ubiquity/issues/43)
- Version 2.1.3 displays the number of version 2.1.2

## [2.1.3] - 2019-05-09
### Added
- Support for Http methods customization (for URequest & Uresponse) via ``Ubiquity\utils\http\foundation\AbstractHttp`` class.
- Support for session customization via ``Ubiquity\utils\http\session\AbstractSession``
- multisites session ``Ubiquity\utils\http\session\MultisiteSession``(1.0.0-beta)
- ``ReactPHP`` server available from the devtools with ``Ubiquity serve -t=react`` command
### Fixed
- [ORM] model Table annotation : fix [#39](https://github.com/phpMv/ubiquity/issues/39)
### Fixed
- [Logging] init logger fails if debug=false : fix [#31](https://github.com/phpMv/ubiquity/issues/31)
### Documentation
- DAO [querying, updates](https://micro-framework.readthedocs.io/en/latest/model/dao.html#loading-data)
- In doc for di : fix [#41](https://github.com/phpMv/ubiquity/issues/41)

## [2.1.2] - 2019-04-27
### Fixed
- Twig views caching : fix https://github.com/phpMv/ubiquity/issues/26
- ORM : sync `$instance->_rest` array with `$instance` updates
- REST:
  - pb on adding in `SimpleRestController` : fix https://github.com/phpMv/ubiquity/issues/27
  - pb an update with manyToOne members : fix https://github.com/phpMv/ubiquity/issues/30

## [2.1.1] - 2019-04-19
### Added
- `Transformer` module see in [documentation](https://micro-framework.readthedocs.io/en/latest/contents/transformers.html)
- `SimpleRestController` + `SimpleApiRestController` classes for Rest part

### Changed
- `Translation` module use default cache system (ArrayCache) and no more APC (performances ++)

### Fixed
- webtools Rest section
  - `Authorization Bearer` pb in input field (no open issue)
  - `POST` request for adding an instance with `RestController` (no open issue)
- webtools Models section, CRUDControllers
  - Model adding or updating in modal form fail see https://github.com/phpMv/ubiquity/issues/25
- JsonAPI finalization
### Documentation
- REST module [rest doc](https://micro-framework.readthedocs.io/en/latest/rest/index.html#rest)
- Transformers module [Transformers doc](https://micro-framework.readthedocs.io/en/latest/contents/transformers.html#transformers)

## [2.1.0] - 2019-04-01
### Added
- Themes manager with bootstrap, Semantic-ui and foundation
  - `AssetsManager` for css,js, fonts and images integration
  - `ThemesManager` for css framework integration
  - Themes part in webtools interface
- Dependency injection annotations
  - `@injected` inject a member in a controller defined by a dependency in config
  - `@autowired` inject an instance of class defined by type with `@var` annotation
   
### Changed
- dependency injection mecanism
  - controller cache for di
  - `@exec`key in `config[di]` for injections at runtime

#### Breaking change possible:
  use `"di"=>["@exec"=>[your injections]] `instead of `"di"=>[your injections]`
  
### Fixed
- An exception is thrown In case of problem with the Database connection (in `DataBase::connect` method) see https://github.com/phpMv/ubiquity/issues/12
>The connection to the database must be protected by a `try/catch` in `app/config/services.php`
```
try{
	\Ubiquity\orm\DAO::startDatabase($config);
}catch(Exception $e){
	echo $e->getMessage();
}
```
### Documentation
- Dependency injection updates [di doc](https://micro-framework.readthedocs.io/en/latest/controller/di/index.html#di)
- Themes managment [Assets and themes doc](https://micro-framework.readthedocs.io/en/latest/view/index.html#assets)

## [2.0.11] - 2019-03-14
### Added
- Rest [JsonAPI](https://jsonapi.org/format/) implementation
  - ``JsonApiRestController`` class
- methods in ``UCookie``
  - ``exists``: Tests the existence of a cookie
  - ``setRaw``: Sends a raw cookie without urlencoding the cookie value
- method in ``UResponse``
  - ``enableCORS``: enables globaly CORS for a domain (this was possible before by using ``setAccessControl*`` methods)
  
### Changed
- method ``set`` in ``UCookie`` (parameters ``$secure`` & ``$httpOnly`` added)

### Fixed
- issue [pb with config variable in Twig views](https://github.com/phpMv/ubiquity/issues/7)
- deprecated ref to apcu in Translation ``ArrayLoader`` removed

## [2.0.10] - 2019-02-22
### Added
- Webtools
  - validation info in models part
- Acceptance, functionnal and unit tests (70% coverage)

### Changed
- Webtools
  - models metadatas presentation
- Documentation
- Restoration of Translation class
- Compatibility with devtools 1.1.5

## [2.0.9] - 2019-01-21
### Removed
- Usage of ``@`` (replaced with ``??`` operator)

## [2.0.8] - 2019-01-20
### Changed
- Optimizations
  - ORM & relations oneToMany
  - apc to apcu cache for Translations
  - Router : routes array minification
  - Scrutinizer debugging : 0 bug !
  - Scrutinizer evaluation : 9.61 very good!
  - Translator=>TranslatorManager with static methods

## [2.0.7] - 2019-01-11
### Changed
- Scrutinizer cleaning
### Added
- String validators

## [2.0.6] - 2018-12-29
Update for phpbenchmarks compatibility

## [2.0.5] - 2018-12-28
### Changed
- TranslatorManager
- ValidatorsManager
- NormalizersManager

## [2.0.4] - 2018-11-21
### Added
- UQL (Ubiquity Query Language)
- AuthControllers
- CRUDControllers

### Changed
- SQL Queries optimization (groupings)

## [2.0.3] - 2018-04-16
### Added
- Config file edition and checking
- @framework location for internal default views

## [2.0.2] - 2018-03-13
### Changed
- manyToMany annot bug fixed
- quote in SqlUtils

## [2.0.1] - 2018-03-11
### Added
- SEO controller for generating robots.txt and sitemap.xml files (webtools interface)
- Adding new utility classes
  - Ubiquity\utils\http\UResponse
  - Ubiquity\utils\http\UCookie
### Changed
- Renaming utility classes:
  - Ubiquity\utils\RequestUtils -> Ubiquity\utils\http\URequest
  - Ubiquity\utils\SessionUtils -> Ubiquity\utils\http\USession
  - Ubiquity\utils\StrUtils -> Ubiquity\utils\base\UString
  - Ubiquity\utils\JArray -> Ubiquity\utils\base\UArray
  - Ubiquity\utils\FsUtils -> Ubiquity\utils\base\UFileSystem
  - Ubiquity\utils\Introspection -> Ubiquity\utils\base\UIntrospection
