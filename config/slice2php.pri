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

slice2php.name = SLICE2PHP ${QMAKE_FILE_IN}
slice2php.commands = $${QMAKE_SLICE2PHP} $${SLICE2PHP_FLAGS} ${QMAKE_FILE_IN}
slice2php.variable_out = php.files
php.CONFIG += no_check_exist
slice2php.output = $${PHP_OUTPUT_DIR}/${QMAKE_FILE_IN_BASE}.php
slice2php.input = SLICES
slice2php.clean =  $${PHP_OUTPUT_DIR}/${QMAKE_FILE_IN_BASE}.php
slice2php.CONFIG = target_predeps
QMAKE_EXTRA_COMPILERS += slice2php

