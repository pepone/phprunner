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

slice2cpp.name = SLICE2CPP ${QMAKE_FILE_IN}
slice2cpp.commands = $${QMAKE_SLICE2CPP} $${SLICE2CPP_FLAGS} ${QMAKE_FILE_IN}
slice2cpp.variable_out = slice2cpp_generated_sources.files SOURCES
slice2cpp_generated_sources.CONFIG += no_check_exist
slice2cpp.output = ${QMAKE_FILE_IN_BASE}.cpp
slice2cpp.input = SLICES
slice2cpp.clean =  ${QMAKE_FILE_IN_BASE}.h  ${QMAKE_FILE_IN_BASE}.cpp
QMAKE_EXTRA_COMPILERS += slice2cpp

#dependency to generate *.h from *.cpp
slice2cpp_hpp.name = SLICE2CPP_HEADER ${QMAKE_FILE_IN}
slice2cpp_hpp.commands = touch .depends
slice2cpp_hpp.depends = ${QMAKE_FILE_BASE}.cpp
slice2cpp_hpp.CONFIG += no_link
slice2cpp_hpp.variable_out = slice2cpp_generated_headers.files HEADERS
slice2cpp_generated_headers.CONFIG += no_check_exist
slice2cpp_hpp.output = ${QMAKE_FILE_BASE}.h
slice2cpp_hpp.input = SLICES
slice2cpp_hpp.clean = ${QMAKE_FILE_BASE}.h
QMAKE_EXTRA_COMPILERS += slice2cpp_hpp
