# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
## [Unreleased]
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
