## [3.1.2](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.1.1...v3.1.2) (2022-12-08)


### Bug Fixes

* bug where dirty was returning plain text instead of cypher text ([509accf](https://git.customd.com/composer/eloquent-model-encrypt/commit/509accf5ebd80586c9b8cc98d72391b0038e7e56))

## [3.1.1](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.1.0...v3.1.1) (2022-11-09)


### Bug Fixes

* fixes type for getRole when not string ([854655c](https://git.customd.com/composer/eloquent-model-encrypt/commit/854655c69b3de23eac67959d702fa6b89ad74659))

# [3.1.0](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.0.5...v3.1.0) (2022-11-09)


### Features

* allows role key to be assigned to multiple roles ([e513ff8](https://git.customd.com/composer/eloquent-model-encrypt/commit/e513ff8bd95e9d4679d6a479be61d7564594229d))

## [3.0.5](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.0.4...v3.0.5) (2022-10-24)


### Bug Fixes

* **DB:** transaction across different connections ([b30d37c](https://git.customd.com/composer/eloquent-model-encrypt/commit/b30d37cc0acd278e11ff90dec5edb9772bf3cc13))

## [3.0.4](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.0.3...v3.0.4) (2022-10-18)


### Bug Fixes

* restore encryption on non-default connection ([1fdb3e3](https://git.customd.com/composer/eloquent-model-encrypt/commit/1fdb3e346685cfabf646bb88e09149cf1b7ddc65))

## [3.0.3](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.0.2...v3.0.3) (2022-08-15)


### Bug Fixes

* missing import DB ([5987b84](https://git.customd.com/composer/eloquent-model-encrypt/commit/5987b84c711228b5d61a71a8e470e8db7af2b0ca))

## [3.0.2](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.0.1...v3.0.2) (2022-08-14)


### Bug Fixes

* strpos null depraction message ([f30a61c](https://git.customd.com/composer/eloquent-model-encrypt/commit/f30a61c35af89d86b4cca1845520cc4c82a913ec))

## [3.0.1](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.0.0...v3.0.1) (2022-08-14)


### Bug Fixes

* fixed encryptable contract definistion ([eb90a17](https://git.customd.com/composer/eloquent-model-encrypt/commit/eb90a176293d90c9ebb87b189003536d831b298d))

# [3.0.0](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.7.3...v3.0.0) (2022-08-11)


### Features

* Release 3 ([3f80042](https://git.customd.com/composer/eloquent-model-encrypt/commit/3f80042fe13d952dd736696106e185f3ee7297c8))


### BREAKING CHANGES

* Dropped support for php8 and other code
improvements, see changelog and upgrade notice

# [3.0.0-beta.5](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.0.0-beta.4...v3.0.0-beta.5) (2022-05-20)


### Features

* added middleware to. init PEM store ([f09f660](https://git.customd.com/composer/eloquent-model-encrypt/commit/f09f66017d83dd8056e2e1d90287bd05033a48f7))

# [3.0.0-beta.4](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.0.0-beta.3...v3.0.0-beta.4) (2022-05-15)


### Bug Fixes

* minor updates for stability ([d158590](https://git.customd.com/composer/eloquent-model-encrypt/commit/d158590a82b522c78959ed296b0315c1d4fc30c2))

# [3.0.0-beta.3](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.0.0-beta.2...v3.0.0-beta.3) (2022-05-15)


### Bug Fixes

* correcting order of params ([cb040ef](https://git.customd.com/composer/eloquent-model-encrypt/commit/cb040efc1cf715830d7aeaeb5613747d47f57cca))
* for better backward compat - make new functionality opt-in ([01f3166](https://git.customd.com/composer/eloquent-model-encrypt/commit/01f3166c1a132a22e874cfdd6caaf359cb9b55d5))


### Features

* stubs for keyproviders ([eb8ac99](https://git.customd.com/composer/eloquent-model-encrypt/commit/eb8ac993e2b92450c8997cbb9dde52f80c64a731))
* user key provider - session / cache ([ed79f18](https://git.customd.com/composer/eloquent-model-encrypt/commit/ed79f18409e09f436f989ec87003759b558a48a7))
* user subscribers to set pem ([33917ed](https://git.customd.com/composer/eloquent-model-encrypt/commit/33917ed3d7c5a79cc1d9cfb9b86283f6e052b972))

# [3.0.0-beta.2](https://git.customd.com/composer/eloquent-model-encrypt/compare/v3.0.0-beta.1...v3.0.0-beta.2) (2022-05-14)


### Bug Fixes

* needs grammer for column type ([80ca723](https://git.customd.com/composer/eloquent-model-encrypt/commit/80ca7232e2aedd760b1012ae60a096bd724523a4))

# [3.0.0-beta.1](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.7.3...v3.0.0-beta.1) (2022-05-14)


### Features

* Lara 9 / php 8 ([a06a05a](https://git.customd.com/composer/eloquent-model-encrypt/commit/a06a05a62545e8caabc8a84a5d883aabae3eaaf1))
* remove migration class extenders - use macro / inbuilt instead ([93a7adc](https://git.customd.com/composer/eloquent-model-encrypt/commit/93a7adc6d17c4e8ace499298a4b250ed6ccd7302))


### BREAKING CHANGES

* no loner supporting older version

## [2.7.3](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.7.2...v2.7.3) (2021-12-07)


### Bug Fixes

* only map through fields that are to be encrypted ([f9ca288](https://git.customd.com/composer/eloquent-model-encrypt/commit/f9ca2880d260ada1e1d23fa671c9bbf438c6d97c))

## [2.7.2](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.7.1...v2.7.2) (2021-12-07)


### Bug Fixes

* encryption engine should expect an encryptable value ([f2c6ed8](https://git.customd.com/composer/eloquent-model-encrypt/commit/f2c6ed8596c2a53d1b1c0b43470e01f03d77d564))

## [2.7.1](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.7.0...v2.7.1) (2021-12-07)


### Bug Fixes

* issue trying to case already encrypted attribute ([55dfc55](https://git.customd.com/composer/eloquent-model-encrypt/commit/55dfc554112b0379ceedbaeda1064e9eb2a54227))

# [2.7.0](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.6.0...v2.7.0) (2021-12-06)


### Features

* configuration to allow encryption of empty / null values ([15230b3](https://git.customd.com/composer/eloquent-model-encrypt/commit/15230b3ed7dcea3d63e87fd0f73e9898412818ac))

# [2.6.0](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.5.0...v2.6.0) (2021-09-10)


### Features

* improve encryption of table to skip existing and prevent race condition([#6](https://git.customd.com/composer/eloquent-model-encrypt/issues/6)) ([e963f22](https://git.customd.com/composer/eloquent-model-encrypt/commit/e963f2298cdce89c1a282d99465d7b80aae8b4d9))

# [2.5.0](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.4.0...v2.5.0) (2021-09-01)


### Features

* fix versioning mismatch ([ef82dcc](https://git.customd.com/composer/eloquent-model-encrypt/commit/ef82dcc4c804fe7ce71121f7239e8e052b15b864))

# [2.4.0](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.3.1...v2.4.0) (2021-09-01)

2.4.0 and 2.5.0 are the same - 2.4.0 / 2.4.1 where untagged in the release schedule

### Features

* Upgrade syntax for migrations ([bc34d33](https://git.customd.com/composer/eloquent-model-encrypt/commit/bc34d3348e4f598cb4545c8b9efbb1c96570860c))

## [2.3.1](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.3.0...v2.3.1) (2021-08-23)


### Bug Fixes

* **php8:** dependancy update for php8 ([09e590c](https://git.customd.com/composer/eloquent-model-encrypt/commit/09e590c1f0a78266748975aa27b09b0cecbb1221))

# [2.3.0](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.2.2...v2.3.0) (2021-05-06)


### Features

* **decryption:** option to throw an exception on failure ([d360f0a](https://git.customd.com/composer/eloquent-model-encrypt/commit/d360f0a12359f25b3be6dc876b79d178b55a8cac))

## [2.2.2](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.2.1...v2.2.2) (2021-04-27)


### Bug Fixes

* fix keystore fetch method ([616537e](https://git.customd.com/composer/eloquent-model-encrypt/commit/616537e8152c02bde0e398785963e3d9e00f3708))

## [2.2.1](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.2.0...v2.2.1) (2021-04-19)


### Bug Fixes

* Register CI Command ([a28686a](https://git.customd.com/composer/eloquent-model-encrypt/commit/a28686a6e8d0e84200910747ef86746111f611f3))

# [2.2.0](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.1.0...v2.2.0) (2021-04-19)


### Features

* **command:** Console command to trigger encrypt ([f3981d9](https://git.customd.com/composer/eloquent-model-encrypt/commit/f3981d99d370b6cc214128d0f94f9e2e37b5d3b7))

# [2.1.0](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.0.2...v2.1.0) (2021-04-19)


### Features

* **Model:** method to force encrypt record ([9bbff9b](https://git.customd.com/composer/eloquent-model-encrypt/commit/9bbff9b3967030778a08b0a1afc0691af2cd820f))

## [2.0.2](https://git.customd.com/composer/eloquent-model-encrypt/compare/v2.0.1...v2.0.2) (2021-04-14)


### Bug Fixes

* Cannot use ::class with dynamic class name ([1062cc4](https://git.customd.com/composer/eloquent-model-encrypt/commit/1062cc438ad6560aee8de511832702eae55e4da2))
