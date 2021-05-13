# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added

### Changed
- Switch the default git branch from master to main (#110)
- Run the TER release script with PHP 7.2 (#106)
- Sort the Composer dependencies (#105)
- Simplify the Composer dependency versions (#101)
- Change from GPL V3+ to GPL V2+ (#95)

### Deprecated

### Removed
- Drop unneeded Travis CI configuration settings (#102, #103)
- Remove the TER as Composer repository (#100)
- Stop running with different dependency versions (#99)

### Fixed
- Always use Composer-installed versions of the dev tools (#109)
- Fix warnings in the `.travis.yml` (#108)
- Do not cache `vendor/` on Travis CI (#107)
- Drop an obsolete "replace" entry from composer.json (#97)
- Properly quote the PHP version strings in .travis.yml (#96)
- Explicitly provide the extension name in the composer.json (#94)

## 2.0.0

### Added
- Add a test case with a minimal valid OpenImmo file (#88)

### Changed
- Streamline ext_emconf.php (#86)

### Removed
- Require TYPO3 >= 7.6 (#87)

### Fixed
- Also process ampersands in the CSV (#90, #91)
- Also provide the extension icon in `Resources/` (#89)

## 1.0.0

### Added
- Auto-release to the TER (#78)
- Convert more utilization types (#73, #74)
- Add compatibility with TYPO3 CMS 8.7 (#70)
- Convert the "habitation" utilization type (#68, #61)
- Always mark the object address as visible (#62, #63)
- Also convert "commercial use" (#31, #56)
- Convert the elevator field (#51, #43)
- Convert parking space types (#36, #42)
- Convert the number of balconies (#26, #40)
- Convert additional heating types (#33, #39)
- Convert the street number as an additional field (#32, #38)
- Add compatibility with PHP 5.5 (#23)

### Changed
- Prefer stable/dist packages by default (#80)
- Change the OpenImmo transfer type from "partial" to "full" (#65)
- Convert the floor as a free-text field (#48, #57)
- Also use "parking" for the object type (#46, #53)
- Convert balcony and patio as one field (#47, #52)
- Mark the import mode as "update" instead of "new" (#34, #41)

### Fixed
- Also create OpenImmo nodes for empty data (#84)
- Create valid OpenImmo for commercial objects (#75, #77)
- Convert only balconies, not balconies and patios combined (#76)
- Add the missing dependency on ext-libxml (#66)
- Add the missing dependency on ext-dom (#64)
- Always set all utilization types to false (#50, #49)
