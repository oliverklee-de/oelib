# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added

- Add support for TYPO3 13LTS (#2427, #2430)

### Changed

- Use DI in the `TestingFramework` (#2385)

### Deprecated

### Removed

- Drop support for TYPO3 11LTS (#2419)
- Drop support for PHP < 8.1 (#2420)
- Drop the `DirectPersist` trait and interface (#2398)
- Drop the `StoragePageAgnostic` trait (#2397)
- Drop deprecated static registry methods (#2392)
- Drop returning the page UID in `createFakeFrontend` (#2383)
- Drop `ExtbaseConfiguration` (#2379)
- Drop the `Time` interface (#2378)
- Drop `FrontEndUserMapper::findByUserName()` (#2377)
- Drop explicit support for readonly models (#2376)
- Drop the `FrontEndUserGroup` model and mapper (#2375)
- Drop the `BackEndUser` model and mapper (#2374)
- Drop `TestingFramework::createTemplate()` (#2373)
- Drop the `PageRepository` (#2372)
- Drop the `ConfigurationProxy` (#2371)
- Drop the `Country` model and mapper (#2370)
- Drop the `Language` model and mapper (#2369)
- Drop `GeoCalculator` and the `Geo` interface (#2368)
- Drop `TestingFramework::logoutFrontEndUser()` (#2367)
- Drop the testing framework cleanup hook system (#2366)
- Drop the configuration check (#2365)
- Drop `$tablePrefix` in `TestingFramework::__construct()` (#2363)
- Drop the `CacheNullifyer` class (#2220)

### Fixed

- Avoid using the deprecated `MapperRegistry::getInstance()` (#2390)
- Provide TypoScript in TYPO3 V13 to the fake FE (#2301)

## 6.3.1: Bugfixes, cross-DBMS compatibility, and improved DI

### Changed

- Make the testing framework injectable (#2353)

### Fixed

- Make `AbstractConfigurationCheck` cross-DBMS-compatible (#2350)
- Fix cross-DBMS compatibility in the testing framework (#2341)

## 6.3.0: DI-capable registries, new ViewHelper, better fake FE, deprecations

### Added

- Expose the non-static `ConfigurationRegistry` getter (#2311)
- Expose the non-static `MapperRegistry` getter/setter (#2320)
- Add `SystemEmailBuilder` (#2294)
- Add `IfIsAdminViewHelper` (#2298)
- Make the registries available via DI (#2292, #2321)
- Use the new TypoScript parser in TYPO3 >= V12 (#2284)
- Provide the fake frontend request with TypoScript (#2282)
- Provide the fake frontend with a rootline (#2277)
- Populate `$_SERVER` in the fake FE in the testing framework (#2275)
- Set more request attributes for the fake FE in the testing framework (#2267)

### Deprecated

- Only provide real TypoScript to the fake FE in TYPO3 V12 (#2302)
- Deprecate `SystemEmailFromBuilder` (#2294)
- Deprecate the configuration check (#2297)
- Deprecate `::getInstance` in the registries (#2292)
- Deprecate the static accessors of `ConfigurationRegistry`, `MapperRegistry`
  and `TemplateRegistry` (#2291)
- Deprecate returning the page UID from `createFakeFrontend` (#2286)
- Deprecate the table name prefix in the testing framework (#2266)
- Deprecate the `Time` interface (#2265)
- Deprecate the `CacheNullifyer` (#2262)
- Deprecate `TestingFramework::logoutFrontEndUser()` (#2261)
- Deprecate the testing framework cleanup hook system (#2260)
- Deprecate `Deprecate `GeoCalculator` and the `Geo` interface` (#2259)
- Deprecate `TestingFramework::createTemplate()` (#2257)

### Fixed

- Release the lock after creating a fake FE (#2359)
- Receive the `BackendConfigurationManager` via DI (#2358)
- Disable caching for the fake frontend (#2322)
- Avoid undefined array access in `SystemEmailFromBuilder` (#2310)
- Provide the request to `newCObj()` for TYPO3 V13 (#2281)
- Avoid the deprecated `TypoScriptFrontendController::getLanguage()` (#2278)
- Avoid the deprecated `determineId()` method in the testing framework (#2274)

## 6.2.1 Performance improvements, new record icon, deprecation fixes

### Added

- Add a separate TCEForms record icon for ZIP codes/postal codes (#2164)

### Changed

- Avoid DB queries for checking the DB schema in the testing framework (#2246)
- Require the latest TYPO3 12LTS security release (#2076, #2161)

### Fixed

- Use `executeStatement()` instead of `executeQuery()` where appropriate (#2237)

## 6.2.0: View helper improvements and deprecations

### Added

- Make configuration key for `IsFieldEnabledViewHelper` configurable (#2059)

### Deprecated

- Deprecate the `PageFinder` (#2058)
- Deprecate creation and change date from `AbstractModel` (#2057)
- Deprecate the `ConfigurationProxy` (#2054)

## 6.1.1: Bug fixes and cleanup

### Fixed

- Add `getAs*Integer` to the `Configuration` interface (#2014, #2015)
- Move `getAs*Integer` to the correct superclass (#2009)
- Improve some type annotations and PHPDoc comments (#2008, #2010, #2013, #2016)

## 6.1.0: Add support for PHP 8.4

### Added

- Add `::getAsNonNegativeInteger()` and `::getAsPositiveInteger()` (#1988)
- Add support for PHP 8.4 (#1960)

### Changed

- Allow Fluid 4.0 (#1986)
- Require TYPO3 >= 11.5.41 (#1985)

### Deprecated

- Deprecate the `StoragePageAgnostic` trait (#2004)
- Deprecate the feature of readonly models (#2003)
- Deprecate `FrontEndUserMapper::findByUserName()` (#2002)
- Deprecate the `PageRepository` (#2001)
- Deprecate the `DirectPersist` trait and interface (#1998)
- Deprecate `BackEndUser` and `FrontEndUserGroup` (#1997)
- Deprecate `AbstractDataMapper::save()` (#1996)
- Deprecate the `Currency` model and mapper (#1995)
- Deprecate the `PriceViewHelper` (#1994)
- Deprecate the `Country` and `Language` models and mappers (#1993)

## 6.0.3: Bug fixes and cleanup

### Changed

- Require TYPO3 >= 11.5.40 (#1958)

### Removed

- Remove unneeded `resname` from the language files (#1963)

### Fixed

- Make implicit nullable parameter explicit (#1962)
- Add missing exception codes (#1945)
- Improve some type annotations (#1929)

## 6.0.2: TestingFramework bug fixes for TYPO3 12LTS

### Fixed

- Provide the request with a language for the fake frontend (#1925)
- Set `$GLOBALS['TYPO3_REQUEST']` when creating a fake frontend (#1924)

## 6.0.1: Bug fixes and cleanup

### Added

- Add a Rector CI job (#1908)
- Add XLIFF linting to the CI workflow (#1907, #1909)

### Changed

- Use modern string functions (#1917)
- Require Fluid >= 2.7.4 (#1906)
- Require at least version 12.4.15 for TYPO3 12LTS (#1897)
- Require TYPO3 >= 11.5.37 (#1889)
- Import classes in `ext_localconf.php` (#1815)

### Fixed

- Use the correct cache frontend in the testing framework (#1901)
- Drop `approved="yes"` from the English locallang files (#1894)
- Drop unnecessary type checks (#1879)
- Improve type annotations (#1871, #1880)
- Drop obsolete options from the `ext_emconf.php` (#1814)

## 6.0.0

### Added

- Add support for TYPO3 12LTS (#1669, #1671, #1672)
- Add Rector to our toolchain (#1659, #1662)

### Changed

- Update the faked user agent in the testing framework (#1700)
- Use `GeneralUtility::setIndpEnv` instead of server/request globals (#1699)
- Convert the DB fixtures from XML to CSV
  (#1689, #1690, #1692, #1693, #1694, #1695, #1696)
- Use the `Context` for getting the current timestamp (#1682, #1684)
- Use more native type declarations (#1664, #1665)
- Upgrade to PHPUnit 9 (#1634)
- Make `TestingFramework::isLoggedIn()` private (#1622)
- Raise the required TYPO3 bugfix version (#1619)

### Removed

- Drop the HTTP classes (#1647)
- Delete localizations that are now on Crowdin (#1772)
- Drop Composer suggestion for static_info_tables (#1650)
- Drop `MapperRegistry::denyDatabaseAccess` (#1646)
- Drop deprecated methods from `AbstractDataMapper`
  (#1640, #1641, #1642, #1643, #1644, #1645)
- Drop `TestingFramework::count()` and `::existsRecordWithUid` (#1639)
- Drop the tracking of changed records from the testing framework (#1638)
- Drop the logging-related interfaces/traits (#1637)
- Drop `ExtendedGeneralUtility` (#1635)
- Drop `EmptyQueryResultException` (#1633)
- Drop `CacheNullifyer::flushMakeInstanceCache` (#1632)
- Drop `Typo3Version` (#1631)
- Drop the login manager classes (#1630)
- Drop the `ConvertableToMimeAddressTrait` interface/trait (#1629)
- Drop the session classes (#1628)
- Drop the visibility classes (#1626)
- Drop the `LazyLoadingProperties` trait (#1625)
- Drop the `ChangeDate` and `CreationDate` interfaces/traits (#1624)
- Drop `TestingFramework::createBackEndUser` (#1623)
- Drop the TypoScript template parsing from the testing framework (#1621)
- Drop `DynamicDateViewHelper` (#1620)
- Remove `SalutationSwitcher` (#1618)
- Drop `TestingQueryResult` (#1617)
- Remove `FrontEndUserGroup::title` and `::description` (#1616)
- Drop `BackEndUserMapper::findByUserName` (#1614)
- Drop `Sortable` (#1613)
- Drop `CachedAssociationCount` (#1612)
- Remove some `Collection` methods (#1611)
- Remove some properties from `FrontEndUser` (#1610)
- Remove `FrontEndUser::getAge` (#1609)
- Drop the geocoding classes (#1608)
- Drop `FrontEndUser.wantsHtmlEmail()` (#1607)
- Drop the auto increment handling from the testing framework (#1605)
- Drop `BackEndUser::getGroups` and `BackEndUserGroup` (#1604)
- Drop the `FederalState` model & mapper (#1600)
- Remove `TemplateHelper` (#1599)
- Drop support for TYPO3 10LTS (#1598, #1601, #1602, #1655)
- Drop support for PHP < 7.4 (#1597)

### Fixed

- Add `resname` to all language labels (#1770)
- Upgrade the XLIFF files to XLIFF 1.2 (#1706)
- Access the superglobals directly (#1685)
- Switch to the new DBAL methods (#1656, #1657, #1658, #1674)

## 5.2.2

### Changed

- Decouple `TemplateHelper` from `SalutationSwitcher` (#1486)
- Mark some classes and methods as `@internal` (#1478, #1522, #1561, #1565)

### Deprecated

- Deprecate `MapperRegistry::denyDatabaseAccess` (#1595)
- De-deprecate the `PriceViewHelper` (#1591)
- De-deprecate the `Currency` model and mapper (#1591)
- De-deprecate the `Country` and `Language` models and mappers (#1562)
- Deprecate `TestingFramework::createBackEndUser()` (#1555)
- Deprecate `TestingFramework::isLoggedIn()` (#1554)
- Deprecate the template parsing in the testing framework (#1552)
- Deprecate the record change tracking in the testing framework (#1551, #1553)
- Deprecate the auto-increment handling in the testing framework (#1549)
- Deprecate `TestingQueryResult` (#1548)
- Deprecate `FrontEndUserGroup::title` and `::description` (#1547)
- Deprecate `BackEndUser::getGroups` and the `BackEndUserGroup` model/mapper
  (#1546)
- Deprecate `BackEndUserMapper::findByUserName` (#1545)
- Deprecate some methods from `AbstractDataMapper` (#1544, #1563, #1587)
- Deprecate `Sortable` (#1543)
- Deprecate `CachedAssociationCount` (#1542)
- Deprecate `SalutationSwitcher` (#1541)
- Deprecate `TemplateHelper` (#1540)
- Deprecate the HTTP classes (#1527)
- Deprecate the `DynamicDateViewHelper` (#1525)
- Deprecate some `Collection` methods (#1523)
- Deprecate some properties from `FrontEndUser` (#1514)
- Deprecate the geocoding classes (#1513)

### Fixed

- Return an empty string in `FrontEndUser::getName()` for no name set (#1592)
- Avoid additional query in `TestingFramework::changeRecord` (#1588)
- Mark the `Configuration` classes and the interface as non-injectable (#1512)
- Stop accessing the `TYPO3_MODE` constant (#1495)
- Stop extending the deprecated `AbstractPlugin` (#1487, #1488)
- Avoid using the deprecated `GeneralUtility::_GP` (#1482)
- Fix type errors in the testing framework (#1480)

## 5.2.1

### Added

- Add support for Symfony 7 (#1440)
- Add the Crowdin configuration (#1437)

### Changed

- Migrate the login functions to the Context API (#1483)
- Also run the PHP 8.2/8.3 functional tests with the lowest dependencies (#1220)

### Fixed

- Properly create the `LanguageService` in a functional test (#1473)
- Avoid using the deprecated `Query::execute()` (#1470)
- Use a TYPO3-version-specific cache configuration again (#1469)
- Avoid the deprecated `setUpBackendUserFromFixture` (#1465)
- Avoid accessing the protected `AbstractPlugin->cObj` (#1462)
- Avoid calling the removed `RootlineUtility::purgeCaches()` in 12LTS (#1461)
- Add missing singletons reset in a test (#1460)
- Drop a nonsensical test (#1459)
- Remove the deprecated `cruser_id` configuration for the test tables (#1458)
- Avoid using the deprecated `ObjectManager` in tests (#1457)
- Add a 12LTS-specific method to `TestingQueryResult` (#1454)
- Improve some type annotations (#1453)

## 5.2.0

### Added

- Add support for PHP 8.3 (#1422)

### Changed

- Make some type annotations more specific
  (#1401, #1405, #1406, #1411, #1412, #1415, #1416, #1420)

### Deprecated

- Deprecate `FrontEndUser::getAge` (#1408)

### Fixed

- Validate UIDs in the ORM more strictly (#1402)

## 5.1.0

### Added

- Add a diverse gender option (#1288)
- Add a `TestingQueryResult` class (#1287)

### Deprecated

- Deprecate `TestingFramework::createBackEndUser` (#1290)
- Deprecate `TestingFramework::count` (#1290)
- Deprecate `TestingFramework::existsRecordWithUid` (#1290)
- Deprecate `CacheNullifyer::flushMakeInstanceCache()` (#1290)
- Deprecate the `ConvertableToMimeAddress` interface and trait (#1290)
- Deprecate `TemplateHelper::isConfigurationCheckEnabled` (#1289)
- Deprecate `EmptyQueryResultException` (#1289)
- Deprecate `BackEndUser::getGroups` and the `BackEndUserGroup` model & mapper
  (#1289)
- Deprecate the `Country` model & mapper (#1289)
- Deprecate the `Currency` model & mapper (#1289)
- Deprecate the `FederalState` model & mapper (#1289)
- Deprecate the `Language` model & mapper (#1289)
- Deprecate the `CreationDate` and `ChangeDate` traits (#1289)
- Deprecate the `LoginManager` classes (#1289)
- Deprecate `FrontEndUser.wantsHtmlEmail()` (#1289)
- Deprecate `FrontEndUser.getLastOrFullName()` (#1289)
- Deprecate `Session` and `FakeSession` (#1289)

## 5.0.2

### Deprecated

- Deprecate the `LazyLoadingModel` trait (#1236)

### Removed

- Stop using Prophecy (#1255, #1257, #1258, #1259)

### Fixed

- Drop the obsolete `showRecordFieldList` TCA part (#1281)
- Enable caching for PHP-CS-Fixer (#1273)
- Improve the type annotations (#1253)

## 5.0.1

### Changed

- Switch the coverage on CI from Xdebug to PCOV (#1231)
- Switch to the TYPO3 coding standard (#1221)

### Fixed

- Fix property name in the `ChangeDate` trait (#1237)
- Fix typo in the services configuration file (#1218)

## 5.0.0

### Added

- Add support for PHP 8.1 and 8.2 (#1127, #1200)
- Add support for TYPO3 11LTS
  (#1114, #1115, #1123, #1126, #1131, #1136, #1174, #1180)
- Add a `ConvertableToMimeAddress` interface and trait (#1092)

### Changed

- Make the page UID for a fake frontend required (#1186)
- Switch to the TYPO3 testing framework (#1139)
- Improve DB-related error message in the testing framework (#1159)
- Switch to the TYPO3 testing framework (#1130, #1132, #1148)

### Deprecated

- Deprecate `Visibility\Tree` and `Visibility\Node` (#1192)
- Deprecate the testing framework auto increment functionality (#1167)

### Removed

- Drop the testing extensions (#1150)
- Drop support for `$additionalTablePrefixes` from the testing framework (#1149)
- Drop functionality for dummy files/folders from `TestingFramework` (#1121)
- Drop `TestingFramework::disableCoreCaches` (#1117)
- Drop `.htaccess` files (#1116, #1119)
- Drop `FrontEndUserMapper::getGroupMembers` (#1113)
- Drop `TemplateHelper::ensureIntegerArrayValues` (#1109)
- Drop `CacheNullifyer::disableCoreCaches` (#1104)
- Drop the `ReadOnlyRepository` trait (#1103)
- Drop `AbstractDataMapper::countByPageUid` (#1102)
- Drop `LoginManager::getLoggedInUser` (#1101)
- Drop the `GoogleMapsViewHelper` (#1099)
- Drop `AbstractModel::getAsList` (#1095)
- Drop support for TYPO3 9LTS (#1094, #1206)

### Fixed

- Stop using `static` in callables (#1209)
- Stop using the deprecated `strftime` (#1207)
- Harden the `ConfigurationRegistry` (#1195)
- Fix invalid array accesses in `TestingFramework` (#1179)
- Fix invalid array accesses in `AbstractDataMapper` (#1170)
- Only work on the auto increment value if the DB supports it (#1166)
- Drop obsolete Doctrine DBAL calls (#1112)
- Use the `TYPO3` constant instead of `TYPO3_MODE` (#1098)

## 4.3.0

### Added

- Add a helper for clearing the `GU::makeInstance()` class name cache (#1091)
- Allow providing data for `TestingFramework::createFrontEndPage()` (#1086)

### Changed

- Make the geo coordinates nullable (#1093)

## 4.2.0

### Added

- Add an `AbstractConfigurationCheckViewHelper` (#1082, #1084)
- Add a configuration-dependent validator (#1075, #1077, #1078, #1080)
- Add a view helper for hiding template parts via configuration (#1070)

### Changed

- Improve the configuration check messages (#1061, #1062, #1063)

### Deprecated

- Deprecate the salutation-switching logic (#1069)
- Deprecate the visibility-related `Node` and `Tree` (#1068)

## 4.1.9

### Changed

- Bump the minimal 10.4 Extbase requirement (#1044)

### Fixed

- Stop injecting the query settings in repositories (#1057)

## 4.1.8

### Fixed

- Bump the minimal 10.4 Extbase requirement (#1044)
- Properly check for falsey configuration values (#1035)

## 4.1.7

### Changed

- Raise PHPStan to level 9 (#1028)
- Allow more versions of `static_info_tables` (#968)
- Mark some internal classes as `@internal` (#963)

### Deprecated

- Deprecate `AbstractDataMapper::countByPageUid` (#1023)
- Deprecate `FrontEndUserMapper::getGroupMembers` (#1003)
- Deprecate `Typo3Version` (#972)
- Deprecate the `LoggingAware` trait and interface (#971)

### Fixed

- Fix PhpStorm code inspection issues (#1029, #1031)
- Stop using the `empty()` construct (#1015)
- Drop redundant type casts (#1009)
- Always drop empty values for `intExplode` calls (#1000)
- Discard invalid gender values (#996)
- Avoid some version-specific tests (#964, #967)
- Avoid calls to deprecated classes and methods (#955, #1014)
- Build a more complete fake frontend (#954)
- Use proper mocking with 11LTS in the storage-related tests (#951)
- Fix compatibility with newer DBAL versions (#950, #1014)
- Make the declaration of `UserWithoutCookies` V11-compatible (#949)
- Avoid crashes in `Collection` in PHP 8.1 (#946)
- Avoid invalid array accesses in the view helpers (#944)
- Move PHPCov to PHIVE (#943, #948)
- Improve the type annotations
  (#941, #942, #995, #1001, #1005, #1016, #1022, #1025, #1027)
- Make the usage of types more strict (#940)

## 4.1.6

### Fixed

- Harden `PageRepository::findDirectSubpages` (#916)

## 4.1.5

### Added

- Add code coverage (#903)

### Changed

- Switch to the TYPO3 Code of Conduct (#923)

### Deprecated

- Deprecate `GoogleMapsViewHelper` (#910)

### Removed

- Drop the outdated SwiftMailer dependency (#913)

### Fixed

- Remove dead code (#921)
- Improve the type annotations (#915)
- Allow a `TemplateHelper` Configuration without cObj (#914)
- Harden `AbstractDataMapper::findByPageUid` (#911)
- Fix more PHPStan warnings (#902)

## 4.1.4

### Fixed

- Discard the previous global request with the fake FE (#894)
- Improve the type annotations (#896, #897, #898, #900)
- Make the Composer dependencies explicit (#895)
- Add `@mixin` annotations to traits (#892)
- Stop using the deprecated `ObjectManager` (#891)

## 4.1.3

### Added

- Test with lowest and highest dependencies on CI (#883)

### Changed

- Switch the TER release to tailor (#888, #889)
- Upgrade to PHPUnit 8.5 (#870, #785)

### Fixed

- Avoid DI-related exception in TYPO3 10LTS (#885)
- Require TYPO3 >= 9.5.16 to avoid missing classes (#884)

## 4.1.2

### Changed

- Use a non-null cache for some caches for testing (#852)
- Clean up the `conflicts` section in the `composer.json` (#851)
- Bump the version number of the static_info_tables suggestion (#850)

### Fixed

- Improve the fake frontend
  (#854, #855, #856, #857, #858, #859, #860, #861, #863, #864, #866)

## 4.1.1

### Fixed

- Also disable the core cache in TYPO3 9LTS (#846)
- Fix the page cache identifier for TYPO3 9LTS (#845)

## 4.1.0

### Added

- Add a `CacheNullifyer` (#835)
- Add `AbstractConfigurationCheck::shouldCheck()` (#834)

### Deprecated

- Deprecate `TestingFramework::disableCoreCaches` (#835)

### Fixed

- Add missing parts to the fake frontend (#837)
- Specify `app-dir` in the `composer.json` (#836)

## 4.0.1

### Added

- Add an explanation to the configuration check output (#815)

### Changed

- Raise PHPStan to level 8 (#820)

### Deprecated

- Deprecate `TemplateHelper::ensureIntegerArrayValues` (#832)
- Deprecate `TemplateHelper::addPathToFileName` (#821)
- Deprecate `TestingFramework::createDummyFile` (#821)

### Removed

- Drop the (cosmetic) `TemplateHelper::cObj` (#814)

### Fixed

- Disable Core caches version-specific in the testing framework (#829)
- Fix a possible null pointer exception in `Session` (#826)
- Improve type safety (#815)

## 4.0.0

### Added

- Add `LoginManager::getLoggedInUserUid()` (#804)
- Add generics for the `Collection` class (#798)
- Add support for TYPO3 10LTS (#431)
- Add Rector to the toolchain (#720, #722, #724)

### Changed

- Handle mapper names in a case-sensitives way in the `MapperRegistry` (#787)
- Allow psr/log in versions 2 and 3 as well (#785)
- Switch the lazy loading callback to a closure (#775)
- Make `TypoScriptConfiguration` and `ConfigurationProxy` immutable (#771)
- Raise PHPStan to level 7 (#770, #801)
- Improve the styling of the config check warnings (#752)
- Use PHP 7.2 features and more type declarations (#721, #723, #730, #731)
- Upgrade to PHP-CS-Fixer V3 (#639)
- Make the testing framework classes final (#702)
- Upgrade to PHPUnit 7 (#696)
- Move PHPStan from PHIVE to Composer (#695)

### Deprecated

- Deprecate `*LoginManager::getLoggedInUser()` (#803)
- Deprecate the `PriceViewHelper` (#743)
- Deprecate the `ReadOnlyRepository` trait (#729)

### Removed

- Drop `addJavaScriptToPageHeader` (#762)
- Drop `AccessDeniedException` (#759)
- Drop the legacy configuration check (#758, #760, #768)
- Drop deprecated methods from `TemplateHelper` (#741, #742)
- Drop the use of removed Core functionality (#735, #736)
- Drop support for `$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']` (#728)
- Drop the deprecated email-related classes (#694)
- Drop columns from `ext_tables.sql` that get autogenerated (#703)
- Drop the `Translator` and `TranslatorRegistry` classes (#704)
- Remove the `DatabaseService` (#700)
- Remove the `ReadOnly` trait (#699)
- Remove the `UppercaseViewHelper` (#698)
- Drop deprecated methods from `AbstractDataMapper` (#697)
- Drop deprecated methods from the testing framework (#693, #716)
- Drop the legacy non-namespaced class aliases and migrations (#692, #761)
- Drop support for TYPO3 8LTS (#691, #701)
- Drop support for PHP 7.0 and 7.1 (#690)

### Fixed

- Do not provide empty geo coordinates in the `Geo` interface (#802)
- Adapt the tests and testing framework to TYPO3 10LTS
  (#767, #777, #778, #779, #780, #781, #783)
- Recognize flexforms data converted to an array (#753)
- Always display the incorrect value in the configuration check (#751)
- Fix the `TemplateHelper` initialization (#740)
- Allow `DateTimeImmutable` as change and creation date (#734)
- Update the usage of the Fluid view helper classes (#732)
- Stop using patches for dependencies (713)
- Improve some type annotations
  (#712, #769, #776, #787, #792, #794, #799, #780, #805, #808)

## 3.6.3

### Changed

- Improve the styling of the config check warnings (#755)

### Deprecated

- Deprecate the setters of `ConfigurationProxy` and `TypoScriptConfiguration`
  (#774)
- Deprecate `ConfigurationCheckable` (#766)

### Fixed

- Improve some type annotations (#791)
- Recognize flexforms data converted to an array (#756)
- Always display the incorrect value in the configuration check (#754)

## 3.6.2

### Fixed

- Stop using patches for dependencies (#714)
- Improve some type annotations (#711)

## 3.6.1

### Changed

- Display the allowed configuration values without enclosing quotes (#686)

### Fixed

- Fix issues with PHP 8.0 (#688)
- Stop relying on a BE user session in the `BackEndUserMapper` (#682)
- Fix the parameter type declaration for
  `TemplateHelper::ensureIntegerArrayValues` (#678)

## 3.6.0

### Added

- Add `HeaderProxyFactory::getHeaderCollector` (#671)

### Removed

- Stop making the mapper generics extendable (#670)

## 3.5.0

### Added

- Annotate `MapperRegistry::get()` as a factory method (#667)
- Add `ConfigurationInterface::getAsTrimmedArray` (#665)

### Changed

- Make the user and group mappers as generic (#666)

### Fixed

- Improve the PHPDoc type annotations (#661)

## 3.4.1

### Changed

- Raise PHPStan to levels 4 and 5 (#654, #658)
- Update the development tools and dependencies (#652, #653)
- Truncate changed tables only for functional tests (#648)

### Deprecated

- Deprecate `TemplateHelper::initializeConfiguration()`,
  `::getCurrentBePageId()`, `::retrievePageConfig()`,
  `::purgeCachedConfigurations()`
  and `::setCachedConfigurationValue()` (#655)

### Fixed

- Stop using the `TYPO3_version` constant (#657)
- Stop exporting the included Composer packages for Composer packages (#651)
- Improve the PHPDoc type annotations (#650, #656, #658)
- Use `$_EXTKEY` in `ext_emconf.php` again (#649)

## 3.4.0

### Added

- Add generics (#636)
- Add PHPStan to the CI builds (#625)
- Allow installations up to PHP 8.0 (#620)

### Changed

- Raise PHPStan to level 3 (#629, #633, #642)
- Update the `.editorconfig` to better match the Core (#614)

### Deprecated

- Deprecate the `ReadOnly` trait (#635)
- De-deprecate the HTTP-related classes (#616)

### Removed

- Drop `type="text/javascript"` from `script` tags (#634)

### Fixed

- Fix some PHPDoc type annotations (#641, #643)
- Stop using the TYPO3-custom whitespace constant (#631)
- Add missing call parameter in the `ConfigurationCheck` (#628)

## 3.3.0

### Added

- Add a reworked configuration check (#582, #585, #586, #587, #588, #589, #591,
  #592, #593, #601, #602, #603, #604, #605, #606, #607, #608, #609, #610, #611)
- Add a `FallbackConfiguration` (#581)
- Add a base class for objects with read-only data (#578)
- Add a `DummyConfiguration` class (#567, #580)
- Add a `Configuration` interface (#565, #568)
- Add a `FlexformsConfiguration` class (#564, #579)
- Add a `DynamicDateViewHelper` (#554)
- Add `AbstractModel::getAsCollection()` as an alias for `::getAsList` (#552)
- Document how to run the tests (#544)

### Changed

- Rename the Configuration class to TypoScriptConfiguration (#557)
- Adopt the `.editorconfig` settings from the TYPO3 Core (#555)

### Deprecated

- Deprecate the old configuration check (#582)
- Deprecate `AbstractModel::getAsList()` (#552)
- Don't deprecate `EmailRole`, `GeneralEmailRole`, `SystemEmailFromBuilder`
  (#551)
- Deprecate the `UppercaseViewHelper` (#548)

### Fixed

- Fix code inspections warnings (#612)
- Simplify resolving of `EXT:` paths (#583)
- Add a type in TCA for the FE users table dummy column (#549)
- Make `is_object` checks more specific (#546)

## 3.2.1

### Fixed

- Drop a return type declaration that breaks seminars (#540)
- Reduce the margin of `GeoCalculator::moveByRandomDistance` (#535)

## 3.2.0

### Added

- Add a `PageRepository` (#522)
- Add an `.editorconfig` file (#432)

### Changed

- Add more parameter and return type declarations (#501)
- [!!!] Namespace all classes (#398, #412, #413, #415, #423, #425, #426, #428,
  #436, #438, #440, #440, #448, #453, #454, #455, #458, #463, #493)
- Move the CI from Travis CI to GitHub Actions (#427, #428)
- Switch the default git branch from `master` to `main` (#424)
- Move the testing framework dummy files from `uploads/` folder to
  `typo3temp/` (#399, #403)

### Deprecated

- Deprecate `HeaderProxy` and `SystemEmailFromBuilder` (#483)
- Deprecate the old extension configuration format. The support of the old one
  will be dropped in version 4.0.
  Please migrate to the new one. Please see the
  [deprecation notice](https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-82254-DeprecateGLOBALSTYPO3_CONF_VARSEXTextConf.html)
  for more information.

### Fixed

- Fix a typo in a migration class alias (#532)
- Remove a breaking parameter type declaration (#531)
- Restore `TemplateHelper::setCachedConfigurationValue` (#527)
- Fix the constant for one degree latitude (#525)
- Harden null checks (#524)
- Fix the seconds per year constant (#520)
- Harden `unserialize` calls of extension configuration (#510)
- Stop using things that are deprecated in TYPO3 V9
  (#484, #491, #504, #506, #507, #508, #509, #512)
- Use correct cache keys in TYPO3 10LTS (#504)
- Add necessary int casts (#502)
- Fix Composer cache keys in the CI configuration file (#433)

## 3.1.0

### Added

- Add a TYPO3 version comparison class (#385)
- Support PHP 7.4 (#376)
- Support PHP 7.3 (#369)

### Changed

- Use `MailUtility` in `SystemEmailFromBuilder` (#386, #375)
- Slim down the Travis setup (#384)
- Update `pelago/emogrifier` to 4.0.0 (#382, #392)
- Upgrade PHPUnit to 7.5.20 for TYPO3 >= 9.5 (#377)
- Sort the entries in the `.gitignore` and `.gitattributes` (#368)

### Fixed

- Add `.0` version suffixes to PHP version requirements (#393)
- Allow non-string marker contents in templates (#390, #391)
- Fix crash if no HTML template path is provided (#389)
- Always use Composer-installed versions of the dev tools (#388)
- Do not build with PHP 7.4 for TYPO3 8.7 on Travis CI (#381)
- Do not cache `vendor/` on Travis CI (#380)
- Fix warnings in the `travis.yml` (#373, #383)
- Improve the code autoformatting (#370)

## 3.0.3

### Fixed

- Use the context API in TYPO3 9.5 for the FE login (#363, #366)
- Streamline the login managers (#365)

## 3.0.2

### Added

- Add php-cs-fixer to the CI (#358, #359)

### Changed

- Use PHP 7.2 for the release script (#357)

### Fixed

- Always provide the table when getting the last insert ID (#361)
- Internally store boolean properties as integers (#360)

## 3.0.1

### Changed

- Initialize `TemplateHelper` lazily (#350, #351, #352, #353, #354)
- Use more type declarations in the tests (#346)

### Removed

- Remove unused `limit` parameter from `DataMapper::findByPageUid` (#348)
- Drop unused fixture methods from the tests (#345)

### Fixed

- Use the new class name for mocks (#349)

## 3.0.0

### Added

- Add support for TYPO3 9LTS (#337)
- Build with PHP 7.2 on Travis CI (#335)
- Display the name of the current functional test (#293)

### Changed

- Use PHP 7.0 language features (#330, #331, #332, #333)
- Stop using the `Exception` base class (#321)
- Sort the Composer dependencies (#319)
- Upgrade to Emogrifier 3.0.0 (#316)
- Speed up the functional tests
  (#308, #309, #310, #311, #312, #313, #315, #317, #318)
- Convert more tests to `nimut/testing-framework`
  (#283, #288, #290, #291, #292, #300, #301, #303, #304)
- Update the testing libraries (#275, #279)
- Mark tests that do not contain any assertions (#277)

### Deprecated

- Deprecate the 4th parameter of `TestingFramework::createRelation` (#333)

### Removed

- Remove the deprecated `Mapper_BackEndUser::findByCliKey` (#327)
- Drop empty constructors (#320)
- Drop 7.6-specific code (#305, #314, #328)
- Drop unneeded Travis CI configuration settings (#295, #296, #297, #298)
- Remove deprecated methods from `TestingFramework` (#285)
- Remove deprecated parameter from `SalutationSwitcher::translate` (#282)
- Remove deprecated methods from the `Db` class (#281)
- Remove non-namespaced Extbase model and repository (#280, #286)
- Remove deprecated traits (#278)
- Drop support for PHP 5 (#274)
- Drop support for TYPO3 7.6 (#273, #287)

### Fixed

- Fix code inspection warnings (#343)
- Fix 1:n relations without `maxitems` (like BE subgroups) (#339)
- Stop using the deprecated `GeneralUtility::getUserObj` (#341)
- Disable the L10N cache in the unit tests (#340)
- git-ignore the tests-generated `var/log/` folder (#338)
- Convert more calls in the `Db` class to `ConnectionPool` (#334)
- Fix class resolution warnings (#326)
- Fix potentially undefined variable (#323)
- Stop using the deprecated `Db` class in the tests (#307)
- Explicitly add transitive dependencies (#306)
- Remove a fragile test (#302)
- Stop using deprecated oelib functionality in the tests (#284)
- Stop using the removed getMock() method (#276)

## 2.3.4

### Deprecated

- Deprecate the header-related classes (#270)
- Deprecate TemplateHelper::addJavaScriptToPageHeader (#269)
- Deprecate Translator and TranslatorRegistry (#268)
- Deprecate some methods in the Db class (#267)
- Deprecate some methods in TestingFramework (#265)

## 2.3.3

### Fixed

- Fix more MySQL errors with boolean properties in TYPO3 8.7 (#262)

## 2.3.2

### Fixed

- Fix MySQL error when saving boolean properties in TYPO3 8.7 (#261)

## 2.3.1

### Added

- Add PHP-CS-Fixer rules for PHPUnit (#226)

### Changed

- Update Emogrifier to version 2.1.1 (#230)
- Upgrade to PHPUnit 5.7 (#223)
- Change from GPL V3 to GPL V2+ (#221)
- Move more tests to `Tests/Unit/` and `Tests/Functional/`
  (#212, #235, #236, #237, #238, #241, #245, #246)

### Deprecated

- Deprecate some config check methods (#239)
- Deprecate the Db class (#234)
- Deprecate Db::enableQueryLogging (#231)
- Mark Db::getDatabaseConnection for removal in version 4.0, not 3.0 (#228)
- Deprecate some methods in the DataMapper (#251)

### Removed

- Delete unused PNG file from the tests (#247)
- Stop building with the lowest Composer dependencies (#244)
- Stop providing the DB query in the exceptions (#231)
- Drop the TYPO3 package repository from composer.json (#227)

### Fixed

- Ignore restrictions with TestingFramework::count/countRecords (#258)
- Fix regressions with the new Connection (#255, #256)
- Move `Tests/` to the dev autoload in `ext_emconf.php` (#249)
- Keep development files out of the packages (#248)
- Add a dependency to `cms/lang` in `composer.json` (#243)
- Use the ConnectionPool for DB queries in TYPO3 >= 8.4
  (#233, #239, #240, #242, #251, #252, #254)
- Provide flags to `htmlspecialchars` (#232)
- Avoid deprecated `TimeTracker` usage in the testing framework (#229)
- Clean up the testing extensions (#224)
- Pin the dev dependency versions (#225)
- Streamline the `composer.json` dependencies (#221)
- Always initialize a BE user in the BE user manager tests (#220)
- Explicitly require MySQL on Travis CI (#219)
- Remove dependency on specific FE-user extensions (#213, #214)
- Prevent rounding errors with the coordinates (#208, #209, #210)

## 2.3.0

### Added

- Initialize the DB in the TestingFramework lazily (#206)
- Add TestingFramework.cleanUpWithoutDatabase() (#204)
- Trait for loading lazy properties (#192)
- Trait and interface for `Repository::persistAll` (#187)

### Changed

- Move more tests to `Tests/Unit/` and `Tests/Functional/` (#203)
- Speed up the new functional by omitting the auto increment reset (#191)
- Rename the PersistAll interface and trait to DirectPersist (#190)
- Run the functional tests in parallel to each other (#183)

### Deprecated

- Deprecate the mailer-related classes (#193)
- Remove the "Trait" suffix from the trait names (#189)

### Fixed

- Throw exception for empty Google geocoding API key (#200)
- Fix the casing of the vfsstream package (#198)
- Ignore relations with a foreign UID of 0 (#194, #196)
- Also provide the extension icon in `Resources/` (#186)
- Fix a typo in a configuration check message (#184)

## 2.2.0

### Added

- Email role from the install tool default from email data (#181)
- Static data of the geo coordinates of German ZIP codes (#166, #172)
- Trait for storage-page-agnostic repositories (#156)
- Trait for cached association counts (#147)
- Make the configuration check class name more flexible (#140)
- Support namespaced data mappers and models (#137)
- Trait and interface for creationDate and changeDate (#136)
- LoggingAwareTrait (#133)
- Trait for read-only repositories (#132)
- Starter tests with `nimut/testing-framework` (#129)
- Test both with the lowest and highest dependency versions (#121)
- Add a configuration for the Google Maps API key (#92, #112)

### Changed

- Use spaces for indenting SQL and .htaccess files (#153, #162)
- Clean up the `ext_icon` SVG file (#155, #163)
- Streamline `ext_emconf.php` (#134, #135)
- Move the old tests to Tests/LegacyUnit/ and Tests/LegacyFunctional/ (#123)
- Prefer stable/dist packages by default (#120)

### Deprecated

- Deprecate TemplateHelper::setFlavor (#140)

### Removed

- Drop `roave/security-advisories` from the dev dependencies (#118)

### Fixed

- Make sure moveByRandomDistance does not move too far (#160, #173, #174)
- Only clean up tables that have a dummy column (#167)
- Create testing data mappers without eval (#150)
- Allow CamelCase class names for the configuration check (#139)
- Use the current composer names of static_info_tables (#127)
- Synchronize the versions of the test extensions (#122)
- Add a conflict with a PHP-7.0-incompatible static_info_tables version (#119)
- Add required PHP extension to the composer.json (#88, #110, #145)

## 2.1.0

### Added

- Auto-release to the TER (#108)
- Log more details for geocoding failures (#105)
- Add a configuration for the geocoding API key (#91, #98)
- Add the possibility to set a reply-to address for a mail object (#86)

### Removed

- Remove the `__destruct` methods (#97)
- Remove no longer necessary destruct method from mail object (#86)

### Fixed

- Update the Google Maps URL (#103)
- Remove the "sensor" parameter from the Google geocoding (#100)
- Rework the geocoding throttling (#87, #95)
- Update the composer package name of static-info-tables (#85)

## 2.0.1

### Fixed

- Work around the PHAR inclusion problem (#84)
- Stop PHP-linting the removed `Migrations/` folder (#82)
- Hide the test tables from BE user table permission lists (#81)
- Fix more deprecation warnings (#80)
- Stop using the deprecated NullTimeTracker in 8.7 (#79)

## 2.0.0

### Added

- Add support for TYPO3 8.7 (#69)
- Add support for PHP 7.1 and 7.2 (#62)

### Changed

- Use Emogrifier from an extension instead of packaging it (#72)
- Suggest static_info_tables >= 6.4.0 (#68)
- Update to PHPUnit 5.3.5 (#59)

### Deprecated

- Deprecate the `$useHtmlSpecialChars` parameter of `translate` (#76)

### Removed

- Drop the class alias map (#71)
- Remove the deprecated ConfigCheck::checkCssStyledContent (#67)
- Drop the deprecated TestingFramework::getTcaForTable (#66)
- Remove Template::getPrefixedMarkers and ::setCss (#65)
- Remove the deprecated Double3Validator (#64)
- Drop deprecated mailer functions (#61)
- Require TYPO3 7.6 and drop support for TYPO3 6.2 (#60)
- Drop support for PHP 5.5 (#58)

### Fixed

- Indent XLF and CSS with spaces instead of tabs (#77)
- Fix deprecation warnings in TYPO3 8.7 (#75)
- Update the TCA for TYPO3 8.7 (#74)
- Use the new PHPUnit test runner on TYPO3 8.7 (#70)
- Make the unit tests not depend on the current time of day (#57)

## 1.4.0

### Added

- Log the reason for geocoding failures (#50)
- Document in the code when deprecated things will be removed (#36)
- Add an SVG extension icon (#25)
- run the unit tests on TravisCI (#10)
- Composer script for PHP linting (#4)
- add TravisCI builds

### Changed

- Fix lots of PhpStorm code inspection warnings (#55)
- Always use a backslash for full class names (#42)
- Use more semantic PHPUnit functions (#35)
- Always use `::class` (#34)
- Move the PHP CS Fixer configuration directly into the `Configuration/` folder
  (#29)
- Update the PHP CS Fixer configuration (#26)
- Update Emogrifier to 2.0.0 and move it to `Resources/Private/Php` (#20)
- move the extension to GitHub

### Removed

- Directly bail from the geocoding for invalid addresses (#44)
- Drop the incorrect TYPO3 Core license headers (#38)
- remove obsolete TypoScript files (#8)

### Fixed

- Drop the Composer dependency on emogrifier (#49)
- Increase the delay when over the geocoding query limit (#47)
- Allow serialization of FE plugins for mkforms caching (#43)
- Make the geocoding compatible with TYPO3 8LTS (#39)
- Provide `cli_dispatch.phpsh` for 8.7 on Travis (#37)
- Fix the DataMapper tests in TYPO3 8.7 (#32)
- Require typo3-minimal for installing TYPO3 on Travis (#28)
- Use multiple attempts for failed geocoding (#22)
- Do not consider anonymous FE sessions as "logged in" (#17)
- Do not allow creation of test records in the BE (#21)
- Use $GLOBALS['TYPO3_CONF_VARS'] instead of $TYPO3_CONF_VARS (#16)
- require static_info_tables for dev (#14)
- skip tests that require static_info_tables if the extension is not installed
  (#11, #12, #13)
- fix autoloading when running the tests in the BE module in non-composer mode
  (#9)
- fix the "replace" section in the composer.json of the test extensions
- provide null page cache in the testing framework
- test failure about the framework hook in 8.7
- `Db::enableFields` should be able to find expired records

## 1.3.0

The [change log up to version 1.3.0](Documentation/changelog-archive.txt)
has been archived.
