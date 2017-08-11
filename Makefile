# ------------------------------------------------------------------------
#
# General stuff
#

# Detect OS
OS = $(shell uname -s)

# Defaults
ECHO = echo

# Make adjustments based on OS
# http://stackoverflow.com/questions/3466166/how-to-check-if-running-in-cygwin-mac-or-linux/27776822#27776822
ifneq (, $(findstring CYGWIN, $(OS)))
	ECHO = /bin/echo -e
endif

# Colors and helptext
NO_COLOR	= \033[0m
ACTION		= \033[32;01m
OK_COLOR	= \033[32;01m
ERROR_COLOR	= \033[31;01m
WARN_COLOR	= \033[33;01m

# Which makefile am I in?
WHERE-AM-I = $(CURDIR)/$(word $(words $(MAKEFILE_LIST)),$(MAKEFILE_LIST))
THIS_MAKEFILE := $(call WHERE-AM-I)

# Echo some nice helptext based on the target comment
HELPTEXT = $(ECHO) "$(ACTION)--->" `egrep "^\# target: $(1) " $(THIS_MAKEFILE) | sed "s/\# target: $(1)[ ]*-[ ]* / /g"` "$(NO_COLOR)"



# ------------------------------------------------------------------------
#
# Specifics
#
BIN     := .bin
PHPUNIT := $(BIN)/phpunit
PHPLOC 	:= $(BIN)/phploc
PHPCS   := $(BIN)/phpcs
PHPCBF  := $(BIN)/phpcbf
PHPMD   := $(BIN)/phpmd
PHPDOC  := $(BIN)/phpdoc
BEHAT   := $(BIN)/behat
SHELLCHECK := $(BIN)/shellcheck
BATS := $(BIN)/bats



# target: help               - Displays help.
.PHONY:  help
help:
	@$(call HELPTEXT,$@)
	@$(ECHO) "Usage:"
	@$(ECHO) " make [target] ..."
	@$(ECHO) "target:"
	@egrep "^# target:" $(THIS_MAKEFILE) | sed 's/# target: / /g'



# target: prepare            - Prepare for tests and build
.PHONY:  prepare
prepare:
	@$(call HELPTEXT,$@)
	[ -d .bin ] || mkdir .bin
	[ -d build ] || mkdir build
	rm -rf build/*



# target: clean              - Removes generated files and directories.
.PHONY: clean
clean:
	@$(call HELPTEXT,$@)
	rm -rf build



# target: clean-cache        - Clean the cache.
.PHONY:  clean-cache
clean-cache:
	@$(call HELPTEXT,$@)
	rm -rf cache/*/*



# target: clean-all          - Removes generated files and directories.
.PHONY:  clean-all
clean-all: clean clean-cache
	@$(call HELPTEXT,$@)
	rm -rf .bin vendor



# target: check              - Check version of installed tools.
.PHONY:  check
check: check-tools-bash check-tools-php
	@$(call HELPTEXT,$@)



# target: test               - Run all tests.
.PHONY:  test
test: phpunit phpcs phpmd phploc behat shellcheck bats
	@$(call HELPTEXT,$@)
	composer validate



# target: doc                - Generate documentation.
.PHONY:  doc
doc: phpdoc
	@$(call HELPTEXT,$@)



# target: build              - Do all build
.PHONY:  build
build: test doc #theme less-compile less-minify js-minify
	@$(call HELPTEXT,$@)



# target: install            - Install all tools
.PHONY:  install
install: prepare install-tools-php install-tools-bash
	@$(call HELPTEXT,$@)



# target: update             - Update the codebase and tools.
.PHONY:  update
update:
	@$(call HELPTEXT,$@)
	[ ! -d .git ] || git pull
	composer update



# target: tag-prepare        - Prepare to tag new version.
.PHONY: tag-prepare
tag-prepare:
	@$(call HELPTEXT,$@)



# ------------------------------------------------------------------------
#
# PHP
#

# target: install-tools-php  - Install PHP development tools.
.PHONY: install-tools-php
install-tools-php:
	@$(call HELPTEXT,$@)
	#curl -Lso $(PHPDOC) https://www.phpdoc.org/phpDocumentor.phar && chmod 755 $(PHPDOC)
	curl -Lso $(PHPDOC) https://github.com/phpDocumentor/phpDocumentor2/releases/download/v2.9.0/phpDocumentor.phar && chmod 755 $(PHPDOC)

	curl -Lso $(PHPCS) https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar && chmod 755 $(PHPCS)

	curl -Lso $(PHPCBF) https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar && chmod 755 $(PHPCBF)

	curl -Lso $(PHPMD) http://static.phpmd.org/php/latest/phpmd.phar && chmod 755 $(PHPMD)

	curl -Lso $(PHPLOC) https://phar.phpunit.de/phploc.phar && chmod 755 $(PHPLOC)

	curl -Lso $(BEHAT) https://github.com/Behat/Behat/releases/download/v3.3.0/behat.phar && chmod 755 $(BEHAT)

	curl -Lso $(PHPUNIT) https://phar.phpunit.de/phpunit-5.7.9.phar && chmod 755 $(PHPUNIT)

	[ ! -f composer.json ] || composer install



# target: check-tools-php    - Check versions of PHP tools.
.PHONY: check-tools-php
check-tools-php:
	@$(call HELPTEXT,$@)
	php --version && echo
	which $(PHPUNIT) && $(PHPUNIT) --version
	which $(PHPLOC) && $(PHPLOC) --version
	which $(PHPCS) && $(PHPCS) --version && echo
	which $(PHPMD) && $(PHPMD) --version && echo
	which $(PHPCBF) && $(PHPCBF) --version && echo
	which $(PHPDOC) && $(PHPDOC) --version && echo
	which $(BEHAT) && $(BEHAT) --version && echo



# target: phpunit            - Run unit tests for PHP.
.PHONY: phpunit
phpunit: prepare
	@$(call HELPTEXT,$@)
	[ ! -d "test" ] || $(PHPUNIT) --configuration .phpunit.xml



# target: phpcs              - Codestyle for PHP.
.PHONY: phpcs
phpcs: prepare
	@$(call HELPTEXT,$@)
	[ ! -f .phpcs.xml ] || $(PHPCS) --standard=.phpcs.xml | tee build/phpcs



# target: phpcbf             - Fix codestyle for PHP.
.PHONY: phpcbf
phpcbf:
	@$(call HELPTEXT,$@)
ifneq ($(wildcard test),)
	[ ! -f .phpcs.xml ] || $(PHPCBF) --standard=.phpcs.xml
else
	[ ! -f .phpcs.xml ] || $(PHPCBF) --standard=.phpcs.xml src
endif



# target: phpmd              - Mess detector for PHP.
.PHONY: phpmd
phpmd: prepare
	@$(call HELPTEXT,$@)
	- [ ! -f .phpmd.xml ] || $(PHPMD) . text .phpmd.xml | tee build/phpmd



# target: phploc             - Code statistics for PHP.
.PHONY: phploc
phploc: prepare
	@$(call HELPTEXT,$@)
	$(PHPLOC) src > build/phploc



# target: phpdoc             - Create documentation for PHP.
.PHONY: phpdoc
phpdoc:
	@$(call HELPTEXT,$@)
	[ ! -d doc ] || $(PHPDOC) --config=.phpdoc.xml



# target: behat              - Run behat for feature tests.
.PHONY: behat
behat:
	@$(call HELPTEXT,$@)
	[ ! -d features ] || $(BEHAT)


# ------------------------------------------------------------------------
#
# Bash
#

# target: install-tools-bash - Install Bash development tools.
.PHONY: install-tools-bash
install-tools-bash:
	@$(call HELPTEXT,$@)
	# Shellcheck
	curl -s https://storage.googleapis.com/shellcheck/shellcheck-latest.linux.x86_64.tar.xz | tar -xJ -C build/ && rm -f .bin/shellcheck && ln build/shellcheck-latest/shellcheck .bin/

	# Bats
	curl -Lso $(BIN)/bats-exec-suite https://raw.githubusercontent.com/sstephenson/bats/master/libexec/bats-exec-suite
	curl -Lso $(BIN)/bats-exec-test https://raw.githubusercontent.com/sstephenson/bats/master/libexec/bats-exec-test
	curl -Lso $(BIN)/bats-format-tap-stream https://raw.githubusercontent.com/sstephenson/bats/master/libexec/bats-format-tap-stream
	curl -Lso $(BIN)/bats-preprocess https://raw.githubusercontent.com/sstephenson/bats/master/libexec/bats-preprocess
	curl -Lso $(BATS) https://raw.githubusercontent.com/sstephenson/bats/master/libexec/bats
	chmod 755 $(BIN)/bats*



# target: check-tools-bash   - Check versions of Bash tools.
.PHONY: check-tools-bash
check-tools-bash:
	@$(call HELPTEXT,$@)
	which $(SHELLCHECK) && $(SHELLCHECK) --version
	which $(BATS) && $(BATS) --version



# target: shellcheck         - Run shellcheck for bash files.
.PHONY: shellcheck
shellcheck:
	@$(call HELPTEXT,$@)
	[ ! -f src/*.bash ] || $(SHELLCHECK) --shell=bash src/*.bash



# target: bats               - Run bats for unit testing bash files.
.PHONY: bats
bats:
	@$(call HELPTEXT,$@)
	[ ! -d bats ] || $(BATS) bats/



# ------------------------------------------------------------------------
#
# Theme
#
# target: theme              - Do make build install in theme/ if available.
.PHONY: theme
theme:
	@$(call HELPTEXT,$@)
	[ ! -d theme ] || $(MAKE) --directory=theme build install
	#[ ! -d theme ] || ( cd theme && make build install )



# ------------------------------------------------------------------------
#
# Cimage
#

define CIMAGE_CONFIG
<?php
return [
    "mode"         => "development",
    "image_path"   =>  __DIR__ . "/../img/",
    "cache_path"   =>  __DIR__ . "/../../cache/cimage/",
    "autoloader"   =>  __DIR__ . "/../../vendor/autoload.php",
];
endef
export CIMAGE_CONFIG

# target: cimage-update           - Install/update Cimage to latest version.
.PHONY: cimage-update
cimage-update:
	@$(call HELPTEXT,$@)
	composer require mos/cimage
	install -d htdocs/cimage cache/cimage
	chmod 777 cache/cimage
	cp vendor/mos/cimage/webroot/img.php htdocs/cimage
	cp vendor/mos/cimage/webroot/img/car.png htdocs/img/
	touch htdocs/cimage/img_config.php

# target: cimage-config-create    - Create configfile for Cimage.
.PHONY: cimage-config-create
cimage-config-create:
	@$(call HELPTEXT,$@)
	$(ECHO) "$$CIMAGE_CONFIG" | bash -c 'cat > htdocs/cimage/img_config.php'
	cat htdocs/cimage/img_config.php
