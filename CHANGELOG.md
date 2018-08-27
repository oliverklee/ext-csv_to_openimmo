# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added
- Always mark the object address as visible (#62, #63)
- Also convert "commercial use" (#31, #56)
- Convert the elevator field (#51, #43)
- Convert parking space types (#36, #42)
- Convert the number of balconies (#26, #40)
- Convert additional heating types (#33, #39)
- Convert the street number as an additional field (#32, #38)
- Add compatibility with PHP 5.5 (#23)

### Changed
- Convert the floor as a free-text field (#48, #57)
- Also use "parking" for the object type (#46, #53)
- Convert balcony and patio as one field (#47, #52)
- Mark the import mode as "update" instead of "new" (#34, #41)

### Deprecated

### Removed

### Fixed
- Add the missing dependency on ext-dom (#64)
- Always set all utilization types to false (#50, #49)
