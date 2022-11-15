# Change Log

All notable changes to this project will be documented in this file. This project adheres
to [Semantic Versioning] (http://semver.org/). For change log format,
use [Keep a Changelog] (http://keepachangelog.com/).

## [Unreleased]

### Added

- Add method `Hungarian::debug(bool $debug = true): void` to enable debug mode

### Removed

- Parameter `$print` on `Hungarian::solve()` method

## [1.0.0] - 2022-11-15

### Added

- Add `PHPUnit` for tests

### Changed

- Bump minimum PHP compatibility to **8.0**
- Cleanup code
- Force square of matrix with 0, result is purged of additional data
- Accept `INF` as value