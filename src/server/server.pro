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

TEMPLATE = app
TARGET = oz-phprunner
DESTDIR = $$TOPDIR/bin
OBJECTS_DIR = .obj
MOC_DIR = .moc
UI_DIR = .ui
CONFIG += qt $$WARN $$MODE dll thread exceptions rtti stl console
QT = core
SOURCES += Server.cpp

INCLUDEPATH += $$ICE_HOME/include $$TOPDIR/src/slice .

LIBS += -L$$ICE_HOME/lib -L$$TOPDIR/lib -lIce -lIceUtil -loz-phprunner

target.path=/opt/phprunner/bin

config.path=/opt/phprunner/config
config.files=$$TOPDIR/config/config.phprunner

documentation.path=/opt/phprunner
documentation.files=$$TOPDIR/README

license.path=/opt/phprunner
license.files=$$TOPDIR/LICENSE

INSTALLS += target config documentation license
