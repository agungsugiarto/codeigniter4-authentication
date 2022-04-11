# Release Notes

## [Unreleased](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v2.0.0...2.x)

## [v2.0.0 (2022-04-11)](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v1.0.8...v2.0.0)
### What's Changed
* Porting gate authorization from laravel by @agungsugiarto in [#28](https://github.com/agungsugiarto/codeigniter4-authentication/pull/28)

**Full Changelog**: [v1.0.8...v2.0.0](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v1.0.8...v2.0.0)

## [v1.0.8 (2022-03-10)](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v1.0.7...v1.0.8)

### Added
- Unit test added InteractsWithAuthentication [(#27)](https://github.com/agungsugiarto/codeigniter4-authentication/pull/27)
- Unit testing using php upto 8.x [(#27)](https://github.com/agungsugiarto/codeigniter4-authentication/pull/27)
## [v1.0.7 (2021-10-05)](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v1.0.6...v1.0.7)

### Fixed
- Register.php calls for invalid file [(aa6eb79)](https://github.com/agungsugiarto/codeigniter4-authentication/commit/aa6eb799dda140a11aa5cfd5352f0cb58635e87c)
- ErrorException | On login with email & password [(aa6eb79)](https://github.com/agungsugiarto/codeigniter4-authentication/commit/aa6eb799dda140a11aa5cfd5352f0cb58635e87c)

## [v1.0.6 (2021-06-08)](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v1.0.5...v1.0.6)

### Fixed
- FIx return on filter authentication [(baf5130)](https://github.com/agungsugiarto/codeigniter4-authentication/commit/baf513071b0c31ec78bfecaab0f0309da2e70830)

## [v1.0.5 (2021-06-08)](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v1.0.4...v1.0.5)

### Changed
- Filter show message when request is ajax [(497af3b)](https://github.com/agungsugiarto/codeigniter4-authentication/commit/497af3be8fc9e922b8cfe22ccc49a4e682ae31f7)
- Login issue bug, throttleKey function [(1cf1999)](https://github.com/agungsugiarto/codeigniter4-authentication/commit/1cf19994dc4b8e3b93dbcbc685888182987317dc)

## [v1.0.4 (2021-06-03)](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v1.0.3...v1.0.4)

### Changed
- Log email when failed to send [(60a158e)](https://github.com/agungsugiarto/codeigniter4-authentication/commit/60a158e28152b60a4446ee21bed8e1282b16f3e5)
- Move path view notification to app [(be475c3)](https://github.com/agungsugiarto/codeigniter4-authentication/commit/be475c3d337a8e2756cddc92270f848e32c5795a)

## [v1.0.3 (2021-06-03)](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v1.0.2...v1.0.3)

### Changed
- Refactor auth view [(f7a4cb7)](https://github.com/agungsugiarto/codeigniter4-authentication/commit/f7a4cb7e3f7c75599225a9c182e258f478a2f32d)

## [v1.0.2 (2021-05-19)](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v1.0.1...v1.0.2)

### Fixed
- Fix Cache key contains reserved characters {}()/\@ [(152bec9)](https://github.com/agungsugiarto/codeigniter4-authentication/commit/152bec9577dc1978ad80abd2fcbce4de7af2c244)
## [v1.0.1 (2021-05-07)](https://github.com/agungsugiarto/codeigniter4-authentication/compare/v1.0.0...v1.0.1)

### Changed
- Remove strict return type on method login() [(#17)](https://github.com/agungsugiarto/codeigniter4-authentication/pull/17)
- Update readme [(#19)](https://github.com/agungsugiarto/codeigniter4-authentication/pull/19)
- Change first parameter to instance auth factory [(#20)](https://github.com/agungsugiarto/codeigniter4-authentication/pull/20)

## v1.0.0 (2021-03-02)

Initial stable release.