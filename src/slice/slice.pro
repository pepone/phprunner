
## *****************************************************************************
##
## Copyright (c) 2010 José Manuel, Gutiérrez de la Concha. All rights reserved.
##
## This copy of PhpRunner is licensed to you under the terms described
## in the LICENSE file included in this distribution.
##
## email: pepone.onrez@gmail.com
##
## *****************************************************************************

TOPDIR = ../..
include($$TOPDIR/config/qmake.rules)
SLICES = $$TOPDIR/src/slice/PhpRunner.ice
PHP_OUTPUT_DIR = $$TOPDIR/src/slice
SLICE2PHP_FLAGS += --output-dir $$PHP_OUTPUT_DIR

include($$TOPDIR/config/slice2cpp.pri)
include($$TOPDIR/config/slice2php.pri)

TEMPLATE = lib
TARGET = oz-phprunner
DESTDIR = $$TOPDIR/lib

CONFIG += $$WARN $$MODE dll thread exceptions rtti stl console

INCLUDEPATH += $$ICE_HOME/include .
LIBS += -L$$ICE_HOME/lib -lIce -lIceUtil

target.path=/opt/phprunner/lib

php.path = /opt/phprunner/php

slice2cpp_generated_headers.path = /opt/phprunner/include

INSTALLS += php slice2cpp_generated_headers target
